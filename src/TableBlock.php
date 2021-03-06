<?php namespace dbm;

class TableBlock{
 
    public $name ="";//"table"
    public $attrs=[];//["ENGINE"=>"","AUTO_INCREMENT"=>"","DEFAULT CHARSET"=""]
    public $index=[];//["UNIQUE KEY `` (``)","PRIMARY KEY "]
    public $cols =[];//["id"=>"`ID` int ","name"=>"`name` varchar"]
    public function __construct($name,$cols=null,$attr=null){ 
        $this->name = $name;

		if(!empty($attr)){
			preg_match_all('/DEFAULT CHARSET=\S+|\w+=\S+/',$attr,$arr);  
			foreach ($arr[0] as $value) {
				$arr = explode('=',$value,2); 
				$this->attrs[trim($arr[0])]=trim($arr[1]);
			} 
		}
		if(!empty($cols)){
			foreach ($cols as $str)  {
				if($str = trim($str));else
					continue;
				if(strtolower(substr($str,0,11))=="primary key"
				|| strtolower(substr($str,0,10))=="unique key"
				|| strtolower(substr($str,0,3))=="key" ){ 
					if(!in_array($str,$this->index)) 
						$this->index[]=$str;   
				}else{ 
					$of = preg_match('/^`?(\S+?)`?\s/',$str,$arr); 
					if(!empty($arr[1])){
						////// BUG /////
						$str = preg_replace('/DEFAULT\s+([\d\.]+)/',"DEFAULT '$1'",$str);
						$str = preg_replace('/DEFAULT\s+current_timestamp\(\)/',"DEFAULT CURRENT_TIMESTAMP",$str);
						////// BUG /////

						$this->cols[strtolower($arr[1])]="`{$arr[1]}` ".substr($str,strlen($arr[0]));	
					}			
				}  
			}  
		}
    }   
    public function __toString(){
        $str=""; $attr="";
        foreach ($this->cols as $key => $value) {
            $str.=",\n    $value ";//.join($value,' ');
        }    
        foreach ($this->index as $value) {
            $str.=",\n  $value";
        }            
		foreach ($this->attrs as $k=>$value) {
            $attr.=" $k=$value";
        }    
        return "create table {$this->name}(".substr($str,1)."\n){$attr}";
    }
    public static function read($str,&$offset=0)  { 
        $offset += preg_match("/create\s*table\s*`?(\S+?)`?\s*\(([^;]*)\)([^;]*)/i",$str,$arr,0,$offset);  
        if(empty($arr)) return; 

        $offset += strlen($arr[0]) ; 
        $arrs=preg_split('/,(?=[^\d`])/',$arr[2]);
        foreach ($arrs as $key => $value)
            $cols[] = trim($value);  
        return new self($arr[1],$cols,$arr[3]); 
    }

    public function merge($table){  
		$new = new TableBlock($table->name);
        $new->index=array_unique(array_merge($table->index,$this->index));
		$new->cols=array_unique(array_merge($table->cols,$this->cols));
		$new->attrs=array_unique(array_merge($table->attrs,$this->attrs));
		return $new;
    }
    public function diffFrom($remote=null){ 
		if(empty($remote))
			return ["{$this};"];//create table
		
		$sql=[];
		//cols
		foreach ($this->cols as $name => $col) { 
			if(empty($remote->cols[$name])){
				$sql[]="alter table `{$this->name}` add {$col};";
				continue;
			}
			if($remote->cols[$name]!=$col){
				$sql[]="alter table `{$this->name}` change `$name`  {$col}; -- {$remote->cols[$name]}";
				continue;
			}
		}  
		//pk
		$diff = array_diff($this->index,$remote->index);
		foreach ($diff as $key) {
			$sql[] = "alter table `{$this->name}` add {$key};";
		}
		//attr
		foreach ($this->attrs as $name => $value) { 
			if(empty($remote->attrs[$name]) || $remote->attrs[$name]!=$value)
				$sql[]="alter table `{$this->name}` $name=$value ;";
		}

		// foreach ($this->index as $name => $value) { 
		// 	if(empty($remote->index[$name]) || trim($remote->index[$name])!=trim($value))
		// 		$sql[]="alter table `{$this->name}` add $value";
		// }
		return $sql; 
    }  
	public function clearFrom(TableBlock $local=null){
		if(empty($local))
			return ["drop table `{$this->name}`;"];
		$sql=[];
		foreach ($this->cols as $name => $col) { 
			if(empty($local->cols[$name]))
				$sql[]="alter table `{$this->name}` drop `{$name}`;";
		}  
		$diff = array_diff($this->index,$local->index);
		foreach ($diff as $key) {
			preg_match('/PRIMARY KEY|KEY `?\S+`?/',$key,$arr);
			$sql[] = "alter table `{$this->name}` drop {$arr[0]};";
		}

		return $sql; 

	}
    


}