<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Laravel 13 ya crea y arranca la aplicacion de pruebas. Mantener el
    // trait heredado de versiones anteriores impide que el framework prepare
    // correctamente su estado interno (incluidas las rutas web).
}
