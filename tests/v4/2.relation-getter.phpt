--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

//<!--SELECT * FROM `zz_post`  WHERE `user_id` in (SELECT Id FROM `zz_user`   )  ;-->
$a=$conn[User::class][Post::class]['text']; 
$b=$conn->sql('zz_user','Id')->ref('zz_post','Id',['user_id'=>'Id'])->val('text');  
print_r([$a,$b]); 

//<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;1-->
//<!--SELECT * FROM `zz_post`  WHERE `user_id`=?  ;1--> 
$a=$conn[User::class](1)[Post::class]['text']; 
$b=$conn->sql('zz_user','Id')->load(1)->ref('zz_post','Id',['user_id'=>'Id'])->val('text'); 
print_r([$a,$b]);

//<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
//<!--SELECT * FROM `zz_post`  WHERE `user_id`=?  ;2-->
$a=$conn[User::class][1][Post::class]['text']; 
$b=$conn->sql('zz_user','Id')->get(1)->ref('zz_post','Id',['user_id'=>'Id'])->val('text'); 
print_r([$a,$b]); 

$a=$conn->sql('zz_user','Id')->find(2)
        ->ref('zz_post','Id',['user_id'=>'Id'])->limit(1,0)
        ->val('text');  
$b=$conn->sql('zz_user','Id')->find(3)
        ->ref('zz_post','Id',['user_id'=>'Id'])->limit(1,1)
        ->val('text'); 
print_r([$a,$b]);
 
?>
--EXPECT-- 
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ))  ;-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ))  ;-->
Array
(
    [0] => text1
    [1] => text1
)
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;1-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id`=?)  ;1-->
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;1-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id`=?)  ;1-->
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
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`  WHERE (`Id`=?)  ))   LIMIT 1;2-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`  WHERE (`Id`=?)  ))   LIMIT 1 OFFSET 1 ;3-->
Array
(
    [0] => user2 22
    [1] => post31
)