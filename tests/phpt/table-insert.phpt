--TEST--


--FILE--
<?php
include __DIR__."/../before.php";


$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

$post = $conn->load(Post::class,1); 
$user = $post[User::class]->fetch();
$user['name']='aaaaaaaaaaaaaa';
$user->save();
 

echo "////////////////////////////////////\n";

$user = $conn->load(User::class,1);  
$post = $user[Post::class]->insertMulit([
    [ 'text'=>1111],
    [ 'text'=>2222],
    [ 'text'=>3333],
]); 

echo "////////////////////////////////////\n";

$pt = new PostType($conn);
$pt['name']='aaa';
$pt->create();

print_r($pt->toArray());

$post = $pt[Post::class]->insert(['text'=>'pp','user_id'=>$user['Id']]);
print_r($post->toArray());

?>
--EXPECTF-- 
<!--SELECT * FROM zz_post  WHERE Id=?  ;1-->
<!--SELECT * FROM zz_user  WHERE Id=?  ;1-->
<!--UPDATE zz_user SET name=?  WHERE Id=?;aaaaaaaaaaaaaa,1-->
////////////////////////////////////
<!--SELECT * FROM zz_user  WHERE Id=?  ;1-->
<!--INSERT INTO zz_post (`text`,`user_id` )VALUES(?,?),(?,?),(?,?);1111,1,2222,1,3333,1-->
////////////////////////////////////
<!--INSERT INTO zz_post_type SET name=?;aaa-->
Array
(
    [name] => aaa
    [Id] => 5
)
<!--SELECT * FROM zz_post_type  WHERE Id=?  ;5-->
<!--INSERT INTO zz_post SET text=?,user_id=?,post_type_id=?;pp,1,5-->
Array
(
    [text] => pp
    [user_id] => 1
    [post_type_id] => 5
    [Id] => 10
)