--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

foreach ($conn[Post::class] as  $value) {
	echo json_encode($value); echo "\n";
}

$a[]= $conn[Post::class]->count(1);
$a[]= $conn[Post::class]->sum('Id');
$a[]= $conn[Post::class]->avg('Id');
$a[]= $conn[Post::class]->sum('Id+post_type_id+user_id');//BUG
print_r($a);

$query = $conn[Post::class]->whereAnd('user_id=1');
$b[]= $query->count();
$b[]= $query->sum('Id');
$b[]= $query->avg('Id');
$b[]= $query->sum('Id+post_type_id+user_id');//BUG
print_r($b);

?>
--EXPECT--
<!--SELECT * FROM `zz_post`   ;-->
{"Id":"1","post_type_id":"1","user_id":"1","text":"text1"}
{"Id":"2","post_type_id":"1","user_id":"1","text":"text2"}
{"Id":"3","post_type_id":"2","user_id":"1","text":"text3"}
{"Id":"4","post_type_id":"3","user_id":"2","text":"user2 22"}
{"Id":"5","post_type_id":"2","user_id":"3","text":"post32"}
{"Id":"6","post_type_id":"1","user_id":"3","text":"post31"}
<!--SELECT count(1) as `__VALUE__` FROM `zz_post`   ;-->
<!--SELECT sum(Id) as `__VALUE__` FROM `zz_post`   ;-->
<!--SELECT avg(Id) as `__VALUE__` FROM `zz_post`   ;-->
<!--SELECT sum(Id+post_type_id+user_id) as `__VALUE__` FROM `zz_post`   ;-->
Array
(
    [0] => 6
    [1] => 21
    [2] => 3.5000
    [3] => 42
)
<!--SELECT count(1) as `__VALUE__` FROM `zz_post`  WHERE (`user_id`=1)  ;-->
<!--SELECT sum(Id) as `__VALUE__` FROM `zz_post`  WHERE (`user_id`=1)  ;-->
<!--SELECT avg(Id) as `__VALUE__` FROM `zz_post`  WHERE (`user_id`=1)  ;-->
<!--SELECT sum(Id+post_type_id+user_id) as `__VALUE__` FROM `zz_post`  WHERE (`user_id`=1)  ;-->
Array
(
    [0] => 3
    [1] => 6
    [2] => 2.0000
    [3] => 13
)