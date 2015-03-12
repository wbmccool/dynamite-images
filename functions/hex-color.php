<?
function hexColorAllocate($image, $hex){
    $hex = ltrim($hex,'#');
    $a = hexdec(substr($hex,0,2));
    $b = hexdec(substr($hex,2,2));
    $c = hexdec(substr($hex,4,2));
    return imagecolorallocate($image, $a, $b, $c);
}

function hexColorAllocateAlpha($image, $hex, $alpha){
    $hex = ltrim($hex,'#');
    $a = hexdec(substr($hex,0,2));
    $b = hexdec(substr($hex,2,2));
    $c = hexdec(substr($hex,4,2));
    return imagecolorallocatealpha($image, $a, $b, $c, $alpha);
}

?>