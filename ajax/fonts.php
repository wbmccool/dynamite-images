<?php

$max_age = 0;//600; //seconds this URL should be cached

header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $max_age) . ' GMT');
header("Cache-Control: public, max-age=$max_age");
header("Content-Type: application/json");
$response = [];

$included_fonts = glob("../fonts/*.ttf");//only ttf for now

foreach($included_fonts as $included_font){
    $response[] = basename($included_font, ".ttf");
}
//sort($response, SORT_NATURAL);
echo json_encode($response);
?>