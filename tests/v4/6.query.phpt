--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
$conn->debug=true;
 
//<!--SELECT * FROM `zz_user`  WHERE (1=1) AND (2=2 or 3=3)  ;-->
print_r($conn->sql(User::class)->whereAnd('1=1')->whereAnd('2=2 or 3=3')->all('Id'));

//<!--SELECT text,Id as `__KEY__` FROM `zz_post`  WHERE (`id`>4)  ;-->
print_r($conn->sql('zz_post','Id')->whereOr('id>4')->field('text')->keypair());

//<!--SELECT * FROM `zz_user`  WHERE (`id`=2)  ;-->
print_r($conn->sql('zz_user','Id')->where('id=1')->where('id=3')->where('id=2')->keypair('name'));

//<!--SELECT * FROM `zz_user`  WHERE (`id`=1) OR (`id`=3)  ;-->
print_r($conn->sql('zz_user','Id')->whereOr('id=1')->whereOr('id=3')->keypair('Id','name'));

//<!--SELECT * FROM `zz_user`   ;-->
print_r($conn->sql(User::class)->map(function(User $u){
    return "{$u['Id']}-{$u['name']}";
}));
  
?>
--EXPECT--
<!--SELECT * FROM `zz_user`  WHERE (1=1) AND (2=2 or 3=3)  ;-->
Array
(
    [0] => 1
    [1] => 2
    [2] => 3
)
<!--SELECT text,Id as `__KEY__` FROM `zz_post`  WHERE (`id`>4)  ;-->
Array
(
    [5] => dbm\Model Object
        (
            [text] => post32
        )

    [6] => dbm\Model Object
        (
            [text] => post31
        )

)
<!--SELECT * FROM `zz_user`  WHERE (`id`=2)  ;-->
Array
(
    [u2] => dbm\Model Object
        (
            [Id] => 2
            [name] => u2
            [type_id] => 1
        )

)
<!--SELECT * FROM `zz_user`  WHERE (`id`=1) OR (`id`=3)  ;-->
Array
(
    [1] => u1
    [3] => u3
)
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [0] => 1-u1
    [1] => 2-u2
    [2] => 3-u3
)