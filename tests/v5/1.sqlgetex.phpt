--TEST-- 
#Model->insert(ARRAY,...)                        //self Model self:          插入到集合
#Model->insert(ARRAY,...)                        //self Model hasmany:       插入到集合?parent.pk 
#Model->insert(ARRAY,...)                        //self Model hasone:        插入到集合?并执行设置parent.fk  
#Model->save(ARRAY)                              //self Model self:first     插入到集合 失败就 修改
#Model->save(ARRAY)                              //self Model hasmany:first  插入到集合 失败就 修改parent.pk 
#Model->save(ARRAY)                              //self Model hasone:first   插入到集合 失败就 修改并执行设置parent.fk
#Model->update(ARRAY)                            //self RowCount             根据当前model条件修改集合(可能空)
#Model->delete(TRUE)                             //self RowCount             根据当前model条件删除集合(可能空)
 
--FILE--
<?php 
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;
 
$user = $conn->sql(User::class,'Id') ;   
$type = $user->ref(UserType::class,'Id',['Id'=>'type_id'])->first(); 
print_r((array)$user + ['type'=>(array)$type]); 

echo "\n\nget:2\n"; 
$user = $conn->sql(User::class,'Id')->get(2);   
$type = $user->ref(UserType::class,'Id',['Id'=>'type_id'])->first(); 
print_r((array)$user + ['type'=>(array)$type]); 

echo "\n\nfirst:0\n"; 
$user = $conn->sql(User::class,'Id')->first(2);   
$type = $user->ref(UserType::class,'Id',['Id'=>'type_id'])->first(); 
print_r((array)$user + ['type'=>(array)$type]); 

echo "\n\nfind:2\n"; 
$user = $conn->sql(User::class,'Id')->find(2);   
$type = $user->ref(UserType::class,'Id',['Id'=>'type_id'])->first(); 
print_r((array)$user + ['type'=>(array)$type]); 
 
 
 
?>
--EXPECTF--
<!--SELECT * FROM `zz_user_type`  WHERE (`Id` in (SELECT type_id FROM `zz_user`   ))  ;-->
Array
(
    [type] => Array
        (
            [Id] => 1
            [name] => tysfdpe1
        )

)


get:2
<!--SELECT * FROM `zz_user`   ;-->
<!--SELECT * FROM `zz_user_type`  WHERE (`Id` in (?,?) )  ;1,2-->
Array
(
    [Id] => 3
    [name] => u3
    [type_id] => 2
    [type] => Array
        (
            [Id] => 2
            [name] => ty21
        )

)


first:0
<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 2 ;-->
<!--SELECT * FROM `zz_user_type`  WHERE (`Id`=?)  ;2-->
Array
(
    [Id] => 3
    [name] => u3
    [type_id] => 2
    [type] => Array
        (
            [Id] => 2
            [name] => ty21
        )

)


find:2
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)   LIMIT 1;2-->
<!--SELECT * FROM `zz_user_type`  WHERE (`Id`=?)  ;1-->
Array
(
    [Id] => 2
    [name] => u2
    [type_id] => 1
    [type] => Array
        (
            [Id] => 1
            [name] => tysfdpe1
        )

)