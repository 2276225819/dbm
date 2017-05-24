<?php namespace dbm;

class Session
{
	/** 
	 * @var Connect
	 */
	public $conn;
    public $cache=[];//[SQL:hash=>[arr,arr,arr]
    /** 
     * @var Session
     */
    public static $instance;
    public static $gc;
    function __construct($conn)
    {
        $this->conn=$conn;
    }
    function select($sql,$all=false)
    {
        $ssql = $sql->bulidSelect();
        $args = $sql->bulidArgs();
        $hash = "$ssql;".join($args, ',');
        if (!isset($this->cache[$hash])) { 
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
    function delete($sql)
    {      
		$str="DELETE FROM {$sql->table} {$sql->wStr}";
        if (!($query = $this->conn->execute($str, $sql->wArgs))) {
            throw new \Exception("Error Processing Delete", 1);
        }
        return $query->rowCount();
    }
    function insert($sql, $data)
    {
        $data = array_merge($data, $sql->rArgs, $sql->sArgs);
        $str = "INSERT INTO {$sql->table} SET ".$sql->kvSQL($param, ',', $data);
        if (!($query = $this->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Insert" );
        }
        //AUTO INCREMENT
        $last_id = $this->conn->lastInsertId();
        if (!empty($last_id)) {
            $key = $sql->pks[0];
            $data[$key]=$last_id;

            if(isset($sql->rsql)){ 
                unset($sql->rArgs);
                $sql->where([$key=>$last_id]);
                foreach ($sql->rref as $i => $k) {
                    $set[$k]=$data[$i];
                }
                $sqlclone = clone $sql->rsql;
                $this->update($sqlclone->where($sqlclone->rArgs),$set); 
            }
        }
		$this->clean($sql->table);
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
        } else {
            foreach ($this->cache as $key => $value) {
                if (strstr($key, $table)) {
                    unset($this->cache[$key]);
                }
            }
        }
    } 
}
