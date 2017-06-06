--TEST-- 
空关联查询
--FILE--
<?php
include __DIR__.'/../before.php';

 

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root'); 
$conn->debug=true; 

$user = $conn[User::class](2); 
$user['type_id'] = 0;
$user->save();
print_r($user);

$type = $user[UserType::class];
$val = $user[UserType::class]->val();
print_r([$type,$val]); 
?>
--EXPECT--
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;2-->
<!--UPDATE `zz_user` SET `type_id`=?  WHERE (`Id`=?);0,2-->
User Object
(
    [Id] => 2
    [name] => u2
    [type_id] => 0
)
Array
(
    [0] => UserType Object
        (
            [:] => SELECT * FROM `zz_user_type`  WHERE (`Id`=?)  ;0
            [?] => []
        )

    [1] => 
)