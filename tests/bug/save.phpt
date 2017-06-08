--TEST--
foreach save

--FILE--
<?php
include __DIR__.'/../before.php'; 

$db = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root'); 
$db->debug=true; 


foreach ($db[User::class] ->order('Id desc') as $user) { 
    $user['Name']=4;
    $user->save(); 
    
    echo $user['Name']."\n";
    foreach ($user[UserType::class] as $t) {
        echo "  t:".$t['name']."\n";
    }  
    foreach ($user[Post::class] as $t) {
        echo "  p:".$t['text']."\n";
    }  
} 

?>
--EXPECTF--
<!--SELECT * FROM `zz_user`   ORDER BY Id desc ;-->
<!--UPDATE `zz_user` SET `Name`=?  WHERE (`Id`=?);4,3-->
4
<!--SELECT * FROM `zz_user_type`  WHERE (`Id` in (?,?) )  ;1,2-->
  t:ty21
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (?,?,?) )  ;1,2,3-->
  p:post32
  p:post31
<!--UPDATE `zz_user` SET `Name`=?  WHERE (`Id`=?);4,2-->
4
  t:tysfdpe1
  p:user2 22
<!--UPDATE `zz_user` SET `Name`=?  WHERE (`Id`=?);4,1-->
4
  t:tysfdpe1
  p:text1
  p:text2
  p:text3