<?php
// Generate multi-resolution favicon.ico from public/images/logo.png
$src = __DIR__ . '/../public/images/logo.png';
$dest = __DIR__ . '/../public/favicon.ico';
$sizes = [16,32,48];

if (!file_exists($src)) {
    fwrite(STDERR, "Source image not found: $src\n");
    exit(2);
}

if (!function_exists('imagecreatefrompng')) {
    fwrite(STDERR, "GD extension with PNG support is required.\n");
    exit(3);
}

$images = [];
foreach ($sizes as $size) {
    $srcImg = imagecreatefrompng($src);
    if (!$srcImg) {
        fwrite(STDERR, "Failed to load source PNG.\n");
        exit(4);
    }

    $w = imagesx($srcImg);
    $h = imagesy($srcImg);

    $dst = imagecreatetruecolor($size, $size);
    // Preserve alpha
    imagealphablending($dst, false);
    imagesavealpha($dst, true);

    // Fill with transparent
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefilledrectangle($dst, 0, 0, $size, $size, $transparent);

    imagecopyresampled($dst, $srcImg, 0, 0, 0, 0, $size, $size, $w, $h);

    ob_start();
    imagepng($dst);
    $png = ob_get_clean();

    imagedestroy($dst);
    imagedestroy($srcImg);

    $images[$size] = $png;
}

// Build ICO file with PNG images inside (Windows 7+ supports PNG inside ICO)
$count = count($images);
$header = pack('v', 0) . pack('v', 1) . pack('v', $count); // reserved, type, count
$entries = '';
$data = '';
$offset = 6 + 16 * $count;

foreach ($images as $size => $png) {
    $pngLen = strlen($png);
    $widthByte = $size >= 256 ? 0 : $size;
    $heightByte = $size >= 256 ? 0 : $size;
    $entry = chr($widthByte) . chr($heightByte) . chr(0) . chr(0); // color count, reserved
    $entry .= pack('v', 1); // planes
    $entry .= pack('v', 32); // bit count
    $entry .= pack('V', $pngLen); // bytes in resource
    $entry .= pack('V', $offset); // image offset

    $entries .= $entry;
    $data .= $png;
    $offset += $pngLen;
}

$ico = $header . $entries . $data;

if (file_put_contents($dest, $ico) === false) {
    fwrite(STDERR, "Failed to write ICO to $dest\n");
    exit(5);
}

fwrite(STDOUT, "Generated multi-resolution favicon at: $dest\n");
exit(0);
