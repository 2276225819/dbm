--TEST--
关联测试2
TODO:关联查询父级where默认删除条件重新查询
--FILE--
<?php
include __DIR__.'/../before.php';


$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test','root','root'); 
$conn->debug=true;
foreach ($conn->sql(User::class)->and('Id=? or `Id`=3','1') as  $user) {
    echo "USER:".$user['name']."\n";
    #### BUG ######
    # foreach ($user[Post::class]->where("text like ?","%3%") as $post) {
    #### BUG ######
    foreach ($user[Post::class]->and("text like ?","%3%") as $post) {
        echo "   POST:".$post['Id']."  ".$post['text']."\n";   
        echo "   USER:".$post->ref('zz_user',['Id'],['Id'=>'user_id'])['name']."\n";  
        echo "   TYPE:".$post[PostType::class]['name']."\n";  
        echo "\n";
    }   
} 
?>
--EXPECT-- 
<!--SELECT * FROM `zz_user`  WHERE (`Id`=? or `Id`=3)  ;1-->
USER:u1
<!--SELECT * FROM `zz_post`  WHERE (`user_id` in (?,?) ) AND (text like ?)  ;1,3,%3%-->
   POST:3  text3
<!--SELECT * FROM `zz_user`  WHERE (`Id` in (?,?) )  ;1,3-->
   USER:u1
<!--SELECT * FROM `zz_post_type`  WHERE (`Id` in (?,?) )  ;1,2-->
   TYPE:type2

USER:u3
   POST:5  post32
   USER:u3
   TYPE:type2

   POST:6  post31
   USER:u3
   TYPE:type1
