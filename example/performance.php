<?php  
include __DIR__."/../vendor/autoload.php";

echo "performance test :select * from [table]\n";
/////////////////////////////////////////////////
class User extends dbm\Model
{
    public static $table="u_user";
    public static $pks=['ID'];  
}
class Baby extends dbm\Model
{
    public static $table="u_baby";
    public static $pks=['ID'];   
    public static $fks=[
        User::class     =>['User_ID'],  
        BabyType::class =>['baby_type_id']
    ]; 
}  
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
$query = $pdo->prepare("select * from u_baby"); 
$query->execute(); 
while($row=$query->fetch(PDO::FETCH_ASSOC))
    $arr1[]=$row;
echo microtime(true)-$time;
echo "\n";
 
echo "notorm           :";
$time=microtime(true); 
foreach ($software->u_baby() as $row){ 
    $arr2[]= iterator_to_array($row) ; 
} 
echo microtime(true)-$time;
echo "\n";

echo "dbm              :";
$time=microtime(true);  
foreach($db->sql('u_baby') as $row){  
    $arr3[]= $row;// ->toArray() ;
}    
echo microtime(true)-$time;
echo "\n";



echo "laravel/database :";
$time=microtime(true);  
foreach(Capsule::table('u_baby')->get() as $row){ 
    $arr4[]= (array)$row; 
}
echo microtime(true)-$time;
echo "\n";

//print_r([$arr1,$arr2,$arr3,$arr4]);