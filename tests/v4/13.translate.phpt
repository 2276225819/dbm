--TEST--

--FILE--
<?php

include __DIR__."/../before.php"; 

$conn = new \dbm\Connect('mysql:dbname=test', 'root', 'root');
$conn->debug=true;

function rollbackScope(\dbm\Connect $conn)
{
    $scope = $conn->scope();
    $conn->execute("DELETE FROM zz_user");
    $conn->execute("INSERT zz_user SET name=1");
    //$scope->__destruct(){ rollback(); }
}
function commitScope(\dbm\Connect $conn)
{
    $scope = $conn->scope();
    $conn->execute("DELETE FROM zz_user");
    $conn->execute("INSERT zz_user SET name=2");
    $scope->commit();
} 
function rollbackTransaction(\dbm\Connect $conn)
{
    $conn->begin();
    $conn->execute("DELETE FROM zz_user");
    $conn->execute("INSERT zz_user SET name=3"); 
    $conn->rollback();
}
function commitTransaction(\dbm\Connect $conn)
{
    $conn->begin();
    $conn->execute("DELETE FROM zz_user");
    $conn->execute("INSERT zz_user SET name=4"); 
    $conn->commit();
}

commitTransaction($conn);
echo "# ".json_encode($conn->sql('zz_user')->all())."\n\n";
commitScope($conn);
echo "# ".json_encode($conn->sql('zz_user')->all())."\n\n";
rollbackTransaction($conn);
echo "# ".json_encode($conn->sql('zz_user')->all())."\n\n";
rollbackScope($conn);
echo "# ".json_encode($conn->sql('zz_user')->all())."\n\n";
?>
--EXPECT--
<!--begin mysql:dbname=test-->
<!--DELETE FROM `zz_user`;-->
<!--INSERT `zz_user` SET `name`=4;-->
<!--commit mysql:dbname=test-->
<!--SELECT * FROM `zz_user`   ;-->
# [{"Id":"4","name":"4","type_id":null}]

<!--begin mysql:dbname=test-->
<!--DELETE FROM `zz_user`;-->
<!--INSERT `zz_user` SET `name`=2;-->
<!--commit mysql:dbname=test-->
<!--SELECT * FROM `zz_user`   ;-->
# [{"Id":"5","name":"2","type_id":null}]

<!--begin mysql:dbname=test-->
<!--DELETE FROM `zz_user`;-->
<!--INSERT `zz_user` SET `name`=3;-->
<!--rollback mysql:dbname=test-->
<!--SELECT * FROM `zz_user`   ;-->
# [{"Id":"5","name":"2","type_id":null}]

<!--begin mysql:dbname=test-->
<!--DELETE FROM `zz_user`;-->
<!--INSERT `zz_user` SET `name`=1;-->
<!--rollback mysql:dbname=test-->
<!--SELECT * FROM `zz_user`   ;-->
# [{"Id":"5","name":"2","type_id":null}]
