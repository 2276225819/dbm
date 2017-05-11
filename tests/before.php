<?php
include __DIR__."/../vendor/autoload.php";
///////////// install database ///////////// 
$sync = new \dbm\DBSync(__DIR__."/../example/example.sql"); 
$sync->setPDO('mysql:host=127.0.0.1;dbname=test','root','root');
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
    static $table="zz_user_type";
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
	['type_id'=>1,'name'=>'u1'],
	['type_id'=>1,'name'=>'u2'],
	['type_id'=>2,'name'=>'u3'],
]);
$conn->sql(Friend::class)->insertMulit([
	['uid1'=>1,'uid2'=>2,'nickname'=>'1->2'],
	['uid1'=>1,'uid2'=>3,'nickname'=>'1->3'],
	['uid1'=>2,'uid2'=>3,'nickname'=>'2->3'],
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
// $conn->debug=true;
// $cache = $conn->scope();
// $a = $conn->sql(User::class)->get();
// $b = $conn->sql(User::class)->get();
// $c = $conn->sql(User::class)->get();
// print_r([$a,$b,$c]);
// $conn->debug=true; 
// //$cache=$conn->scope();
// foreach ($conn->sql(Post::class) as $v){
// 	//$v = (new Post($conn,$v));
// 	print_r($v);
// } 
// echo "end"; 
//$conn->sql()->;

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
 