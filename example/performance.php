<?php  
include __DIR__."/../vendor/autoload.php"; 
 
  
$count=10;
echo "performance test :select * from [table] * $count\n";
/////////////////////////////////////////////////
 
$db = new dbm\Connect("mysql:dbname=test", 'root', 'root');  
class Post extends \dbm\Model{
    static $table="zz_post";
};
// for ($i=0; $i < 10000; $i++) { 
//     $db->execute("insert zz_post set text='".rand(0,999)."', post_type_id='".rand(0,999)."', user_id='".rand(0,999)."';");
// } exit;

$pdo = new PDO("mysql:dbname=test", 'root', 'root');
$connection = new PDO("mysql:dbname=test","root","root");
$structure = new NotORM_Structure_Convention(
    $primary = "ID", // id_$table
    $foreign = "id_%s", // id_$table
    $table = "%s"// {$table}s
    //$prefix = "wp_" // wp_$table
);
$software = new NotORM($connection,$structure); 

use Illuminate\Database\Capsule\Manager as Capsule;
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'database'  => 'test',
    'username'  => 'root',
    'password'  => 'root',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);  
$capsule->setAsGlobal();  
$capsule->bootEloquent(); 

echo "foreach          :";
$time=microtime(true); 
$query = $pdo->prepare("select * from zz_post"); 
$query->execute(); 
$rows=$query->fetchAll(PDO::FETCH_ASSOC);
for ($i=0; $i < $count; $i++){ 
    foreach ($rows as $row)  
        $arr1 = $row; 
} 
echo microtime(true)-$time;
echo "\n";

echo "native           :";
$time=microtime(true); 
$query = $pdo->prepare("select * from zz_post"); 
for ($i=0; $i < $count; $i++){
    $query->execute(); 
    while($row=$query->fetch(PDO::FETCH_ASSOC))
        $arr2 =$row; 
} 
echo microtime(true)-$time;
echo "\n";

echo "dbm              :";
$time=microtime(true);  
for ($i=0; $i < $count; $i++) {
    foreach($db->sql('zz_post','ID') as $row) 
        $arr3 = $row->toArray()  ;
}    
echo microtime(true)-$time;
echo "\n";


echo "dbm.v4           :";
$time=microtime(true);  
for ($i=0; $i < $count; $i++) {
    foreach($db->model('zz_post','ID') as $row) 
        $arr4 = $row->toArray();
}    
echo microtime(true)-$time;
echo "\n";


echo "notorm           :";
$time=microtime(true); 
for ($i=0; $i < $count; $i++){
    foreach ($software->zz_post() as $row)
        $arr5 =  iterator_to_array($row) ; 
} 
echo microtime(true)-$time;
echo "\n";



echo "laravel/database :";
$time=microtime(true);     
for ($i=0; $i < $count; $i++) {
    foreach(Capsule::table('zz_post')->get() as $row) 
    $arr6 = (array)$row; 
}
echo microtime(true)-$time;
echo "\n";

 
//print_r([$arr1 ,$arr2 ,$arr3,$arr4,$arr5,$arr6]);
/*
# 数据*10000 默认*1 单位/秒
performance test :select * from [table]
foreach          :0.013476848602295
native           :0.013571977615356
dbm              :0.019706010818481
dbm.v4           :0.017701148986816
notorm           :0.038381099700928
laravel/database :0.043095111846924

# 数据*10000 默认*100 单位/秒
performance test :select * from [table]
foreach          :0.036971092224121
native           :1.2759008407593
dbm              :0.67776989936829
dbm.v4           :0.33889412879944
notorm           :4.4621398448944
laravel/database :1.5763001441956

# 数据*10000 默认*1000 单位/秒
performance test :select * from [table]
foreach          :0.25033402442932
native           :12.837521076202
dbm              :6.5474979877472
dbm.v4           :3.3023991584778
notorm           :43.468582868576
laravel/database :16.637385129929



# 数据*10000  xDebug*1 单位/秒
foreach          :0.015952110290527
native           :0.10339283943176
dbm              :0.30134701728821
dbm.v4           :0.18534588813782
notorm           :1.3557300567627
laravel/database :0.048876047134399

# 数据*10000  xDebug*10 单位/秒
performance test :select * from [table]
foreach          :0.033928871154785
native           :0.98890900611877
dbm              :2.8212649822235
dbm.v4           :1.7280220985413
notorm           :12.573439121246
laravel/database :0.20769906044006

 
*/
