<?php  
include __DIR__."/../vendor/autoload.php"; 
  
echo "performance test :select * from [table]\n";
$count=1000;
  
/////////////////////////////////////////////////
 
$db = new dbm\Connect("mysql:dbname=test", 'root', 'root');  
class Post extends \dbm\Model{
    static $table="zz_post";
};

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
for ($i=0; $i < $count; $i++) foreach ($software->zz_post() as $row){ 
    $arr3 =  iterator_to_array($row) ; 
} 
echo microtime(true)-$time;
echo "\n";



echo "laravel/database :";
$time=microtime(true);     
for ($i=0; $i < $count; $i++) foreach(Capsule::table('zz_post')->get() as $row){ 
    $arr4 = (array)$row; 
}
echo microtime(true)-$time;
echo "\n";

 
print_r([$arr1 ,$arr2 ,$arr3,$arr4]);