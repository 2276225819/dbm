--TEST--
不能后向兼容
--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

//<!--SELECT * FROM `zz_user`   ;-->
$a=$conn[User::class]['name'];
$b=$conn->sql('zz_user','Id')->val('name');
print_r([$a,$b]);

//<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
$a=$conn[User::class][1]['name'];
$b=$conn->sql('zz_user','Id')->get(1)->val('name');
print_r([$a,$b]);    

//<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
$a=(string)$conn[User::class]->limit(1,1);//['name'];
$b=(string)$conn->sql('zz_user','Id')->limit(1,1);//->val('name');
print_r([$a,$b]);   
unset($a,$b); 
 
//<!--SELECT * FROM `zz_user`  WHERE `Id`=?  ;3-->
$a=$conn[User::class](3)['name'];
$b=$conn->sql('zz_user','Id')->load(3)->val('name');
print_r([$a,$b]);  

//<!--SELECT * FROM `zz_user`  WHERE `Id`=?  ;3-->
$a=(string)$conn[User::class]->find(3);
$b=(string)$conn->sql('zz_user','Id')->find(3);
print_r([$a,$b]); 
unset($a,$b); 

?>
--EXPECT--
<!--SELECT * FROM `zz_user`   ;-->
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [0] => u1
    [1] => u1
)
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;-->
Array
(
    [0] => u2
    [1] => u2
)
Array
(
    [0] => SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;
    [1] => SELECT * FROM `zz_user`    LIMIT 1 OFFSET 1 ;
)
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;3-->
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;3-->
Array
(
    [0] => u3
    [1] => u3
)
Array
(
    [0] => SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;3
    [1] => SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;3
)