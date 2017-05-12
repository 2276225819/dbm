--TEST--
TextFetch
--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;
print_r($conn->sql('zz_post')->limit(1)->val());
print_r($conn->sql('zz_post')->limit(1,3)->val());

?>
--EXPECT-- 
<!--SELECT * FROM `zz_post`    LIMIT 1;-->
Array
(
    [Id] => 1
    [post_type_id] => 1
    [user_id] => 1
    [text] => text1
)
<!--SELECT * FROM `zz_post`    LIMIT 1 OFFSET 3 ;-->
Array
(
    [Id] => 4
    [post_type_id] => 3
    [user_id] => 2
    [text] => user2 22
)