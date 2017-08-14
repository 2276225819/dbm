--TEST--


--FILE--
<?php 
include __DIR__.'/../before.php'; 
$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root'); 
$conn->debug=true; 


echo isset($conn[User::class][0]['name']);
 
$a = $conn[User::class];
echo isset($a[0]['name'])?'1':'0';

//$a = $conn[User::class];
echo isset($a[0]['ewfw'])?'1':'0';

//$a = $conn[User::class];
echo isset($a[484878]['ewfw'])?'1':'0';


$a = $a[0];
echo isset($a['name'])?'1':'0';

//$a = $conn[User::class][0];
echo isset($a['frwe'])?'1':'0';

 
 
?>
--EXPECTF--
<!--SELECT * FROM `zz_user`    LIMIT 1;-->
1<!--SELECT * FROM `zz_user`    LIMIT 1;-->
10<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 484878 ;-->
010