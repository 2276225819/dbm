--TEST--
ObjectFetch
--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

$users = $conn->sql(User::class);

foreach ($users as $key => $value) {
	print_r($value->toArray()); 
} 
foreach ($users as $key => $value) {
	print_r($value->toArray()); 
} 

?>
--EXPECT--
<!--SELECT * FROM zz_user   ;-->
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