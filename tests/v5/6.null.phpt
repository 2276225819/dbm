--TEST--  
--FILE--
<?php 
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;
 
$user = $conn->sql(User::class,'Id')->first() ; 
echo (json_encode($user)."\n");

$user->save(['name'=>null]);

echo (json_encode($user)."\n");

$user['type_id'] = null ;

echo (json_encode($user)."\n");


?> 
--EXPECTF-- 
<!--SELECT * FROM `zz_user`    LIMIT 1;-->
{"Id":"1","name":"u1","type_id":"1"}
<!--INSERT INTO `zz_user` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;,1,-->
{"Id":"1","name":null,"type_id":"1"}
<!--INSERT INTO `zz_user` (`type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `type_id`=?;,1,-->
{"Id":"1","name":null,"type_id":null}