--TEST--
foreach save

--FILE--
<?php
include __DIR__.'/../before.php'; 

$db = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root'); 

$db[User::class]->where('1=1')->update(['name'=>1]);
$db->debug=true; 

foreach ($db[User::class]->field('Id,name') ->order('Id desc') as $user) { 
    $user = clone $user;
    $user['name']+=1;
    $user->save();    
    print_r($user);
} 

?>
--EXPECTF--
<!--SELECT Id,name FROM `zz_user`   ORDER BY Id desc ;-->
<!--UPDATE `zz_user` SET `name`=?  WHERE (`Id`=?);2,3-->
User Object
(
    [Id] => 3
    [name] => 2
)
<!--UPDATE `zz_user` SET `name`=?  WHERE (`Id`=?);2,2-->
User Object
(
    [Id] => 2
    [name] => 2
)
<!--UPDATE `zz_user` SET `name`=?  WHERE (`Id`=?);2,1-->
User Object
(
    [Id] => 1
    [name] => 2
)