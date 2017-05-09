<?php namespace dbm;

trait SqlRelation
{  
	public function many($model,$model_pks,$model_fks){
		return $this->ref($model,array_combine((array)$model_fks,(array)$model_pks));
	}
	public function one($model,$model_pks,$local_fks){
		return $this->ref($model,array_combine((array)$model_pks,(array)$local_fks));
	} 
	function ref($model,$ref){   
		$pks = array_keys($ref);
		$sql = $this->db->sql($model,...$pks); 
		$vstr = substr(str_repeat(",?", count($ref)), 1);
		foreach($this->getAll() as $row){    
			$s=[];foreach ($ref as $k=>$f) $s[]=$row[$f]; 
			if(empty($strlist[$k=join($s)])){
				$strlist[$k]=",($vstr)";
				array_push($sql->wArgs,...$s);
			}  
		} 
		$valstr = substr(join(array_values($strlist)),1);
		$keystr = join($pks,',');
		$sql->wStr=" WHERE ($keystr) in ($valstr)";
		return $sql;
	}
}
