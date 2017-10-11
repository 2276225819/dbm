--TEST-- 
 
--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2', 'root', 'root');
$conn->debug=true;

$user = $conn[User::class][] = new User([
    'name'=>'nUser',
]);
print_r((array)$user);

$obj    = $user[UserType::class] = new UserType([
    'name'=>'nUserType'
]);
print_r((array)$obj);

$obj    = $user[Post::class][] = new Post([
    'text'=>'nPost1',
    // PostType::class => new PostType([ //未实现
    //     'id'=>'2',  'name'=>'b'
    // ]),
]);
print_r((array)$obj);

$obj    = $user[Post::class][] = new Post([
    'text'=>'nPost2',
    // PostType::class => new PostType([ //未实现
    //     'id'=>'2',  'name'=>'b'
    // ]),
]);
print_r((array)$obj);



?>
--EXPECTF--   
<!--REPLACE zz_user SET `name`=?;nUser-->
Array
(
    [name] => nUser
    [Id] => 4
)
<!--REPLACE zz_user_type SET `name`=?;nUserType-->
Array
(
    [name] => nUserType
    [Id] => 3
)
<!--REPLACE zz_post SET `text`=?,`user_id`=?;nPost1,4-->
Array
(
    [text] => nPost1
    [user_id] => 4
    [Id] => 7
)
<!--REPLACE zz_post SET `text`=?,`user_id`=?;nPost2,4-->
Array
(
    [text] => nPost2
    [user_id] => 4
    [Id] => 8
)
