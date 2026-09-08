<?php

namespace App\Services;

use App\Models\Voucher;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

class MedichileQrImageService
{
    public function ensureForVoucher(Voucher $voucher, string $qrUrl): array
    {
        $directory = public_path('qr-vouchers');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'medichile-'.$this->safeCode($voucher->codigo).'.png';
        $path = $directory.DIRECTORY_SEPARATOR.$filename;

        $shouldRegenerate = ! file_exists($path)
            || filemtime($path) < optional($voucher->updated_at)->timestamp
            || filesize($path) < 1024;

        if ($shouldRegenerate) {
            $this->render($qrUrl, $voucher->codigo, $path);
        }

        return [
            'path' => $path,
            'url' => url('qr-vouchers/'.$filename),
            'filename' => $filename,
        ];
    }

    private function render(string $qrUrl, string $code, string $path): void
    {
        $width = 720;
        $height = 860;

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 8, 24, 36);
        $blue = imagecolorallocate($image, 0, 99, 150);
        $muted = imagecolorallocate($image, 95, 116, 112);
        $soft = imagecolorallocate($image, 244, 250, 249);

        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        $this->centerText($image, 'MEDICHILE · SDI SALUD DIGITAL INTEGRADA', 32, 30, $blue, true);
        $this->centerText($image, 'Bono digital seguro', 32, 72, $muted, false);

        $cardX = 90;
        $cardY = 122;
        $cardW = 540;
        $cardH = 660;
        $radius = 42;

        $this->roundedGradientRectangle($image, $cardX, $cardY, $cardW, $cardH, $radius, [0, 118, 168], [15, 188, 135]);

        $this->roundedRectangle($image, $cardX + 142, $cardY + 28, 58, 58, 17, imagecolorallocatealpha($image, 255, 255, 255, 35));
        $this->outlineRoundedRectangle($image, $cardX + 142, $cardY + 28, 58, 58, 17, imagecolorallocatealpha($image, 255, 255, 255, 105));
        $this->text($image, 'MC', $cardX + 157, $cardY + 46, 19, $white, true);
        $this->text($image, 'MEDICHILE', $cardX + 210, $cardY + 30, 24, $white, true);
        $this->text($image, 'Bono digital seguro', $cardX + 210, $cardY + 62, 14, $white, true);

        $qrBoxX = $cardX + 44;
        $qrBoxY = $cardY + 132;
        $qrBoxW = $cardW - 88;
        $qrBoxH = $cardW - 88;

        $this->roundedRectangle($image, $qrBoxX, $qrBoxY, $qrBoxW, $qrBoxH, 28, $white);
        $this->drawQrMatrix($image, $qrUrl, $qrBoxX + 36, $qrBoxY + 36, $qrBoxW - 72, $black, $white);

        $this->centerText($image, strtoupper($code), $cardX, $cardY + $cardH - 54, $white, true, $cardW, 19);
        $this->centerText($image, 'Presente esta imagen QR para validar el bono.', 0, 814, $muted, false, $width, 16);

        imagepng($image, $path, 9);
        imagedestroy($image);
    }

    private function drawQrMatrix(\GdImage $image, string $qrUrl, int $x, int $y, int $size, int $dark, int $light): void
    {
        $matrix = Encoder::encode($qrUrl, ErrorCorrectionLevel::H())->getMatrix();
        $moduleCount = $matrix->getWidth();
        $quietZone = 4;
        $totalModules = $moduleCount + ($quietZone * 2);
        $moduleSize = max(1, (int) floor($size / $totalModules));
        $qrSize = $moduleSize * $totalModules;
        $offsetX = $x + (int) floor(($size - $qrSize) / 2);
        $offsetY = $y + (int) floor(($size - $qrSize) / 2);

        imagefilledrectangle($image, $offsetX, $offsetY, $offsetX + $qrSize, $offsetY + $qrSize, $light);

        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount; $col++) {
                if ($matrix->get($col, $row) !== 1) {
                    continue;
                }

                $left = $offsetX + (($col + $quietZone) * $moduleSize);
                $top = $offsetY + (($row + $quietZone) * $moduleSize);

                imagefilledrectangle(
                    $image,
                    $left,
                    $top,
                    $left + $moduleSize - 1,
                    $top + $moduleSize - 1,
                    $dark
                );
            }
        }
    }

    private function roundedGradientRectangle(\GdImage $image, int $x, int $y, int $w, int $h, int $r, array $from, array $to): void
    {
        for ($line = 0; $line < $h; $line++) {
            $ratio = $h <= 1 ? 0 : $line / ($h - 1);
            $red = (int) round($from[0] + (($to[0] - $from[0]) * $ratio));
            $green = (int) round($from[1] + (($to[1] - $from[1]) * $ratio));
            $blue = (int) round($from[2] + (($to[2] - $from[2]) * $ratio));
            $color = imagecolorallocate($image, $red, $green, $blue);
            $currentY = $y + $line;
            $startX = $x;
            $endX = $x + $w;

            if ($line < $r) {
                $dy = $r - $line;
                $dx = (int) ceil($r - sqrt(max(0, ($r * $r) - ($dy * $dy))));
                $startX += $dx;
                $endX -= $dx;
            } elseif ($line > $h - $r) {
                $dy = $line - ($h - $r);
                $dx = (int) ceil($r - sqrt(max(0, ($r * $r) - ($dy * $dy))));
                $startX += $dx;
                $endX -= $dx;
            }

            imageline($image, $startX, $currentY, $endX, $currentY, $color);
        }
    }

    private function roundedRectangle(\GdImage $image, int $x, int $y, int $w, int $h, int $r, int $color): void
    {
        imagefilledrectangle($image, $x + $r, $y, $x + $w - $r, $y + $h, $color);
        imagefilledrectangle($image, $x, $y + $r, $x + $w, $y + $h - $r, $color);
        imagefilledellipse($image, $x + $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($image, $x + $w - $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($image, $x + $r, $y + $h - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($image, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
    }

    private function outlineRoundedRectangle(\GdImage $image, int $x, int $y, int $w, int $h, int $r, int $color): void
    {
        imagearc($image, $x + $r, $y + $r, $r * 2, $r * 2, 180, 270, $color);
        imagearc($image, $x + $w - $r, $y + $r, $r * 2, $r * 2, 270, 360, $color);
        imagearc($image, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, 0, 90, $color);
        imagearc($image, $x + $r, $y + $h - $r, $r * 2, $r * 2, 90, 180, $color);
        imageline($image, $x + $r, $y, $x + $w - $r, $y, $color);
        imageline($image, $x + $r, $y + $h, $x + $w - $r, $y + $h, $color);
        imageline($image, $x, $y + $r, $x, $y + $h - $r, $color);
        imageline($image, $x + $w, $y + $r, $x + $w, $y + $h - $r, $color);
    }

    private function centerText(\GdImage $image, string $text, int $x, int $y, int $color, bool $bold = false, ?int $width = null, int $size = 18): void
    {
        $width ??= imagesx($image);
        $font = $this->fontPath($bold);

        if ($font) {
            $box = imagettfbbox($size, 0, $font, $text);
            $textWidth = abs($box[2] - $box[0]);
            imagettftext($image, $size, 0, $x + (int) (($width - $textWidth) / 2), $y + $size, $color, $font, $text);
            return;
        }

        $fontId = $bold ? 5 : 4;
        $textWidth = imagefontwidth($fontId) * strlen($text);
        imagestring($image, $fontId, $x + (int) (($width - $textWidth) / 2), $y, $text, $color);
    }

    private function text(\GdImage $image, string $text, int $x, int $y, int $size, int $color, bool $bold = false): void
    {
        $font = $this->fontPath($bold);

        if ($font) {
            imagettftext($image, $size, 0, $x, $y + $size, $color, $font, $text);
            return;
        }

        imagestring($image, $bold ? 5 : 4, $x, $y, $text, $color);
    }

    private function fontPath(bool $bold = false): ?string
    {
        $candidates = $bold
            ? [
                'C:\Windows\Fonts\arialbd.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            ]
            : [
                'C:\Windows\Fonts\arial.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function safeCode(string $code): string
    {
        return preg_replace('/[^A-Z0-9_-]/i', '', $code) ?: 'voucher';
    }
}
