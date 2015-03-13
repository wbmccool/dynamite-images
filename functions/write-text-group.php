<?php
require_once('functions/write-text-w-letter-tracking.php');

function writeTextGroup($image, $image_width, $image_height, $params){

    // Path to our ttf font file
    $font_file = './fonts/'.$params['font-family'].'.ttf';
    $color = hexColorAllocate($image, $params['color']);

    $topset = 0;
    $lines = array();

    if($params['text-transform']=="uppercase"){
        $params['text'] = strtoupper( $params['text'] );
    }
    elseif($params['text-transform']=="capitalize"){
        $params['text'] = ucwords( $params['text'] );
    }

    /*write the text*/
    if($params['white-space']=="normal"){
        $words = explode(' ', $params['text']);
        $mlength = $params['max-width']>0?
            $params['max-width'] : abs($params['text-align'] == "right"?
                $image_width - $params['right'] : $image_width - $params['left']);

        $line = '';

        foreach ($words as &$word){

            $sizeWithWord = imagettfbboxWithTracking($params['font-size'],  $params['angle'], $font_file, $line==""? $word: ($line .' '.$word), $params['letter-spacing']);

            if(($sizeWithWord[2] - abs($sizeWithWord[0]) > $mlength)){

                if($params['text-align'] == "center"){
                    $tmpleft = centerText($image, $params['font-size'], $font_file, $line, $image_width, $params['left'], $params['letter-spacing']);
                }
                elseif($params['text-align'] == "right"){
                    $tmpleft = rightalignText($image, $params['font-size'], $font_file, $line, $image_width, $params['left'], $params['letter-spacing']);
                }
                else{
                    $tmpleft = $params['left'];
                }

                $sizeWithWord = imagettfbbox($params['font-size'],  $params['angle'], $font_file, $line);
                $lines[] = array('line_text'=>$line,'line_left'=>$tmpleft, 'font_height'=> abs($sizeWithWord[7])-abs($sizeWithWord[1]) );

                //writeTextLine($image, $params['font-size'], $params['angle'], $tmpleft, $params['top']+$topset, $color, $font_file, $line, $params['text-shadow'], $params['outline'], $params['letter-spacing']);
                $line = $word;
                //$topset = $topset + ($params['line-height']*$params['font-size']);
            }else{
                $line = $line==""? $word: ($line .' '.$word);
            }
        }

        /*deal with the final line*/
        if($params['text-align'] == "center"){
            $tmpleft = centerText($image, $params['font-size'], $font_file, $line, $image_width, $params['left'], $params['letter-spacing']);
        }
        elseif($params['text-align'] == "right"){
            $tmpleft = rightalignText($image, $params['font-size'], $font_file, $line, $image_width, $params['left'], $params['letter-spacing']);
        }
        else{
            $tmpleft = $params['left'];
        }
        $sizeWithWord = imagettfbbox($params['font-size'],  $params['angle'], $font_file, $line);
        $lines[] = array('line_text'=>$line,'line_left'=>$tmpleft, 'font_height'=> abs($sizeWithWord[7])-abs($sizeWithWord[1]));
        //writeTextLine($image, $params['font-size'], $params['angle'], $tmpleft, $params['top']+$topset, $color, $font_file, $line, $params['text-shadow'], $params['outline'], $params['letter-spacing']);//remaining lines

    }
    else{

        if($params['text-align'] == "center"){
            $params['left'] = centerText($image, $params['font-size'], $font_file, $params['text'], $image_width, $params['left'], $params['letter-spacing']);
        }
        elseif ($params['text-align'] == "right"){
            $params['left'] = rightalignText($image, $params['font-size'], $font_file, $params['text'], $image_width, $params['left'], $params['letter-spacing']);
        }
        $sizeWithWord = imagettfbbox($params['font-size'],  $params['angle'], $font_file, $line);
        $lines[] = array('line_text'=>$params['text'],'line_left'=>$params['left'], 'font_height'=> abs($sizeWithWord[7])-abs($sizeWithWord[1]));
        //writeTextLine($image, $params['font-size'], $params['angle'], $params['left'], $params['top'], $color, $font_file, $params['text'], $params['text-shadow'], $params['outline'], $params['letter-spacing']);
    }

    // $block_height = count($lines)<1 ? $lines[0]['font_height'] :
    //     array_reduce($lines, function($carry, $val){
    //         return $carry + $val['font_height'];
    //     }, 0);// +
    //     ($params['line-height'] * $params['font-size'] * (count($lines)-1));

    $block_height =
        ($params['line-height'] * $params['font-size'] * (count($lines)-1)) +//lines + our artifical line-height spacing
        $lines[0]['font_height'];//all lines are the same height, so using the first instead of the last is ok...

    /*now deal with vertical alignment*/
    if ($params['vertical-align'] == "middle"){
        $params['top'] = centerVert($image, $params['font-size'], $font_file, $params['text'], $image_height, $params['top'], $block_height, $lines[0]['font_height']);
    }
    elseif ($params['vertical-align'] == "bottom"){
        $params['top'] = bottomalignText($image, $params['font-size'], $font_file, $params['text'], $image_height, $params['top'], $block_height, $lines[0]['font_height']);
    }
    else{
        $params['top'] = $params['top'] + $params['font-size'];//start the line at the top of the chosen position instead of the baseline
    }

    imagestring($image, 5, 11, 30, 'bh: '.$block_height.' ' .count($lines) . ' '. $params['line-height'] * $params['font-size'] * (count($lines)-1), $black);//shadow

    foreach ($lines as &$line){
        writeTextLine($image, $params['font-size'], $params['angle'], $line['line_left'], $params['top']+$topset, $color, $font_file, $line['line_text'], $params['text-shadow'], $params['outline'], $params['letter-spacing']);//remaining lines
        $topset = $topset + ($params['line-height']*$params['font-size']);
    }

}
?>