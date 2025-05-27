<?php



//4× upscale, 40 is ok too, didnt bother to test other values
$scale = 10;

//size canvas
$width = 200 * $scale;
$height = 0;

//get the flags from the github repo
$flags = file_get_contents('https://raw.githubusercontent.com/polo-nyan/common-ressources/refs/heads/main/lgbtq/flags/flags.json');

/*$flags = <<<JSON

JSON;*/

$flags = json_decode($flags, true);

if (!$flags) {
    die('Error loading flags data.');
}


//precalculate flag rows and add height to image
$flagRows = ceil(count($flags) / 9); // Assuming 10 flags per row
$height = $flagRows * (14 * $scale) + (20 * $scale); // 10 flags height + 20px padding



// Create a blank image
header("Content-Type: image/png");
$im = imagecreatetruecolor($width, $height);

// Colors
$white = imagecolorallocate($im, 255, 255, 255);
// Background
imagefill($im, 0, 0, $white);

// Function to convert hex color to RGB
function hexToRgb($hex)
{
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
function createFlagImage($flagData, $width, $height)
{
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
                round($cx - $dx * $half - $nx * $w),
                round($cy - $dy * $half - $ny * $w),
                round($cx - $dx * $half + $nx * $w),
                round($cy - $dy * $half + $ny * $w),
                round($cx + $dx * $half + $nx * $w),
                round($cy + $dy * $half + $ny * $w),
                round($cx + $dx * $half - $nx * $w),
                round($cy + $dy * $half - $ny * $w),
            ];

            imagefilledpolygon($flag, $polygon, $col);
        }
    } else {
        // Default horizontal stripes
        $stripeHeight = $height / count($colors);
        foreach ($colors as $i => $color) {
            $rgb = hexToRgb($color);
            $col = imagecolorallocate($flag, $rgb[0], $rgb[1], $rgb[2]);
            imagefilledrectangle($flag, 0, (int) ($i * $stripeHeight), $width, (int) (($i + 1) * $stripeHeight), $col);
        }
    }

    return $flag;
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

    //add label
    $label = $flagData['name'] ?? 'Unknown';
    $fontSize = 2 * $scale; // Font size for the label
    $textColor = imagecolorallocate($im, 0, 0, 0); // Black color for text
    $textWidth = imagefontwidth($fontSize) * strlen($label);
    $textX = $flagX + ($flagWidth - $textWidth) / 2; // Center the text
    $textY = $flagY + $flagHeight + 2 * $scale; // Position below the flag

    imagestring($im, $fontSize, (int) $textX, (int) $textY, $label, $textColor);

    $flagX += $flagWidth + 5 * $scale; // Move to the right for the next flag
    if ($flagX + $flagWidth > $width - 10 * $scale) {
        $flagX = 10 * $scale; // Reset X position for the next row
        $flagY += $flagHeight + 4 * $scale; // Move down for the next row
    }
}


imagepng($im);
imagedestroy($im);
