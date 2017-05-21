--TEST--


--FILE--
<?php
include __DIR__."/../before.php";
 
$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->sql('zz_user_type')->where('1=1')->delete();
$conn->debug=true; 

$user = $conn->sql(User::class)->get();

try{
    $a=$user->ref('zz_user_type',['Id'],['Id'=>'type_id'])->insert([
        'name'=>'aa',
    ]);
    $b=$user[UserType::class]->insert([
        'name'=>'bb',
    ]); 
}catch(Throwable $e){  
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
<!--INSERT INTO `zz_user_type` SET `name`=?,`Id`=?;aa,1-->
<!--INSERT INTO `zz_user_type` SET `name`=?,`Id`=?;bb,1-->
Array
(
    [0] => dbm\Entity Object
        (
            [name] => aa
            [Id] => 1
        )

    [1] => 
)
<!--SELECT * FROM `zz_user_type`  WHERE `Id`=?  ;1-->
<!--UPDATE `zz_user_type` SET `name`=?  WHERE `Id`=?;b,1-->
<!--UPDATE `zz_user_type` SET `name`=?  WHERE `Id`=?;c,1-->

<!--INSERT INTO `zz_post` SET `text`=?,`user_id`=?;u1t1,1-->
<!--INSERT INTO `zz_post` SET `text`=?,`user_id`=?;u1t2,1-->
<!--INSERT INTO `zz_post` SET `user_id`=?,`text`=?;1,u1t1-->
<!--INSERT INTO `zz_post` SET `user_id`=?,`text`=?;1,u1t2-->  