--TEST--


--FILE--
<?php
include __DIR__.'/../before.php';

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

$user = $conn->load('zz_user',1,'id');

echo "new friend:\n";
echo $user->hasMany('zz_friend','uid1','Id')->insertMulit([
    ['uid2'=>2],['uid2'=>3]
]); 
echo "\n";


echo "following:\n";
print_r($user->hasMany('zz_friend','uid1','Id')->fetchAll(\PDO::FETCH_ASSOC));


echo "followers:\n";
print_r($user->hasMany('zz_friend','uid2','Id')->fetchAll(\PDO::FETCH_ASSOC));
 
echo "\n";

echo "unfollow table;\n"; 
$conn->load('zz_friend',[1,3],['uid1','uid2'])->destroy(['Id']);

echo "\n";

echo "unfollow model;\n"; 
$conn->load(Friend::class,[1,2])->destroy();

echo "\n";

?>
--EXPECTF--
<!--SELECT * FROM zz_user  WHERE id=?  ;1-->
new friend:
<!--INSERT INTO zz_friend (`uid2`,`uid1` )VALUES(?,?),(?,?);2,1,3,1-->
2
following:
<!--SELECT * FROM zz_friend  WHERE uid1=?  ;1-->
Array
(
    [0] => Array
        (
            [Id] => 1
            [uid1] => 1
            [uid2] => 2
        )

    [1] => Array
        (
            [Id] => 2
            [uid1] => 1
            [uid2] => 3
        )

)
followers:
<!--SELECT * FROM zz_friend  WHERE uid2=?  ;1-->
Array
(
)

unfollow table;
<!--SELECT * FROM zz_friend  WHERE uid1=? AND uid2=?  ;1,3-->
<!--DELETE FROM zz_friend  WHERE Id=?;2-->

unfollow model;
<!--SELECT * FROM zz_friend  WHERE uid1=? AND uid2=?  ;1,2-->
<!--DELETE FROM zz_friend  WHERE uid1=? AND uid2=?;1,2-->
