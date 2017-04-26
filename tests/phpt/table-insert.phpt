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

?>
--EXPECTF--
<!--SELECT * FROM zz_post  WHERE Id=?  ;1-->
<!--SELECT * FROM zz_user  WHERE Id=?  ;1-->
<!--UPDATE zz_user SET name=?  WHERE Id=?;aaaaaaaaaaaaaa,1-->
////////////////////////////////////
<!--SELECT * FROM zz_user  WHERE Id=?  ;1-->
<!--INSERT INTO zz_post (`text` )VALUES(?),(?),(?);1111,2222,3333-->