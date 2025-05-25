<?php



//4× upscale, 40 is ok too, didnt bother to test other values
$scale = 4;

//size canvas
$width = 200 * $scale;
$height = 50 * $scale;

//get the flags from the github repo
$flags = file_get_contents('https://raw.githubusercontent.com/polo-nyan/common-ressources/refs/heads/main/lgbtq/flags.json');
$flags = json_decode($flags, true);

if (!$flags) {
    die('Error loading flags data.');
}

// Create a blank image
header("Content-Type: image/png");
$im = imagecreatetruecolor($width, $height);

// Colors
$white = imagecolorallocate($im, 255, 255, 255);
// Background
imagefill($im, 0, 0, $white);

// Function to convert hex color to RGB
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

// Function to create a flag image
function createFlagImage($colors, $width, $height) {
    $flag = imagecreatetruecolor($width, $height);
    $y = 0;
    foreach ($colors as $color) {
        $rgb = hexToRgb($color);
        $col = imagecolorallocate($flag, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledrectangle($flag, 0, $y, $width, $y + ($height / count($colors)), $col);
        $y += ($height / count($colors));
    }
    return $flag;
}


//draw all the flags#
$flagHeight = 10 * $scale; // Height of each flag
$flagWidth = 15 * $scale; // Width of each flag
$flagX = 10 * $scale; // Starting X position for flags
$flagY = 10 * $scale; // Starting Y position for flags
foreach ($flags as $flagData) {
    $flagImage = createFlagImage($flagData['colors'], $flagWidth, $flagHeight);
    imagecopy($im, $flagImage, $flagX, $flagY, 0, 0, $flagWidth, $flagHeight);
    imagedestroy($flagImage);
    $flagX += $flagWidth + 5 * $scale; // Move to the right for the next flag
    if ($flagX + $flagWidth > $width - 10 * $scale) {
        $flagX = 10 * $scale; // Reset X position for the next row
        $flagY += $flagHeight + 2.5 * $scale; // Move down for the next row
    }
}


imagepng($im);
imagedestroy($im);
