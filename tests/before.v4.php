<?php
include __DIR__."/../vendor/autoload.php";
///////////// install database ///////////// 
$sync = new \dbm\DBSync(__DIR__."/../example/example.sql"); 
$sync->setPDO('mysql:host=127.0.0.1;dbname=test','root','root');
//$sync->pull();  
$sync->push();
$sync->clear();
//////////// model ///////////////////


$conn = new \dbm\Connect('mysql:dbname=test','root','root');
 
class User extends dbm\Model
{
    const table="zz_user";
	const pks=['Id'];  
	const ref=[ 
		UserType::class=>['Id'=>'type_id'],
		Post::class=>['user_id'=>'Id'], 
	];    
} 

class UserType  extends dbm\Model
{
    const table="zz_user_type";
    const pks=['Id']; 
	const ref=[
		User::class=>['type_id'=>'Id'],
	]; 
}
class Post extends dbm\Model
{
    const table="zz_post";
    const pks=['Id'];   
    const ref=[
    	User::class     =>['Id'=>'user_id'],  
    	PostType::class =>['Id'=>'post_type_id']
    ];  
} 
class PostType extends dbm\Model
{
    const table="zz_post_type";
    const pks=['Id'];   
	const ref=[
		Post::class=>['post_type_id'=>'Id'],
	];
}
class Friend extends dbm\Model
{
    const table="zz_friend";
    const pks=['uid1','uid2'];    
}

$conn->session(User::class)->insertMulit([ 
	['type_id'=>1,'name'=>'u1'],
	['type_id'=>1,'name'=>'u2'],
	['type_id'=>2,'name'=>'u3'],
]);
$conn->session(Friend::class)->insertMulit([
	['uid1'=>1,'uid2'=>2,'nickname'=>'1->2'],
	['uid1'=>1,'uid2'=>3,'nickname'=>'1->3'],
	['uid1'=>2,'uid2'=>3,'nickname'=>'2->3'],
]);

$conn->session(UserType::class)->insertMulit([
	['name'=>'tysfdpe1'],  
	['name'=>'ty21'],  
]);

$conn->session(PostType::class)->insertMulit([
	['name'=>'type1'],
	['name'=>'type2'],
	['name'=>'type3'],
	['name'=>'nn'],
]);


$conn->session(Post::class)->insertMulit([
	['user_id'=>1,'post_type_id'=>1, 'text'=>'text1'],
	['user_id'=>1,'post_type_id'=>1, 'text'=>'text2'],
	['user_id'=>1,'post_type_id'=>1, 'text'=>'text3'],
	['user_id'=>2,'post_type_id'=>3, 'text'=>'user2 22'],
	['user_id'=>3,'post_type_id'=>2, 'text'=>'post32'],
	['user_id'=>3,'post_type_id'=>1, 'text'=>'post31'],
]); 