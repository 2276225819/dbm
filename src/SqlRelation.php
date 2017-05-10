<?php namespace dbm;

trait SqlRelation
{  
	public function many($model,$model_pks,$model_fks){
		return $this->ref($model,(array)$model_pks,
			array_combine((array)$model_fks,(array)$model_pks)
		);
	}
	public function one($model,$model_pks,$local_fks){
		return $this->ref($model,(array)$model_pks,
			array_combine((array)$model_pks,(array)$local_fks)
		);
	} 
	function ref($model,array $pks,array $ref){
		$sql = $this->db->sql($model,...$pks); 
		$arr = iterator_to_array($this->getAllIterator());
		if(count($arr)>1){ 
			$vstr = count($ref)>1?"(".substr(str_repeat(",?", count($ref)), 1).')':'?';
			foreach($arr as $row){    
				$s=[];foreach ($ref as $k=>$f) $s[]=$row[$f]; 
				if(empty($strlist[$k=join($s)])){
					$strlist[$k]=",$vstr";
					array_push($sql->wArgs,...$s);
				}  
			} 
			sort($sql->wArgs);//hash匹配一致
			$valstr = substr(join(array_values($strlist)),1); 
			$pks = array_keys($ref);
			$keystr = count($pks)>1?"(".join($pks,',').")":$pks[0];
			$sql->wStr=" WHERE $keystr in ($valstr) ";
		}else{ 
			$str='';
			foreach ($ref as $key => $value) {
				$str.=" AND $key=?";
				$sql->wArgs[]=$this[$value];
			}
			$sql->wStr=" WHERE".substr($str,4); 
		}
		return $sql;
	}
}
