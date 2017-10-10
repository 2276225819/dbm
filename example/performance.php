<?php  
include __DIR__."/../vendor/autoload.php"; 
 
 
$row=10000;
$count=100;
echo "#performance test :select * from [table]  $row/row * $count/times\n";
/////////////////////////////////////////////////
$pdo = new PDO("mysql:dbname=test2", 'root', 'root');
$db = new dbm\Connect("mysql:dbname=test2", 'root', 'root'); 

// $db->execute('begin');
// $db->execute('truncate zz_post');
// for ($i=0; $i < $row; $i++) { 
//     $db->execute("insert zz_post set text='".rand(0,999)."', post_type_id='".rand(0,999)."', user_id='".rand(0,999)."';");
// } 
// $db->execute('commit');
// exit;

$connection = new PDO("mysql:dbname=test2","root","root");
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
    'database'  => 'test2',
    'username'  => 'root',
    'password'  => 'root',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);  
$capsule->setAsGlobal();  
$capsule->bootEloquent(); 

echo "foreach(Global)  :                      ";//遍历数组 理论最高速度
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

echo "dbquery          :                      ";//查数据库 理论最高速度
$time=microtime(true); 
$query = $pdo->prepare("select * from zz_post"); 
for ($i=0; $i < $count; $i++){
    $query->execute(); 
    while($row=$query->fetch(PDO::FETCH_ASSOC))
        $arr2 =$row; 
} 
echo microtime(true)-$time;
echo "\n"; 
 


echo "dbm.v5           :";
$time1=microtime(true);   
for ($i=0; $i < $count; $i++) {
    foreach($db->sql('zz_post','Id') as $row) 
        $arr5 = (array)$row;
}    
echo microtime(true)-$time1;
echo "    \t"; 
$time2=microtime(true);   
for ($i=0; $i < $count; $i++) {
    foreach($db->sql('zz_post','Id') as $row) 
        $arr5 = (array)$row;
}    
echo microtime(true)-$time2;
echo "\n"; 


echo "dbm.v4           :";
$time1=microtime(true);   
for ($i=0; $i < $count; $i++) {
    foreach($db->v4('zz_post','ID') as $row) 
        $arr4 = $row->toArray();
}    
echo microtime(true)-$time1;
echo "    \t"; 
$time2=microtime(true);   
for ($i=0; $i < $count; $i++) {
    foreach($db->v4('zz_post','ID') as $row) 
        $arr4 = $row->toArray();
}    
echo microtime(true)-$time2;
echo "\n";
 
 
echo "dbm.v3(Global)   :";
$time1=microtime(true);  
for ($i=0; $i < $count; $i++) {
    foreach($db->entity('zz_post','ID') as $row) 
        $arr3 = $row->toArray()  ;
}    
echo microtime(true)-$time1;
echo "    \t";  
$time2=microtime(true);  
for ($i=0; $i < $count; $i++) {
    foreach($db->entity('zz_post','ID') as $row) 
        $arr3 = $row->toArray()  ;
}    
echo microtime(true)-$time2;
echo "\n";


echo "notorm           :";
$time1=microtime(true); 
for ($i=0; $i < $count; $i++){
    foreach ($software->zz_post() as $row)
        $arr6 =  iterator_to_array($row) ; 
} 
echo microtime(true)-$time1;
echo "    \t"; 
$time2=microtime(true); 
for ($i=0; $i < $count; $i++){
    foreach ($software->zz_post() as $row)
        $arr6 =  iterator_to_array($row) ; 
} 
echo microtime(true)-$time2;
echo "\n";



echo "laravel/database :";
$time1=microtime(true);     
for ($i=0; $i < $count; $i++) {
    foreach(Capsule::table('zz_post')->get() as $row) 
        $arr7 = (array)$row; 
}
echo microtime(true)-$time1;
echo "    \t"; 
$time2=microtime(true);     
for ($i=0; $i < $count; $i++) {
    foreach(Capsule::table('zz_post')->get() as $row) 
        $arr7 = (array)$row; 
}
echo microtime(true)-$time2;
echo "\n";

echo "end";
//print_r([$arr1 ,$arr2 ,$arr3,$arr4,$arr5,$arr6,$arr7]);
exit;exit;exit;
 
/*
#performance test :select * from [table]  1/row * 1/times
foreach(Global)  :                      0.00021791458129883
dbquery          :                      6.1988830566406E-5
dbm.v5           :0.00038290023803711  	8.0108642578125E-5
dbm.v4           :9.7990036010742E-5  	9.0122222900391E-5
dbm.v3(Global)   :0.0024499893188477  	1.5020370483398E-5
notorm           :0.00032711029052734  	9.7036361694336E-5
laravel/database :0.010205984115601  	0.00036907196044922
end

#performance test :select * from [table]  1/row * 100/times
foreach(Global)  :                      0.00017213821411133
dbquery          :                      0.0039019584655762
dbm.v5           :0.0066611766815186   	0.0057430267333984
dbm.v4           :0.0062539577484131   	0.0064260959625244
dbm.v3(Global)   :0.0018379688262939   	0.00043511390686035
notorm           :0.0095710754394531   	0.0094048976898193
laravel/database :0.029716968536377   	0.019244909286499
end

#performance test :select * from [table]  1/row * 10000/times
foreach(Global)  :                      0.0010700225830078
dbquery          :                      0.43901395797729
dbm.v5           :0.70329594612122   	0.76866388320923
dbm.v4           :0.84424495697021   	0.73879790306091
dbm.v3(Global)   :0.039921045303345   	0.03781795501709
notorm           :0.83988404273987   	0.80965399742126
laravel/database :1.5887479782104   	1.7457339763641
end

#performance test :select * from [table]  100/row * 1/times
foreach(Global)  :                      0.00038504600524902
dbquery          :                      0.00019383430480957
dbm.v5           :0.00057792663574219  	0.00027585029602051
dbm.v4           :0.00024700164794922  	0.00025200843811035
dbm.v3(Global)   :0.0015048980712891  	6.2942504882812E-5
notorm           :0.00059199333190918  	0.00042200088500977
laravel/database :0.010113000869751  	0.00049304962158203
end

#performance test :select * from [table]  100/row * 100/times
foreach(Global)  :                      0.00075006484985352
dbquery          :                      0.018127918243408
dbm.v5           :0.024785041809082  	0.027948141098022
dbm.v4           :0.023828983306885  	0.022302150726318
dbm.v3(Global)   :0.0076360702514648  	0.0056309700012207
notorm           :0.046808958053589  	0.047224998474121
laravel/database :0.056231021881104  	0.032281875610352
end

#performance test :select * from [table]  100/row * 10000/times
foreach(Global)  :                      0.024943113327026
dbquery          :                      1.7919030189514
dbm.v5           :2.4413340091705    	2.4686191082001
dbm.v4           :2.3816838264465    	2.4301030635834
dbm.v3(Global)   :0.5615029335022    	0.55530214309692
notorm           :4.7131578922272    	5.0123908519745
laravel/database :3.6274359226227    	3.6631269454956
end

#performance test :select * from [table]  10000/row * 1/times
foreach(Global)  :                      0.013427972793579
dbquery          :                      0.012860059738159
dbm.v5           :0.021126985549927    	0.01988697052002
dbm.v4           :0.019299030303955    	0.019561052322388
dbm.v3(Global)   :0.020287990570068    	0.0067648887634277
notorm           :0.038771867752075    	0.051630973815918
laravel/database :0.038722038269043    	0.017718076705933
end

#performance test :select * from [table]  10000/row * 100/times
foreach(Global)  :                      0.035434007644653
dbquery          :                      1.2636430263519
dbm.v5           :1.7911200523376    	1.7857141494751
dbm.v4           :1.858286857605    	1.8483180999756
dbm.v3(Global)   :0.67568111419678    	0.66301417350769
notorm           :4.2804410457611    	4.4814329147339
laravel/database :1.5430729389191    	1.588604927063
end
*/
