--TEST--
不能后向兼容
--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

//$cache = $conn->scope();

//<!--SELECT * FROM `zz_user`   ;-->
$a=$conn[User::class]['name'];
$b=$conn->sql('zz_user','Id')['name'];
print_r([$a,$b]);

//<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
$a=$conn[User::class][1]['name'];
$b=$conn->sql('zz_user','Id')->get(1)->val('name');
print_r([$a,$b]);  

//不能后向兼容
//<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ))  ;-->
$a=$conn[User::class][Post::class]['text']; 
$b=$conn->sql('zz_user','Id')->ref('zz_post','Id',['user_id'=>'Id'])->val('text');  
print_r([$a,$b]); 
//不能后向兼容


//<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
//<!--SELECT * FROM `zz_post`  WHERE `user_id`=?  ;2-->
$a=$conn[User::class][1][Post::class]['text']; 
$b=$conn->sql('zz_user','Id')->get(1)->ref('zz_post','Id',['user_id'=>'Id'])->val('text'); 
print_r([$a,$b]);

//<!--SELECT * FROM `zz_user`  WHERE `Id`=?  ;3-->
$a=$conn[User::class]->load(3);
$b=$conn->sql('zz_user','Id')->load(3);
print_r([(array)$a,(array)$b]);
unset($a,$b);

//<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
//<!--SELECT * FROM `zz_post`  WHERE `user_id`=?  ;2-->
$a=$conn[User::class][1][Post::class]->get(); 
$b=$conn->sql('zz_user','Id')->get(1)->ref('zz_post','Id',['user_id'=>'Id'])->get(); 
print_r([(array)$a,(array)$b]);

?>
--EXPECT--

<!--SELECT * FROM `zz_user`   ;-->
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [0] => u1
    [1] => u1
)
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
Array
(
    [0] => u2
    [1] => u2
)
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ))  ;-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ))  ;-->
Array
(
    [0] => text1
    [1] => text1
)
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id`=?)  ;2-->
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id`=?)  ;2-->
Array
(
    [0] => user2 22
    [1] => user2 22
)
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;3-->
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;3-->
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
            [Id] => 3
            [name] => u3
            [type_id] => 2
        )

)
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id`=?)  ;2-->
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id`=?)  ;2-->
Array
(
    [0] => Array
        (
            [Id] => 4
            [post_type_id] => 3
            [user_id] => 2
            [text] => user2 22
        )

    [1] => Array
        (
            [Id] => 4
            [post_type_id] => 3
            [user_id] => 2
            [text] => user2 22
        )

)