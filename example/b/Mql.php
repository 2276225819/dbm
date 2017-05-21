<?php namespace dbm;


class Mql {

    public $jStr='', $wStr='',$lStr='',$oStr='',$fStr='*';
    public $rArgs=[],$wArgs=[], $fArgs=[], $sArgs=[],$oArgs=[];


	
    /**
     * Undocumented function
     *
     * @param string $model
     * @param array $pks
     * @param array $ref
     * @return Sql
     */
    public function ref($model, $pks = null, $ref = null)
    {
        $CLASS = $this->model;
        if (is_string($pks)) {
            $pks = (array)$pks;
        }
        if (!is_array($pks)) {
            $pks = $CLASS::$pks;
        }
        if (!is_array($ref)) {
            $ref = $CLASS::$ref[$model];
        }
        return $this->relation($model, (array)$pks, (array)$ref);
    }

    /**
     * ... LIMIT {$limit} OFFSET {$offset} ...
     * @param int $limit
     * @param int $offset
     * @return Sql
     */
    public function limit($limit, $offset = 0)
    {
        $this->lStr=" LIMIT ".intval($limit);
        if (!empty($offset)) {
            $this->lStr.=' OFFSET '.intval($offset).' ';
        }
        return $this;
    }
    /**
     * ... ORDER {$order} ...
     * @param string $order
     * @param array ...$arr
     * @return Sql
     */
    public function order(string $order, ...$arr)
    {
        $this->oStr=" ORDER BY ".$order;
        $this->oArgs=$arr;
        return $this;
    }
    /**
     * SELECT {$fileds} FROM ...
     * @param string|array $fields
     * @return Sql
     */
    public function field($fields)
    {
        $this->fStr=$this->kvSQL($this->fArgs, ',', $fields);
        return $this;
    }

    /**
     * ... WHERE {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Sql
     */
    public function where($w, ...$arr)
    {
        $this->wArgs=[];
        $this->wStr=' WHERE '.$this->kvSQL($this->wArgs, ' AND ', $w, $arr);
        return $this;
    }
    /**
     * ... WHERE ... AND {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Sql
     */
    public function and($w, ...$arr)
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" AND ";
        $this->wStr.=$this->kvSQL($this->wArgs, ' AND ', $w, $arr);
        return $this;
    }
    /**
     * ... WHERE ... OR {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Sql
     */
    public function or($w, ...$arr)
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" OR ";
        $this->wStr.=$this->kvSQL($this->wArgs, ' OR ', $w, $arr);
        return $this;
    }
    
    /**
     * ... FROM [TABLE] JOIN {$str} ...
     * @param string  $str
     * @return Sql
     */
    public function join($str)
    {
        $this->jStr=" $str";
        return $this;
    }

    /**
     * ... WHERE `PrimaryKey` = {$pkv} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Sql
     */
    public function find(...$pkv)
    {
        if (is_array($pkv[0] && empty($pkv[0][0]))) {
            $arr = $pkv[0];
        } else {
            $arr = array_combine($this->pks, $pkv);
        }
        return $this->and($arr);
    }
    

	


	
    public function many($model, $model_pks, $model_fks)
    {
        return $this->relation($model, (array)$model_pks,
            array_combine((array)$model_fks, (array)$model_pks)
        );
    }
    public function one($model, $model_pks, $local_fks)
    {
        return $this->relation($model, (array)$model_pks,
            array_combine((array)$model_pks, (array)$local_fks)
        );
    }
    
    public function relation($model, $pks, $ref)
    {
        $sql = $this->db->sql($model, $pks);
        $arr = iterator_to_array($this->getAllIterator());
        if (count($arr)>1) {
            $vstr = count($ref)>1?"(".substr(str_repeat(",?", count($ref)), 1).')':'?';
            foreach ($arr as $row) {
                $s=[];
                foreach ($ref as $k => $f) {
                    $s[]=$row[$f];
                }
                if (empty($strlist[$k = join($s)])) {
                    $strlist[$k]=",$vstr";
                    array_push($sql->wArgs, ...$s);
                }
            }
            sort($sql->wArgs);//hash匹配一致
            $valstr = substr(join(array_values($strlist)), 1);
            $pks = array_keys($ref);
            $keystr = count($pks)>1?"(".join($pks, ',').")":$pks[0];
            $sql->wStr=" WHERE $keystr in ($valstr) ";
        } else {
            $str='';
            foreach ($ref as $key => $value) {
                $str.=" AND $key=?";
                $sql->wArgs[]=$this[$value];
            }
            $sql->wStr=" WHERE".substr($str, 4);
        }
        return $sql;
    }
}