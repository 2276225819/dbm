--TEST--
ObjectFetch
--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
$conn->debug=true;

$users = $conn->sql(User::class);

foreach ($users as $key => $value) {
	print_r($value); 
} 
foreach ($users as $key => $value) {
	print_r($value); 
} 

?>
--EXPECT--
<!--SELECT * FROM `zz_user`   ;-->
User Object
(
    [Id] => 1
    [name] => u1
    [type_id] => 1
)
User Object
(
    [Id] => 2
    [name] => u2
    [type_id] => 1
)
User Object
(
    [Id] => 3
    [name] => u3
    [type_id] => 2
)
User Object
(
    [Id] => 1
    [name] => u1
    [type_id] => 1
)
User Object
(
    [Id] => 2
    [name] => u2
    [type_id] => 1
)
User Object
(
    [Id] => 3
    [name] => u3
    [type_id] => 2
)