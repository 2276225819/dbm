--TEST-- 
--FILE--
<?php
include __DIR__.'/../before.php'; 

$db = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root'); 
$db->debug=true;


try{ 
    $user = new User;
    $user['Id']= 1;
    $user->where(['Id'=>2]);
    $user['name']=3;
    $u = $user->save();
}catch(Throwable $e){
    echo $e->getMessage();
}

 


?>
--EXPECTF--
<!--INSERT INTO `zz_user` (`Id`,`name` )VALUES(?,?);1,3-->
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1' for key 'PRIMARY'