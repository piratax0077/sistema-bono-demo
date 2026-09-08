<?php

namespace App\Services;

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret($length = 32)
    {
        $alphabet = self::BASE32_ALPHABET;
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $secret;
    }

    public function provisioningUri($issuer, $account, $secret)
    {
        $label = rawurlencode($issuer.':'.$account);
        $issuer = rawurlencode($issuer);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    public function verify($secret, $code, $window = 1)
    {
        $code = preg_replace('/\D+/', '', (string) $code);

        if (strlen($code) !== 6) {
            return false;
        }

        $timeSlice = (int) floor(time() / 30);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->totp($secret, $timeSlice + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    private function totp($secret, $timeSlice)
    {
        $key = $this->base32Decode($secret);
        $counter = pack('N*', 0).pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $counter, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode($secret)
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', (string) $secret));
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        foreach (str_split($secret) as $char) {
            $value = strpos(self::BASE32_ALPHABET, $char);

            if ($value === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $value;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
