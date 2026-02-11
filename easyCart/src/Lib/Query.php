<?php
namespace App\Lib;

class Query
{
    private $type = 'select';
    private $cols = [];
    private $table;
    private $conditions = [];
    private $joins = [];
    private $order = [];
    private $limit;
    private $offset;
    private $binds = [];
    private $updates = [];
    private $insertValues = [];

    public function select($cols = ['*'])
    {
        $this->type = 'select';
        $this->cols = is_array($cols) ? $cols : func_get_args();
        return $this;
    }

    public function from($table, $alias = null)
    {
        $this->table = $alias ? "$table AS $alias" : $table;
        return $this;
    }

    public function where($condition, $bind = null)
    {
        if ($condition) {
            $this->conditions[] = $condition;
            if ($bind !== null) {
                if (is_array($bind)) {
                    $this->binds = array_merge($this->binds, $bind);
                } else {
                    $this->binds[] = $bind;
                }
            }
        }
        return $this;
    }

    public function join($table, $condition, $type = 'LEFT')
    {
        $this->joins[] = "$type JOIN $table ON $condition";
        return $this;
    }

    public function orderBy($col, $dir = 'ASC')
    {
        $this->order[] = "$col $dir";
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }

    public function resetOrder()
    {
        $this->order = [];
        return $this;
    }

    public function resetLimit()
    {
        $this->limit = null;
        return $this;
    }

    public function resetOffset()
    {
        $this->offset = null;
        return $this;
    }

    public function insert($table, $data)
    {
        $this->type = 'insert';
        $this->table = $table;
        $this->insertValues = $data;
        return $this;
    }

    public function update($table, $data)
    {
        $this->type = 'update';
        $this->table = $table;
        $this->updates = $data;
        return $this;
    }

    public function delete($table)
    {
        $this->type = 'delete';
        $this->table = $table;
        return $this;
    }

    public function __toString()
    {
        switch ($this->type) {
            case 'select':
                return $this->buildSelect();
            case 'insert':
                return $this->buildInsert();
            case 'update':
                return $this->buildUpdate();
            case 'delete':
                return $this->buildDelete();
            default:
                return '';
        }
    }

    private function buildSelect()
    {
        $sql = "SELECT " . implode(', ', $this->cols);
        $sql .= " FROM " . $this->table;

        if (!empty($this->joins)) {
            $sql .= " " . implode(' ', $this->joins);
        }

        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(' AND ', $this->conditions);
        }

        if (!empty($this->order)) {
            $sql .= " ORDER BY " . implode(', ', $this->order);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET " . $this->offset;
        }

        return $sql;
    }

    private function buildInsert()
    {
        $columns = array_keys($this->insertValues);
        // Use placeholders for values to ensure safety and correct quoting
        $placeholders = array_fill(0, count($columns), '?');

        return "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
    }

    private function buildUpdate()
    {
        $sets = [];
        foreach ($this->updates as $col => $val) {
            $sets[] = "$col = ?";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(' AND ', $this->conditions);
        }
        return $sql;
    }

    private function buildDelete()
    {
        $sql = "DELETE FROM {$this->table}";
        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(' AND ', $this->conditions);
        }
        return $sql;
    }

    public function getBinds()
    {
        return $this->binds;
    }
}
