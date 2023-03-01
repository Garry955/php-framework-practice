<?php
namespace core\abstracts;

use core\classes\database;

abstract class model
{

    /**
     * Database connection.
     *
     * @var database
     */
    protected $db;

    /**
     * Entity's table name.
     *
     * @var string
     */
    private $table;

    /**
     * Entity's ID.
     *
     * @var integer
     */
    public $id = 0;

    public final function __construct()
    {
        $this->db = $GLOBALS["db"];
        $this->init();
    }

    /**
     * Get entity's table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Initialize entity.
     *
     * @param string $table
     *            Custom table name.
     */
    public function init($table = null)
    {
        if (! $table) {
            $pieces = explode("\\", get_called_class());
            $table = strtolower(end($pieces)) . "s";
        }
        $this->table = $table;
    }

    /**
     * Get this entity's datas as array.
     *
     * @param array $columns
     *            Filter columns. Optional.
     *
     * @return array
     */
    public function toArray($columns = [])
    {
        $ref = new \ReflectionClass(get_called_class());
        $datas = [];
        foreach ($ref->getProperties() as $prop) {
            if (($columns && ! in_array($prop->getName(), $columns)) || $prop->getName() == "db") {
                continue;
            }
            $datas[$prop->getName()] = $prop->getValue($this);
        }
        $ref = null;
        unset($ref);
        return $datas;
    }

    /**
     * Create a new entity.
     *
     * @param array $datas
     *            New values for this entity. Optional.
     *
     * @return model
     */
    public function create($datas = [])
    {
        if (! $datas) {
            $datas = $this->toArray();
        }
        foreach ($datas as $prop => $value) {
            if ($prop == "id") {
                unset($datas[$prop]);
                continue;
            }
            $this->$prop = $value;
        }
        $this->db->insert($this->table, $datas);
        $this->id = $this->db->lastInsertId($this->table . ".id");
        return $this;
    }

    /**
     * Update this entity.
     *
     * @param array $datas
     *            New values for this entity. Optional.
     *
     * @return model
     */
    public function update($datas = [])
    {
        if (! $datas) {
            $datas = $this->toArray();
        }
        foreach ($datas as $prop => $value) {
            if ($prop == "id") {
                unset($datas[$prop]);
                continue;
            }
            $this->$prop = $value;
        }
        $sql = $this->db->update($this->table, $datas, [
            [
                "key" => "id",
                "value" => $this->id
            ]
        ]);
        return $this->refresh();
    }

    /**
     * Save this entity.
     * If it does not exist then create it otherwise update it.
     *
     * @param array $datas
     *            New values for this entity. Optional.
     *
     * @return model
     */
    public function save($datas = [])
    {
        return $this->id ? $this->update($datas) : $this->create($datas);
    }

    /**
     * Delete this entity.
     *
     * @return boolean
     */
    public function delete()
    {
        $success = false;
        if ($this->id) {
            $sql = $this->db->delete($this->table, [
                [
                    "key" => "id",
                    "value" => $this->id
                ]
            ]);
            $success = (boolean) $sql->rowCount();
        }
        return $success;
    }

    /**
     * Find first by the given params.
     *
     * @param integer|array $params
     *            ID or custom parameters.
     *
     * @return model|boolean
     */
    public static function findFirst($params)
    {
        if (! is_array($params)) {
            $params = [
                [
                    "key" => "id",
                    "value" => $params
                ]
            ];
        }
        $className = get_called_class();
        $self = new $className();
        $sql = $self->db->selectWithAnd($self->table, "*", $params);
        $result = $sql->fetchObject();
        if (! $result) {
            return false;
        }
        foreach ($result as $prop => $value) {
            $self->$prop = $value;
        }
        return $self;
    }

    /**
     * Find all by the given params.
     *
     * @param array $params
     *            Custom parameters.
     *
     * @return model[]
     */
    public static function find($params = [])
    {
        $className = get_called_class();
        $self = new $className();
        $sql = $self->db->selectWithAnd($self->table, "*", $params);
        $results = $sql->fetchAll(\PDO::FETCH_ASSOC);
        $items = [];
        foreach ($results as $item) {
            $entity = clone $self;
            foreach ($item as $prop => $value) {
                $entity->$prop = $value;
            }
            $items[] = $entity;
        }
        return $items;
    }

    /**
     * Refresh this entity's datas.
     *
     * @return model
     */
    public function refresh()
    {
        return self::findFirst($this->id);
    }

    public function __sleep()
    {
        $this->db = null;
        return array_keys($this->toArray());
    }

    public function __wakeup()
    {
        $this->db = $GLOBALS["db"];
    }
}

