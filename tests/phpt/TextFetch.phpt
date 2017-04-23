--TEST--
TextFetch
--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
print_r($conn->sql('zz_post')->limit(1)->fetch());
print_r($conn->sql('zz_post')->limit(1,3)->fetch());

?>
--EXPECT--
Array
(
    [Id] => 1
    [post_type_id] => 1
    [user_id] => 1
    [text] => text1
)
Array
(
    [Id] => 4
    [post_type_id] => 3
    [user_id] => 2
    [text] => user2 22
)