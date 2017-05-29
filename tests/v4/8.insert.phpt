--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
$conn->debug=true;

echo $conn->sql(Post::class)->delete(true)."\n";   
echo $conn->sql(User::class)->delete(true)."\n";  


echo "\n#sql[User::class]->insertMulit\n";
$i = $conn->sql(User::class)->insertMulit([
	['name'=>'u1'],  
	['name'=>'u2'] 
]);
echo $i;

echo "\n#sql[Post::class]->insertMulit\n";
$i = $conn->sql(Post::class)->insert( 
	['text'=>'insertMulit','user_id'=>'1','post_type_id'=>1]
)->toArray();
print_r($i);



echo "\n#sql[User::class](5)[Post::class]->insert\n";
$conn->sql(User::class)->load(5)->ref(Post::class)->insert([ 
	'text'=>'insertUser' ,'post_type_id'=>1
]); 
try{ 
echo "\n#sql[Post::class][0][PostType::class]->insert\n";
$conn->sql(Post::class)->get(0)->ref(PostType::class)->insert([
	'name'=>'type new'
]); 
}catch(Throwable $e){
	echo $e->getMessage();
}


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
<!--DELETE FROM `zz_post` ;-->
6
<!--DELETE FROM `zz_user` ;-->
3

#sql[User::class]->insertMulit
<!--INSERT INTO `zz_user` (`name` )VALUES(?),(?);u1,u2-->
2
#sql[Post::class]->insertMulit
<!--INSERT INTO `zz_post` (`text`,`user_id`,`post_type_id` )VALUES(?,?,?);insertMulit,1,1-->
Array
(
    [text] => insertMulit
    [user_id] => 1
    [post_type_id] => 1
    [Id] => 7
)

#sql[User::class](5)[Post::class]->insert
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;5-->
<!--INSERT INTO `zz_post` (`text`,`post_type_id`,`user_id` )VALUES(?,?,?);insertUser,1,5-->

#sql[Post::class][0][PostType::class]->insert
<!--SELECT * FROM `zz_post`    LIMIT 1;-->
<!--INSERT INTO `zz_post_type` (`name`,`Id` )VALUES(?,?);type new,1-->
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'PRIMARY'
---------------
<!--SELECT * FROM `zz_post`   ;-->
{"Id":"7","post_type_id":"1","user_id":"1","text":"insertMulit"}
{"Id":"8","post_type_id":"1","user_id":"5","text":"insertUser"}
<!--SELECT * FROM `zz_post_type`   ;-->
{"Id":"1","name":"type1"}
{"Id":"2","name":"type2"}
{"Id":"3","name":"type3"}
{"Id":"4","name":"nn"}
---------------
