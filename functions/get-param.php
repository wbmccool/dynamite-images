<?
function getParam($string, $fallback){
    if(isset($string) && isset($_GET[$string])){
        return $_GET[$string];
    }
    elseif(isset($fallback)){
        return $fallback;
    }else {
        return '';
    }
}

function getParamAsArray($string, $fallback){
    if(isset($string) && isset($_GET[$string]) && $_GET[$string]!=""){
        $string = $_GET[$string];
        return is_array($string) ? $string : array($string);
    }else{
        return array($fallback);
    }
}


?>