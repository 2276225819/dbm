--TEST--
关联测试2
TODO:关联查询父级where默认删除条件重新查询
--FILE--
<?php
include __DIR__.'/../before.php';


$conn = new \dbm\Connect('mysql:dbname=test','root','root');

$conn->debug=true;
foreach ($conn->sql(User::class)->where("name like ?","%u%") as $user) {
	echo "USER:".$user['name']."\n";  
	foreach ($user[Post::class]->where('post_type_id!=3') as $post) { 
		echo " ui:{$post['user_id']}   type:{$post['post_type_id']}  txt:{$post['text']}\n";
		echo "  ".$post[PostType::class]['name']."\n"; 
	}  
} 



?>
--EXPECT--
SELECT * FROM zz_user  WHERE name like ?  Array
(
    [0] => %u%
)

USER:u1
SELECT * FROM zz_post  WHERE post_type_id!=3 AND  user_id in (?,?,?)  Array
(
    [0] => 1
    [1] => 2
    [2] => 3
)

 ui:1   type:1  txt:text1
SELECT * FROM zz_post_type  WHERE  Id in (?,?)  Array
(
    [0] => 1
    [1] => 2
)

  type1
 ui:1   type:1  txt:text2
  type1
 ui:1   type:1  txt:text3
  type1
USER:u2
USER:u3
 ui:3   type:2  txt:post32
  type2
 ui:3   type:1  txt:post31
  type1