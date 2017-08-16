--TEST--

--FILE--
<?php  
include __DIR__."/../before.php";

$db = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root');
$db->debug=true;
 
foreach($db->sql('zz_post_type','Id') as $pt){
    echo "POST_TYPE: ".$pt->val('name')."\n"; 
    foreach ($pt->ref('zz_post',['Id'],['post_type_id'=>'Id']) as $post) {
        echo "  ID     : ".$post->val('Id')."\n";
        echo "  Author : ".$post->ref('zz_user',['Id'],['Id'=>'user_id'])->val('name')."\n";
        echo "  Type   : ".$post->ref('zz_post_type',['Id'],['Id'=>'post_type_id'])->val('name')."\n";
        echo "\n";
    } 
}


exit;

// new User([
//     'name'=>'nUser',
// ]);
echo "------------------------------\n";
 
$user = $db->sql(User::class)->insert([
    'name'=>'oUser',
]);
$a = $user->ref(UserType::class)->replace([
    'name'=>'oUserType' 
]);
$b = $user->ref(Post::class)->insert([
    'text'=>'oPost1' ,
    PostType::class => new PostType([
        'id'=>'1',  'name'=>'a'
    ]),
]);
$c = $user->ref(Post::class)->insert([
    'text'=>'oPost2' ,
    PostType::class => new PostType([
        'id'=>'1', 'name'=>'a'
    ]),
]);   
$user->save();
$b->save();
$c->save();
echo "------------------------------\n"; 
$user = $db[User::class][] = new User([
    'name'=>'nUser',
]);
$a    = $user[UserType::class] = new UserType([
    'name'=>'nUserType'
]);
$b    = $user[Post::class][] = new Post([
    'text'=>'nPost1',
    PostType::class => new PostType([
        'id'=>'2',  'name'=>'b'
    ]),
]); 
$c    = $user[Post::class][] = new Post([
    'text'=>'nPost2',
    PostType::class => new PostType([
        'id'=>'2',  'name'=>'b'
    ]),
]);   
//$db->save();
//unset($user,$a,$b,$c); 
echo "------------------------------\n";
 
$db[User::class][] = new User([
    'name'=>'aaa',
    UserType::class => new UserType([
        'name'=>'bbb'
    ]),
    Post::class => [
        new Post([
            'text'=>'aa',
            PostType::class => new PostType([
                'id'=>'3',  'name'=>'c'
            ]),
        ]),
        new Post([
            'text'=>'bb',
            PostType::class => new PostType([
                'id'=>'3',  'name'=>'c'
            ]),
        ]),
    ],
]);    
//unset($user,$a,$b,$c);
echo "------------------------------\n";

$db->sql(User::class)->insert([
    'name'=>'aaaa',
    UserType::class=> new UserType([
        'name'=>'bbb'
    ]),
    Post::class=>[
        new Post([
            'text'=>'aa',
            PostType::class => new PostType([
                'id'=>'4',  'name'=>'d'
            ]),
        ]),
        new Post([
            'text'=>'bb',
            PostType::class => new PostType([
                'id'=>'4',  'name'=>'d'
            ]),
        ]),
    ],
]);

//unset($user,$a,$b,$c);
?>
--EXPECT--  
<!--INSERT INTO `zz_user` (`name` )VALUES(?);oUser-->
<!--REPLACE zz_user_type SET `name`=?;oUserType-->
<!--UPDATE `zz_user` SET `type_id`=?  WHERE (`Id`=?);1,1-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);oPost1,1-->
<!--REPLACE zz_post_type SET `id`=?,`name`=?;1,a-->
<!--UPDATE `zz_post` SET `text`=?,`user_id`=?,`post_type_id`=?  WHERE (`Id`=?);oPost1,1,1,1-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);oPost2,1-->
<!--REPLACE zz_post_type SET `id`=?,`name`=?;1,a-->
<!--UPDATE `zz_post` SET `text`=?,`user_id`=?,`post_type_id`=?  WHERE (`Id`=?);oPost2,1,1,2-->
------------------------------
<!--INSERT INTO `zz_user` (`name` )VALUES(?);nUser-->
<!--REPLACE zz_user_type SET `name`=?;nUserType-->
<!--UPDATE `zz_user` SET `type_id`=?  WHERE (`Id`=?);2,2-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);nPost1,2-->
<!--REPLACE zz_post_type SET `id`=?,`name`=?;2,b-->
<!--UPDATE `zz_post` SET `text`=?,`user_id`=?,`post_type_id`=?  WHERE (`Id`=?);nPost1,2,2,3-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);nPost2,2-->
<!--REPLACE zz_post_type SET `id`=?,`name`=?;2,b-->
<!--UPDATE `zz_post` SET `text`=?,`user_id`=?,`post_type_id`=?  WHERE (`Id`=?);nPost2,2,2,4-->
------------------------------
<!--INSERT INTO `zz_user` (`name` )VALUES(?);aaa-->
<!--REPLACE zz_user_type SET `name`=?;bbb-->
<!--UPDATE `zz_user` SET `name`=?,`type_id`=?  WHERE (`Id`=?);aaa,3,3-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);aa,3-->
<!--REPLACE zz_post_type SET `id`=?,`name`=?;3,c-->
<!--UPDATE `zz_post` SET `text`=?,`user_id`=?,`post_type_id`=?  WHERE (`Id`=?);aa,3,3,5-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);bb,3-->
<!--REPLACE zz_post_type SET `id`=?,`name`=?;3,c-->
<!--UPDATE `zz_post` SET `text`=?,`user_id`=?,`post_type_id`=?  WHERE (`Id`=?);bb,3,3,6-->
------------------------------
<!--INSERT INTO `zz_user` (`name` )VALUES(?);aaaa-->
<!--REPLACE zz_user_type SET `name`=?;bbb-->
<!--UPDATE `zz_user` SET `name`=?,`type_id`=?  WHERE (`Id`=?);aaaa,4,4-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);aa,4-->
<!--REPLACE zz_post_type SET `id`=?,`name`=?;4,d-->
<!--UPDATE `zz_post` SET `text`=?,`user_id`=?,`post_type_id`=?  WHERE (`Id`=?);aa,4,4,7-->
<!--INSERT INTO `zz_post` (`text`,`user_id` )VALUES(?,?);bb,4-->
<!--REPLACE zz_post_type SET `id`=?,`name`=?;4,d-->
<!--UPDATE `zz_post` SET `text`=?,`user_id`=?,`post_type_id`=?  WHERE (`Id`=?);bb,4,4,8-->