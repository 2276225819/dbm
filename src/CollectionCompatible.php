<?php namespace dbm;
 
trait CollectionCompatible
{   
    function __invoke(...$pks)
    {
        return (clone $this)->find(...$pks);
    }
    
    /**
     * phpbug  基类 ArrayObject 不能重载 __debugInfo
     * https://bugs.php.net/bug.php?id=69264 
     */ 
    function __debugInfo()
    { 
        // if(empty($this->data)){ 
        //     $arr[":"]=(string)$this->sql;
        //     if (isset($this->sql->rArgs)) {
        //         $arr['?']=json_encode($this->sql->rArgs);
        //     }
        //     return $arr;
        // }else{
        //     return $this->toArray();
        // } 
    } 
    
    /////////// curd:value ///////////

    public function set($data)
    { 
        // //关联修改 
        if( isset($this->parent) && $this->parent->isRow() ){ 
            foreach($this->refpks as $k=>$v){
                if( $this->parent->val($v) ){
                    $data[$k] = $this->parent->val($v); 
                }
            }
        }  
        foreach ($this->tablepks as $key) {
            if (isset($data[$key])) {
                $where[$key]=$data[$key];
            }
        }
        if (isset($where)) {
            if ($row = $this->where($where)->get()) {  
                foreach ($data as $key => $value) {
                    if ($row->val($key) == $data[$key]) {
                        unset($data[$key]);
                    }
                } 
                return $row->save($data);  
            }
        }
        return $this->insert($data); 
    }

    public function destroy(...$args)
    {
        return $this->delete(...$args);
    }

    public function insertMulit($args)
    { 
        $this->insert(...$args);
        return count($args);
    }
    
    /////////// query:model{table} ///////////

    public function load(...$pkv)
    {
        if (is_array($pkv[0] && empty($pkv[0][0]))) {
            $arr = $pkv[0];
        } else {
            $arr = array_combine($this->tablepks, $pkv);
        }
        $model = (clone $this);
        $model->rArgs = $arr;
        return $model->where($arr)->get();
    }

    public function map($fn){
        return $this->all($fn);
    }


     
    /////////// filter:self{table sql} ////////////

    /**
     * ... WHERE ... AND {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Pql
     */
    public function and($w, ...$arr)
    {
        if (!empty($w)) {
            $this->wStr.=empty($this->wStr)?" WHERE ":" AND ";
            $this->wStr.="(".$this->kvSQL($this->wArgs, ' AND ', $w, $arr).")";
        }
        return $this->uncache();
    }
    /**
     * ... WHERE ... OR {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Pql
     */
    public function or($w, ...$arr)
    {
        if (!empty($w)) {
            $this->wStr.=empty($this->wStr)?" WHERE ":" OR ";
            $this->wStr.="(".$this->kvSQL($this->wArgs, ' OR ', $w, $arr).")";
        }
        return $this->uncache();
    }

}

