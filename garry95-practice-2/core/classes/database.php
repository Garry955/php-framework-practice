<?php
namespace core\classes;

class database extends \PDO
{

    public $start;

    public function __construct($dsn = null, $user = null, $pass = null, $options = null)
    {
        $config = require_once "config/dbconfig.php";
        try {
            parent::__construct("mysql:host=" . $config["host"] . ";dbname=" . $config["dbname"] . ";charset=utf8", $config["user"], $config["password"], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ]);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    private function select($table, $columns = ["*"])
    {
        $this->start = microtime(true);
        return "SELECT " . implode(", ", $columns) . " FROM `" . $table . "`";
    }

    /**
     *
     * @example <pre>$this->db->select("users", "id", [<br>["key"=>"email","value"=>$email]<br>]);</pre>
     * @example <pre>$this->db->select("users", ["id","email"], [<br>["key"=>"email","value"=>$email,"op"=>"<>"],<br>["key"=>"active","value"=>1]<br>]);</pre>
     * @example <pre>$this->db->select("users", ["id","name"]);</pre>
     * @example <pre>$this->db->select("users");</pre>
     * @param string $table
     * @param string|array $columns
     * @param array $where
     * @return \PDOStatement
     */
    public function selectWithAnd($table, $columns = "*", $where = [])
    {
        if (! is_array($columns)) {
            $columns = [
                $columns
            ];
        }
        $statement = $this->select($table, $columns);
        if ($where) {
            $whereStr = [];
            foreach ($where as $condition) {
                $whereStr[] = $condition["key"] . " " . (isset($condition["op"]) ? $condition["op"] : "=") . " :" . $condition["key"];
            }
            $statement .= " WHERE " . implode(" AND ", $whereStr);
        }
        $sql = $this->prepare($statement);
        if ($where) {
            foreach ($where as $condition) {
                $sql->bindValue(":" . $condition["key"], $condition["value"]);
            }
        }

        try {
            $sql->execute();
        } catch (\Exception $e) {
            new logger("sql", [
                "ts" => round(microtime(true) - $this->start, 2),
                "method" => __FUNCTION__,
                "params" => func_get_args(),
                "results" => $e->getMessage()
            ]);
        }
        new logger("sql", [
            "ts" => round(microtime(true) - $this->start, 2),
            "method" => __FUNCTION__,
            "params" => func_get_args(),
            "results" => $sql->rowCount()
        ]);
        return $sql;
    }

    /**
     * Deletes with where statement
     * Default operand in where is "="
     *
     * @example <pre>$this->db->delete("users", [<br>["key"=>"email","value"=>$email,"op"=>"<>"],<br>["key"=>"id","value"=>$id]<br>]);</pre>
     *
     * @param string $table
     * @param array $where
     * @return \PDOStatement
     */
    public function delete($table, $where = [])
    {
        $this->start = microtime(true);
        $statement = "DELETE FROM `" . $table . "` ";

        if ($where) {
            $whereStr = [];
            foreach ($where as $condition) {
                $whereStr[] = $condition["key"] . " " . (isset($condition["op"]) ? $condition["op"] : "=") . " :" . $condition["key"];
            }
            $statement .= "WHERE " . implode(" AND ", $whereStr);
        }
        $sql = $this->prepare($statement);
        if ($where) {
            foreach ($where as $condition) {
                $sql->bindValue(":" . $condition["key"], $condition["value"]);
            }
        }

        try {
            $sql->execute();
        } catch (\Exception $e) {
            new logger("sql", [
                "ts" => round(microtime(true) - $this->start, 2),
                "method" => __FUNCTION__,
                "params" => func_get_args(),
                "results" => $e->getMessage()
            ]);
        }
        new logger("sql", [
            "ts" => round(microtime(true) - $this->start, 2),
            "method" => __FUNCTION__,
            "params" => func_get_args(),
            "results" => $sql->rowCount()
        ]);

        return $sql;
    }

    /**
     * Update needs columns array and where array
     * Default operand in where is "="
     *
     * @exception Cant pass the id to columns
     *
     * @example <pre>$this->db->update("users", [<br>["key"=>"email","value"=>$email,"op"=>"<>"],<br>["key"=>"id","value"=>$id]<br>]);</pre>
     *
     * @param string $table
     * @param array $columns
     * @param array $where
     * @return \PDOStatement
     */
    public function update($table, $columns = [], $where = [])
    {
        $this->start = microtime(true);
        if (! is_array($columns)) {
            $columns = [
                $columns
            ];
        }

        if (! is_array($where)) {
            $where = [
                $where
            ];
        }

        $statement = "UPDATE `" . $table . "` SET ";

        if ($columns) {
            $setStr = [];
            foreach ($columns as $key => $condition) {
                $setStr[] = $key . " = :s" . $key;
            }
            $statement .= implode(", ", $setStr);
        }
        if ($where) {
            $whereStr = [];
            foreach ($where as $condition) {
                $whereStr[] = $condition["key"] . " " . (isset($condition["op"]) ? $condition["op"] : "=") . " :" . $condition["key"];
            }
            $statement .= " WHERE " . implode(" AND ", $whereStr);
        }
        $sql = $this->prepare($statement);
        if ($columns) {
            foreach ($columns as $key => $condition) {
                $sql->bindValue(":s" . $key, $condition);
            }
        }
        if ($where) {
            foreach ($where as $condition) {
                $sql->bindValue(":" . $condition["key"], $condition["value"]);
            }
        }

        try {
            $sql->execute();
        } catch (\Exception $e) {
            new logger("sql", [
                "ts" => round(microtime(true) - $this->start, 2),
                "method" => __FUNCTION__,
                "params" => func_get_args(),
                "results" => $e->getMessage()
            ]);
        }
        new logger("sql", [
            "ts" => round(microtime(true) - $this->start, 2),
            "method" => __FUNCTION__,
            "params" => func_get_args(),
            "results" => $sql->rowCount()
        ]);
        return $sql;
    }

    /**
     * Requires table name, and associative array with syntax: [ "field_name" : $Value" ]
     *
     * @param string $table
     * @param array $columns
     *
     * @return \PDOStatement
     */
    public function insert($table, $columns)
    {
        $this->start = microtime(true);
        $statement = "INSERT INTO `" . $table . "` ";
        $statement .= "(" . implode(", ", array_keys($columns)) . ") VALUES (:" . implode(", :", array_keys($columns)) . ")";

        $sql = $this->prepare($statement);
        foreach ($columns as $key => $val) {
            $sql->bindValue(":" . $key, $val);
        }
        try {
            $sql->execute();
        } catch (\Exception $e) {
            new logger("sql", [
                "ts" => round(microtime(true) - $this->start, 2),
                "method" => __FUNCTION__,
                "params" => func_get_args(),
                "results" => $e->getMessage(),
            ]);
        }
        new logger("sql", [
            "ts" => round(microtime(true) - $this->start, 2),
            "method" => __FUNCTION__,
            "params" => func_get_args(),
            "results" => $sql->rowCount()
        ]);
        return $sql;
    }
}