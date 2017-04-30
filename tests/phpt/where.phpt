--TEST--

--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;


$conn->sql('user')->all();
$conn->sql('user')->where('1=1 and 1=1')->where('1=1')->all(); 
$conn->sql('user')->and('1=1 and 1=1')->and('1=1')->all(); 
$conn->sql('user')->or('1=1 and 1=1')->or('1=1')->all(); 


$conn->sql('user')->where('1=1')->and('2=2')->and('3=3')->or('4=4')->or('5=5')->or('6=6')->all();

?>
--EXPECT--
<!--SELECT * FROM zz_user   ;-->
<!--SELECT * FROM zz_user  WHERE 1=1  ;-->
<!--SELECT * FROM zz_user  WHERE 1=1 and 1=1 AND 1=1  ;-->
<!--SELECT * FROM zz_user  WHERE 1=1 and 1=1 OR 1=1  ;-->
<!--SELECT * FROM zz_user  WHERE 1=1 AND 2=2 AND 3=3 OR 4=4 OR 5=5 OR 6=6  ;-->