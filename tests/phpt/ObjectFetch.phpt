--TEST--
ObjectFetch
--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');

foreach ($conn->sql(User::class) as $key => $value) {
	print_r($value->toArray()); 
} 

?>
--EXPECT--
Array
(
    [Id] => 1
    [name] => u1
)
Array
(
    [Id] => 2
    [name] => u2
)
Array
(
    [Id] => 3
    [name] => u3
)