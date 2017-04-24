--TEST-- 
TODO:多次查询待改进
--FILE--
<?php
include __DIR__.'/../before.php';

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

$user = $conn->sql(User::class)->field('name,Id');


print_r($user->keypair());
echo "\n";
print_r($user->value());
echo "\n"; 
print_r($user->list());

?>
--EXPECT--
SELECT name,Id FROM zz_user   Array
(
)

Array
(
    [u1] => 1
    [u2] => 2
    [u3] => 3
)

SELECT name,Id FROM zz_user   Array
(
)

u1
SELECT name,Id FROM zz_user   Array
(
)

Array
(
    [0] => u1
    [1] => u2
    [2] => u3
)