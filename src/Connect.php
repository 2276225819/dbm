<?php namespace dbm;

class Connect implements \ArrayAccess
{
    use ConnectAccess;

    public $debug=false;
    /**
     * @return int
     */
    public function lastInsertId()
    {
        return static::$conn[$this->dns]->lastInsertId();
    }
    /**
     * @param string $sql
     * @param array $args
     * @return \PDOStatement
     */
    public function execute($sql, $args = [])
    {
        $sql = static::bulidSql($sql);
        if ($this->debug) {
            echo "<!--$sql;".join($args, ',')."-->\n";
        }
        while (true) {
            try {
                $query = static::$conn[$this->dns]->prepare($sql);
                return $query->execute($args)?$query:false;
            } catch (\Throwable $e) {
                if ($e->errorInfo[0] == 70100||$e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013) {
                    sleep(1);//必须的？？
                    $this->reload();
                    continue;
                }
                throw $e;
            }
        }
    }
    public function begin()
    {
        if ($this->debug) {
            echo "<!--begin {$this->dns}-->\n";
        }
        if ($row=static::$conn[$this->dns]->beginTransaction()) {
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }
    public function commit()
    {
        if ($this->debug) {
            echo "<!--commit {$this->dns}-->\n";
        }
        if ($row=static::$conn[$this->dns]->commit()) {
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }
    public function rollback()
    {
        if ($this->debug) {
            echo "<!--rollback {$this->dns}-->\n";
        }
        if ($row=static::$conn[$this->dns]->rollBack()) {
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }

    public function inTransaction()
    {
        return static::$conn[$this->dns]->inTransaction();
    }
 

    /**
     * ## Transaction
     * $cache = \dbm\Connect->scope()
     *
     * ....
     *
     * $cache->commit();
     *
     * @param boolean $transaction
     * @return \dbm\Transaction
     */
    public function scope()
    {
        return new Transaction($this);
    }

    /**
     * ## dbm v3
     * \dbm\Connect->sql(string $model )
     *
     * \dbm\Connect->sql(string $model, array $pks )
     *
     * @param string $model
     * @param array $pks
     * @return \dbm\Sql
     */
    public function entity($model, $pks = null)
    {
        $pks = (array)$pks;
        if (class_exists($model) && isset($model::$table)) {
            $table = $model::$table;
            $pks = count($pks)?$pks:$model::$pks;
            $model = $model;
        } else {
            $table=$model;
            $pks=(array)$pks;
            $model = \dbm\Entity::class;
        }
        return new \dbm\Sql($this, $table, $pks, $model);
    }
    /**
     * ## dbm v4
     * \dbm\Connnect->sql(string $model)
     *
     * \dbm\Connnect->sql(string $model ,array $pks )
     *
     * @param string $model
     * @param array $pks
     * @return \dbm\Model
     */
    public function v4($model, $pks = null)
    {
        return Model::byName(new Session($this), $model, $pks);
    }

    // public function sql($model, $pks = null)
    // {
    //     return v5\Collection::byName(new Session($this),$model, $pks   );
    // }

    /**
     * ## dbm v5
     * \dbm\Connnect->sql(string $model)
     *
     * \dbm\Connnect->sql(string $model ,array $pks )
     *
     * @param string $model
     * @param array $pks
     * @return \dbm\Collection
     */
    public function sql($model, $pks = null)
    {
        return Collection::new($model, $pks, new Session($this) );
    }
}
