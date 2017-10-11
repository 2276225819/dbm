--TEST-- 
#Model->insert(ARRAY,...)        #Model[]=...    //self Model self:          插入到集合
#Model->insert(ARRAY,...)        #Model[]=...    //self Model hasmany:       插入到集合?parent.pk 
#Model->insert(ARRAY,...)        #Model[]=...    //self Model hasone:        插入到集合?并执行设置parent.fk  
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

$users = $conn->sql(User::class,'Id'); 
print_r((string)$users);
print_r((array)$users); 

echo "\n--------------------\n";
 
$u1 = $users->save(['name'=>'u1']); 
print_r((string)$u1);
print_r((array)$u1); 


$u11 = $u1->save(['name'=>'u1111']); 
print_r((string)$u1);
print_r((array)$u1); 
print_r((string)$u11);
print_r((array)$u11); 
 
echo "\n--------------------\n";


$u2 = $users->insert(['name'=>'u2']); 
print_r((string)$u2);
print_r((array)$u2); 


$u22 = $u2->save(['name'=>'u2222']); 
print_r((string)$u2);
print_r((array)$u2); 
print_r((string)$u22);
print_r((array)$u22); 

echo "\n--------------------\n";

(clone $users)->limit(2)->delete(true); 
(clone $users)->where(true)->update(['name'=>666]); 
print_r($users->all(function($x){return (array)$x;}));
 
?>
--EXPECTF--
SELECT * FROM `zz_user`   ;Array
(
)

--------------------
<!--INSERT INTO `zz_user` (`name` )VALUES(?) ON DUPLICATE KEY UPDATE `name`=?;u1,u1-->
SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;4Array
(
    [name] => u1
    [Id] => 4
)
<!--INSERT INTO `zz_user` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;u1111,4,u1111-->
SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;4Array
(
    [name] => u1111
    [Id] => 4
)
SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;4Array
(
    [name] => u1111
    [Id] => 4
)

--------------------
<!--INSERT INTO `zz_user` (`name` )VALUES(?);u2-->
SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;5Array
(
    [name] => u2
    [Id] => 5
)
<!--INSERT INTO `zz_user` (`name`,`Id` )VALUES(?,?) ON DUPLICATE KEY UPDATE `name`=?;u2222,5,u2222-->
SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;5Array
(
    [name] => u2222
    [Id] => 5
)
SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;5Array
(
    [name] => u2222
    [Id] => 5
)

--------------------
<!--SELECT * FROM `zz_user`    LIMIT 2;-->
<!--DELETE FROM `zz_user`  WHERE (`Id` in (?,?) ) AND (1);1,2-->
<!--UPDATE `zz_user` SET `name`=?  WHERE (1);666-->
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [0] => Array
        (
            [Id] => 3
            [name] => 666
            [type_id] => 2
        )

    [1] => Array
        (
            [Id] => 4
            [name] => 666
            [type_id] => 
        )

    [2] => Array
        (
            [Id] => 5
            [name] => 666
            [type_id] => 
        )

)