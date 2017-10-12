--TEST--  
--FILE--
<?php 
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

$user = $conn->sql(User::class,'Id')->first(2); 

$user->ref(Post::class,'Id',['user_id'=>'Id'])->save([
    'text'=>'789',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->save([
    'text'=>'123',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->save([
    'text'=>'456',
]);
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->save([
    'name'=>'UserType1',
]); 
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->save([
    'name'=>'UserType2',
]); 


$user->ref(Post::class,'Id',['user_id'=>'Id'])->first()->save([
    'text'=>'789',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->first()->save([
    'text'=>'123',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->first()->save([
    'text'=>'456',
]);
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->first()->save([
    'name'=>'UserType1',
]); 
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->first()->save([
    'name'=>'UserType2',
]); 




?>
--EXPECTF--
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 2 ;-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `text`=?,`user_id`=?;789,3,789,3-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `text`=?,`user_id`=?;123,3,123,3-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `text`=?,`user_id`=?;456,3,456,3-->
<!--INSERT INTO `zz_user_type` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;UserType1,2,UserType1-->
<!--INSERT INTO `zz_user_type` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;UserType2,2,UserType2-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id`=?)  ;3-->
<!--INSERT INTO `zz_post` (`text`,`Id`,`user_id` )VALUES(?,?,?) ON DUPLICATE KEY UPDATE `text`=?,`user_id`=?;789,5,3,789,3-->
<!--INSERT INTO `zz_post` (`text`,`Id`,`user_id` )VALUES(?,?,?) ON DUPLICATE KEY UPDATE `text`=?,`user_id`=?;123,5,3,123,3-->
<!--INSERT INTO `zz_post` (`text`,`Id`,`user_id` )VALUES(?,?,?) ON DUPLICATE KEY UPDATE `text`=?,`user_id`=?;456,5,3,456,3-->
<!--INSERT INTO `zz_user_type` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;UserType1,2,UserType1-->
<!--INSERT INTO `zz_user_type` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;UserType2,2,UserType2-->