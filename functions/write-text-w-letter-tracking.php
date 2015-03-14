<?php
//letter spacing/"tracking"


//the logic here is wrong.  We need to get the original tracking values and then subtract or add to them per character

function imagettftextWithTracking($image, $font_size, $angle, $x, $y, $color, $font_file, $text, $tracking) {
    //imagestring($image, 5, 11, 30, 'tracking: '.$tracking, $black);//shadow
    if(!isset($tracking) ){
        return imagettftext($image, $font_size, $angle, $x, $y, $color, $font_file, $text);
    }else{
        $numchar = strlen($text);
        $pos = 0;
        $lastcharwidth = 0;
        $trackingtmp = 0;
        $character = '';
        $lastcharacter = NULL;
        $lastcharacterWidth = 0;

        for($i = 0; $i < $numchar; $i++) {
            $character = substr($text, $i, 1);

            //width of this character
            $charwidth = imagettfbbox($font_size, $angle, $font_file, $character);
            $charwidth = $charwidth[2] - abs($charwidth[0]);

            //width of this character and the last character together
            $twocharwidth = imagettfbbox($font_size, $angle, $font_file, ($lastcharacter . $character) );
            $twocharwidth = $twocharwidth[2] - abs($twocharwidth[0]);


            //space between characters as a percentage of their total width
            $trackingtmp = ($lastcharacter!=NULL) ?
                $tracking - ($twocharwidth - $lastcharacterWidth - $charwidth) :
                0;


            //imagestring($image, 5, 11, ($i*12)+150, ($lastcharacter!=NULL) ? ($lastcharacter . $character).' '.$twocharwidth.'-'.$lastcharacterWidth.'-'.$charwidth.'='.($twocharwidth - $lastcharacterWidth - $charwidth).' +'.round((($twocharwidth - $lastcharacterWidth - $charwidth)/$charwidth) * $tracking).' = '.$trackingtmp : 'NA', $black);

            imagestring($image, 5, 11, ($i*12)+150, ($lastcharacter . $character).' '.$twocharwidth.'-'.$lastcharacterWidth.'-'.$charwidth.'='.$trackingtmp , $black);

            imagettftext($image, $font_size, $angle, ($x + $pos + $trackingtmp), $y, $color, $font_file, $character);

            $pos = $pos + $charwidth + $trackingtmp;
            $lastcharacter = $character;
            $lastcharacterWidth = $charwidth;
        }
    }
}

function imagettfbboxWithTracking($font_size, $angle, $font_file, $text, $tracking = NULL) {
    if(!isset($tracking) || $tracking==NULL){
        $box = imagettfbbox($font_size, $angle, $font_file, $text);

        if($box[0] >= -1) {
            $box['x'] = abs($box[0] + 1) * -1;
        } else {
            //$box['x'] = 0;
            $box['x'] = abs($box[0] + 2);
        }

        //calculate actual text width
        $box['width'] = abs($box[2] - $box[0]);
        if($box[0] < -1) {
            $box['width'] = abs($box[2]) + abs($box[0]) - 1;
        }

        //calculate y baseline
        $box['y'] = abs($box[5] + 1);

        //calculate actual text height
        $box['height'] = abs($box[7]) - abs($box[1]);
        if($box[3] > 0) {
            $box['height'] = abs($box[7] - $box[1]) - 1;
        }

        return $box;
    }else{
        $numchar = strlen($text);
        $pos = 0;
        for($i = 0; $i < $numchar; $i++) {
            $character = substr($text, $i, 1);
            $width = imagettfbbox($font_size, $angle, $font_file, $character);
            $pos = $pos + ($width[2]-abs($width[0]));
        }
        return $pos - ($tracking*$numchar);//last space shoudn't count
    }
}


?>