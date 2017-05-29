<?php namespace dbm;

trait SqlAccess
{
    /** @var Connect */
    public $db;
    public $model;
    public $table;
    public $pks;
    
    public $jStr='', $wStr='',$lStr='',$oStr='',$fStr='*';
    public $rArgs=[],$wArgs=[], $fArgs=[], $sArgs=[],$oArgs=[];
 
 
    public static $gc;
    public function __construct(Connect $db, $table, $pks, $model)
    {
        $this->db=$db;
        $this->model=$model;
        $this->table=$table;
        $this->pks=(array)$pks;
        // if($this->db->debug)
        //     echo str_repeat("\t",static::$gc).
        //         "<!--> $this->table($this->model) !!!-->\n";
        ++static::$gc;
    }
    public function __destruct()
    {
        --static::$gc;
        // if($this->db->debug)
        //     echo str_repeat("\t",static::$gc).
        //         "<!--< $this->table($this->model) !!!-->\n";
        if (!static::$gc) {
            // if($this->db->debug)
            //     echo "<!--GC-->\n";
            static::$qs=[];
            static::$cs=[];
        }
    }
    public function __clone()
    {
        ++static::$gc;//new
    }
    public function __call($name, $args)
    {
        if (!count($args)) {
            $args[0]='1';
        }
        $attr="$name({$args[0]}) as __VALUE__";
        foreach ($this->field($attr)->getAllIterator() as $row) {
            return $row['__VALUE__'];
        }
    }

    public function offsetUnset($offset)
    {
    }
    public function offsetSet($offset, $value)
    {
    }
    public function offsetExists($offset)
    {
        return $this[$offset];
    }
    public function offsetGet($offset)
    {
        //offset == NULL
        if ($offset===null) {
            foreach ($this as $value) {
                return $value;
            }
        }
        //offset > Row
        if (is_numeric($offset)) {
            $hash = $this->bulidHash();
            if (empty(static::$qs[$hash])) {
                $this->limit(1, $offset);
            }
            foreach ($this as $row) {
                if ($offset--<=0) {
                    break;
                }
            }
            return $row??null;
        }
        //relation > SQL
        if (class_exists($offset)) {
            $CLASS = $this->model;
            return $this->ref($offset, $offset::$pks, $CLASS::$ref[$offset]);
        }
        //first > mixed
        foreach ($this as $row) {
            return $row[$offset];
        }
    }
    public function __invoke(...$pkv)
    {
        return $this->find(...$pkv);//->get();
    }

    
    public function kvSQL(&$param, $jtag = ' AND ', $arr, $attr = null, $sql = '')
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
}
