--TEST--  
--FILE--
<?php 
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

$user = $conn->sql(User::class,'Id')->first(2);  
$un2 = $user->replace([  'name'=>'__user___name__' ] + (array)$user);

$user->ref(Post::class,'Id',['user_id'=>'Id'])->replace([
    'text'=>'789',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->replace([
    'text'=>'123',
]);
$user->ref(Post::class,'Id',['user_id'=>'Id'])->replace([
    'text'=>'456',
]);
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->replace([
    'name'=>'UserType1',
]); 
$user->ref(UserType::class,'Id',['Id'=>'type_id'])->replace([
    'name'=>'UserType2',
]); 

print_r([
    (array)$user,
    (array)$un2,
])

?>
--EXPECTF--
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 2 ;-->
<!--REPLACE zz_user SET `name`=?,`Id`=?,`type_id`=?;__user___name__,3,2-->
<!--REPLACE zz_post SET `text`=?,`user_id`=?;789,3-->
<!--REPLACE zz_post SET `text`=?,`user_id`=?;123,3-->
<!--REPLACE zz_post SET `text`=?,`user_id`=?;456,3-->
<!--REPLACE zz_user_type SET `name`=?,`Id`=?;UserType1,2-->
<!--REPLACE zz_user_type SET `name`=?,`Id`=?;UserType2,2-->
Array
(
    [0] => Array
        (
            [Id] => 3
            [name] => u3
            [type_id] => 2
        )

    [1] => Array
        (
            [name] => __user___name__
            [Id] => 3
            [type_id] => 2
        )

)