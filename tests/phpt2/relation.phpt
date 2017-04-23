--TEST--
user->post
--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');

//\dbm\Connect::$debug=true;
foreach ($conn->sql(User::class) as $user) {
	echo "USER:".$user['name']."\n"; 
	foreach ($user[Post::class]  as $post) { 
		print_r($post->toArray());
	}  
} 



?>
--EXPECT--
USER:u1
Array
(
    [Id] => 1
    [post_type_id] => 1
    [user_id] => 1
    [text] => text1
)
Array
(
    [Id] => 2
    [post_type_id] => 1
    [user_id] => 1
    [text] => text2
)
Array
(
    [Id] => 3
    [post_type_id] => 1
    [user_id] => 1
    [text] => text3
)
USER:u2
Array
(
    [Id] => 4
    [post_type_id] => 3
    [user_id] => 2
    [text] => user2 22
)
USER:u3
Array
(
    [Id] => 5
    [post_type_id] => 2
    [user_id] => 3
    [text] => post32
)
Array
(
    [Id] => 6
    [post_type_id] => 1
    [user_id] => 3
    [text] => post31
)