--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');


$conn[User::class]->delete(true);
$conn[UserType::class]->delete(true);
$conn->debug=true;

$a = $conn[User::class][]=['name'=>'s1'];
$b = $conn->sql(User::class)->insert(['name'=>'s2']); 
print_r([$a,$b]);
 
$a = $conn[User::class][UserType::class][]=['name'=>'t1'];
$b = $conn->sql(User::class)->ref(UserType::class)->insert(['name'=>'t2']);
print_r([$a,$b]);


$a = $conn[User::class][0][UserType::class][]=['name'=>'t1'];
$b = $conn->sql(User::class)->get(0)->ref(UserType::class)->insert(['name'=>'t2']);
print_r([$a,$b]);

$a = $conn[User::class][0][Post::class][]=['text'=>'t1'];
$b = $conn->sql(User::class)->get(0)->ref(Post::class)->insert(['text'=>'t2']);
print_r([$a,$b]);
 


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

    [1] => User Object
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

    [1] => UserType Object
        (
            [name] => t2
            [Id] => 4
        )

)
<!--SELECT * FROM `zz_user`    LIMIT 1;-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);t1-->
<!--UPDATE `zz_user` SET `type_id`=?  WHERE (`Id`=?);5,4-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);t2-->
<!--UPDATE `zz_user` SET `type_id`=?  WHERE (`Id`=?);6,4-->
Array
(
    [0] => Array
        (
            [name] => t1
        )

    [1] => UserType Object
        (
            [name] => t2
            [Id] => 6
        )

)
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);t1,4-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);t2,4-->
Array
(
    [0] => Array
        (
            [text] => t1
        )

    [1] => Post Object
        (
            [text] => t2
            [user_id] => 4
            [Id] => 8
        )

)