--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
$conn->debug=true;

foreach ($conn[User::class]->where('`id` in (1,3)') as $user) {
	echo "{$user['Id']}:{$user['name']}\n";
	foreach ($user[Post::class] as $post) {
		echo "\tPOST:{$post[PostType::class]['name']}\t{$post['text']}\n"; 
	}
	echo "\n";
}


?>
--EXPECT--
<!--SELECT * FROM `zz_user`  WHERE (`id` in (1,3))  ;-->
1:u1
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (?,?) )  ;1,3-->
<!--SELECT * FROM `zz_post_type`  WHERE (`Id` in (?,?) )  ;1,2-->
	POST:type1	text1
	POST:type1	text2
	POST:type2	text3

3:u3
	POST:type2	post32
	POST:type1	post31
