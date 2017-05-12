<?php

function ddir($dname,$flag='.'){ 
    $arr=[];
    if(is_dir($dname)){
        foreach (glob("$dname/*") as $value) 
            if($a=ddir($value,$flag))
                array_push($arr,...$a); 
    }
    if(preg_match("/$flag/",$dname)){
        $arr[]=$dname;
    } 
    return $arr; 
} 
$arr=array(
    '/(\s)([a-z_]\w*)\.([a-z_]\w*)/i'
        =>"$1`$2`.`$3`",
    '/((?:join|truncate|from|create table|alter table|as)\s+)([a-z_]\w*)/i'
        =>"$1`$2`",
    '/([a-z_]\w*)\s+(read|write|set)/i'
        =>"`$1` $2",
    '/(\s)([a-z_]\w*)\s*=/i'
        =>"$1`$2`=",
    '/([a-z_]\w*)\s+\bin\b/i'
        =>"`$1` in"
);
$key=array_keys($arr);
$val=array_values($arr); 

foreach (ddir(__DIR__,'\.phpt$') as $file) {
    $f = file_get_contents($file);
    file_put_contents($file,preg_replace($key,$val,$f));
}



