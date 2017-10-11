--TEST-- 
#Model->val(KEY)                 #Model[KEY]     //VALUE or PK or NULL       读取首行单列(第一列)
#Model->val(KEY,VALUE)           #Model[KEY]=... //void                      修改首行单列
#Model->val(REF,Model)           #Model[KEY]=... //void                      修改首行单列 
#Model->replace(ARRAY)           #Model[REF]=... //self Model self:first     覆盖首行(删除首行并插入) 
#Model->replace(ARRAY)           #Model[REF]=... //self Model hasmany:first  覆盖首行(删除首行并插入)parent.pk 
#Model->replace(ARRAY)           #Model[REF]=... //self Model hasone:first   覆盖首行(删除首行并插入)并执行设置parent.fk 
--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2', 'root', 'root');
$conn->debug=true;

$users = $conn->sql(User::class, 'Id');

echo "-----{count=n}-------\n";
print_r([
    $users->val("Id"),
    $users->val("name"),
    $users->val("type_id"),
    $users->val("aaaaaaaaaaaaaaa"),
]);

$user = $users->get();
echo "-----{count=1}-------\n";
print_r([
    $user->val("Id"),
    $user->val("name"),
    $user->val("type_id"),
    $user->val("aaaaaaaaaaaaaaa"),
]);
 
echo "\n";

$u1 = $users->replace([
    'name'=>777
]);
print_r([
    (string)$u1,
    (array)$u1,
]);

$u2 = $user->replace([
    'name'=>888
]);
print_r([
    (string)$u2,
    (array)$u2,
]);

$u3 = $user->replace([
    'Id'=>4,
    'name'=>999
]);
print_r([
    (string)$u3,
    (array)$u3,
]);

echo "\n";


?>
--EXPECTF--
-----{count=n}-------
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [0] => 1
    [1] => u1
    [2] => 1
    [3] => 
)
-----{count=1}-------
Array
(
    [0] => 1
    [1] => u1
    [2] => 1
    [3] => 
)

<!--REPLACE zz_user SET `name`=?;777-->
Array
(
    [0] => SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;4
    [1] => Array
        (
            [name] => 777
            [Id] => 4
        )

)
<!--REPLACE zz_user SET `name`=?;888-->
Array
(
    [0] => SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;5
    [1] => Array
        (
            [name] => 888
            [Id] => 5
        )

)
<!--REPLACE zz_user SET `Id`=?,`name`=?;4,999-->
Array
(
    [0] => SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;4
    [1] => Array
        (
            [Id] => 4
            [name] => 999
        )

)

