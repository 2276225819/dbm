--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

foreach ($conn->sql('zz_user','Id')->where('`id` in (1,3)') as $user) {
	echo $user->val('Id').":".$user->val('name')."\n"; 
	foreach ($user->ref('zz_post','Id',['user_id'=>'Id']) as $post) {
		echo "\tPOST:".$post->ref('zz_post_type','Id',['Id'=>'post_type_id'])->val('name');
		echo "\t".$post->val('text');
		echo "\n";
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
