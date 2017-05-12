<?php  
include __DIR__."/../vendor/autoload.php"; 
  
echo "performance test :select * from [table]\n";
$count=10;
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
    'host'      => 'localhost',
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
        $arr1 =$row; 
} 
echo microtime(true)-$time;
echo "\n";

echo "dbm              :";
$time=microtime(true);  
for ($i=0; $i < $count; $i++) {
    foreach($db->sql('zz_post','ID') as $row) 
        $arr2 = $row->toArray()  ;
}    
echo microtime(true)-$time;
echo "\n";

 
echo "notorm           :";
$time=microtime(true); 
for ($i=0; $i < $count; $i++){
    foreach ($software->zz_post() as $row)
        $arr3 =  iterator_to_array($row) ; 
} 
echo microtime(true)-$time;
echo "\n";



echo "laravel/database :";
$time=microtime(true);     
for ($i=0; $i < $count; $i++) {
    foreach(Capsule::table('zz_post')->get() as $row) 
    $arr4 = (array)$row; 
}
echo microtime(true)-$time;
echo "\n";

 
//print_r([$arr1 ,$arr2 ,$arr3,$arr4]);
/*
# 数据*10000 默认*1 单位/秒
performance test :select * from [table]
foreach          :0.033241987228394
native           :0.019859790802002
dbm              :0.029505014419556
notorm           :0.037763833999634
laravel/database :1.0491268634796 

# 数据*10000 默认*100 单位/秒
performance test :select * from [table]
foreach          :0.077172994613647
native           :1.2954540252686
dbm              :0.65180110931396
notorm           :4.3124899864197
laravel/database :2.6619429588318

# 数据*10000 默认*1000 单位/秒
performance test :select * from [table]
foreach          :0.3026750087738
native           :13.114972829819
dbm              :6.2873830795288
notorm           :43.723829984665
laravel/database :17.558816194534



# 数据*10000  xDebug*1 单位/秒
foreach          :0.025607824325562
native           :0.10158801078796
dbm              :0.4140510559082
notorm           :1.2742419242859
laravel/database :1.0866839885712

# 数据*10000  xDebug*10 单位/秒
performance test :select * from [table]
native           :1.2090330123901
dbm              :4.2746579647064
notorm           :12.927770137787
laravel/database :1.2445261478424

 
*/
