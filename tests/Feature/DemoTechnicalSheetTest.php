<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DemoTechnicalSheetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['demo.enabled' => true, 'demo.technical_sheet_path' => base_path('tests/Fixtures/technical-sheet.html')]);
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1']);
    }

    private function signIn(): void
    {
        $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Prueba', 'rol' => 'cliente']));
    }

    public function test_guests_cannot_read_the_document(): void
    {
        $this->get('/demo/ficha-tecnica')->assertRedirect('/login');
    }

    public function test_local_authenticated_access_is_not_cached(): void
    {
        $this->signIn();
        $response = $this->get('/demo/ficha-tecnica');
        $response->assertOk()->assertSee('Ficha técnica de prueba')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_remote_access_is_denied_even_when_authenticated(): void
    {
        $this->signIn();
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])->get('/demo/ficha-tecnica')->assertForbidden();
    }

    public function test_document_is_unavailable_outside_demo(): void
    {
        $this->signIn();
        config(['demo.enabled' => false]);
        $this->get('/demo/ficha-tecnica')->assertForbidden();
    }

    public function test_missing_file_returns_not_found(): void
    {
        $this->signIn();
        config(['demo.technical_sheet_path' => base_path('tests/Fixtures/nonexistent.html')]);
        $this->get('/demo/ficha-tecnica')->assertNotFound();
    }
}
