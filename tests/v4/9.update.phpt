--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
$conn->debug=true;

echo $conn->sql(Post::class)->delete(true)."\n";
echo $conn->sql(User::class)->delete(true)."\n";  
echo $conn->sql(PostType::class)->delete(true)."\n";  

echo "\n[new]#user->save()\n";
$user = new User;
$user['name']='new User';
$user->save();
print_r($user); 

echo "\n[1:n]#user[Post]->save(),#post->save()\n";
$posts = $user->ref(Post::class);
$post = $posts->set(['text'=>'a']); 
$post['text']='b';
$post->save(); 
print_r($post); 

echo "\n[1:n]#posts[PostType]->save()\n";  
$ptype = $post->ref(PostType::class)->save(['name'=>'a']);  
print_r($post); 
print_r($ptype);  

?>
--EXPECT--
<!--DELETE FROM `zz_post` ;-->
6
<!--DELETE FROM `zz_user` ;-->
3
<!--DELETE FROM `zz_post_type` ;-->
4

[new]#user->save()
<!--INSERT INTO `zz_user` (`name` )VALUES(?);new User-->
User Object
(
    [name] => new User
    [Id] => 4
)

[1:n]#user[Post]->save(),#post->save()
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);a,4-->
<!--UPDATE `zz_post` SET `text`=?  WHERE (`user_id`=? AND `Id`=?);b,4,7-->
Post Object
(
    [text] => b
    [user_id] => 4
    [Id] => 7
)

[1:n]#posts[PostType]->save()
<!--INSERT INTO `zz_post_type` (`name` )VALUES(?);a-->
<!--UPDATE `zz_post` SET `post_type_id`=?  WHERE (`user_id`=? AND `Id`=?);5,4,7-->
Post Object
(
    [text] => b
    [user_id] => 4
    [Id] => 7
    [post_type_id] => 5
)
PostType Object
(
    [name] => a
    [Id] => 5
)