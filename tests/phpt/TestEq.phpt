--TEST--  

--FILE--
<?php
include __DIR__.'/../before.php';

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
$conn->debug=true;

$user = $conn->sql(User::class) ;


print_r($user->keypair('name','Id'));
echo "\n";
print_r($user->val('name'));
echo "\n"; 
print_r($user->all('name'));

?>
--EXPECT-- 
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [u1] => 1
    [u2] => 2
    [u3] => 3
)

u1
Array
(
    [0] => u1
    [1] => u2
    [2] => u3
)