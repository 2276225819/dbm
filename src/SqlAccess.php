<?php namespace dbm;

trait SqlAccess
{

 
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
        foreach ($this->field($n="$name({$args[0]})")->getAllIterator() as $row) {
            return $row[$n];
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
        if($offset===NULL){
            foreach ($this as $value) {
                return $value;
            }
        }
        //offset > Row
        if (is_numeric($offset)) {
			$hash = $this->bulidHash();
			if (empty(static::$qs[$hash])) {
				$this->limit(1,$offset);
			}
            foreach ($this as $row) {
                if ($offset--<=0) {
                    break;
                }
            }
            return $row??NULL;
        }
        //relation > SQL
        if (class_exists($offset)) {
            return $this->ref($offset, $offset::$pks, $this->model::$ref[$offset]);
        }
        //first > mixed
        foreach ($this as $row) {
            return $row[$offset];
        }
    }
    public function __invoke(...$pkv)
    {
		return $this->find(...$pkv)->get();
    }
}
