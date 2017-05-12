<?php namespace dbm;

trait ConnectTransaction{

    public function begin(){ 
         return $this->db->beginTransaction();
    }
    public function commit(){
         return $this->db->commit(); 
    } 
    public function rollback(){
         return $this->db->rollBack(); 
    } 
}