--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

echo $conn->sql(Post::class)->delete(true)."\n";
echo $conn->sql(User::class)->delete(true)."\n";  
echo $conn->sql(PostType::class)->delete(true)."\n";  

echo "\n---------------------------\n";
$user1 = $conn->sql(User::class)->insert(['name'=>'a']); 
$user2 = $conn->sql(User::class)->set(['name'=>'b']); 

echo "\n---------------------------\n";
$type1 = $user1->ref(UserType::class)->insert(['name'=>'a']);
$type2 = $user2->ref(UserType::class)->set(['name'=>'b']); 
echo "\n";
 
$type3 = $user1->ref(UserType::class)->set(['name'=>'c']);
$type4 = $user2->ref(UserType::class)->insert(['name'=>'d']);
 

echo "\n---------------------------\n";
$post1 = $user1->ref(Post::class)->insert(['text'=>'a']);
$post2 = $user2->ref(Post::class)->set(['text'=>'b']); 
echo "\n";
 
$post3 = $user1->ref(Post::class)->set(['text'=>'c']);
$post4 = $user2->ref(Post::class)->insert(['text'=>'d']);  
 
echo "\n"; 

print_r([
    'user1'=>(array)$user1??null,
    'user2'=>(array)$user2??null,
    'user_type1'=>(array)$type1??null,
    'user_type2'=>(array)$type2??null,
    'user_type3'=>(array)$type3??null,
    'user_type4'=>(array)$type4??null,
    'user_post1'=>(array)$post1??null,
    'user_post2'=>(array)$post2??null,
    'user_post3'=>(array)$post3??null,
    'user_post4'=>(array)$post4??null,
]);


?>
--EXPECT--
<!--DELETE FROM `zz_post`  WHERE (1);-->
6
<!--DELETE FROM `zz_user`  WHERE (1);-->
3
<!--DELETE FROM `zz_post_type`  WHERE (1);-->
4

---------------------------
<!--INSERT INTO `zz_user` (`name` )VALUES(?);a-->
<!--INSERT INTO `zz_user` (`name` )VALUES(?);b-->

---------------------------
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);a-->
<!--INSERT INTO `zz_user` (`type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `type_id`=?;3,4,3-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);b-->
<!--INSERT INTO `zz_user` (`type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `type_id`=?;4,5,4-->

<!--INSERT INTO `zz_user_type` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;c,3,c-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);d-->
<!--INSERT INTO `zz_user` (`type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `type_id`=?;5,5,5-->

---------------------------
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);a,4-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);b,5-->

<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);c,4-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);d,5-->

Array
(
    [user1] => Array
        (
            [name] => a
            [Id] => 4
            [type_id] => 3
        )

    [user2] => Array
        (
            [name] => b
            [Id] => 5
            [type_id] => 5
        )

    [user_type1] => Array
        (
            [name] => c
            [Id] => 3
        )

    [user_type2] => Array
        (
            [name] => b
            [Id] => 4
        )

    [user_type3] => Array
        (
            [name] => c
            [Id] => 3
        )

    [user_type4] => Array
        (
            [name] => d
            [Id] => 5
        )

    [user_post1] => Array
        (
            [text] => a
            [user_id] => 4
            [Id] => 7
        )

    [user_post2] => Array
        (
            [text] => b
            [user_id] => 5
            [Id] => 8
        )

    [user_post3] => Array
        (
            [text] => c
            [user_id] => 4
            [Id] => 9
        )

    [user_post4] => Array
        (
            [text] => d
            [user_id] => 5
            [Id] => 10
        )

)