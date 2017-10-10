--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');


$conn[User::class]->delete(true);
$conn[UserType::class]->delete(true);
$conn->debug=true;

$a = (array)$conn[User::class][]=['name'=>'s1'];
$b = (array)$conn->sql(User::class)->insert(['name'=>'s2']); 
print_r([$a,$b]);
unset($a);
unset($b);
 
$a = (array)$conn[User::class][UserType::class][]=['name'=>'t1'];
$b = (array)$conn->sql(User::class)->ref(UserType::class)->insert(['name'=>'t2']);
print_r([$a,$b]);
unset($a);
unset($b);


$a = (array)$conn[User::class][0][UserType::class][]=['name'=>'u1'];
$b = (array)$conn->sql(User::class)->get(0)->ref(UserType::class)->insert(['name'=>'u2']);
print_r([$a,$b]);
unset($a);
unset($b);


$a = (array)$conn[User::class][0][Post::class][]=['text'=>'v1'];
$b = (array)$conn->sql(User::class)->get(0)->ref(Post::class)->insert(['text'=>'v2']);
print_r([$a,$b]);
unset($a);
unset($b);
 


?>
--EXPECT--
<!--INSERT INTO `zz_user` (`name` )VALUES(?);s1-->
<!--INSERT INTO `zz_user` (`name` )VALUES(?);s2-->
Array
(
    [0] => Array
        (
            [name] => s1
        )

    [1] => Array
        (
            [name] => s2
            [Id] => 5
        )

)
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);t1-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);t2-->
Array
(
    [0] => Array
        (
            [name] => t1
        )

    [1] => Array
        (
            [name] => t2
            [Id] => 4
        )

)
<!--SELECT * FROM `zz_user`    LIMIT 1;-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);u1-->
<!--INSERT INTO `zz_user` (`type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `type_id`=?;5,4,5-->
<!--SELECT * FROM `zz_user`    LIMIT 1;-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);u2-->
<!--INSERT INTO `zz_user` (`type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `type_id`=?;6,4,6-->
Array
(
    [0] => Array
        (
            [name] => u1
        )

    [1] => Array
        (
            [name] => u2
            [Id] => 6
        )

)
<!--SELECT * FROM `zz_user`    LIMIT 1;-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);v1,4-->
<!--SELECT * FROM `zz_user`    LIMIT 1;-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);v2,4-->
Array
(
    [0] => Array
        (
            [text] => v1
        )

    [1] => Array
        (
            [text] => v2
            [user_id] => 4
            [Id] => 8
        )

)