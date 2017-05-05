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
    public static $table="zz_user";
    public static $pks=['Id'];  
    public static $fks=[
		UserType::class=>['type_id']
	];  
}
class UserType  extends dbm\Model
{
    public static $table="zz_user_type";
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
class Friend extends dbm\Model
{
    public static $table="zz_friend";
    public static $pks=['uid1','uid2'];    
}

$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->sql(User::class)->insertMulit([
	['name'=>'u1'],
	['name'=>'u2'],
	['name'=>'u3'],
	// ['Type_id'=>1,'name'=>'u1'],
	// ['Type_id'=>1,'name'=>'u2'],
	// ['Type_id'=>1,'name'=>'u3'],
]);
// $conn->sql('zz_type')->insertMulit([
// 	['name'=>'tysfdpe1'],  
// ]);

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

