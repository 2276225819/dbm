<?php  
include __DIR__."/../vendor/autoload.php"; 
  
echo "performance test :select * from [table]\n";
$count=1000;
  
/////////////////////////////////////////////////
 
$db = new dbm\Connect("mysql:dbname=test", 'root', 'root');  
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
$query->execute(); 
for ($i=0; $i < $count; $i++) while($row=$query->fetch(PDO::FETCH_ASSOC))
    $arr1 =$row;
echo microtime(true)-$time;
echo "\n";

echo "dbm              :";
$time=microtime(true);  
for ($i=0; $i < $count; $i++) foreach($db->sql('zz_post','ID') as $row){  
    $arr3 = (array)$row  ;
}    
echo microtime(true)-$time;
echo "\n";

 
echo "notorm           :";
$time=microtime(true); 
for ($i=0; $i < $count; $i++) foreach ($software->zz_post() as $row){ 
    $arr2 =  ($row) ; 
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

exit;
//print_r([$arr1[1],$arr2[1],$arr3[1],$arr4[1]]);