--TEST--  
--FILE--
<?php 
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

$user = $conn->sql(User::class,'Id')->first(2); 
$user->delete();
$user->ref(Post::class,'Id',['user_id'=>'Id'])->delete([
    //'text'=>'789',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->delete([
    //'text'=>'123',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->delete([
    //'text'=>'456',
]);
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->delete([
   // 'name'=>'UserType1',
]); 
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->delete([
   // 'name'=>'UserType2',
]); 

?>
--EXPECTF--
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 2 ;-->
<!--DELETE FROM `zz_user`  WHERE (`Id`=?);3-->
<!--DELETE FROM `zz_post`  WHERE (`user_id`=?);3-->
<!--DELETE FROM `zz_post`  WHERE (`user_id`=?);3-->
<!--DELETE FROM `zz_post`  WHERE (`user_id`=?);3-->
<!--DELETE FROM `zz_user_type`  WHERE (`Id`=?);2-->
<!--DELETE FROM `zz_user_type`  WHERE (`Id`=?);2-->