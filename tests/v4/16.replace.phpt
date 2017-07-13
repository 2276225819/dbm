--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
$conn->debug=true;

$conn->sql('zz_user')->replace([
    'name'=>'1'
]);

$conn->sql('zz_user')->replace([
    'name'=>'1'
]);

$conn->sql('zz_user')->replace([
    'Id'=>4, 'name'=>'2'
]);

?>
--EXPECT-- 
<!--REPLACE zz_user SET `name`=?;1-->
<!--REPLACE zz_user SET `name`=?;1-->
<!--REPLACE zz_user SET `Id`=?,`name`=?;4,2-->