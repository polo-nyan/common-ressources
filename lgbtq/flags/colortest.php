<?php



//4× upscale, 40 is ok too, didnt bother to test other values
$scale = 20;

//size canvas
$width = 200 * $scale;
$height = 50 * $scale;

//get the flags from the github repo
$flags = file_get_contents('https://raw.githubusercontent.com/polo-nyan/common-ressources/refs/heads/main/lgbtq/flags/flags.json');


/*$flags = <<<JSON

JSON;*/

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
function createFlagImage($flagData, $width, $height) {
    $flag = imagecreatetruecolor($width, $height);
    $colors = $flagData['colors'];
    $type = $flagData['type'] ?? 'horizontal-stripes';

    // Extract angle if the type ends with -degree-stripes
    $angle = null;
    if (preg_match('/(\d+)-degree-stripes$/', $type, $matches)) {
        $angle = intval($matches[1]);
    }

if ($angle !== null) {
    $diagonalLength = sqrt($width * $width + $height * $height);
    $stripeWidth = $diagonalLength / count($colors);
    $radians = deg2rad($angle);

    // Stripe direction
    $dx = cos($radians);
    $dy = sin($radians);

    // Normal vector (perpendicular to stripe direction)
    $nx = -$dy;
    $ny = $dx;

    for ($i = 0; $i < count($colors); $i++) {
        $rgb = hexToRgb($colors[$i]);
        $col = imagecolorallocate($flag, $rgb[0], $rgb[1], $rgb[2]);

        // Offset along perpendicular to get the stripe center
        $centeredIndex = $i - (count($colors) - 1) / 2;
        $cx = $width / 2 + $nx * $centeredIndex * $stripeWidth;
        $cy = $height / 2 + $ny * $centeredIndex * $stripeWidth;

        // Compute corners
        $half = $diagonalLength;
        $w = $stripeWidth / 2;

        $polygon = [
            round($cx - $dx * $half - $nx * $w), round($cy - $dy * $half - $ny * $w),
            round($cx - $dx * $half + $nx * $w), round($cy - $dy * $half + $ny * $w),
            round($cx + $dx * $half + $nx * $w), round($cy + $dy * $half + $ny * $w),
            round($cx + $dx * $half - $nx * $w), round($cy + $dy * $half - $ny * $w),
        ];

        imagefilledpolygon($flag, $polygon, $col);
    }
}

 else {
        // Default horizontal stripes
        $stripeHeight = $height / count($colors);
        foreach ($colors as $i => $color) {
            $rgb = hexToRgb($color);
            $col = imagecolorallocate($flag, $rgb[0], $rgb[1], $rgb[2]);
            imagefilledrectangle($flag, 0, (int)($i * $stripeHeight), $width, (int)(($i + 1) * $stripeHeight), $col);
        }
    }

    // Handle custom elements
    if (!empty($flagData['elements'])) {
        foreach ($flagData['elements'] as $element) {
            drawFlagElement($flag, $element, $width, $height);
        }
    }

    return $flag;
}


//adds a flag element to the flag image
function drawFlagElement($img, $element, $width, $height) {
    $type = $element['type'];
    $color = hexToRgb($element['color']);
    $col = imagecolorallocate($img, $color[0], $color[1], $color[2]);

    $elW = ($element['size']['width'] / 100) * $width;
    $elH = ($element['size']['height'] / 100) * $height;

    // Position
    $x = is_numeric($element['position']['x']) ? $element['position']['x'] :
        ($element['position']['x'] === 'center' ? ($width - $elW) / 2 :
        ($element['position']['x'] === 'left' ? 0 :
        ($element['position']['x'] === 'right' ? $width - $elW : 0)));

    $y = is_numeric($element['position']['y']) ? $element['position']['y'] :
        ($element['position']['y'] === 'center' ? ($height - $elH) / 2 :
        ($element['position']['y'] === 'top' ? 0 :
        ($element['position']['y'] === 'bottom' ? $height - $elH : 0)));

    // Padding
    if (!empty($element['padding']) && $element['padding']['type'] === 'percent') {
        $x += $width * ($element['padding']['left'] ?? 0) / 100;
        $y += $height * ($element['padding']['top'] ?? 0) / 100;
    }

    switch ($type) {
        case 'bone': //kinda
            /*imagefilledrectangle($img, $x, $y + $elH / 4, $x + $elW, $y + 3 * $elH / 4, $col);
            imagefilledellipse($img, $x, $y + $elH / 2, $elH, $elH, $col);
            imagefilledellipse($img, $x + $elW, $y + $elH / 2, $elH, $elH, $col);
            break;*/
            // Draw a bone shape with two roundes at each end
            imagefilledrectangle($img, $x, $y + $elH / 4, $x + $elW, $y + 3 * $elH / 4, $col);
            //left end upper circle
            imagefilledellipse($img, $x, $y + $elH / 4, $elH, $elH, $col);
            //right end upper circle
            imagefilledellipse($img, $x + $elW - $elH / 4, $y + $elH / 4, $elH, $elH, $col);
            //left end lower circle
            imagefilledellipse($img, $x, $y + $elH / 2 + ($elH/4), $elH, $elH, $col);
            //right end upper circle
            imagefilledellipse($img, $x + $elW - $elH /4, $y + $elH / 2 + ($elH/4), $elH, $elH, $col);
                
            break;

        case 'yarn-with-catears': // very basic representation
            imagefilledellipse($img, $x + $elW / 2, $y + $elH / 2, $elW, $elH, $col);
            imagefilledpolygon($img, [
                $x + $elW * 0.2, $y,
                $x + $elW * 0.35, $y - $elH * 0.3,
                $x + $elW * 0.5, $y
            ], $col);
            imagefilledpolygon($img, [
                $x + $elW * 0.8, $y,
                $x + $elW * 0.65, $y - $elH * 0.3,
                $x + $elW * 0.5, $y
            ], $col);
            break;
    }
}




//draw all the flags#
$flagHeight = 10 * $scale; // Height of each flag
$flagWidth = 15 * $scale; // Width of each flag
$flagX = 10 * $scale; // Starting X position for flags
$flagY = 10 * $scale; // Starting Y position for flags
//draw the flags on the canvas
foreach ($flags as $flagData) {
    $flagImage = createFlagImage($flagData, $flagWidth, $flagHeight);
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
