--TEST--  
--FILE--
<?php 
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

$user = $conn->sql(User::class,'Id')->first(2); 

$user->ref(Post::class,'Id',['user_id'=>'Id'])->update([
    'text'=>'789',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->update([
    'text'=>'123',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->update([
    'text'=>'456',
]);
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->update([
    'name'=>'UserType1',
]); 
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->update([
    'name'=>'UserType2',
]); 

?>
--EXPECTF--    
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 2 ;-->
<!--UPDATE `zz_post` SET `text`=?  WHERE (`user_id`=?);789,3-->
<!--UPDATE `zz_post` SET `text`=?  WHERE (`user_id`=?);123,3-->
<!--UPDATE `zz_post` SET `text`=?  WHERE (`user_id`=?);456,3-->
<!--UPDATE `zz_user_type` SET `name`=?  WHERE (`Id`=?);UserType1,2-->
<!--UPDATE `zz_user_type` SET `name`=?  WHERE (`Id`=?);UserType2,2-->