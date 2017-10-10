--TEST--
foreach save

--FILE--
<?php
include __DIR__.'/../before.php'; 

$db = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root'); 

$db[User::class]->where('1=1')->update(['name'=>1]);
$db->debug=true; 

foreach ($db[User::class]->field('Id,name') ->order('Id desc') as $user) { 
    $user = clone $user;//FIXBUG 
    $user['name']+=1;
    $user->save();    
    print_r((array)$user);
} 

?>
--EXPECTF--
<!--SELECT Id,name FROM `zz_user`   ORDER BY Id desc ;-->
<!--INSERT INTO `zz_user` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;2,3,2-->
Array
(
    [Id] => 3
    [name] => 2
)
<!--INSERT INTO `zz_user` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;2,2,2-->
Array
(
    [Id] => 2
    [name] => 2
)
<!--INSERT INTO `zz_user` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;2,1,2-->
Array
(
    [Id] => 1
    [name] => 2
)