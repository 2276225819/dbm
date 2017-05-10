<?php namespace dbm;

class Connect implements \ArrayAccess
{ 
	use ConnectAccess;
	use ConnectTransaction;//deprecated

	
    public $debug=false; 
    public $attr=[
        \PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_PERSISTENT=>true,
    ];

    
    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }
    public function execute($sql, $args = []):\PDOStatement
    {
        if (isset($this->prefix)) {
            $pf = $this->prefix;
            $sql = preg_replace(
                array('/((?:join|truncate|into|from|create table|alter table|as)\s+)([\w]+)/' ,
                        '/(\w+)\s+(read|write|set)/',  '(\w+\.[\w\*]+)'),
                array("$1 `$pf$2`","`$pf$1` $2" ,"`$pf$0`"), $sql );
        }
        while (true) {
            try {
                if ($this->debug) {
                    echo "<!--$sql;".join($args,',')."-->\n";
                }
                $query = $this->db->prepare($sql);
                return $query->execute($args)?$query:false;
            } catch (Throwable $e) {
                if ($e->errorInfo[0] == 70100||$e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013) {
                    sleep(1);//必须的？？
                    $this->__reload();
                    continue;
                }
                throw $e;
            }
        }
    }
 


	public function scope($transaction=false):Transaction { 
		return new Transaction($transaction?$this->db:null);		
	}
	public function sql($model,...$pks):Sql
	{ 
        if (class_exists($model)&& isset( $model::$table) ) {
            $table = $model::$table??$model;
			$pks = count($pks)?$pks:$model::$pks;
            $model = $model;
        } else {
            $table=$model;
			$pks=(array)$pks;
			$model = \dbm\Model::class;
        }
		return new \dbm\Sql($this,$table,$pks,$model);

	} 
}
