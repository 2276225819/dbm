--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;



echo (string)$conn->sql('zz_user_type')
			->many('zz_user','Id','type_id')
			->many('zz_post','Id','user_id');
echo "\n"; 
echo (string)$conn->sql('zz_post')
			->one('zz_user','Id','user_id')
			->one('zz_post','Id','type_id');




?>
--EXPECT--