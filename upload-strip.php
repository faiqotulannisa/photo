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

$w = imagesx($img1);
$h = imagesy($img1);

// ==== CANVAS ====
$headerH = 120;
$footerH = 120;
$finalH  = ($h*3) + $headerH + $footerH;

$final = imagecreatetruecolor($w, $finalH);
$white = imagecolorallocate($final,255,255,255);
imagefill($final,0,0,$white);

// ==== TEXT ====
$textColor = imagecolorallocate($final,0,0,0);
$font = __DIR__."/assets/arial.ttf";

// header
if (file_exists($font)) {
    imagettftext($final, 28, 0, 40, 70, $textColor, $font, $title);
}

// ==== PHOTOS ====
imagecopy($final,$img1,0,$headerH,0,0,$w,$h);
imagecopy($final,$img2,0,$headerH+$h,0,0,$w,$h);
imagecopy($final,$img3,0,$headerH+$h*2,0,0,$w,$h);

// footer
if (file_exists($font)) {
    imagettftext($final, 18, 0, 40, $finalH-60, $textColor, $font, $date);
    imagettftext($final, 18, 0, 40, $finalH-30, $textColor, $font, $footer);
}

// ==== FRAME OVERLAY ====
if (file_exists($frame)) {
    $frameImg = imagecreatefrompng($frame);
    imagecopy($final, $frameImg, 0, 0, 0, 0, imagesx($frameImg), imagesy($frameImg));
}

// ==== SAVE RESULT ====
$file = $result."strip_".time().".png";
imagepng($final, $file);

// ==== RESPONSE ====
echo json_encode([
    "status" => "success",
    "file"   => $file
]);
exit;
