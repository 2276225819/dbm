--TEST-- 

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

$user = $conn[User::class]->get();
print_r((array)$user);

$user->update([
    'name'=>'updated',
]);
print_r((array)$user);



?>
--EXPECTF-- 
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [Id] => 1
    [name] => u1
    [type_id] => 1
)
<!--UPDATE `zz_user` SET `name`=?  WHERE (`Id`=?);updated,1-->
Array
(
    [Id] => 1
    [name] => updated
    [type_id] => 1
)