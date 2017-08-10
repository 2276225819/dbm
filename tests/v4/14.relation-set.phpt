--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
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
    'user1'=>$user1??null,
    'user2'=>$user2??null,
    'user_type1'=>$type1??null,
    'user_type2'=>$type2??null,
    'user_type3'=>$type3??null,
    'user_type4'=>$type4??null,
    'user_post1'=>$post1??null,
    'user_post2'=>$post2??null,
    'user_post3'=>$post3??null,
    'user_post4'=>$post4??null,
]);


?>
--EXPECT--
<!--DELETE FROM `zz_post` ;-->
6
<!--DELETE FROM `zz_user` ;-->
3
<!--DELETE FROM `zz_post_type` ;-->
4

---------------------------
<!--INSERT INTO `zz_user` (`name` )VALUES(?);a-->
<!--INSERT INTO `zz_user` (`name` )VALUES(?);b-->

---------------------------
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);a-->
<!--UPDATE `zz_user` SET `type_id`=?  WHERE (`Id`=?);3,4-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);b-->
<!--UPDATE `zz_user` SET `type_id`=?  WHERE (`Id`=?);4,5-->

<!--UPDATE `zz_user_type` SET `name`=?  WHERE (`Id`=?);c,3-->
<!--INSERT INTO `zz_user_type` (`name` )VALUES(?);d-->
<!--UPDATE `zz_user` SET `type_id`=?  WHERE (`Id`=?);5,5-->

---------------------------
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);a,4-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);b,5-->

<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);c,4-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);d,5-->

Array
(
    [user1] => User Object
        (
            [name] => a
            [Id] => 4
            [type_id] => 3
        )

    [user2] => User Object
        (
            [name] => b
            [Id] => 5
            [type_id] => 5
        )

    [user_type1] => UserType Object
        (
            [name] => c
            [Id] => 3
        )

    [user_type2] => UserType Object
        (
            [name] => b
            [Id] => 4
        )

    [user_type3] => UserType Object
        (
            [name] => c
            [Id] => 3
        )

    [user_type4] => UserType Object
        (
            [name] => d
            [Id] => 5
        )

    [user_post1] => Post Object
        (
            [text] => a
            [user_id] => 4
            [Id] => 7
        )

    [user_post2] => Post Object
        (
            [text] => b
            [user_id] => 5
            [Id] => 8
        )

    [user_post3] => Post Object
        (
            [text] => c
            [user_id] => 4
            [Id] => 9
        )

    [user_post4] => Post Object
        (
            [text] => d
            [user_id] => 5
            [Id] => 10
        )

)