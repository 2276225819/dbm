--TEST--

--FILE--
<?php  
include __DIR__."/../before.v4.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;
 
echo "# not cache\n";
$conn->model(User::class)->each(function(User $u){
	$u['name']=1;
	$u->save(); 
});  
echo "# new query\n";
print_r($conn->model(User::class)->map(function(User $u){
	return $u['name'];
}));

echo "# cache query\n";
foreach ($conn->model(User::class) as $u){ 
	$u['name']=2;
	$u->save(); 
} 
echo "# old query\n";
print_r($conn->model(User::class)->map(function(User $u){
	return $u['name'];
})); 

echo "# clear cache\n";
unset($u);

echo "# new query\n";
print_r($conn->model(User::class)->map(function(User $u){
	return $u['name'];
}));

?>
--EXPECT-- 
# not cache
<!--SELECT * FROM `zz_user`   ;-->
<!--UPDATE `zz_user` SET `name`=?  WHERE (`Id`=?);1,1-->
<!--UPDATE `zz_user` SET `name`=?  WHERE (`Id`=?);1,2-->
<!--UPDATE `zz_user` SET `name`=?  WHERE (`Id`=?);1,3-->
# new query
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [0] => 1
    [1] => 1
    [2] => 1
)
# cache query
<!--SELECT * FROM `zz_user`   ;-->
<!--UPDATE `zz_user` SET `name`=?  WHERE (`Id`=?);2,1-->
<!--UPDATE `zz_user` SET `name`=?  WHERE (`Id`=?);2,2-->
<!--UPDATE `zz_user` SET `name`=?  WHERE (`Id`=?);2,3-->
# old query
Array
(
    [0] => 1
    [1] => 1
    [2] => 1
)
# clear cache
# new query
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [0] => 2
    [1] => 2
    [2] => 2
)