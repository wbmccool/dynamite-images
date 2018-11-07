<?php
require_once('functions/hex-color.php');
require_once('functions/write-text-w-letter-tracking.php');


function writeTextLine($image, $fontSize, $angle, $left, $top, $color, $font_file, $text, $textShadow, $textOutline, $tracking){


    if($textShadow){
        $tsArray = explode(' ',$textShadow);//left,top,color,alphaopacity
        imagettftextWithTracking($image, $fontSize, $angle, $left + str_replace('px','',$tsArray[0]), $top + str_replace('px','',$tsArray[1]), hexColorAllocateAlpha($image, $tsArray[2],  $tsArray[3]), $font_file, $text, $tracking);
    }

    if($textOutline){
        $otArray = explode(' ',$textOutline);//spread color alpha
        for($c1 = ($left-abs($otArray[0])); $c1 <= ($left+abs($otArray[0])); $c1++){
            for($c2 = ($top-abs($otArray[0])); $c2 <= ($top+abs($otArray[0])); $c2++){
                imagettftextWithTracking($image, $fontSize, $angle, $c1, $c2, hexColorAllocateAlpha($image, $otArray[1],  $otArray[2]), $font_file, $text, $tracking);
            }
        }
    }
    imagettftextWithTracking($image, $fontSize, $angle, $left, $top, $color, $font_file, $text, $tracking);
    /*terrible underlines
    $textdim = imagettfbbox($fontSize, 0, $font_file, $text);
    ImageLine($image, $left, $top+$textdim[1]-5, $left+$textdim[4],  $top+$textdim[1]-5, $color);
    */
}
?>
