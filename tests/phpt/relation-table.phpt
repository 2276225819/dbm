--TEST--


--FILE--
<?php
include __DIR__."/../before.php";


$conn = new \dbm\Connect('mysql:dbname=test','root','root');
$conn->debug=true;
 
foreach($conn->sql('zz_post_type') as $pt){
    echo "POST_TYPE: ".$pt['name']."\n"; 
    foreach ($pt->many('zz_post','Id','post_type_id') as $post) {
        echo "  ID     : ".$post['Id']."\n";
        echo "  Author : ".$post->one('zz_user','Id','user_id')->get()['name']."\n";
        echo "  Type   : ".$post->one('zz_post_type','Id','post_type_id')->get()['name']."\n";
        echo "\n";
    }

}
?>
--EXPECTF-- 
<!--SELECT * FROM zz_post_type   ;-->
POST_TYPE: type1
<!--SELECT * FROM zz_post  WHERE  post_type_id in (?,?,?,?)   ;1,2,3,4-->
  ID     : 1
<!--SELECT * FROM zz_user  WHERE  Id in (?,?,?)   ;1,2,3-->
  Author : u1
<!--SELECT * FROM zz_post_type  WHERE  Id in (?,?,?)   ;1,2,3-->
  Type   : type1

  ID     : 2
  Author : u1
  Type   : type1

  ID     : 3
  Author : u1
  Type   : type1

  ID     : 6
  Author : u3
  Type   : type1

POST_TYPE: type2
  ID     : 5
  Author : u3
  Type   : type2

POST_TYPE: type3
  ID     : 4
  Author : u2
  Type   : type3

POST_TYPE: nn