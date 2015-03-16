<?php

error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);

require('functions/get-param.php');
require_once('functions/hex-color.php');
require_once('functions/write-text-w-letter-tracking.php');
require('functions/text-alignment.php');
require('functions/write-text-line.php');
require('functions/write-text-group.php');


$file_name = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

/*if it looks like someone is just hitting this file directly, redirect to the editor*/
if($file_name == '/index.php' || $file_name == '/'){
    header("Location: /upload.php");
    die();
}

$max_age = 0;//600; //seconds this URL should be cached

header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $max_age) . ' GMT');
header("Cache-Control: public, max-age=$max_age");


if (!function_exists('boolval')) {
        function boolval($val) {
                return (bool) $val;
        }
}

/*Get and set basic parameters and defaults*/
$local = boolval(getParam('local', false));//if local, option to use local files for testing
$debug = boolval(getParam('debug', false));//local defaults to debug mode
$pngcomp = getParam('pngcomp',5);
$jpgquality = getParam('jpgquality',100);

/*determine what the image might be and */
$url= explode('.',$file_name);
$image_type = end($url);//detect image type from filename
$app_host = array_pop(explode("~", $_SERVER["APPLICATION_ID"])) . ".appspot.com";
$sourceimg = $local?'./img'.urldecode($file_name):"gs://" . $app_host . urldecode($file_name);

$defaults = array(
    'text'=>'',
    'text-align'=>NULL,
    'vertical-align'=>NULL,
    'color'=>'#000000',
    'font-size'=>24,
    'top'=>0,
    'bottom'=>0,
    'left'=>0,
    'right'=>0,
    'letter-spacing'=>NULL,
    'text-transform'=>NULL,
    'font-family'=>'OpenSans-Regular',
    'line-height'=>1.5,
    'angle'=>0,
    'white-space'=>'nowrap',
    'max-width'=>'none',
    'text-shadow'=> NULL,
    'outline'=> NULL
);
$keys = array_keys($defaults);

foreach ($defaults as $key => $value){
    $params[$key] = getParamAsArray($key,$value);
}
$textStrings = count($params['text'])-1;
$textGroups = array();

$context = [
        'gs' => [
        ]
    ];

/*Set headers and stream content, then attempt to open the image*/
if($image_type == "png"){
    header("Content-type: image/png");
    $context["gs"]['Content-Type'] = 'image/png';
    stream_context_set_default($context);
    $image = @imagecreatefrompng($sourceimg);

    /*deal with alpha transparency*/
    $background = imagecolorallocate($image, 0, 0, 0);
    imagecolortransparent($image, $background);// removing the black from the placeholder
    imagealphablending($image, true);
    imagesavealpha($image, true);
}else{
    header('Content-Type: image/jpeg');
    $context["gs"]['Content-Type'] = 'image/jpeg';
    stream_context_set_default($context);
    $image = @imagecreatefromjpeg($sourceimg);
}

/*determine where we're writing the text*/
if($image){
    $black = imagecolorallocate($image, 0, 0, 0);
    $image_width = imagesx($image);
    $image_height = imagesy($image);

    for ($i = 0; $i <= $textStrings; $i++) {
        $textGroups[$i] = array();
        foreach($defaults as $k => $v){
            $textGroups[$i][$k] = isset($params[$k][$i]) ? $params[$k][$i] : $v;
        }
        //echo print_r($textGroups[$i]);
        writeTextGroup($image, $image_width, $image_height, $textGroups[$i]);
    }

    if($debug=="true"){
        $debugstring = ('width: '.$image_width.', height: '.$image_height.', top: '.$top.', left: '.$left.($image_type == "png"?', $pngcomp: '.$pngcomp:',$jpgquality: '.$jpgquality) );

        imagestring($image, 5, 11, $image_height-39, $debugstring, $black);//shadow
        imagestring($image, 5, 10, $image_height-40, $debugstring, $color);//color
    }

    /*output the image then destroy it*/
    if($image_type == "png"){
        imagepng($image, NULL, $pngcomp, NULL);
    }else{
        imagejpeg($image, NULL, $jpgquality);
    }
    imagedestroy($image);
}
else{
    /*if we couldn't create the image for some reason, create a 600px wide, 1px high white image*/
    /*nah, just fail gloriously instead
    $image = @imagecreatetruecolor(600, 1);
    $bg = imagecolorallocate ( $image, 255, 255, 255 );
    imagefilledrectangle($image,0,0,600,1,$bg);
    if($image_type == "png"){
        imagepng($image);
    }else{
        imagejpeg($image);
    }
    imagedestroy($image);
    */
}

?>