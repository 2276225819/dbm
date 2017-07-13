--TEST--
分组双循环
--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
$conn->debug=true;
  
foreach ($conn[User::class]->where('`id` in (1,3)') as $user) {
	echo "{$user['Id']}:{$user['name']}\n";
    echo count($user[Post::class]->and('id>1')->all());
	echo "\n";
}


?>
--EXPECT-- 
<!--SELECT * FROM `zz_user`  WHERE (`id` in (1,3))  ;-->
1:u1
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (?,?) ) AND (`id`>1)  ;1,3-->
2
3:u3
2