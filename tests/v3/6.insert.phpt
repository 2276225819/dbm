--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

echo $conn->sql(Post::class)->delete(true)."\n";

echo "#sql[Post::class]->insert\n";
$conn->sql(Post::class)->insert([
	'text'=>'insert','user_id'=>'1' 
]);
echo "#sql[Post::class]->insertMulit\n";
$conn->sql(Post::class)->insertMulit([
	['text'=>'insertMulit','user_id'=>'1','post_type_id'=>1], 
]);
echo "#sql[User::class](2)[Post::class]->insert\n";
$conn->sql(User::class)->load(2)->ref(Post::class)->insert([ 
	'text'=>'insertUser' ,'post_type_id'=>1
]); 

echo "#sql[Post::class][0][PostType::class]->insert\n";
$conn->sql(Post::class)->get(0)->ref(PostType::class)->insert([
	'name'=>'type new'
]);
echo "---------------\n";
$conn[Post::class]->map(function(Post $value){
	echo json_encode($value); echo "\n"; 
});
$conn[PostType::class]->map(function(PostType $value){
	echo json_encode($value); echo "\n"; 
});
echo "#sql[Post::class](7)[PostType::class]->update\n";
$conn->sql(Post::class)->load(7)->ref(PostType::class)->update([
	'name'=>'type5'
]); 
$conn[Post::class]->map(function(Post $value){
	echo json_encode($value); echo "\n"; 
});
$conn[PostType::class]->map(function(PostType $value){
	echo json_encode($value); echo "\n"; 
}); 

?>
--EXPECT--
<!--DELETE FROM zz_post ;-->
6
#sql[Post::class]->insert
<!--INSERT INTO zz_post SET text=?,user_id=?;insert,1-->
#sql[Post::class]->insertMulit
<!--INSERT INTO zz_post (`text`,`user_id`,`post_type_id` )VALUES(?,?,?);insertMulit,1,1-->
#sql[User::class](2)[Post::class]->insert
<!--SELECT * FROM zz_user  WHERE Id=?  ;2-->
<!--INSERT INTO zz_post SET text=?,post_type_id=?,user_id=?;insertUser,1,2-->
#sql[Post::class][0][PostType::class]->insert
<!--SELECT * FROM zz_post    LIMIT 1;-->
<!--INSERT INTO zz_post_type SET name=?,Id=?;type new,-->
<!--UPDATE zz_post SET post_type_id=?  WHERE Id=?;5,7-->
---------------
<!--SELECT * FROM zz_post   ;-->
{"Id":"7","post_type_id":"5","user_id":"1","text":"insert"}
{"Id":"8","post_type_id":"1","user_id":"1","text":"insertMulit"}
{"Id":"9","post_type_id":"1","user_id":"2","text":"insertUser"}
<!--SELECT * FROM zz_post_type   ;-->
{"Id":"1","name":"type1"}
{"Id":"2","name":"type2"}
{"Id":"3","name":"type3"}
{"Id":"4","name":"nn"}
{"Id":"5","name":"type new"}
#sql[Post::class](7)[PostType::class]->update
<!--SELECT * FROM zz_post  WHERE Id=?  ;7-->
<!--UPDATE zz_post_type SET name=?  WHERE Id=?;type5,5-->
<!--SELECT * FROM zz_post   ;-->
{"Id":"7","post_type_id":"5","user_id":"1","text":"insert"}
{"Id":"8","post_type_id":"1","user_id":"1","text":"insertMulit"}
{"Id":"9","post_type_id":"1","user_id":"2","text":"insertUser"}
<!--SELECT * FROM zz_post_type   ;-->
{"Id":"1","name":"type1"}
{"Id":"2","name":"type2"}
{"Id":"3","name":"type3"}
{"Id":"4","name":"nn"}
{"Id":"5","name":"type5"}