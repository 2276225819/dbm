--TEST-- 
test
--FILE--
<?php
include __DIR__.'/../before.php';
$db = new \dbm\Connect('mysql:dbname=test','root','root'); 
$db->debug=true;



echo "#SQL->load(...PKV)          #ROW/THROW\n";
$a=$db->sql('zz_user','Id')->load(3); 
$b=$db[User::class](3);
$c=$db[User::class](3333);
print_r([$a,$b,$c]);


echo "#SQL->get(INDEX)            #ROW/NULL\n";
$d=$db->sql('zz_user','Id')->get()->val('name');
$e=$db[User::class][0]['name'];
$f=$db[User::class][0]['n'];
print_r([$d,$e,$f]);


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
$e=$db->sql('zz_user','Id')->load(2)->many('zz_post','Id','user_id')->load(4)->val('text');
$f=$db[User::class](2)[Post::class](4)['text'];
print_r([$e,$f]);
echo "//////////////////////////\n";

// select * from zz_user where type=7
// select * from zz_type where ID in ( select Type_ID from zz_user where type=7 )
$g=$db->sql('zz_post','Id')->load(2)->one('zz_user','Id','user_id')->get()->val('name');
$h=$db[Post::class](2)[User::class][0]['name'];
print_r([$g,$h]);
?>
--EXPECT--
#SQL->load(...PKV)          #ROW/THROW
<!--SELECT * FROM zz_user  WHERE Id=?  ;3-->
<!--SELECT * FROM zz_user  WHERE Id=?  ;3333-->
Array
(
    [0] => dbm\Model Object
        (
            [Id] => 3
            [name] => u3
        )

    [1] => User Object
        (
            [Id] => 3
            [name] => u3
        )

    [2] => 
)
#SQL->get(INDEX)            #ROW/NULL
<!--SELECT * FROM zz_user   ;-->
Array
(
    [0] => u1
    [1] => u1
    [2] => 
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
<!--SELECT * FROM zz_post  WHERE user_id=? AND Id=?  ;2,4-->
Array
(
    [0] => user2 22
    [1] => user2 22
)
//////////////////////////
<!--SELECT * FROM zz_post  WHERE Id=?  ;2-->
<!--SELECT * FROM zz_user  WHERE Id=?  ;1-->
Array
(
    [0] => u1
    [1] => u1
)