--TEST--


--FILE--
<?php
include __DIR__."/../before.php";
 
$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;
 
$user = $conn->sql(User::class)->get(0);

try{
    $a=$user->one('zz_user_type','Id','type_id')->insert([
        'name'=>'aa',
    ]);
    // $b=$user[UserType::class]->set([
    //     'name'=>'aa',
    // ]); 
}catch(Throwable $e){  
} 
print_r([$a??'',$b??'']);

  
$sql = $user->one('zz_user_type','Id','type_id')->set([
    'name'=>'b',
]); 
$sql = $user[UserType::class]->set([
    'name'=>'c',
]);  
echo "\n";


$sql = $user->many('zz_post','Id','user_id')->insert([
    'text'=>'u1t1'
]);
$sql = $user[Post::class]->insert([
    'text'=>'u1t2'
]);
 

$sql = $user->many('zz_post','Id','user_id')->set([
    'text'=>'u1t1'
]);
$sql = $user[Post::class]->set([
    'text'=>'u1t2'
]);




?>
--EXPECTF--  
<!--SELECT * FROM zz_user   ;-->
<!--INSERT INTO zz_user_type SET name=?,Id=?;aa,-->
<!--UPDATE zz_user SET type_id=?  WHERE Id=?;1,1-->
Array
(
    [0] => dbm\Model Object
        (
            [name] => aa
            [Id] => 1
        )

    [1] => 
)
<!--SELECT * FROM zz_user_type  WHERE Id=?  ;1-->
<!--UPDATE zz_user_type SET name=?  WHERE Id=?;b,1-->
<!--UPDATE zz_user_type SET name=?  WHERE Id=?;c,1-->

<!--INSERT INTO zz_post SET text=?,user_id=?;u1t1,1-->
<!--INSERT INTO zz_post SET text=?,user_id=?;u1t2,1-->
<!--INSERT INTO zz_post SET user_id=?,text=?;1,u1t1-->
<!--INSERT INTO zz_post SET user_id=?,text=?;1,u1t2-->  