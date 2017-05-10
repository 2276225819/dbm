<?php namespace dbm;

trait SqlGetter
{ 
	
    /** @var Connect */
    public $db;
    public $model;
    public $table;
    public $pks;
	
    //public $jStr='';
    public $wStr='',$lStr='',$oStr='',$fStr='*';
    public $rArgs=[],$wArgs=[], $fArgs=[], $sArgs=[],$oArgs=[]; 
    // public function join($string):Sql{ 
    //     $this->jStr=$string;
    //     return $this;
    // }

    public function kvSQL(&$param, $jtag = ' AND ', $arr, $attr = null, $sql = ''):string
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $v) {
                if (is_array($v)) {
                    if (count($v)>1) {
                        $str= substr(str_repeat(",?", count($v)), 1);
                        $sql.="{$jtag} {$key} in ($str) " ;
                        $param=array_merge($param, $v);
                        continue;
                    } else {
                        $v=$v[0];
                    }
                }
                $sql.= "{$jtag}{$key}=?";
                $param[]=$v;
            }
            $sql=substr($sql, strlen($jtag));
        } else {
            $sql=$arr;
            if (is_array($attr)) {
                $param=array_merge($param, $attr);
            }
        }
        return $sql;
    }

	
    ////////////  alias /////////////  

	
    public function list($key=null){ 
        foreach($this as $row){
            $arr[] = $key?$row[$key]:$row;
        }
        return $arr??[]; 
    }

}

