--TEST--


--FILE--
<?php

include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;

$a = $conn->execute("select * from zz_post");
print_r($a);

try{ 
$a = $conn->execute("select * from null");
print_r($a);
}catch(Throwable $e){ 
print_r($e->getMessage());
echo "\n";
}

$a = $conn->execute("create table zz_test(Id int); "); 
print_r([$a,$a->rowCount(),$a->columnCount()]);

$a = $conn->execute("select * from zz_test"); 
print_r([$a,$a->rowCount(),$a->columnCount()]);
 

$a = $conn->execute("insert zz_test values(?),(?),(?),(?),(?)",[1,1,1,1,1]); 
print_r([$a,$a->rowCount(),$a->columnCount()]);


print_r([$conn->lastInsertId()]);
 
$a = $conn->execute("drop table zz_test ");
print_r([$a,$a->rowCount(),$a->columnCount()]);


?>
--EXPECTF--
<!--select * from zz_post;-->
PDOStatement Object
(
    [queryString] => select * from zz_post
)
<!--select * from null;-->
SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'null' at line 1
<!--create table zz_test(Id int); ;-->
Array
(
    [0] => PDOStatement Object
        (
            [queryString] => create table zz_test(Id int); 
        )

    [1] => 0
    [2] => 0
)
<!--select * from zz_test;-->
Array
(
    [0] => PDOStatement Object
        (
            [queryString] => select * from zz_test
        )

    [1] => 0
    [2] => 1
)
<!--insert zz_test values(?),(?),(?),(?),(?);1,1,1,1,1-->
Array
(
    [0] => PDOStatement Object
        (
            [queryString] => insert zz_test values(?),(?),(?),(?),(?)
        )

    [1] => 5
    [2] => 0
)
Array
(
    [0] => 0
)
<!--drop table zz_test ;-->
Array
(
    [0] => PDOStatement Object
        (
            [queryString] => drop table zz_test 
        )

    [1] => 0
    [2] => 0
)