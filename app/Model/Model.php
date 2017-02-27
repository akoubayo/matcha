<?php
namespace App\Model;

use \PDO;

/**
*
*/

class Model
{
    private $db;
    private $req;
    private $array = array();
    private $debug = false;

    public function __construct()
    {
        try {
            $this->db = new PDO('mysql:host=localhost:8889;dbname=matcha;charset=utf8', 'root', 'root');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $this->db->exec("SET CHARACTER SET utf8");
        } catch (Exception $e) {
               die('Erreur : '.$e->getMessage());
        }
    }
    public function all($order = "desc", $limit = "", $offset = "")
    {
        if (!empty($limit) && (int)$limit >= 0) {
            $limit = " LIMIT ".(int)$limit;
        }
        if (!empty($offset) && (int)$offset >= 0) {
            $offset = " OFFSET " . (int)$offset;
        }
        $this->req = 'SELECT * FROM ' . $this->table . " ORDER BY id_" . $this->table . ' ' . $order . $limit . $offset ;
        return $req = $this->get();
    }

    public function select()
    {
        $this->req = 'SELECT * FROM ' . $this->table;
        return $this;
    }

    public function find($id)
    {
        $this->req = "SELECT * FROM ".$this->table." WHERE id_". $this->table . " = ? ";
        $this->array[]=$id;
        $ret = $this->get();
        if (count($ret) == 1) {
            $ret = $ret[0];
        }
        $this->req = '';
        $this->array = array();
        return $ret;
    }

    public static function insert($data)
    {
        $ret = '';
        $class = static::maClass();
        $new = new $class();
    }

    public function save()
    {
        $req = 'INSERT INTO ' . $this->table . '(';
        $val = 'VALUES(';
        $array = array();
        foreach ($this->champs as $value) {
            if (isset($this->$value) && !empty($this->$value)) {
                $req .= $value . ', ';
                $val .= ':' . $value . ', ';
                $array[$value] = $this->$value;
            }
        }
        $req = substr($req, 0, -2) . ') ' . substr($val, 0, -2) . ')';
        $req = $this->db->prepare($req);
        $req->execute($array);
        $id = 'id_'.$this->table;
        $this->$id = $this->db->lastInsertId();
        return $this;
    }

    public function update()
    {
        $this->req = 'UPDATE ' . $this->table . ' SET ';
        $idTable = 'id_' . $this->table;
        $this->array = array();
        foreach ($this->champs as $value) {
            if (isset($this->$value) && !empty($this->$value)) {
                $this->req .= $value . ' = :' . $value . ', ';
                $this->array[$value] = $this->$value;
            }
        }
        $this->req = substr($this->req, 0, -2) . ' WHERE id_' . $this->table . ' = ' . $this->$idTable;
        $req = $this->db->prepare($this->req);
        $req->execute($this->array);
        $this->req = '';
        $this->array = array();
    }

    public function where($data)
    {
        if ($this->req == '') {
            $this->req = "SELECT * FROM ".$this->table." WHERE ";
            $this->array = array();
        } elseif (strpos($this->req, 'WHERE') === false) {
            $this->req .= " WHERE ";
        } else {
            $this->req .= " AND ";
        }
        foreach ($data as $value) {
            $this->req .= $value[0] . " " . $value[1] . " :" .$value[0] . " AND ";
            $this->array[$value[0]] = $value[2];
        }
        $this->req = substr($this->req, 0, -4);
        return $this;
    }

    public function whereOr($data)
    {
        if ($this->req == '') {
            $this->req = "SELECT * FROM ".$this->table." WHERE ";
        } elseif (strpos($this->req, 'WHERE') === false) {
            $this->req .= " WHERE ";
        } else {
            $this->req .= " OR ";
        }
        foreach ($data as $value) {
            $this->req .= $value[0] . " " . $value[1] . " :" .$value[0] . " OR ";
            $this->array[$value[0]] = $value[2];
        }
        $this->req = substr($this->req, 0, -3);
        return $this;
    }

    public function get()
    {
        if (strpos($this->req, 'COUNT') !== false) {
            return $this->count();
        }
        $req = $this->db->prepare($this->req);
        $req->execute($this->array);
        $this->req = '';
        $this->array = array();
        return $this->returnObject($req, get_class($this));
    }

    protected function returnObject($req, $class)
    {
        if (isset($this->foreignClass)) {
            $class = $this->foreignClass;
            $foreign_ = "foreign_";
            unset($this->foreignClass);
            unset($this->foreing_col);
            unset($this->foreignKey);
        }
        $ret = array();
        $i = 0;
        while ($don = $req->fetch(PDO::FETCH_ASSOC)) {
            $ret[$i] = new $class();
            foreach ($don as $key => $value) {
                if (in_array($key, $ret[$i]->champs) || substr($key, 0, 2) == 'id' && !isset($this->foreignClass)) {
                    $ret[$i]->$key = $value;
                } else if (in_array(substr($key, 8), $ret[$i]->champs) || substr($key, 0, 2) == 'id') {
                    $key = substr($key, 8);
                    $ret[$i]->$key = $value;
                    unset($this->key);
                }
            }
            $i++;
        }
        if ($this->debug == true) {
            if ($ret == false) {
                echo "<br/>Aucune donnée trouvée</br>";
            } else {
                var_dump($ret);
            }
        }
        $this->req = '';
        $this->array = array();
        return $ret;
    }

    public function foreignKey($related, $foreignTable)
    {
        $foreign = new $related();
        $col = "";
        foreach ($foreign->champs as $value) {
            $col .= $foreignTable.'.'.$value . ' AS foreign_' . $value . ', ';
        }
        $this->foreing_col = substr($col, 0, -2);
    }

    public function hasMany($related, $foreignKey, $option = false)
    {
        $foreignTable = explode('\\', $related);
        $foreignTable = strtolower($foreignTable[(count($foreignTable) - 1)]);
        $id = 'id_' . $this->table;
        $this->foreignClass = $related;
        $this->foreignKey = $foreignKey;
        $this->foreignKey($related, $foreignTable);
        $this->req = 'SELECT  ' . $this->foreing_col .' FROM ' . $foreignTable . ', ' .$this->table. ' WHERE ' . $foreignKey . ' = id_' . $this->table . ' AND id_'. $this->table . ' = ' .$this->$id;
        if ($option == false) {
            $req = $this->db->prepare($this->req);
            $img = $req->execute();
            return $this->returnObject($req, $related);
        }
        return $this;
    }

    public function belongsTo($related, $foreignKey, $option = false)
    {
        $foreignTable = explode('\\', $related);
        $foreignTable = strtolower($foreignTable[(count($foreignTable) - 1)]);
        $id = $foreignTable . '_id';
        $this->foreignClass = $related;
        $this->foreignKey = $foreignKey;
        $this->foreignKey($related, $foreignTable);
        $this->req = 'SELECT '. $this->foreing_col . ' FROM ' . $foreignTable . ' WHERE ' . $foreignKey . ' = ' . $this->$id;
        if ($option == false) {
            $req = $this->db->prepare($this->req);
            $img = $req->execute();
            $ret = $this->returnObject($req, $related);
            if (count($ret) == 1) {
                $ret = $ret[0];
            }
            return $ret;
        }

        return $this;
    }

    public function order($data)
    {
        $this->req .= ' ORDER BY ';
        foreach ($data as $value) {
                $this->req .= $value[0] . " " . $value[1] . " AND ";
        }
        $this->req = substr($this->req, 0, -4);
        return $this;
    }

    public function limit($limit)
    {
        $this->req .= ' LIMIT ' . $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->req .= ' OFFSET ' . $offset;
        return $this;
    }
    public function count($option = false)
    {
        if ($this->req == '') {
            $this->req = 'SELECT COUNT(*) as count FROM ' . $this->table;
        }
        if ($option == false) {
            $req = $this->db->prepare($this->req);
            $req->execute($this->array);
            $ret = $req->fetch(PDO::FETCH_ASSOC);
            $this->req = "";
            $this->array = array();
            return $ret['count'];
        }
        return $this;
    }

    public function debug()
    {
        var_dump($this->req);
        echo '<br/>';
        var_dump($this->array);
        $this->debug = true;
        return $this;
    }
}
