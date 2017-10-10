--TEST--


--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;

echo "#Connect->execute(STRING[,...args]); #PDOStatement\n";
print_r($conn->execute('select 1+2+3+?+?',[4,5])->fetchAll(\PDO::FETCH_ASSOC));


echo "#Connect->sql(TABLE,...PKS)\n";
$a=$conn->sql(User::class)->all(function($model){return (array)$model;});
$b=$conn->sql('zz_user','Id')->all(function($model){return (array)$model;});
print_r([$a,$b]);
 
?>
--EXPECTF-- 
#Connect->execute(STRING[,...args]); #PDOStatement
<!--select 1+2+3+?+?;4,5-->
Array
(
    [0] => Array
        (
            [1+2+3+'4'+'5'] => 15
        )

)
#Connect->sql(TABLE,...PKS)
<!--SELECT * FROM `zz_user`   ;-->
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [0] => Array
        (
            [0] => Array
                (
                    [Id] => 1
                    [name] => u1
                    [type_id] => 1
                )

            [1] => Array
                (
                    [Id] => 2
                    [name] => u2
                    [type_id] => 1
                )

            [2] => Array
                (
                    [Id] => 3
                    [name] => u3
                    [type_id] => 2
                )

        )

    [1] => Array
        (
            [0] => Array
                (
                    [Id] => 1
                    [name] => u1
                    [type_id] => 1
                )

            [1] => Array
                (
                    [Id] => 2
                    [name] => u2
                    [type_id] => 1
                )

            [2] => Array
                (
                    [Id] => 3
                    [name] => u3
                    [type_id] => 2
                )

        )

)