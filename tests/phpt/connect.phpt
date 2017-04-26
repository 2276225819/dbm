--TEST--


--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

$a=$conn->sql(User::class)->fetchAll();
$b=$conn->sql('zz_user')->fetchAll();
echo count($a)==count($b);
echo "\n";

$a=$conn->load(User::class,1)->toArray();
$b=$conn->load('zz_user',1,'Id')->toArray();
echo $a==$b;
echo "\n";

print_r([$a,$b]);
?>
--EXPECTF--
<!--SELECT * FROM zz_user   ;-->
<!--SELECT * FROM zz_user   ;-->
1
<!--SELECT * FROM zz_user  WHERE Id=?  ;1-->
<!--SELECT * FROM zz_user  WHERE Id=?  ;1-->
1
Array
(
    [0] => Array
        (
            [Id] => 1
            [name] => u1
        )

    [1] => Array
        (
            [Id] => 1
            [name] => u1
        )

)