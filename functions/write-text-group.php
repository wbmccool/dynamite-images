<?php
require_once 'functions/write-text-w-letter-tracking.php';

function writeTextGroup($image, $image_width, $image_height, $params)
{
    global $Arabic;
    // Path to our ttf font file
    $font_file = './fonts/' . $params['font-family'] . '.ttf';
    $color = hexColorAllocate($image, $params['color']);
    $is_rtl = false; // is this a right to left language? Default = false.

    if ($params['text-transform'] == "uppercase") {
        $params['text'] = strtoupper($params['text']);
    } elseif ($params['text-transform'] == "capitalize") {
        $params['text'] = ucwords($params['text']);
    }

    if ($params['vertical-align'] == "middle") {
        $params['top'] = centerVert($image, $params['font-size'], $font_file, $params['text'], $image_height, $params['top']);
    }
    if ($params['vertical-align'] == "bottom") {
        $params['top'] = bottomalignText($image, $params['font-size'], $font_file, $params['text'], $image_height, $params['top']);
    }

    // sort out any Arabic Character glyph issues
    // These fonts get special handling to ensure their glyphs are rendered correctly.
    $arabic_fonts = ["DroidKufi-Bold.ttf", "DroidKuf-Regular.ttf", "terafik.ttf"];

    syslog(LOG_INFO, $params['text']);
    if (in_array(basename($font_file), $arabic_fonts)) {

        $params['text'] = $Arabic->utf8Glyphs($params['text']);
        $is_rtl = true;
    }

    /*write the text*/
    if ($params['white-space'] == "normal") {
        $words = explode(' ', $params['text']);
        $mlength = $params['max-width'] > 0 ?
        $params['max-width'] : abs(
            $params['text-align'] == "right" ?
            $image_width - $params['right'] : $image_width - $params['left']
        );
        $topset = 0;
        $line = '';

        foreach ($words as &$word) {
            $sizeWithWord = imagettfbboxWithTracking($params['font-size'], $params['angle'], $font_file, $line == "" ? $word : ($line . ' ' . $word), $params['letter-spacing']);
            if (($sizeWithWord[2] - $sizeWithWord[0] > $mlength)) {
                if ($params['text-align'] == "center") {
                    $tmpleft = centerText($image, $params['font-size'], $font_file, $line, $image_width, $params['left'], $params['letter-spacing']);
                } elseif ($params['text-align'] == "right") {
                    $tmpleft = rightalignText($image, $params['font-size'], $font_file, $line, $image_width, $params['left'], $params['letter-spacing']);
                } else {
                    $tmpleft = $params['left'];
                }
                writeTextLine($image, $params['font-size'], $params['angle'], $tmpleft, $params['top'] + $topset, $color, $font_file, $line, $params['text-shadow'], $params['outline'], $params['letter-spacing']);
                $line = $word;
                $topset = $topset + ($params['line-height'] * $params['font-size']);
            } else {
                $line = $line == "" ? $word : ($line . ' ' . $word);
            }
        }
        if ($params['text-align'] == "center") {
            $tmpleft = centerText($image, $params['font-size'], $font_file, $line, $image_width, $params['left'], $params['letter-spacing']);
        } elseif ($params['text-align'] == "right") {
            $tmpleft = rightalignText($image, $params['font-size'], $font_file, $line, $image_width, $params['left'], $params['letter-spacing']);
        } else {
            $tmpleft = $params['left'];
        }

        writeTextLine($image,
            $params['font-size'],
            $params['angle'],
            $tmpleft,
            $params['top'] + $topset,
            $color,
            $font_file,
            $line,
            $params['text-shadow'],
            $params['outline'],
            $params['letter-spacing']
        ); //remaining lines

    } else {

        if ($params['text-align'] == "center") {
            $params['left'] = centerText($image,
                $params['font-size'], $font_file, $params['text'], $image_width, $params['left'], $params['letter-spacing']);
        }
        if ($params['text-align'] == "right") {
            $params['left'] = rightalignText($image, $params['font-size'], $font_file, $params['text'], $image_width, $params['left'], $params['letter-spacing']);
        }

        writeTextLine($image, $params['font-size'], $params['angle'], $params['left'], $params['top'], $color, $font_file, $params['text'], $params['text-shadow'], $params['outline'], $params['letter-spacing']);
    }
}
