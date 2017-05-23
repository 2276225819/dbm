--TEST--

--FILE--
<?php  
include __DIR__."/../before.v4.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

//<!--SELECT * FROM `zz_user`  WHERE `Id`=?  ;3-->
print_r($conn[User::class]->load(3));
print_r($conn->session(User::class)->load(3));

//<!--SELECT * FROM `zz_friend`  WHERE `uid1`=? AND `uid2`=?   LIMIT 1;1,2-->
print_r($conn[Friend::class]->load(1,2));
print_r($conn->session(Friend::class)->load(2,3));

//SELECT * FROM `zz_post`  WHERE `user_id`=1 AND 1=? and 2=2 AND  `post_type_id` in (?,?,?)   ;1,2,3,4
echo $conn->session(Post::class)->and('user_id=1')->and('1=? and 2=2',1)->and(['post_type_id'=>[2,3,4]])."\n";

//<!--SELECT name FROM `zz_post_type`  WHERE name like ?  ;%type%-->
print_r($conn->session(PostType::class)->field('name')->and('name like ?','%type%')->all());

//SELECT * FROM `zz_post`   ORDER BY id desc  LIMIT 2;
echo ($conn->session(Post::class)->order('id desc')->limit(2));

?>
--EXPECT--
<!--SELECT * FROM `zz_user`  WHERE `Id`=?  ;3-->
User Object
(
    [Id] => 3
    [name] => u3
    [type_id] => 2
)
<!--SELECT * FROM `zz_user`  WHERE `Id`=?  ;3-->
User Object
(
    [Id] => 3
    [name] => u3
    [type_id] => 2
)
<!--SELECT * FROM `zz_friend`  WHERE `uid1`=? AND `uid2`=?  ;1,2-->
Friend Object
(
    [uid1] => 1
    [uid2] => 2
    [nickname] => 1->2
)
<!--SELECT * FROM `zz_friend`  WHERE `uid1`=? AND `uid2`=?  ;2,3-->
Friend Object
(
    [uid1] => 2
    [uid2] => 3
    [nickname] => 2->3
)
SELECT * FROM `zz_post`  WHERE `user_id`=1 AND 1=? and 2=2 AND  `post_type_id` in (?,?,?)   ;1,2,3,4
<!--SELECT name FROM `zz_post_type`  WHERE name like ?  ;%type%-->
Array
(
    [0] => PostType Object
        (
            [name] => type1
        )

    [1] => PostType Object
        (
            [name] => type2
        )

    [2] => PostType Object
        (
            [name] => type3
        )

)
SELECT * FROM `zz_post`   ORDER BY id desc  LIMIT 2;