<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

header("Content-Type: application/json");

// ================= INPUT =================
$images = $_POST['images'] ?? [];
$frame  = $_POST['frame'] ?? '';
$title  = $_POST['title_text'] ?? 'PHOTO BOOTH';
$footer = $_POST['footer_text'] ?? '';

if (count($images) < 1) {
    echo json_encode(["status"=>"error","msg"=>"No images"]);
    exit;
}

// ================= STRIP SIZE =================
$width  = 900;
$height = 1800;
$strip  = imagecreatetruecolor($width, $height);

imagesavealpha($strip, true);
$transparent = imagecolorallocatealpha($strip, 0, 0, 0, 127);
imagefill($strip, 0, 0, $transparent);

// ================= FOTO =================
$y = 200; // ruang title
foreach ($images as $img64) {
    $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#', '', $img64));
    $img = imagecreatefromstring($imgData);

    imagecopyresampled(
        $strip, $img,
        50, $y,
        0, 0,
        $width - 100, 450,
        imagesx($img), imagesy($img)
    );

    imagedestroy($img);
    $y += 480;
}

// ================= FRAME PNG =================
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


// ================= TEXT =================
$textColor = imagecolorallocate($strip, 255,255,255);
$font = __DIR__ . "/assets/arial.ttf"; // WAJIB ADA

imagettftext($strip, 36, 0, 80, 80, $textColor, $font, $title);
imagettftext($strip, 24, 0, 80, $height - 40, $textColor, $font, $footer);

// ================= SAVE TEMP =================
$tmpFile = sys_get_temp_dir() . "/photobooth_" . time() . ".png";
imagepng($strip, $tmpFile);
imagedestroy($strip);

// ================= GOOGLE DRIVE =================
$client = new Client();
$client->setAuthConfig(__DIR__ . "/credentials.json");
$client->addScope(Drive::DRIVE);

$service = new Drive($client);

$fileMetadata = new DriveFile([
    'name' => basename($tmpFile),
    'parents' => ['1_TCvYGVCsLwroi5JrPv2noej4FOG8RYu']
]);

$file = $service->files->create($fileMetadata, [
    'data' => file_get_contents($tmpFile),
    'mimeType' => 'image/png',
    'uploadType' => 'multipart'
]);

// public
$service->permissions->create($file->id, [
    'type' => 'anyone',
    'role' => 'reader'
]);

unlink($tmpFile);

// ================= RESPONSE =================
echo json_encode([
    "status" => "success",
    "file_id" => $file->id,
    "drive_link" => "https://drive.google.com/file/d/{$file->id}/view"
]);
