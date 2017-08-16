--TEST--


--FILE--
<?php
include __DIR__."/../before.php";
 
$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
$conn->sql('zz_user_type')->where('1=1')->delete();
$conn->debug=true; 

$user = $conn->sql(User::class)->get();
 
$a=$user->ref('zz_user_type',['Id'],['Id'=>'type_id'])->insert([
    'name'=>'aa',
]);
if(function_exists('xdebug_disable')){
    xdebug_disable();
}
try{ 
    $b=$user[UserType::class]->insert([
        'name'=>'bb',
    ]); //💩💩💩💩💩 
}catch(\Throwable $e){ }
if(function_exists('xdebug_enable')){
    xdebug_enable();
}
print_r([$a??'',$b??'']);

 
$sql = $user->ref('zz_user_type',['Id'],['Id'=>'type_id'])->set([
    'name'=>'b',
]); 
$sql = $user[UserType::class]->set([
    'name'=>'c',
]);  


echo "\n";


$sql = $user->ref('zz_post',['Id'],['user_id'=>'Id'])->insert([
    'text'=>'u1t1'
]);
$sql = $user[Post::class]->insert([
    'text'=>'u1t2'
]);
 

$sql = $user->ref('zz_post',['Id'],['user_id'=>'Id'])->set([
    'text'=>'u1t1'
]);
$sql = $user[Post::class]->set([
    'text'=>'u1t2'
]);




?>
--EXPECTF--  
<!--SELECT * FROM `zz_user`   ;-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);aa-->
<!--INSERT INTO `zz_user` (`type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `type_id`=?;3,1,3-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);bb-->
<!--INSERT INTO `zz_user` (`type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `type_id`=?;4,1,4-->
Array
(
    [0] => dbm\Collection Object
        (
            [name] => aa
            [Id] => 3
        )

    [1] => UserType Object
        (
            [name] => bb
            [Id] => 4
        )

)
<!--INSERT INTO `zz_user_type` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;b,4,b-->
<!--INSERT INTO `zz_user_type` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;c,4,c-->

<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);u1t1,1-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);u1t2,1-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);u1t1,1-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);u1t2,1-->