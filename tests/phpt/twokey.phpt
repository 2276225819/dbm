--TEST--


--FILE--
<?php
include __DIR__.'/../before.php';

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

$user = $conn->sql('zz_user','Id')->load(1);

echo "new friend:\n";
echo $user->ref('zz_friend',['Id'],['uid1'=>'Id'])->insertMulit([
    ['uid2'=>2],['uid2'=>3]
]); 
echo "\n";


echo "following:\n";
print_r($user->ref('zz_friend',['Id'],['uid1'=>'Id'])->all());


echo "followers:\n";
print_r($user->ref('zz_friend',['Id'],['uid2'=>'Id'])->all());
 
echo "\n";

echo "unfollow table;\n"; 
$conn->sql('zz_friend',['uid1','uid2'])->load(1,3)->destroy();

echo "\n";

echo "unfollow model;\n"; 
$conn->sql(Friend::class)->load(1,2)->destroy();

echo "\n";

?>
--EXPECTF--
<!--SELECT * FROM `zz_user`  WHERE `Id`=?  ;1-->
new friend:
<!--INSERT INTO `zz_friend` (`uid2`,`uid1` )VALUES(?,?),(?,?);2,1,3,1-->
2
following:
<!--SELECT * FROM `zz_friend`  WHERE `uid1`=?  ;1-->
Array
(
    [0] => dbm\Entity Object
        (
            [uid1] => 1
            [uid2] => 2
            [nickname] => 1->2
        )

    [1] => dbm\Entity Object
        (
            [uid1] => 1
            [uid2] => 3
            [nickname] => 1->3
        )

    [2] => dbm\Entity Object
        (
            [uid1] => 1
            [uid2] => 2
            [nickname] => 
        )

    [3] => dbm\Entity Object
        (
            [uid1] => 1
            [uid2] => 3
            [nickname] => 
        )

)
followers:
<!--SELECT * FROM `zz_friend`  WHERE `uid2`=?  ;1-->
Array
(
)

unfollow table;
<!--SELECT * FROM `zz_friend`  WHERE `uid1`=? AND `uid2`=?  ;1,3-->
<!--DELETE FROM `zz_friend`  WHERE `uid1`=? AND `uid2`=?;1,3-->

unfollow model;
<!--SELECT * FROM `zz_friend`  WHERE `uid1`=? AND `uid2`=?  ;1,2-->
<!--DELETE FROM `zz_friend`  WHERE `uid1`=? AND `uid2`=?;1,2-->
