--TEST--  
--FILE--
<?php 
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

$user = $conn->sql(User::class,'Id')->first(2); 

$user->ref(Post::class,'Id',['user_id'=>'Id'])->insert([
    'text'=>'789',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->insert([
    'text'=>'123',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->insert([
    'text'=>'456',
]);
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->insert([
    'name'=>'UserType1',
]); 
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->insert([
    'name'=>'UserType2',
]); 

?>
--EXPECTF--
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 2 ;-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);789,3-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);123,3-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);456,3-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);UserType1-->
<!--INSERT INTO `zz_user` (`type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `type_id`=?;3,3,3-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);UserType2-->
<!--INSERT INTO `zz_user` (`type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `type_id`=?;4,3,4-->