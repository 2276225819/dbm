<?php 
include __DIR__."/../vendor/autoload.php";

///////////// install database ///////////// 
$sync = new \dbm\DBSync(__DIR__."/example.sql"); 
$sync->setPDO('mysql:dbname=test','root','root');
//$sync->pull();  
$sync->push();
$sync->clear();
//////////// model ///////////////////
class User extends dbm\Model
{
    public static $table="zz_user";
    public static $pks=['Id'];  
}
class Post extends dbm\Model
{
    public static $table="zz_post";
    public static $pks=['Id'];   
    public static $fks=[
        User::class     =>['user_id'],  
        PostType::class =>['post_type_id']
    ]; 
} 
class PostType extends dbm\Model
{
    public static $table="zz_post_type";
    public static $pks=['Id'];    
}

///////////// orm test ///////////////////

$test = new \dbm\Connect('mysql:dbname=test','root','root');
 
$affected = $test->sql(User::class)->insertMulit([
	['name'=>'user1'],
	['name'=>'user2'],
	['name'=>'user3'],
]);  
// INSERT INTO zz_user (`name` )VALUES(?),(?),(?) 
// [0] => user1
// [1] => user2
// [2] => user3 


$affected = $test->sql('zz_user')->where('Id=?',3)->update("name=?,Id=4","updated 4");
// UPDATE zz_user SET name=?,Id=4  WHERE Id=?
// [0] => updated 4
// [1] => 3

$affected = $test->sql(User::class)->where([
	'id'=>3
])->delete();
// DELETE FROM zz_user  WHERE  id=? 
// [0] => 3



$post_type = $test->sql('zz_post_type')->insert([
	'name'=>'type1'
],'Id');
// INSERT INTO zz_post_type SET  name=?
// [0] => type1

$post = new Post($test);
$post['post_type_id']=$post_type['Id'];
$post['user_id']='1';
$post['text']='null post';
$bool = $post->create();
// INSERT INTO zz_post SET  post_type_id=?, user_id=?, text=?
// [0] => 1
// [1] => 1
// [2] => null post

$user = $test->load(User::class,'2');
// SELECT * FROM zz_user  WHERE  Id=? 
// [0] => 2

$user['name']='user2 updated';
$bool = $user->save();
// UPDATE zz_user SET  name=?  WHERE  Id=?
// [0] => user2 updated
// [1] => 2
  
$post = $user[Post::class]->insert([
	'text'=>'user2 post1',
	'post_type_id'=>$post_type['Id']
]);
// INSERT INTO zz_post SET  text=?, post_type_id=?, user_id=?
// [0] => user2 post1
// [1] => 1
// [2] => 2

$post = $user[Post::class]->insert([
	'text'=>'user2 post2',
	'post_type_id'=>$post_type['Id']
]);
// INSERT INTO zz_post SET  text=?, post_type_id=?, user_id=? 
// [0] => user2 post2
// [1] => 1
// [2] => 2

$post->destroy();
// DELETE FROM zz_post  WHERE  Id=? 
// [0] => 3 
 

foreach ($test->sql(User::class) as $user) { 
	$posts=array();
	$c = $user[Post::class]; 
	foreach ($c as $post) { 
		$a=1;
		$posts[]=array(
			'user'=>$post[User::class]['name'],
			'type'=>$post[PostType::class]['name'],
			'text'=>$post['text'],
		);
	} 
	$row[] = array(
		'id'=>$user['Id'],
		'name'=>$user['name'],
		'posts'=>$posts,
	);
}     
// SELECT * FROM zz_user    

// SELECT * FROM zz_post  WHERE  user_id in (?,?,?)  
// [0] => 1
// [1] => 2
// [2] => 4 

// SELECT * FROM zz_user  WHERE  Id in (?,?) 
// [0] => 1
// [1] => 2 

// SELECT * FROM zz_post_type  WHERE  Id in (?)  
// [0] => 1 
$test->debug=true;

print_r($test->sql('zz_user')->where('Id=?',2)->fetchAll(\PDO::FETCH_ASSOC));
// SELECT * FROM zz_user  WHERE Id=?
// [0] => 2
 
print_r($test->sql('zz_user')->order('Id desc')->fetchAll(\PDO::FETCH_ASSOC));
// SELECT * FROM zz_user   ORDER BY Id desc


print_r($test->sql('zz_user')->limit(2)->fetchAll(\PDO::FETCH_ASSOC));
// SELECT * FROM zz_user    LIMIT 2


print_r($test->sql('zz_user')->field('Id')->limit(2,1)->fetchAll(\PDO::FETCH_ASSOC));
// SELECT Id FROM zz_user    LIMIT 2 OFFSET 1 


print_r($test->sql('zz_user')->value('count(1)'));
// 3

print_r($test->sql('zz_user')->list('name'));
// Array
// (
//     [0] => user1
//     [1] => user2 updated
//     [2] => updated 4
// )

print_r($test->sql('zz_user')->keypair('name','Id') );
// Array
// (
//     [user1] => 1
//     [user2 updated] => 2
//     [updated 4] => 4
// )


