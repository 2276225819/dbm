--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

//<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 2 ;-->
//<!--SELECT * FROM `zz_user_type`  WHERE `Id`=?  ;2-->
print_r($conn->sql(User::class)->get(2)->ref(UserType::class)->all(function($m){return (array)$m;}));

//<!--SELECT * FROM `zz_user`  WHERE `id` =2 or `id`=3  ;-->
print_r($uids = $conn->sql('zz_user')->where('id =2 or `id`=3')->all('Id'));

//<!--SELECT * FROM `zz_post`  WHERE  `user_id` in (?,?)   ;2,3-->
print_r($conn->sql('zz_post','Id')->where(['user_id'=>$uids])->keypair('Id',function($m){return (array)$m;}));

//<!--SELECT * FROM `zz_post`  WHERE  `user_id` in (?,?)   ;2,3-->
print_r($conn->sql('zz_post')->where(['user_id'=>$uids])->keypair('Id','text'));



?>
--EXPECT--
<!--SELECT * FROM `zz_user`   ;-->
<!--SELECT * FROM `zz_user_type`  WHERE (`Id` in (?,?) )  ;1,2-->
Array
(
    [0] => Array
        (
            [Id] => 2
            [name] => ty21
        )

)
<!--SELECT * FROM `zz_user`  WHERE (`id` =2 or `id`=3)  ;-->
Array
(
    [0] => 2
    [1] => 3
)
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (?,?) )  ;2,3-->
Array
(
    [4] => Array
        (
            [Id] => 4
            [post_type_id] => 3
            [user_id] => 2
            [text] => user2 22
        )

    [5] => Array
        (
            [Id] => 5
            [post_type_id] => 2
            [user_id] => 3
            [text] => post32
        )

    [6] => Array
        (
            [Id] => 6
            [post_type_id] => 1
            [user_id] => 3
            [text] => post31
        )

)
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (?,?) )  ;2,3-->
Array
(
    [4] => user2 22
    [5] => post32
    [6] => post31
)