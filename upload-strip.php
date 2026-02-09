<?php

ini_set('display_errors', 1);
error_reporting(0);
header('Content-Type: application/json');

/* =========================================================
   PHOTO BOOTH STRIP - SAVE TO LOCAL
   ========================================================= */

/* ================= INPUT ================= */
$images = $_POST['images'] ?? [];
$frame  = $_POST['frame'] ?? '';
$title  = $_POST['title_text'] ?? 'PHOTO BOOTH';
$footer = $_POST['footer_text'] ?? '';

if (!is_array($images)) {
    echo json_encode([
        'status' => 'error',
        'msg'    => 'Invalid images format'
    ]);
    exit;
}

if (count($images) !== 3) {
    echo json_encode([
        'status' => 'error',
        'msg'    => 'Foto harus 3'
    ]);
    exit;
}

/* ================= STRIP SIZE ================= */
$width        = 900;
$titleSpace   = 160;
$footerSpace  = 100;
$defaultPhotoHeight = 450;
$defaultGap   = 30;

$height = $titleSpace
        + (count($images) * ($defaultPhotoHeight + $defaultGap))
        + $footerSpace;

/* ================= CREATE CANVAS ================= */
$strip = imagecreatetruecolor($width, $height);
imagesavealpha($strip, true);
$transparent = imagecolorallocatealpha($strip, 0, 0, 0, 127);
imagefill($strip, 0, 0, $transparent);

/* ================= FRAME ================= */
if ($frame) {
    $framePath = __DIR__ . '/' . ltrim($frame, '/');
    if (file_exists($framePath)) {
        $frameImg = imagecreatefrompng($framePath);
        imagecopyresampled(
            $strip,
            $frameImg,
            0, 0, 0, 0,
            $width,
            $height,
            imagesx($frameImg),
            imagesy($frameImg)
        );
        imagedestroy($frameImg);
    }
}

/* ================= PHOTO AREA (CSS → PX) =================
   CSS:
   top: 4.5%;
   left: 10%;
   width: 80%;
   height: 59%;
   gap: 13px;
=========================================================== */

$areaX      = intval($width  * 0.10);
$areaY      = intval($height * 0.045);
$areaWidth  = intval($width  * 0.80);
$areaHeight = intval($height * 0.59);

$photoCount = count($images);
$gap        = 30;

$photoHeight = intval(
    ($areaHeight - ($gap * ($photoCount - 1))) / $photoCount
);
$photoWidth = $areaWidth;

/* ================= PLACE PHOTOS ================= */
$y = $areaY;

foreach ($images as $img64) {

    if (strlen($img64) > 6_000_000) continue;

    $imgData = base64_decode(
        preg_replace('#^data:image/\w+;base64,#', '', $img64)
    );
    if (!$imgData) continue;

    $img = imagecreatefromstring($imgData);
    if (!$img) continue;

    // Maintain aspect ratio (center crop)
    $srcW = imagesx($img);
    $srcH = imagesy($img);
    $srcRatio = $srcW / $srcH;
    $dstRatio = $photoWidth / $photoHeight;

    if ($srcRatio > $dstRatio) {
        $newH = $srcH;
        $newW = intval($srcH * $dstRatio);
        $srcX = intval(($srcW - $newW) / 2);
        $srcY = 0;
    } else {
        $newW = $srcW;
        $newH = intval($srcW / $dstRatio);
        $srcX = 0;
        $srcY = intval(($srcH - $newH) / 2);
    }

    imagecopyresampled(
        $strip,
        $img,
        $areaX, $y,
        $srcX, $srcY,
        $photoWidth, $photoHeight,
        $newW, $newH
    );

    imagedestroy($img);
    $y += $photoHeight + $gap;
}

/* ================= FONT ================= */
$font = __DIR__ . '/photos/assets/arial.ttf';

if (!file_exists($font)) {
    echo json_encode([
        'status' => 'error',
        'msg'    => 'Font not found',
        'path'   => $font
    ]);
    imagedestroy($strip);
    exit;
}

/* ================= TEXT ================= */
$textColor = imagecolorallocate($strip, 255, 255, 255);
imagettftext($strip, 36, 0, 60, 90,               $textColor, $font, $title);
imagettftext($strip, 22, 0, 60, $height - 40,     $textColor, $font, $footer);

/* ================= SAVE TO LOCAL ================= */
$saveDir = __DIR__ . '/output';
if (!is_dir($saveDir)) {
    mkdir($saveDir, 0777, true);
}

$fileName = 'PhotoBooth_' . date('Ymd_His') . '.png';
$filePath = $saveDir . '/' . $fileName;

imagepng($strip, $filePath);
imagedestroy($strip);

/* ================= RESPONSE ================= */
echo json_encode([
    'status'    => 'success',
    'file_name'=> $fileName,
    'path'     => $filePath
]);
exit;
