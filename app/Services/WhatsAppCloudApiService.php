<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WhatsAppCloudApiService
{
    public function enabled(): bool
    {
        return (bool) config('services.whatsapp.enabled')
            && filled(config('services.whatsapp.token'))
            && filled(config('services.whatsapp.phone_number_id'));
    }

    public function sendImage(string $phone, string $imagePath, string $caption): array
    {
        $phone = preg_replace('/\D+/', '', $phone);
        $allowed = preg_replace('/\D+/', '', (string) config('services.whatsapp.test_recipient'));
        if (!$this->enabled()) return ['ok'=>false, 'mensaje'=>'WhatsApp Cloud API no está configurada.'];
        if ($allowed === '' || !hash_equals($allowed, $phone)) return ['ok'=>false, 'mensaje'=>'El teléfono no coincide con WHATSAPP_TEST_RECIPIENT.'];
        if (!is_file($imagePath)) return ['ok'=>false, 'mensaje'=>'No se encontró la imagen QR que se debe enviar.'];

        $base = 'https://graph.facebook.com/'.config('services.whatsapp.graph_version').'/'.config('services.whatsapp.phone_number_id');
        try {
            $upload = Http::withToken(config('services.whatsapp.token'))->timeout(20)
                ->attach('file', fopen($imagePath, 'r'), basename($imagePath))
                ->post($base.'/media', ['messaging_product'=>'whatsapp','type'=>'image/png']);
            if (!$upload->successful() || !$upload->json('id')) return ['ok'=>false,'mensaje'=>$upload->json('error.message') ?: 'Meta no pudo cargar la imagen QR.'];

            $send = Http::withToken(config('services.whatsapp.token'))->timeout(20)->post($base.'/messages', [
                'messaging_product'=>'whatsapp', 'recipient_type'=>'individual', 'to'=>$phone,
                'type'=>'image', 'image'=>['id'=>$upload->json('id'), 'caption'=>mb_substr($caption, 0, 1024)],
            ]);
            if (!$send->successful() || !$send->json('messages.0.id')) return ['ok'=>false,'mensaje'=>$send->json('error.message') ?: 'Meta no pudo enviar el mensaje.'];

            return ['ok'=>true,'mensaje'=>'Mensaje enviado mediante WhatsApp Cloud API.','message_id'=>$send->json('messages.0.id'),'media_id'=>$upload->json('id')];
        } catch (ConnectionException $exception) {
            return ['ok'=>false,'mensaje'=>'No fue posible conectar con WhatsApp Cloud API: '.$exception->getMessage()];
        }
    }
}
