<?php namespace dbm;
 
trait SqlSetter
{
	

    public function set($data){
        $data = array_merge($this->rArgs,$data);
        foreach ($this->pks as $key) {
            if(!empty($data[$key]))
                $where[$key]=$data[$key]; 
        }
        if(empty($where)){
            $this->insert($data);  
        }
        else if($row = $this->where($where)[0]){
            foreach ($data as $key => $value) {
                $row[$key]=$value;
            }
            $row->save();
        } 
        return $this;
    } 
	//////////////////////////////////


	public function insert($data, $auto_increment_key = null)
    {
        $data = array_merge($data,$this->rArgs, $this->sArgs);
        $sql="INSERT INTO {$this->table} SET ".$this->kvSQL($param, ',', $data);
        if (!($query = $this->db->execute($sql, $param))) {
            throw new \Exception("Error Processing Insert" );
        }
        //AUTO INCREMENT
        $last_id = $this->db->lastInsertId();
        if(!empty($last_id)){
            $key = $auto_increment_key??$this->pks[0];
            $data[$key]=$last_id; 
            if(isset($this->rModel)){
                foreach ($this->rfks as $i => $k) {
                    $this->rModel[$k]=$data[$this->pks[$i]];
                } 
                $this->rModel->save();
            }
        }  
        $row = new $this->model($this->db,$data,$this); 
        return $row;
    }
  	public function insertMulit($list) :int
    {
        $param=[];
        $sql1 = "";
        $sql2 = "";
        foreach ($list as &$arr) {
            $arr = array_merge($arr, $this->sArgs,$this->rArgs);
            $sql2.=",(".substr(str_repeat(",?", count($arr)), 1).")";
            array_push($param, ...array_values($arr));
        }
        foreach ($list[0] as $key => $value) {
            $sql1.=",`{$key}`";
        }
        $sql="INSERT INTO {$this->table} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        if (!($query = $this->db->execute($sql, $param))) {
            throw new \Exception("Error Processing Insert Mulit", 1);
        }
        return $query->rowCount();
    }
    public function update($data, ...$arr) :int
    {
        if (empty($this->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $param=[];
        $data=$this->kvSQL($param, ',', $data, $arr);
        $sql="UPDATE {$this->table} SET {$data} {$this->wStr}";
        $param = array_merge($param, $this->wArgs);
        if (!($query = $this->db->execute($sql, $param))) {
            throw new \Exception("Error Processing Update", 1);
        }
        return $query->rowCount();
    }
    public function delete() :int
    {
        if (empty($this->wStr)) {
            return false;
        }
        $sql="DELETE FROM {$this->table} {$this->wStr}";
        if (!($query = $this->db->execute($sql, $this->wArgs))) {
            throw new \Exception("Error Processing Delete", 1);
        }
        return $query->rowCount();
    }


}
