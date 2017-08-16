--TEST--
foreach save

--FILE--
<?php
include __DIR__.'/../before.php'; 

$db = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root'); 

$db[User::class]->where('1=1')->update(['name'=>1]);
$db->debug=true; 

foreach ($db[User::class]->field('Id,name') ->order('Id desc') as $user) { 
    $user = clone $user;//FIXBUG 
    $user['name']+=1;
    $user->save();    
    print_r($user);
} 

?>
--EXPECTF--
<!--SELECT Id,name FROM `zz_user`   ORDER BY Id desc ;-->
<!--INSERT INTO `zz_user` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;2,3,2-->
User Object
(
    [Id] => 3
    [name] => 2
)
<!--INSERT INTO `zz_user` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;2,2,2-->
User Object
(
    [Id] => 2
    [name] => 2
)
<!--INSERT INTO `zz_user` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;2,1,2-->
User Object
(
    [Id] => 1
    [name] => 2
)