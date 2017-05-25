--TEST--

--FILE--
<?php  
include __DIR__."/../before.v4.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

// echo $conn->model(Post::class)->delete(true)."\n";
// echo $conn->model(User::class)->delete(true)."\n";  
// echo $conn->model(PostType::class)->delete(true)."\n";  

// echo "\n[new]#user->save()\n";
// $user = new User;
// $user['name']='new User';
// $user->save();
// print_r($user); 

// echo "\n[1:n]#user[Post]->insert()->save()\n";
// $posts = $user->ref(Post::class);
// $post = $posts->insert(['text'=>'a']); 
// $post['text']='b';
// $post->save(); 

// echo "\n[1:1]#post[PostType]->set()         \n";
// $posttypes = $post->ref(PostType::class,'Id');
// $posttypes->set([ 
// 	'name'=>'type2'
// ]); 
// $posttypes->set([ 
// 	'name'=>'type2 change'
// ]); 

//print_r($post);
// echo "#sql[Post::class](7)[PostType::class]->update\n";
// $conn->model(Post::class)->load(7)->ref(PostType::class)->update([
// 	'name'=>'type5'
// ]); 

// echo "\n---------------\n";
// $conn[Post::class]->map(function(Post $value){
// 	echo json_encode($value); echo "\n"; 
// });
// $conn[PostType::class]->map(function(PostType $value){
// 	echo json_encode($value); echo "\n"; 
// }); 
// echo "---------------\n\n";

?>
--EXPECT--  