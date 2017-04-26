<?php  
include __DIR__."/../vendor/autoload.php"; 
/* 
# XDEBUG:
performance test :select * from [table]
native           :0.0076408386230469
notorm           :5.7928161621094
dbm              :3.4440350532532
laravel/database :3.9809930324554
# DEFAULT:
performance test :select * from [table]
native           :0.0061118602752686
notorm           :2.7986099720001
dbm              :2.2209341526031
laravel/database :3.1847031116486
*/
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
$db = new dbm\Connect("mysql:dbname=bplus", 'root', 'root');  
// $pdo = new PDO("mysql:dbname=bplus", 'root', 'root');
// $connection = new PDO("mysql:dbname=bplus","root","root");
// $structure = new NotORM_Structure_Convention(
//     $primary = "ID", // id_$table
//     $foreign = "id_%s", // id_$table
//     $table = "%s"// {$table}s
//     //$prefix = "wp_" // wp_$table
// );
// $software = new NotORM($connection,$structure); 

// use Illuminate\Database\Capsule\Manager as Capsule;
// $capsule = new Capsule;
// $capsule->addConnection([
//     'driver'    => 'mysql',
//     'host'      => 'localhost',
//     'database'  => 'bplus',
//     'username'  => 'root',
//     'password'  => 'root',
//     'charset'   => 'utf8',
//     'collation' => 'utf8_unicode_ci',
//     'prefix'    => '',
// ]);  
// $capsule->setAsGlobal();  
// $capsule->bootEloquent();
$count=100;
  

// echo "native           :";
// $time=microtime(true); 
// $query = $pdo->prepare("select * from h_temperature"); 
// $query->execute(); 
// for ($i=0; $i < $count; $i++) while($row=$query->fetch(PDO::FETCH_ASSOC))
//     $arr1 =$row;
// echo microtime(true)-$time;
// echo "\n";
 
// echo "notorm           :";
// $time=microtime(true); 
// for ($i=0; $i < $count; $i++) foreach ($software->h_temperature() as $row){ 
//     $arr2 =  ($row) ; 
// } 
// echo microtime(true)-$time;
// echo "\n";

echo "dbm              :";
$time=microtime(true);  
for ($i=0; $i < $count; $i++) foreach($db->sql('h_temperature') as $row){  
    $arr3 = $row  ;
}    
echo microtime(true)-$time;
echo "\n";



// echo "laravel/database :";
// $time=microtime(true);     
// for ($i=0; $i < $count; $i++) foreach(Capsule::table('h_temperature')->get() as $row){ 
//     $arr4 = (array)$row; 
// }
// echo microtime(true)-$time;
// echo "\n";

//print_r([$arr1[1],$arr2[1],$arr3[1],$arr4[1]]);