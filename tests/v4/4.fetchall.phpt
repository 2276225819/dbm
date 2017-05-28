--TEST--

--FILE--
<?php  
include __DIR__."/../before.v4.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;
 
//<!--SELECT Id,name FROM `zz_user`   ;-->
print_r($conn->model(User::class)->field(['Id','name'])->all());

//<!--SELECT Id as `id`,name FROM `zz_user`  WHERE (`id` =2 or `id`=3)  ;-->
print_r($conn->model('zz_user','id')->field('Id as id,name')->whereAnd('id =2 or `id`=3')->all());

//<!--SELECT text FROM `zz_post`  WHERE (`id` in (?,?) )  ;1,3-->
print_r($conn->model('zz_post')->field('text')->whereAnd(['id'=>[1,3]])->all());

//<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`  WHERE (`Id`=?)  ))  ;2-->
print_r($conn->model(Post::class)->whereAnd([
    'user_id' => $conn->model('zz_user')->whereAnd(['Id'=>2])
])->all());

?>
--EXPECT--
<!--SELECT Id,name FROM `zz_user`   ;-->
Array
(
    [0] => User Object
        (
            [Id] => 1
            [name] => u1
        )

    [1] => User Object
        (
            [Id] => 2
            [name] => u2
        )

    [2] => User Object
        (
            [Id] => 3
            [name] => u3
        )

)
<!--SELECT Id as `id`,name FROM `zz_user`  WHERE (`id` =2 or `id`=3)  ;-->
Array
(
    [0] => dbm\Model Object
        (
            [id] => 2
            [name] => u2
        )

    [1] => dbm\Model Object
        (
            [id] => 3
            [name] => u3
        )

)
<!--SELECT text FROM `zz_post`  WHERE (`id` in (?,?) )  ;1,3-->
Array
(
    [0] => dbm\Model Object
        (
            [text] => text1
        )

    [1] => dbm\Model Object
        (
            [text] => text3
        )

)
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`  WHERE (`Id`=?)  ))  ;2-->
Array
(
    [0] => Post Object
        (
            [Id] => 4
            [post_type_id] => 3
            [user_id] => 2
            [text] => user2 22
        )

)