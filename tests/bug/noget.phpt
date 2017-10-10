--TEST--


--FILE--
<?php
include __DIR__.'/../before.php';

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root'); 
$conn->debug=true;  
$conn[UserType::class]->delete(true);
$conn[Post::class]->delete(true);


echo $conn[User::class]['name'];
echo "\n";
 
echo $conn[User::class][UserType::class]['name'];
echo "\n";
 
echo $conn[User::class][Post::class]['text'];
echo "\n";


foreach ($conn[User::class] as $row) {
    echo "-----------------------\n"; 
    echo $row['name'];
    echo "\n";
    
    echo $row[UserType::class]['name'];
    echo "\n";
    
    echo $row[Post::class]['text'];
    echo "\n"; 
}


?>
--EXPECTF--
<!--DELETE FROM `zz_user_type`  WHERE (1);-->
<!--DELETE FROM `zz_post`  WHERE (1);-->
<!--SELECT * FROM `zz_user`   ;-->
u1
<!--SELECT * FROM `zz_user_type`  WHERE (`Id` in (SELECT type_id FROM `zz_user`   ))  ;-->

<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ))  ;-->

<!--SELECT * FROM `zz_user`   ;-->
-----------------------
u1
<!--SELECT * FROM `zz_user_type`  WHERE (`Id` in (?,?) )  ;1,2-->

<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (?,?,?) )  ;1,2,3-->

-----------------------
u2


-----------------------
u3

