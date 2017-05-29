<?php namespace dbm;

class Transaction
{
    /**
     * @var \dbm\Connect
     */
    public $db;

    public function __construct(Connect $pdo)
    {
        if (!empty($pdo)) {
            $this->db=$pdo;
            $this->db->begin();
        }
    }
    public function commit()
    {
        if (isset($this->db)) {
            $this->db->commit();
            unset($this->db);
        }
    }
    public function __destruct()
    {
        if (isset($this->db)) {
            $this->db->rollback();
            unset($this->db);
        }
    }
}
