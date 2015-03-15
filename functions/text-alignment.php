<?php
require_once('functions/write-text-w-letter-tracking.php');

function centerText($image_r, $fontSize_r, $font_file_r, $text_r, $iwidth, $left_r, $tracking){
    $text_box = imagettfbboxWithTracking($fontSize_r, 0, $font_file_r, $text_r, $tracking);
    $text_width = abs($text_box[2] - $text_box[0]);
    if(!isset($left_r)){
        $left_r = 0;
    }
    return $left_r - round($text_width/2);
}

function rightalignText($image_r, $fontSize_r, $font_file_r, $text_r, $iwidth, $right_r, $tracking){
    $text_box = imagettfbboxWithTracking($fontSize_r, 0, $font_file_r, $text_r, $tracking);
    $textWidth = abs($text_box[4] - $text_box[0]);
    return $iwidth - $textWidth - $right_r;
}

function centerVert($image_r, $fontSize_r, $font_file_r, $text_r, $iheight, $top_r, $bheight, $first_line_height){
    return $top_r - round($bheight/2) + $first_line_height;
}

function bottomalignText($image_r, $fontSize_r, $font_file_r, $text_r, $iheight, $top_r, $bheight, $first_line_height){
    if(!isset($top_r)){
        $top_r = 0;
    }
    return ($top_r - $bheight) + $first_line_height;
}

//true center
// function centerVert($image_r, $fontSize_r, $font_file_r, $text_r, $iheight, $top_r){
//     $text_box = imagettfbbox($fontSize_r, 0, $font_file_r, $text_r);
//     $text_height = $text_box[3]-$text_box[1];
//     if(!isset($top_r)){
//         $top_r = 0;
//     }
//     return abs($iheight/2) - ($text_height/2) + $top_r;
// }

// function bottomalignText($image_r, $fontSize_r, $font_file_r, $text_r, $iheight, $top_r){
//     $text_box = imagettfbbox($fontSize_r, 0, $font_file_r, $text_r);
//     $text_height = $text_box[3]-$text_box[1];
//     return $iheight - $text_height - $top_r;
// }
?>