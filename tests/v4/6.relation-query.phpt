--TEST--

--FILE--
<?php  
include __DIR__."/../before.v4.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

//<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`  WHERE (`Id`=?)  )) AND (`post_type_id`=?)  ;1,1-->
print_r($conn->model('zz_user','Id')->find(1)
        ->ref('zz_post','Id',['user_id'=>'Id'])->whereAnd('post_type_id=?',"1")
        ->all());

//<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;1-->
//<!--SELECT * FROM `zz_post`  WHERE (`user_id`=?) AND (`post_type_id`=?)  ;1,1-->
print_r($conn->model('zz_user','Id')->load(1)
        ->ref('zz_post','Id',['user_id'=>'Id'])->whereAnd('post_type_id=?',"1")
        ->all());

//<!--SELECT * FROM `zz_user`  WHERE (`Id` in (SELECT user_id FROM `zz_post`  WHERE (`id` in(3,4))  ))  ;-->
print_r($conn->model(Post::class)->where('id in(3,4)')
        ->ref(User::class)->map(function(User $u){
            return "{$u['Id']}:{$u['name']}";
        }));

?>
--EXPECT--
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`  WHERE (`Id`=?)  )) AND (`post_type_id`=?)  ;1,1-->
Array
(
    [0] => dbm\Model Object
        (
            [Id] => 1
            [post_type_id] => 1
            [user_id] => 1
            [text] => text1
        )

    [1] => dbm\Model Object
        (
            [Id] => 2
            [post_type_id] => 1
            [user_id] => 1
            [text] => text2
        )

)
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;1-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id`=?) AND (`post_type_id`=?)  ;1,1-->
Array
(
    [0] => dbm\Model Object
        (
            [Id] => 1
            [post_type_id] => 1
            [user_id] => 1
            [text] => text1
        )

    [1] => dbm\Model Object
        (
            [Id] => 2
            [post_type_id] => 1
            [user_id] => 1
            [text] => text2
        )

)
<!--SELECT * FROM `zz_user`  WHERE (`Id` in (SELECT user_id FROM `zz_post`  WHERE (`id` in(3,4))  ))  ;-->
Array
(
    [0] => 1:u1
    [1] => 2:u2
)