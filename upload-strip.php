<?php
header("Content-Type: application/json");

// ==== INPUT ====
$images = $_POST['images'] ?? [];
$title  = $_POST['title_text'] ?? 'PHOTO BOOTH';
$footer = $_POST['footer_text'] ?? '';
$date   = date("d M Y");

// ==== FOLDER ====
$temp   = "photos/temp/";
$result = "photos/result/";
$frame  = "assets/frame.png";

// ==== VALIDASI ====
if (count($images) !== 3) {
    echo json_encode(["status"=>"error","message"=>"Images not complete"]);
    exit;
}

if (!file_exists($frame)) {
    echo json_encode(["status"=>"error","message"=>"Frame not found"]);
    exit;
}

if (!is_dir($temp)) mkdir($temp,0777,true);
if (!is_dir($result)) mkdir($result,0777,true);

// ==== SAVE TEMP IMAGES ====
$paths = [];
foreach ($images as $i => $img) {
    $img = str_replace('data:image/png;base64,','',$img);
    $img = str_replace(' ','+',$img);
    $path = $temp."img_$i.png";
    file_put_contents($path, base64_decode($img));
    $paths[] = $path;
}

// ==== LOAD IMAGES ====
$img1 = imagecreatefrompng($paths[0]);
$img2 = imagecreatefrompng($paths[1]);
$img3 = imagecreatefrompng($paths[2]);

// ==== LOAD FRAME (ACUAN UKURAN) ====
$frameImg = imagecreatefrompng($frame);
$frameW = imagesx($frameImg);
$frameH = imagesy($frameImg);

// ==== AREA FOTO (SESUAIKAN DENGAN FIGMA) ====
$photoX = 100;
$photoY = 200;
$photoW = $frameW - 200;
$photoH = ($frameH - 400) / 3;

// ==== CANVAS FINAL ====
$final = imagecreatetruecolor($frameW, $frameH);
$white = imagecolorallocate($final,255,255,255);
imagefill($final,0,0,$white);

// ==== TEXT SETUP ====
$textColor = imagecolorallocate($final,0,0,0);
$font = __DIR__."/assets/arial.ttf";

// ==== HEADER TEXT ====
if (file_exists($font)) {
    imagettftext($final, 36, 0, $photoX, 120, $textColor, $font, $title);
}

// ==== COPY & RESIZE FOTO ====
imagecopyresampled($final, $img1,
    $photoX, $photoY,
    0, 0,
    $photoW, $photoH,
    imagesx($img1), imagesy($img1)
);

imagecopyresampled($final, $img2,
    $photoX, $photoY + $photoH,
    0, 0,
    $photoW, $photoH,
    imagesx($img2), imagesy($img2)
);

imagecopyresampled($final, $img3,
    $photoX, $photoY + ($photoH * 2),
    0, 0,
    $photoW, $photoH,
    imagesx($img3), imagesy($img3)
);

// ==== FOOTER TEXT ====
if (file_exists($font)) {
    imagettftext($final, 22, 0, $photoX, $frameH - 80, $textColor, $font, $date);
    imagettftext($final, 22, 0, $photoX, $frameH - 40, $textColor, $font, $footer);
}

// ==== OVERLAY FRAME (PALING AKHIR) ====
imagecopy($final, $frameImg, 0, 0, 0, 0, $frameW, $frameH);

// ==== SAVE RESULT ====
$file = $result."strip_".time().".png";
imagepng($final, $file);

// ==== CLEAN MEMORY ====
imagedestroy($img1);
imagedestroy($img2);
imagedestroy($img3);
imagedestroy($frameImg);
imagedestroy($final);

// ==== RESPONSE ====
echo json_encode([
    "status" => "success",
    "file"   => $file
]);
exit;
