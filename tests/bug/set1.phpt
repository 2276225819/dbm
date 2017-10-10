--TEST-- 
--FILE--
<?php
include __DIR__.'/../before.php'; 

$db = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root'); 
$db->debug=true;


$user1=$db[User::class]->set(['Id'=>1]);
$user2=$db[User::class]->set(['Id'=>2]);
$user9=$db[User::class]->set(['Id'=>9]); 

print_r([(array)$user1,(array)$user2,(array)$user9]);

?>
--EXPECTF-- 
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;1-->
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;2-->
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;9-->
<!--INSERT INTO `zz_user` (`Id` )VALUES(?);9-->
Array
(
    [0] => Array
        (
            [Id] => 1
            [name] => u1
            [type_id] => 1
        )

    [1] => Array
        (
            [Id] => 2
            [name] => u2
            [type_id] => 1
        )

    [2] => Array
        (
            [Id] => 9
        )

)