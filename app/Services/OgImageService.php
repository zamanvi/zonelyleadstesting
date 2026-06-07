<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class OgImageService
{
    /**
     * Generate and stream (or serve from cache) an OG image for the given seller.
     * Uses raw header()/exit to stream binary PNG — intentional for this use case.
     */
    public function render(User $user): void
    {
        $cacheDir  = storage_path('app/public/og-cache');
        $cacheFile = $cacheDir . '/' . $user->slug . '-' . $user->updated_at->timestamp . '.png';

        // Serve from cache instantly (important for social crawler timeouts)
        if (file_exists($cacheFile)) {
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=31536000, immutable');
            readfile($cacheFile);
            exit;
        }

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $W = 1080; $H = 1080;
        $img = imagecreatetruecolor($W, $H);
        imagealphablending($img, true);

        // Palette
        $cBg        = imagecolorallocate($img,  8,  42,  40);
        $cPanel     = imagecolorallocate($img, 13,  55,  52);
        $cGold      = imagecolorallocate($img, 212, 175,  55);
        $cGoldLight = imagecolorallocate($img, 245, 215, 140);
        $cGoldMid   = imagecolorallocate($img, 230, 195, 100);
        $cWhite     = imagecolorallocate($img, 255, 255, 255);
        $cWhiteDim  = imagecolorallocate($img, 200, 222, 220);

        // Fonts
        $fontPaths = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        ];
        $fontRegPaths = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
        ];
        $fontB = collect($fontPaths)->first(fn($p) => file_exists($p));
        $fontR = collect($fontRegPaths)->first(fn($p) => file_exists($p)) ?? $fontB;
        $ttf   = (bool)$fontB;

        // Two-panel background
        $photoW = 540;
        imagefilledrectangle($img, 0,       0, $photoW, $H, $cBg);
        imagefilledrectangle($img, $photoW, 0, $W,      $H, $cPanel);

        // Outer gold border
        $borderC = imagecolorallocatealpha($img, 212, 175, 55, 70);
        imagesetthickness($img, 3);
        imagerectangle($img, 2, 2, $W - 2, $H - 2, $borderC);
        imagesetthickness($img, 1);

        // Corner accent marks
        $ac = 28; $at = 3;
        imagefilledrectangle($img, 14, 14, 14 + $ac, 14 + $at, $cGold);
        imagefilledrectangle($img, 14, 14, 14 + $at, 14 + $ac, $cGold);
        imagefilledrectangle($img, $W - 14 - $ac, $H - 14 - $at, $W - 14, $H - 14, $cGold);
        imagefilledrectangle($img, $W - 14 - $at, $H - 14 - $ac, $W - 14, $H - 14, $cGold);

        // Photo loading
        $photoLoaded = false;
        $src = false;
        if ($user->profile_photo) {
            if (str_starts_with($user->profile_photo, 'http')) {
                $imgData = @file_get_contents($user->profile_photo);
                if ($imgData) $src = @imagecreatefromstring($imgData);
            } else {
                $photo = ltrim($user->profile_photo, '/');
                $fsPaths = [
                    storage_path('app/public/' . preg_replace('#^storage/#', '', $photo)),
                    public_path($photo),
                    base_path('public/' . $photo),
                ];
                foreach ($fsPaths as $tryPath) {
                    if ($src) break;
                    if (!file_exists($tryPath)) continue;
                    $ext = strtolower(pathinfo($tryPath, PATHINFO_EXTENSION));
                    $src = match($ext) {
                        'jpg','jpeg' => @imagecreatefromjpeg($tryPath),
                        'png'        => @imagecreatefrompng($tryPath),
                        'webp'       => @imagecreatefromwebp($tryPath),
                        default      => false,
                    };
                }
            }
            if ($src) {
                $sw = imagesx($src); $sh = imagesy($src);
                $ratio = $photoW / $H;
                if ($sw / $sh > $ratio) {
                    $cropH = $sh; $cropW = (int)($sh * $ratio);
                    $srcX  = (int)(($sw - $cropW) / 2); $srcY = 0;
                } else {
                    $cropW = $sw; $cropH = (int)($sw / $ratio);
                    $srcX  = 0;   $srcY  = 0;
                }
                imagecopyresampled($img, $src, 0, 0, $srcX, $srcY, $photoW, $H, $cropW, $cropH);
                imagedestroy($src);
                $photoLoaded = true;
            }
        }

        if (!$photoLoaded) {
            $cPanelBg = imagecolorallocate($img, 14, 68, 64);
            imagefilledrectangle($img, 0, 0, $photoW - 1, $H, $cPanelBg);
            $logoPath = public_path('frontend/img/zonely_logo.png');
            $logo     = file_exists($logoPath) ? @imagecreatefrompng($logoPath) : false;
            if ($logo) {
                $lw = imagesx($logo); $lh = imagesy($logo);
                $scale = min(280 / $lw, 170 / $lh, 1);
                $dw = (int)($lw * $scale); $dh = (int)($lh * $scale);
                imagealphablending($img, true);
                imagesavealpha($logo, true);
                imagecopyresampled($img, $logo, (int)(($photoW - $dw) / 2), (int)(($H - $dh) / 2), 0, 0, $dw, $dh, $lw, $lh);
                imagedestroy($logo);
            }
        }

        if ($photoLoaded) {
            $tintC = imagecolorallocatealpha($img, 8, 42, 40, 100);
            imagefilledrectangle($img, 0, 0, $photoW - 1, $H, $tintC);
        }

        // Right-edge scrim
        $scrimW = 180;
        for ($sx = 0; $sx < $scrimW; $sx++) {
            $alpha = (int)(127 * (1 - $sx / $scrimW));
            $c = imagecolorallocatealpha($img, 8, 42, 40, $alpha);
            imageline($img, $photoW - $scrimW + $sx, 0, $photoW - $scrimW + $sx, $H, $c);
        }

        // Gold separator
        $sepC = imagecolorallocatealpha($img, 212, 175, 55, 85);
        imagesetthickness($img, 2);
        imageline($img, $photoW, 6, $photoW, $H - 6, $sepC);
        imagesetthickness($img, 1);

        // Verified badge
        if ($user->status) {
            $bw = 120; $bh = 30; $bx = 18; $by = 18; $rb = 15;
            imagefilledrectangle($img, $bx + $rb, $by, $bx + $bw - $rb, $by + $bh, $cGold);
            imagefilledrectangle($img, $bx, $by + $rb, $bx + $bw, $by + $bh - $rb, $cGold);
            imagefilledellipse($img, $bx + $rb,       $by + $rb, $rb * 2, $rb * 2, $cGold);
            imagefilledellipse($img, $bx + $bw - $rb, $by + $rb, $rb * 2, $rb * 2, $cGold);
            if ($ttf) imagettftext($img, 12, 0, $bx + 16, $by + 21, $cBg, $fontB, 'VERIFIED');
        }

        // Content panel
        $rx = $photoW + 58;
        $rw = $W - $rx - 20;

        $name      = $user->name ?? 'Professional';
        $desig     = Str::limit($user->designation ?? $user->category?->title ?? '', 44);
        $specialty = Str::limit(trim(Str::before($user->title ?? '', '|')), 46);
        $loc       = $user->city ? ($user->city . ($user->state ? ', ' . $user->state : '')) : '';
        $exp       = (int)($user->experience ?? 0);
        $svcs      = $user->services->take(6)->filter(function($s) {
            $t = preg_replace('/[^\x20-\x7E]/u', '', $s->title ?? '');
            return trim($t) !== '';
        })->take(4)->values();
        $rating    = $user->reviews->whereNotNull('rating')->avg('rating');
        $revCount  = $user->reviews->whereNotNull('rating')->count();

        $fs = 60;
        if ($ttf) {
            foreach ([60, 50, 40, 30] as $try) {
                $b = @imagettfbbox($try, 0, $fontB, $name);
                if (!$b || abs($b[4] - $b[0]) <= $rw) { $fs = $try; break; }
            }
        }

        $btnY1 = $H - 72;
        $cH    = $fs + 18;
        if ($desig)              $cH += 42;
        $cH += 14;
        if ($specialty)          $cH += 36;
        if ($loc)                $cH += 36;
        if ($exp)                $cH += 34;
        $cH += 18;
        $cH += $svcs->count() * 38;
        if ($rating && $revCount > 0) $cH += 36;
        $cy = max(36, (int)(($btnY1 - $cH) / 2));

        imagefilledrectangle($img, $rx, $cy - 18, $rx + 60, $cy - 13, $cGold);

        if ($ttf) imagettftext($img, $fs, 0, $rx, $cy, $cGoldLight, $fontB, $name);
        $cy += $fs + 18;

        if ($ttf && $desig) {
            imagettftext($img, 25, 0, $rx, $cy, $cGoldMid, $fontB, $desig);
            $cy += 42;
        }
        $cy += 14;
        if ($ttf && $specialty) {
            imagettftext($img, 23, 0, $rx, $cy, $cWhite, $fontB, $specialty);
            $cy += 36;
        }
        if ($ttf && $loc) {
            imagettftext($img, 22, 0, $rx, $cy, $cGoldMid, $fontR ?? $fontB, $loc);
            $cy += 36;
        }
        if ($ttf && $exp) {
            imagettftext($img, 20, 0, $rx, $cy, $cGoldLight, $fontB, $exp . '+ Yrs Experience');
            $cy += 34;
        }

        $cy += 10;
        $divC = imagecolorallocatealpha($img, 212, 175, 55, 100);
        imagesetthickness($img, 2);
        imageline($img, $rx, $cy, $rx + $rw, $cy, $divC);
        imagesetthickness($img, 1);
        $cy += 18;

        foreach ($svcs as $svc) {
            $t  = Str::limit(preg_replace('/[^\x20-\x7E]/u', '', $svc->title ?? ''), 40);
            if (!trim($t)) continue;
            $bx = $rx + 14; $by = $cy - 9;
            imageellipse($img, $bx, $by, 22, 22, $cGoldLight);
            imagefilledellipse($img, $bx, $by, 10, 10, $cGoldLight);
            if ($ttf) imagettftext($img, 20, 0, $rx + 38, $cy, $cWhiteDim, $fontR ?? $fontB, $t);
            $cy += 38;
        }

        if ($rating && $revCount > 0 && $ttf) {
            $ratingTx = number_format($rating, 1) . '/5  (' . $revCount . ' reviews)';
            imagettftext($img, 20, 0, $rx, $cy + 8, $cGold, $fontB, $ratingTx);
            $cy += 36;
        }

        // CTA Button
        $btnY2 = $H - 4;
        $btnX1 = $rx;
        $btnX2 = $W - 12;
        $btnH  = $btnY2 - $btnY1;
        $br    = (int)($btnH / 2);

        $glowC = imagecolorallocatealpha($img, 212, 175, 55, 80);
        imagefilledrectangle($img, $btnX1 + $br, $btnY1 + 5, $btnX2 - $br, $btnY2 + 5, $glowC);
        imagefilledellipse($img, $btnX1 + $br, $btnY1 + $br + 5, $br * 2, $br * 2, $glowC);
        imagefilledellipse($img, $btnX2 - $br, $btnY1 + $br + 5, $br * 2, $br * 2, $glowC);

        $cBtnGold = imagecolorallocate($img, 230, 195, 60);
        imagefilledrectangle($img, $btnX1 + $br, $btnY1, $btnX2 - $br, $btnY2, $cBtnGold);
        imagefilledrectangle($img, $btnX1, $btnY1 + $br, $btnX2, $btnY2 - $br, $cBtnGold);
        imagefilledellipse($img, $btnX1 + $br, $btnY1 + $br, $br * 2, $br * 2, $cBtnGold);
        imagefilledellipse($img, $btnX2 - $br, $btnY1 + $br, $br * 2, $br * 2, $cBtnGold);

        $hiC = imagecolorallocatealpha($img, 255, 255, 255, 90);
        imagefilledrectangle($img, $btnX1 + $br, $btnY1, $btnX2 - $br, $btnY1 + 6, $hiC);

        if ($ttf) {
            $btnTx = 'VIEW PROFILE  >>';
            $bbox  = @imagettfbbox(23, 0, $fontB, $btnTx);
            $txW   = $bbox ? abs($bbox[4] - $bbox[0]) : 180;
            $txX   = $btnX1 + (int)(($btnX2 - $btnX1 - $txW) / 2);
            imagettftext($img, 23, 0, $txX, $btnY1 + (int)($btnH / 2) + 9, $cBg, $fontB, $btnTx);
        }

        // Save to cache then serve
        if (is_dir($cacheDir) && is_writable($cacheDir)) {
            @imagepng($img, $cacheFile, 6);
            imagedestroy($img);
            if (file_exists($cacheFile)) {
                header('Content-Type: image/png');
                header('Cache-Control: public, max-age=31536000, immutable');
                readfile($cacheFile);
                exit;
            }
        }

        // Fallback: stream directly
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=3600');
        imagepng($img, null, 6);
        imagedestroy($img);
        exit;
    }
}
