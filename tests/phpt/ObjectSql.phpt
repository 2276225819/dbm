--TEST-- 
test
--FILE--
<?php
include __DIR__.'/../before.php';
$db = new \dbm\Connect('mysql:dbname=test','root','root'); 
$db->debug=true;



$cache = $db->scope();


echo "#SQL->find(...PKV)          #ROW/THROW\n";
$a=$db->sql('zz_user','Id')->find(3)->get(); 
$b=$db[User::class](3)[0];
$c=$db[User::class](3333)[0];
print_r([$a,$b,$c]);


echo "#SQL->get(INDEX)            #ROW/NULL\n";
$d=$db->sql('zz_user','Id')->get()['name'];
$e=$db[User::class][0]['name']; 
print_r([$d,$e]);


echo "#SQL->all(KEY)              #[VALUE,VALUE...]/[]\n";
print_r($db[User::class]->all('name'));
print_r($db[User::class]->where('1=0')->all('name'));


echo "#SQL->keypair(KEY,VAL)      #[KEY=>VALUE,KEY=>VALUE...]/[]\n";
print_r($db[User::class]->keypair('name','Id'));
print_r($db[User::class]->where('1=0')->keypair('name','Id'));


echo "#SQL->val(FIELD)            #MIXED\n";
print_r($db[User::class]->val("count(1)"));



echo "//////////////////////////\n";
// select * from zz_user limit 1
// select * from zz_post where User_ID in ( select Type_ID from zz_user limit 1 )
$e=$db->sql('zz_user','Id')->find(2)->many('zz_post','Id','user_id')->val('text');
$f=$db[User::class](2)[Post::class]->val('text');
print_r([$e,$f]);
echo "//////////////////////////\n";

// select * from zz_user where type=7
// select * from zz_type where ID in ( select Type_ID from zz_user where type=7 )
$g=$db->sql('zz_post','Id')->find(2)->one('zz_user','Id','user_id')->val('name');
$h=$db[Post::class](2)[User::class]->val('name');
print_r([$g,$h]);

echo "//////////////////////////\n";

$q=$db->sql('zz_post','Id')->one('zz_user','Id','user_id')->val('name');
$w=0;//$db->sql('zz_post','Id')[User::class]->val('name');
$e=$db[Post::class]->one('zz_user','Id','user_id')->val('name');
$r=$db[Post::class][User::class]->val('name');
print_r([$q,$w,$e,$r]);


$q=$db->sql('zz_user','Id')->many('zz_post','Id','user_id')->val('text');
$w=0;//$db->sql('zz_user','Id')[Post::class]->val('text');
$e=$db[User::class]->many('zz_post','Id','user_id')->val('text');
$r=$db[User::class][Post::class]->val('text');
print_r([$q,$w,$e,$r]);

?>
--EXPECT--
#SQL->find(...PKV)          #ROW/THROW
<!--SELECT * FROM zz_user  WHERE Id=?  ;3-->
<!--SELECT * FROM zz_user  WHERE Id=?  ;3333-->
Array
(
    [0] => Array
        (
            [Id] => 3
            [name] => u3
            [type_id] => 2
        )

    [1] => Array
        (
            [Id] => 3
            [name] => u3
            [type_id] => 2
        )

    [2] => 
)
#SQL->get(INDEX)            #ROW/NULL
<!--SELECT * FROM zz_user   ;-->
Array
(
    [0] => u1
    [1] => u1
)
#SQL->all(KEY)              #[VALUE,VALUE...]/[]
Array
(
    [0] => u1
    [1] => u2
    [2] => u3
)
<!--SELECT * FROM zz_user  WHERE 1=0  ;-->
Array
(
)
#SQL->keypair(KEY,VAL)      #[KEY=>VALUE,KEY=>VALUE...]/[]
Array
(
    [u1] => 1
    [u2] => 2
    [u3] => 3
)
Array
(
)
#SQL->val(FIELD)            #MIXED
<!--SELECT count(1) FROM zz_user   ;-->
3//////////////////////////
<!--SELECT * FROM zz_user  WHERE Id=?  ;2-->
<!--SELECT text FROM zz_post  WHERE (user_id) in ((?))  ;2-->
Array
(
    [0] => user2 22
    [1] => user2 22
)
//////////////////////////
<!--SELECT * FROM zz_post  WHERE Id=?  ;2-->
<!--SELECT name FROM zz_user  WHERE (Id) in ((?))  ;1-->
Array
(
    [0] => u1
    [1] => u1
)
//////////////////////////
<!--SELECT * FROM zz_post   ;-->
<!--SELECT name FROM zz_user  WHERE (Id) in ((?),(?),(?))  ;1,2,3-->
Array
(
    [0] => u1
    [1] => 0
    [2] => u1
    [3] => u1
)
<!--SELECT text FROM zz_post  WHERE (user_id) in ((?),(?),(?))  ;1,2,3-->
Array
(
    [0] => text1
    [1] => 0
    [2] => text1
    [3] => text1
)