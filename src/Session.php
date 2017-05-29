<?php namespace dbm;

class Session
{
	/** 
	 * @var Connect
	 */
	public $conn=null;
    public $cache=[];//[SQL:hash=>[arr,arr,arr]
    /** 
     * @var Session
     */
    public static $instance;
    public static $gc;
    function __construct(&$conn)
    {
        $this->conn=$conn;
    }
    function select($sql,$all=false)
    {
        $hash = (string)$sql; 
        if (!isset($this->cache[$hash])) { 
            $ssql = $sql->bulidSelect();
            $args = $sql->bulidArgs();
			$fetch = $this->conn->execute($ssql, $args);
			$fetch->setFetchMode(\PDO::FETCH_ASSOC);
			$this->cache[$hash]=$fetch->fetchAll(); 
		}
		if($all or !count($sql->rArgs)){
			return $this->cache[$hash];
		}else{
			foreach ($this->cache[$hash] as $row) {
				foreach ($sql->rArgs as $k => $v) {
					if ($row[$k]!=$v) {
						continue 2;
					}
				}
				$arr[]=$row;
			}
			return $arr??[];
		} 
    }
    function delete($sql)
    {      
		$str="DELETE FROM {$sql->table} {$sql->wStr}";
        if (!($query = $this->conn->execute($str, $sql->wArgs))) {
            throw new \Exception("Error Processing Delete", 1);
        }
        return $query->rowCount();
    }
    function update($sql,$data,...$arr)
    {
        $param = [];
        $data = $sql->kvSQL($param, ',', $data, $arr);
        $str = "UPDATE {$sql->table} SET {$data} {$sql->wStr}";
        $param = array_merge($param, $sql->wArgs);
        if (!($query = $this->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Update", 1);
        }
        return $query->rowCount();
    }
    function insert($sql, $data)
    {
        $data = array_merge($data, $sql->rArgs, $sql->sArgs); 
        $sql1 = '';
        $sql2 = ",(".substr(str_repeat(",?", count($data)), 1).")"; 
        foreach ($data as $key => $value) {
            $sql1.=",`{$key}`";
            $param[]=$value;
        } 
        $str="INSERT INTO {$sql->table} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        if (!($query = $this->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Insert" );
        }
        //AUTO INCREMENT
        $last_id = $this->conn->lastInsertId();
        if (!empty($last_id)) { 
            $data[$sql->pks[0]]=$last_id;
        } 
		//$this->clean($sql->table);
        return $data;
    }
	function insertMulit($sql,$list){
		$param=[];
        $sql1 = "";
        $sql2 = "";
        foreach ($list as &$arr) {
            $arr = array_merge($arr, $sql->sArgs, $sql->rArgs);
            $sql2.=",(".substr(str_repeat(",?", count($arr)), 1).")";
            foreach ($arr as $value) {
                $param[]=$value;
            }
        }
        foreach ($list[0] as $key => $value) {
            $sql1.=",`{$key}`";
        }
        $str="INSERT INTO {$sql->table} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        if (!($query = $this->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Insert Mulit", 1);
        }
        return $query->rowCount();
	}

    function clean($table = null)
    {
        if (empty($table)) {
            $this->cache=[];
            $this->db=null;
        } else {
            foreach ($this->cache as $key => $value) {
                if (strstr($key, $table)) {
                    unset($this->cache[$key]);
                }
            }
        }
    } 
}
