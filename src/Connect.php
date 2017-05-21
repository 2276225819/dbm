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

    public function bulidSql($sql){   
        return preg_replace($this->preg_key,$this->preg_val,$sql);    
    }
    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }
    /** 
     * @param string $sql
     * @param array $args
     * @return \PDOStatement
     */
    public function execute($sql, $args = [] ) 
    {   
        $sql = $this->bulidSql($sql); 
        if ($this->debug) {
            echo "<!--$sql;".join($args,',')."-->\n";
        }
        while (true) {
            try { 
                $query = $this->db->prepare($sql);
                return $query->execute($args)?$query:false; 
            } catch (Throwable $e) {
                if ($e->errorInfo[0] == 70100||$e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013) {
                    sleep(1);//必须的？？
                    $this->reload();
                    continue;
                }
                throw $e;
            }
        }
    }
 

    /** 
     * @param boolean $transaction 
     * @return Transaction
     */
	public function scope($transaction=false) { 
		return new Transaction($transaction?$this->db:null);		
	}
  /** 
     * @param string $model
     * @param array $pks
     * @return Sql
     */
	public function sql($model, $pks=[]) 
	{ 
        $pks = (array)$pks;
        if (class_exists($model) && isset($model::$table) ) {
            $table = $model::$table;
			$pks = count($pks)?$pks:$model::$pks;
            $model = $model;
        } else {
            $table=$model;
			$pks=(array)$pks;
			$model = \dbm\Entity::class;
        }
		return new \dbm\Sql($this,$table,$pks,$model);

    }

    public function session(){
        return new Session($this);
    }
}
