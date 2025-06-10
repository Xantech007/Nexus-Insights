<?php
// resize_icon.php
$source = 'assets/images/favicon.png';
$sizes = [192 => 'icon-192.png', 512 => 'icon-512.png'];
foreach ($sizes as $size => $output) {
    $img = imagecreatefrompng($source);
    $resized = imagescale($img, $size, $size);
    imagepng($resized, "assets/images/$output");
    imagedestroy($img);
    imagedestroy($resized);
}
echo "Icons generated at assets/images/icon-192.png and assets/images/icon-512.png";
?>
