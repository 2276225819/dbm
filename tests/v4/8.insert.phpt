--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
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
echo "\n#sql[Post::class][0][PostType::class]->insert\n";
$conn->sql(Post::class)->get(0)->ref(PostType::class)->insert([
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



?>
--EXPECT-- 
<!--DELETE FROM `zz_post`  WHERE (1);-->
6
<!--DELETE FROM `zz_user`  WHERE (1);-->
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
<!--INSERT INTO `zz_post_type` (`name` )VALUES(?);type new-->
<!--INSERT INTO `zz_post` (`post_type_id`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `post_type_id`=?;5,7,5-->

---------------
<!--SELECT * FROM `zz_post`   ;-->
{"Id":"7","post_type_id":"5","user_id":"1","text":"insertMulit"}
{"Id":"8","post_type_id":"1","user_id":"5","text":"insertUser"}
<!--SELECT * FROM `zz_post_type`   ;-->
{"Id":"1","name":"type1"}
{"Id":"2","name":"type2"}
{"Id":"3","name":"type3"}
{"Id":"4","name":"nn"}
{"Id":"5","name":"type new"}
---------------
