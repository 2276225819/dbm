--TEST--  
#Model->toArray()                (array)#Model   //row                       集合指针行(未查询指向空数组)
#Model->get(OFFSET)              #Model[OFFSET]  //Model{sql,ref,pk} or NULL 查询全部(多行集合)
#Model->first(OFFSET)                            //Model{sql,ref,pk} or NULL 查询首行(单行集合)
#Model->last(OFFSET)                             //Model{sql,ref,pk} or NULL 查询尾行(单行集合)
#Model->find(...PK)                              //Model{sql,ref,pk}         查询主键(单行集合)
--FILE--
<?php
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2', 'root', 'root');
$conn->debug=true;
$users = $conn->sql(User::class, 'Id');
$user1 = $conn->sql(User::class, 'Id')->get(2);
$user2 = $conn->sql(User::class, 'Id')->first();
$user3 = $conn->sql(User::class, 'Id')->find(2);
 
print_r([
    (array) $users,
    (array) $user1,
    (array) $user2,
    (array) $user3,
]); 

?>
--EXPECTF-- 
<!--SELECT * FROM `zz_user`   ;-->
<!--SELECT * FROM `zz_user`    LIMIT 1;-->
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)   LIMIT 1;2-->
Array
(
    [0] => Array
        (
        )

    [1] => Array
        (
            [Id] => 3
            [name] => u3
            [type_id] => 2
        )

    [2] => Array
        (
            [Id] => 1
            [name] => u1
            [type_id] => 1
        )

    [3] => Array
        (
            [Id] => 2
            [name] => u2
            [type_id] => 1
        )

)