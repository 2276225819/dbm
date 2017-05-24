--TEST--

--FILE--
<?php  
include __DIR__."/../before.v4.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

echo $conn->model(Post::class)->delete(true)."\n";
echo $conn->model(User::class)->delete(true)."\n";

echo "\n#sql[Post::class]->insert\n";
$conn->model(Post::class)->insert([
	'text'=>'insert','user_id'=>'1' 
]);
echo "\n#sql[Post::class]->insertMulit\n";
$conn->model(Post::class)->insertMulit([
	['text'=>'insertMulit','user_id'=>'1','post_type_id'=>1], 
]);


echo "\n#sql[User::class](2)[Post::class]->insert\n";
$conn->model(User::class)->load(2)->ref(Post::class)->insert([ 
	'text'=>'insertUser' ,'post_type_id'=>1
]); 

echo "\n#sql[Post::class][0][PostType::class]->insert\n";
$conn->model(Post::class)->get(0)->ref(PostType::class)->insert([
	'name'=>'type new'
]); 


echo "\n---------------\n";
$conn[Post::class]->map(function(Post $value){
	echo json_encode($value); echo "\n"; 
});
$conn[PostType::class]->map(function(PostType $value){
	echo json_encode($value); echo "\n"; 
});
echo "---------------\n\n";


echo "#sql[Post::class](7)[PostType::class]->update\n";
$conn->model(Post::class)->load(7)->ref(PostType::class)->update([
	'name'=>'type5'
]); 

echo "\n---------------\n";
$conn[Post::class]->map(function(Post $value){
	echo json_encode($value); echo "\n"; 
});
$conn[PostType::class]->map(function(PostType $value){
	echo json_encode($value); echo "\n"; 
}); 
echo "---------------\n\n";

?>
--EXPECT-- 