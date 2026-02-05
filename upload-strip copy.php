<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

header("Content-Type: application/json");

/* ================= INPUT ================= */
$images = $_POST['images'] ?? [];
$frame  = $_POST['frame'] ?? '';
$title  = $_POST['title_text'] ?? 'PHOTO BOOTH';
$footer = $_POST['footer_text'] ?? '';

if (!is_array($images)) {
    echo json_encode([
        "status" => "error",
        "msg" => "Invalid images format"
    ]);
    exit;
}

if (count($images) !== 3) {
    echo json_encode([
        "status" => "error",
        "msg" => "Foto harus 3"
    ]);
    exit;
}


/* ================= STRIP SIZE (DINAMIS) ================= */
$width        = 900;
$photoHeight = 450;
$gap          = 30;
$titleSpace   = 160;
$footerSpace  = 100;

$height = $titleSpace + (count($images) * ($photoHeight + $gap)) + $footerSpace;

$strip = imagecreatetruecolor($width, $height);
imagesavealpha($strip, true);
$transparent = imagecolorallocatealpha($strip, 0, 0, 0, 127);
imagefill($strip, 0, 0, $transparent);

/* ================= FOTO ================= */
$y = $titleSpace;

foreach ($images as $img64) {

    if (strlen($img64) > 6_000_000) continue; // limit size

    $imgData = base64_decode(
        preg_replace('#^data:image/\w+;base64,#', '', $img64)
    );

    if (!$imgData) continue;

    $img = imagecreatefromstring($imgData);
    if (!$img) continue;

    imagecopyresampled(
        $strip,
        $img,
        50, $y,
        0, 0,
        $width - 100, $photoHeight,
        imagesx($img),
        imagesy($img)
    );

    imagedestroy($img);
    $y += $photoHeight + $gap;
}

/* ================= FRAME PNG ================= */
if ($frame) {
    $framePath = __DIR__ . "/" . ltrim($frame, "/");
    if (file_exists($framePath)) {
        $frameImg = imagecreatefrompng($framePath);
        imagecopyresampled(
            $strip,
            $frameImg,
            0, 0, 0, 0,
            $width, $height,
            imagesx($frameImg),
            imagesy($frameImg)
        );
        imagedestroy($frameImg);
    }
}
/* ================= FONT ================= */
$font = __DIR__ . "/photos/assets/arial.ttf";

if (!file_exists($font)) {
    echo json_encode([
        "status" => "error",
        "msg" => "Font not found",
        "path" => $font
    ]);
    exit;
}

/* ================= TEXT ================= */
$textColor = imagecolorallocate($strip, 255, 255, 255);
imagettftext($strip, 36, 0, 60, 80, $textColor, $font, $title);
imagettftext($strip, 22, 0, 60, $height - 40, $textColor, $font, $footer);

/* ================= SAVE TEMP ================= */
$tmpFile = sys_get_temp_dir() . "/photobooth_" . uniqid() . ".png";
imagepng($strip, $tmpFile);
imagedestroy($strip);

/* ================= GOOGLE DRIVE ================= */
$client = new Client();
$client->setAuthConfig(__DIR__ . "/credentials.json");
$client->addScope(Drive::DRIVE);
$client->setAccessType('offline');

$service = new Drive($client);

$fileMetadata = new DriveFile([
    'name' => 'PhotoBooth_' . date('Ymd_His') . '.png',
    'parents' => ['1_TCvYGVCsLwroi5JrPv2noej4FOG8RYu']
]);


$file = $service->files->create(
    $fileMetadata,
    [
        'data' => file_get_contents($tmpFile),
        'mimeType' => 'image/png',
        'uploadType' => 'multipart',
        'fields' => 'id',
        'supportsAllDrives' => true
    ]
);

// public access
$service->permissions->create($file->id, [
    'type' => 'anyone',
    'role' => 'reader'
    ],
    ['supportsAllDrives' => true]
);

unlink($tmpFile);

/* ================= RESPONSE ================= */
echo json_encode([
    "status" => "success",
    "file_id" => $file->id,
    "drive_link" => "https://drive.google.com/file/d/{$file->id}/view"
]);
