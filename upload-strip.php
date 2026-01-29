<?php
header("Content-Type: application/json");

// ================== INPUT ==================
$images = $_POST['images'] ?? [];
$title  = $_POST['title_text'] ?? 'PHOTO BOOTH';
$footer = $_POST['footer_text'] ?? '';
$date   = date("d M Y");
$frame  = $_POST['frame'] ?? 'photos/assets/frame1.png';

// ================== VALIDASI ==================
$allowedFrames = [
    "photos/assets/frame1.png",
    "photos/assets/frame2.png",
    "photos/assets/frame3.png"
];

if (!in_array($frame, $allowedFrames)) {
    echo json_encode(["status"=>"error","message"=>"Invalid frame"]);
    exit;
}

if (count($images) !== 3) {
    echo json_encode(["status"=>"error","message"=>"Images not complete"]);
    exit;
}

if (!file_exists($frame)) {
    echo json_encode(["status"=>"error","message"=>"Frame not found"]);
    exit;
}

// ================== FOLDER ==================
$tempDir   = "photos/temp/";
$resultDir = "photos/result/";

if (!is_dir($tempDir)) mkdir($tempDir,0777,true);
if (!is_dir($resultDir)) mkdir($resultDir,0777,true);

// ================== SAVE TEMP IMAGES ==================
$paths = [];
foreach ($images as $i => $img) {
    $img = preg_replace('#^data:image/\w+;base64,#','',$img);
    $img = str_replace(' ', '+', $img);

    $path = $tempDir."img_$i.png";
    file_put_contents($path, base64_decode($img));
    $paths[] = $path;
}

// ================== LOAD FOTO ==================
$img1 = imagecreatefrompng($paths[0]);
$img2 = imagecreatefrompng($paths[1]);
$img3 = imagecreatefrompng($paths[2]);

// ================== LOAD FRAME ==================
$frameImg = imagecreatefrompng($frame);
imagesavealpha($frameImg, true);

$frameW = imagesx($frameImg);
$frameH = imagesy($frameImg);

// ================== AREA FOTO ==================
$photoX = 100;
$photoY = 200;
$photoW = $frameW - 200;
$photoH = ($frameH - 400) / 3;

// ================== CANVAS FINAL ==================
$final = imagecreatetruecolor($frameW, $frameH);
imagesavealpha($final, true);
$transparent = imagecolorallocatealpha($final,0,0,0,127);
imagefill($final,0,0,$transparent);

// ================== TEXT ==================
$textColor = imagecolorallocate($final,0,0,0);
$font = __DIR__."/assets/arial.ttf";

if (file_exists($font)) {
    imagettftext($final, 36, 0, $photoX, 120, $textColor, $font, $title);
}

// ================== COPY FOTO ==================
imagecopyresampled($final, $img1, $photoX, $photoY, 0,0,
    $photoW,$photoH, imagesx($img1), imagesy($img1)
);

imagecopyresampled($final, $img2, $photoX, $photoY + $photoH, 0,0,
    $photoW,$photoH, imagesx($img2), imagesy($img2)
);

imagecopyresampled($final, $img3, $photoX, $photoY + ($photoH*2), 0,0,
    $photoW,$photoH, imagesx($img3), imagesy($img3)
);

// ================== FOOTER ==================
if (file_exists($font)) {
    imagettftext($final, 22, 0, $photoX, $frameH - 80, $textColor, $font, $date);
    imagettftext($final, 22, 0, $photoX, $frameH - 40, $textColor, $font, $footer);
}

// ================== OVERLAY FRAME ==================
imagecopy($final, $frameImg, 0,0,0,0, $frameW, $frameH);

// ================== SAVE FILE ==================
$filename = "strip_".time().".png";
$consider = $resultDir.$filename;
imagepng($final, $consider);

// ================== UPLOAD KE GOOGLE DRIVE ==================
require __DIR__ . '/vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/credentials.json');
$client->addScope(Google_Service_Drive::DRIVE_FILE);

$service = new Google_Service_Drive($client);

// ✅ FOLDER TUJUAN
$folderId = '1_TCvYGVCsLwroi5JrPv2noej4FOG8RYu';

// Upload file
$fileMetadata = new Google_Service_Drive_DriveFile([
    'name' => basename($consider),
    'parents' => [$folderId]
]);

$content = file_get_contents($consider);

$driveFile = $service->files->create($fileMetadata, [
    'data' => $content,
    'mimeType' => 'image/png',
    'uploadType' => 'multipart',
    'fields' => 'id'
]);

// ================== SET PUBLIC ==================
$permission = new Google_Service_Drive_Permission([
    'type' => 'anyone',
    'role' => 'reader'
]);

$service->permissions->create(
    $driveFile->id,
    $permission
);

// ================== PUBLIC LINK ==================
$driveLink = "https://drive.google.com/file/d/".$driveFile->id."/view";


// ================== CLEAN ==================
foreach ($paths as $p) unlink($p);
imagedestroy($img1);
imagedestroy($img2);
imagedestroy($img3);
imagedestroy($frameImg);
imagedestroy($final);

// ================== RESPONSE ==================
echo json_encode([
    "status" => "success",
    "file"   => $consider,
    "drive"  => $driveLink
]);
exit;
