<?php

namespace infra\database;

use PDO;
use PDOException;

class Database
{
    private $pdo;
    private $lastQuery;
    private $lastParams;
    
    public function __construct($host = 'localhost', $dbname = 'u474089460_connectmobile', $username = 'u474089460_patrik', $password = 'C0nn3ct@Mob1l&')
    {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error connecting to database: " . $e->getMessage());
        }
    }

    public function select($table, $select, $join = '', $where = [], $groupBy = '', $having = '', $orderBy = '', $limits = [])
    {
        try {
            $query = "SELECT $select FROM $table a ";

            if (!empty($join)) {
                $query .= " $join ";
            }

            $params = [];
            if (!empty($where)) {
                $query .= " WHERE " . $this->buildWhereClause($where, $params);
            }

            if (!empty($groupBy)) {
                $query .= " GROUP BY $groupBy ";
            }

            if (!empty($having)) {
                $query .= " HAVING $having ";
            }

            if (!empty($orderBy)) {
                $query .= " ORDER BY $orderBy ";
            }

            if (!empty($limits)) {
                $start = $limits['start'] ?? 0;
                $limit = $limits['limit'] ?? 10;
                $query .= " LIMIT :start, :limit";
                $params[':start'] = (int)$start;
                $params[':limit'] = (int)$limit;
            }

            $this->lastQuery = $query;
            $this->lastParams = $params;

            $stmt = $this->pdo->prepare($query);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, $this->getPDOParamType($value));
            }

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($result)) {
                return [['result' => 'empty']];
            }

            return $result;
        } catch (PDOException $e) {
            return ['erro' => $e->getMessage()];
        }
    }

    private function getPDOParamType($value)
    {
        switch (gettype($value)) {
            case 'integer':
                return PDO::PARAM_INT;
            case 'boolean':
                return PDO::PARAM_BOOL;
            case 'NULL':
                return PDO::PARAM_NULL;
            case 'string':
                return PDO::PARAM_STR;
            default:
                return PDO::PARAM_STR;
        }
    }

    private function buildWhereClause($where, &$params)
    {
        $clauses = [];
        foreach ($where as $key => $value) {
            $paramKey = ':' . str_replace(['.', ' ', '>=', '<=', '<', '>'], '_', $key);
            if (strpos($key, 'like') === 0) {
                $clauses[] = str_replace('like ', '', $key) . " LIKE $paramKey";
                $params[$paramKey] = "%$value%";
            } elseif (strpos($key, 'likeAfter') === 0) {
                $clauses[] = str_replace('likeAfter ', '', $key) . " LIKE $paramKey";
                $params[$paramKey] = "$value%";
            } elseif (strpos($key, 'likeBefore') === 0) {
                $clauses[] = str_replace('likeBefore ', '', $key) . " LIKE $paramKey";
                $params[$paramKey] = "%$value";
            } elseif (strpos($key, 'notLike') === 0) {
                $clauses[] = str_replace('notLike ', '', $key) . " NOT LIKE $paramKey";
                $params[$paramKey] = "$value";
            } elseif (strpos($key, 'or') === 0) {
                $clauses[] = str_replace('or ', '', $key) . " = $paramKey OR ";
                $params[$paramKey] = $value;
            } elseif (strpos($key, 'notin') === 0) {
                $clauses[] = str_replace('notin ', '', $key) . " NOT IN ($paramKey)";
                $params[$paramKey] = $value;
            } elseif (strpos($key, 'in') === 0) {
                $clauses[] = str_replace('in ', '', $key) . " IN ($paramKey)";
                $params[$paramKey] = $value;
            } elseif (strpos($key, 'isnot') === 0) {
                $clauses[] = str_replace('isnot ', '', $key) . " IS NOT $paramKey";
                $params[$paramKey] = $value;
            } elseif (strpos($key, 'is') === 0) {
                $clauses[] = str_replace('is ', '', $key) . " IS $paramKey";
                $params[$paramKey] = $value;
            } elseif (strpos($key, 'diff') === 0) {
                $clauses[] = str_replace('diff ', '', $key) . " != $paramKey";
                $params[$paramKey] = $value;
            } elseif (strpos($key, '<=') === 0) {
                $clauses[] = str_replace('<= ', '', $key) . " <= $paramKey";
                $params[$paramKey] = $value;
            } elseif (strpos($key, '>=') === 0) {
                $clauses[] = str_replace('>= ', '', $key) . " >= $paramKey";
                $params[$paramKey] = $value;
            } elseif (strpos($key, '<') === 0) {
                $clauses[] = str_replace('< ', '', $key) . " < $paramKey";
                $params[$paramKey] = $value;
            } elseif (strpos($key, '>') === 0) {
                $clauses[] = str_replace('> ', '', $key) . " > $paramKey";
                $params[$paramKey] = $value;
            } elseif (strpos($key, 'sql') === 0) {
                $clauses[] = "$value";
            } else {
                $clauses[] = "$key = $paramKey";
                $params[$paramKey] = $value;
            }
        }

        return implode(' AND ', $clauses);
    }

    public function paginateQuery($page = 1, $itemsPerPage = 10)
    {
        $page = ($page < 1) ? 1 : $page;

        $start = ($page - 1) * $itemsPerPage;

        return [
            'start' => $start,
            'limit' => $itemsPerPage
        ];
    }

    public function insert($table, $set)
    {
        try {
            $this->pdo->beginTransaction();

            $columns = implode(', ', array_keys($set));
            $placeholders = ':' . implode(', :', array_keys($set));
            $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";

            $stmt = $this->pdo->prepare($query);

            foreach ($set as $column => $value) {
                $stmt->bindValue(":$column", $value, $this->getPDOParamType($value));
            }

            $this->lastQuery = $query;
            $this->lastParams = $set;

            $stmt->execute();

            $id = $this->pdo->lastInsertId();

            $this->pdo->commit();

            return $id;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['erro' => $e->getMessage()];
        }
    }

    public function count($table, $column = '*', $join = '', $where = [], $groupBy = '', $having = '')
    {
        $select = "COUNT($column) as total";

        $result = $this->select($table, $select, $join, $where, $groupBy, $having);

        return $result[0]['total'] ?? 0;
    }

    public function update($table, $set, $where)
    {
        try {
            $this->pdo->beginTransaction();

            $setClauses = [];
            $params = [];
            foreach ($set as $column => $value) {
                $setClauses[] = "$column = :$column";
                $params[":$column"] = $value;
            }
            $setClause = implode(', ', $setClauses);

            $whereClause = $this->buildWhereClause($where, $params);

            $query = "UPDATE $table SET $setClause WHERE $whereClause";

            $stmt = $this->pdo->prepare($query);

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, $this->getPDOParamType($value));
            }

            $this->lastQuery = $query;
            $this->lastParams = $params;

            $stmt->execute();

            $this->pdo->commit();

            if ($stmt->rowCount() > 0) {
                return ['success' => 'Tupla atualizada com sucesso'];
            } else {
                return ['success' => 'Nenhuma alteração feita'];
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['erro' => $e->getMessage()];
        }
    }

    public function delete($table, $where)
    {
        try {
            $this->pdo->beginTransaction();

            $params = [];
            $whereClause = $this->buildWhereClause($where, $params);

            $query = "DELETE FROM $table WHERE $whereClause";

            $stmt = $this->pdo->prepare($query);

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, $this->getPDOParamType($value));
            }

            $this->lastQuery = $query;
            $this->lastParams = $params;

            $stmt->execute();

            $this->pdo->commit();

            if ($stmt->rowCount() > 0) {
                return ['success' => 'Tupla excluída com sucesso'];
            } else {
                return ['success' => 'Nenhuma tupla encontrada para exclusão'];
            }
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['erro' => $e->getMessage()];
        }
    }

    public function debug()
    {
        $sentQuery = $this->lastQuery;

        foreach ($this->lastParams as $param => $value) {
            $sentQuery = str_replace($param, $this->pdo->quote($value), $sentQuery);
        }

        $debugInfo = [
            'SQL' => $this->lastQuery,
            'SENT_SQL' => $sentQuery
        ];

        echo '<pre>';
        print_r($debugInfo);
    }
}
