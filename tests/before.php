<?php
include __DIR__."/../vendor/autoload.php";
///////////// install database ///////////// 
$sync = new \dbm\DBSync(__DIR__."/../example/example.sql"); 
$sync->setPDO('mysql:dbname=test','root','root');
//$sync->pull();  
$sync->push();
$sync->clear();
//////////// model ///////////////////
class User extends dbm\Model
{
    static $table="zz_user";
	static $pks=['Id'];  
	static $ref=[ 
		UserType::class=>['Id'=>'type_id'],
		Post::class=>['user_id'=>'Id'], 
	];    
} 

class UserType  extends dbm\Model
{
    static $table="zz_type";
    static $pks=['Id']; 
	static $ref=[
		User::class=>['type_id'=>'Id'],
	]; 
}
class Post extends dbm\Model
{
    static $table="zz_post";
    static $pks=['Id'];   
    static $ref=[
    	User::class     =>['Id'=>'user_id'],  
    	PostType::class =>['Id'=>'post_type_id']
    ];  
} 
class PostType extends dbm\Model
{
    static $table="zz_post_type";
    static $pks=['Id'];   
	static $ref=[
		Post::class=>['post_type_id'=>'Id'],
	];
}
class Friend extends dbm\Model
{
    static $table="zz_friend";
    static $pks=['uid1','uid2'];    
}

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->sql(User::class)->insertMulit([
	// ['name'=>'u1'],
	// ['name'=>'u2'],
	// ['name'=>'u3'],
	['type_id'=>1,'name'=>'u1'],
	['type_id'=>1,'name'=>'u2'],
	['type_id'=>2,'name'=>'u3'],
]);
$conn->sql(UserType::class)->insertMulit([
	['name'=>'tysfdpe1'],  
	['name'=>'ty21'],  
]);

$conn->sql(PostType::class)->insertMulit([
	['name'=>'type1'],
	['name'=>'type2'],
	['name'=>'type3'],
	['name'=>'nn'],
]);


$conn->sql(Post::class)->insertMulit([
	['user_id'=>1,'post_type_id'=>1, 'text'=>'text1'],
	['user_id'=>1,'post_type_id'=>1, 'text'=>'text2'],
	['user_id'=>1,'post_type_id'=>1, 'text'=>'text3'],
	['user_id'=>2,'post_type_id'=>3, 'text'=>'user2 22'],
	['user_id'=>3,'post_type_id'=>2, 'text'=>'post32'],
	['user_id'=>3,'post_type_id'=>1, 'text'=>'post31'],
]);

// # @var Post
// $a=aaSS();

// $a->
 

// $i=$conn->sql()->getIterator();
// for (;$row = new User($itor->next());) {  

// 	$conn($row)[Post::class][0]['name'];
// 	$conn->obj($row)->one('')->get()->name;
// 	$e->getName();
// }



// foreach ($conn->sql() as $key => $value) {
// 	$row = new User($value,$db); 
// }
 
// $conn->sql()->map(function(Post $p){
// 	return [
// 		'name'=>$p['name'],
// 		'type'=>$p['id'],
// 		'aa'=>$p->cc()
// 	];
// });

// class A{ 
// 	const table=[
// 		'table'=>'name',
// 		'pk'=>'id',
// 	];

// 	const id = int::class;
// 	public $id; 


// 	const name = string::class;
// 	public $name;
// }; 


// $a = new A();
// print_r(A::id);
 