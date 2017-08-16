--TEST-- 
test
--FILE--
<?php
include __DIR__.'/../before.php';
$db = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root'); 
$db->debug=true;

echo "#SQL->find(...PKV)          #ROW/THROW\n";
$a=$db->sql('zz_user','Id')->find(3)->get(); 
$b=$db[User::class]->find(3)->get();
$c=$db[User::class]->find(3333)->get();
print_r([$a,$b,$c]);


echo "#SQL->get(INDEX)            #ROW/NULL\n";
$d=$db->sql('zz_user','Id')->val('name');
$e=$db[User::class]->val('name'); 
print_r([$d,$e]);


echo "#SQL->all(KEY)              #[VALUE,VALUE...]/[]\n";
print_r($db[User::class]->all('name'));
print_r($db[User::class]->where('1=0')->all('name'));


echo "#SQL->keypair(KEY,VAL)      #[KEY=>VALUE,KEY=>VALUE...]/[]\n";
print_r($db[User::class]->keypair('name','Id'));
print_r($db[User::class]->where('1=0')->keypair('name','Id'));


echo "#SQL->val(FIELD)            #MIXED\n";
print_r($db[User::class]->count(1));



echo "//////////////////////////\n";
// select * from `zz_user` limit 1
// select * from `zz_post` where `User_ID` in ( select Type_ID from `zz_user` limit 1 )
$e=$db->sql('zz_user','Id')->find(2)->ref('zz_post',['Id'],['user_id'=>'Id'])->val('text');
$f=$db[User::class](2)[Post::class]->val('text');
print_r([$e,$f]);
echo "//////////////////////////\n";
 
$g=$db->sql('zz_post','Id')->find(2)->ref('zz_user',['Id'],['Id'=>'user_id'])->val('name');
$h=$db[Post::class](2)[User::class]->val('name');
print_r([$g,$h]);

echo "//////////////////////////\n";

$q=$db->sql('zz_post','Id')->ref('zz_user',['Id'],['Id'=>'user_id'])->val('name');
$w=0;//$db->sql('zz_post','Id')[User::class]->val('name');
$e=$db[Post::class]->ref('zz_user',['Id'],['Id'=>'user_id'])->val('name');
$r=$db[Post::class][User::class]->val('name');
print_r([$q,$w,$e,$r]);


$q=$db->sql('zz_user','Id')->ref('zz_post',['Id'],['user_id'=>'Id'])->val('text');
$w=0;//$db->sql('zz_user','Id')[Post::class]->val('text');
$e=$db[User::class]->ref('zz_post',['Id'],['user_id'=>'Id'])->val('text');
$r=$db[User::class][Post::class]->val('text');
print_r([$q,$w,$e,$r]);

?>
--EXPECT--
#SQL->find(...PKV)          #ROW/THROW
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;3-->
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;3-->
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;3333-->
Array
(
    [0] => dbm\Collection Object
        (
            [Id] => 3
            [name] => u3
            [type_id] => 2
        )

    [1] => User Object
        (
            [Id] => 3
            [name] => u3
            [type_id] => 2
        )

    [2] => 
)
#SQL->get(INDEX)            #ROW/NULL
<!--SELECT * FROM `zz_user`   ;-->
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [0] => u1
    [1] => u1
)
#SQL->all(KEY)              #[VALUE,VALUE...]/[]
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [0] => u1
    [1] => u2
    [2] => u3
)
<!--SELECT * FROM `zz_user`  WHERE (1=0)  ;-->
Array
(
)
#SQL->keypair(KEY,VAL)      #[KEY=>VALUE,KEY=>VALUE...]/[]
<!--SELECT * FROM `zz_user`   ;-->
Array
(
    [u1] => 1
    [u2] => 2
    [u3] => 3
)
<!--SELECT * FROM `zz_user`  WHERE (1=0)  ;-->
Array
(
)
#SQL->val(FIELD)            #MIXED
<!--SELECT count(1) as `__VALUE__` FROM `zz_user`   ;-->
3//////////////////////////
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`  WHERE (`Id`=?)  ))  ;2-->
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;2-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id`=?)  ;2-->
Array
(
    [0] => user2 22
    [1] => user2 22
)
//////////////////////////
<!--SELECT * FROM `zz_user`  WHERE (`Id` in (SELECT user_id FROM `zz_post`  WHERE (`Id`=?)  ))  ;2-->
<!--SELECT * FROM `zz_post`  WHERE (`Id`=?)  ;2-->
<!--SELECT * FROM `zz_user`  WHERE (`Id`=?)  ;1-->
Array
(
    [0] => u1
    [1] => u1
)
//////////////////////////
<!--SELECT * FROM `zz_user`  WHERE (`Id` in (SELECT user_id FROM `zz_post`   ))  ;-->
<!--SELECT * FROM `zz_user`  WHERE (`Id` in (SELECT user_id FROM `zz_post`   ))  ;-->
<!--SELECT * FROM `zz_user`  WHERE (`Id` in (SELECT user_id FROM `zz_post`   ))  ;-->
Array
(
    [0] => u1
    [1] => 0
    [2] => u1
    [3] => u1
)
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ))  ;-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ))  ;-->
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ))  ;-->
Array
(
    [0] => text1
    [1] => 0
    [2] => text1
    [3] => text1
)