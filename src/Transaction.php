<?php namespace dbm;

class Transaction {

	/**
	 * @var \PDO
	 */
	public $db;

	public function __construct($pdo=null){
		if(!empty($pdo)){
			$this->db=$pdo;
			$tihs->db->beginTransaction();  
		}
   
		Sql::$gc++;
	}
	public function __destruct(){
		$this->end(); 
		Sql::$gc--; 
	}

	public function commit(){
		if(isset($this->db)){
			$this->db->commit();
			unset($this->db); 
		}
	}
	public function clear(){ 
		Sql::$qs=[];
		Sql::$cs=[];
	}
	public function end(){
		if(isset($this->db)){
			$this->db->rollback();
			unset($this->db);  
		} 
	} 
}