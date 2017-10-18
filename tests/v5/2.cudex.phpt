--TEST-- 
#Model->insert(ARRAY,...)        #Model[]=...    //self Model self:          插入到集合
#Model->insert(ARRAY,...)        #Model[]=...    //self Model hasmany:       插入到集合?parent.pk 
#Model->insert(ARRAY,...)        #Model[]=...    //self Model hasone:        插入到集合?并执行设置parent.fk  
#Model->save(ARRAY)                              //self Model self:first     插入到集合 失败就 修改
#Model->save(ARRAY)                              //self Model hasmany:first  插入到集合 失败就 修改parent.pk 
#Model->save(ARRAY)                              //self Model hasone:first   插入到集合 失败就 修改并执行设置parent.fk
#Model->update(ARRAY)                            //self RowCount             根据当前model条件修改集合(可能空)
#Model->delete(TRUE)                             //self RowCount             根据当前model条件删除集合(可能空)
 
--FILE--
<?php 
include __DIR__."/../before.php";

$conn = new \dbm\Connect('mysql:host=127.0.0.1;dbname=test2','root','root');
$conn->debug=true;


$user = $conn->sql(User::class,'Id') ;  
$posts = $user->ref(Post::class,'Id',['user_id'=>'Id']);
$posts->update(['text'=>'666']);
$posts->delete();

echo "\n";


$user = $conn->sql(User::class,'Id')->get();  
$posts = $user->ref(Post::class,'Id',['user_id'=>'Id']);
$posts->update(['text'=>'666']);
$posts->delete();

echo "\n";

$user = $conn->sql(User::class,'Id')->first(2);  
$posts = $user->ref(Post::class,'Id',['user_id'=>'Id']);
$posts->update(['text'=>'777']);
$posts->delete();

echo "\n";


?>
--EXPECTF--  
<!--UPDATE `zz_post` SET `text`=?  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ));666-->
<!--DELETE FROM `zz_post`  WHERE (`user_id` in (SELECT Id FROM `zz_user`   ));-->

<!--SELECT * FROM `zz_user`   ;-->
<!--UPDATE `zz_post` SET `text`=?  WHERE (`user_id`=?);666,1-->
<!--DELETE FROM `zz_post`  WHERE (`user_id`=?);1-->

<!--SELECT * FROM `zz_user`    LIMIT 1 OFFSET 2 ;-->
<!--UPDATE `zz_post` SET `text`=?  WHERE (`user_id`=?);777,3-->
<!--DELETE FROM `zz_post`  WHERE (`user_id`=?);3-->