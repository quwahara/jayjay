<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use \Exception;
use \PDO;

class DAService
{
    public $pdo;
    public $dbdec_;

    public function init($pdo, $dbdec)
    {
        $this->pdo = $pdo;
        $this->dbdec_ = $dbdec;
        return $this;
    }

    public function createTables()
    {
        $el = PHP_EOL;
        foreach ($this->dbdec_['tables'] as $table) {

            $d = '';
            $d .= 'DROP TABLE IF EXISTS ' . $table['tableName'] . ';';
            $st = $this->pdo->prepare($d);
            if (!$st) {
                throw new Exception(print_r($this->pdo->errorInfo(), true));
            }
            if (!$st->execute()) {
                throw new Exception(print_r($st->errorInfo(), true));
            }

            $d = '';
            $d .= 'CREATE TABLE ' . $table['tableName'] . '(';
            $cnm = '';
            foreach ($table['columns'] as $column) {
                $d .= $cnm . $el . $column['fieldName'] . ' ' . $column['definition'];
                $cnm = ',';
            }
            foreach ($table['index_definitions'] as $idx_def) {
                $d .= $cnm . $el . $idx_def;
                $cnm = ',';
            }
            $d .= $el . ');';
            $st = $this->pdo->prepare($d);
            if (!$st) {
                throw new Exception(print_r([$d, $this->pdo->errorInfo()], true));
            }
            if (!$st->execute()) {
                throw new Exception(print_r($st->errorInfo(), true));
            }
        }
    }

    public function insert($table, $values)
    {
        $el = PHP_EOL;
        $q = '';
        $q .= 'INSERT INTO ' . $table['tableName'] . '(';
        $cnm = '';
        $c = '';
        $v = '';
        foreach ($values as $col => $value) {
            $c .= $cnm . $el . $col;
            $v .= $cnm . $el . ':' . $col;
            $cnm = ',';
        }
        $q .= $c . $el . ')VALUES(' . $v . $el . ');' . $el;
        $st = $this->pdo->prepare($q);
        if (!$st) {
            throw new Exception(print_r($this->pdo->errorInfo(), true));
        }
        foreach ($values as $col => $value) {
            $column = $this->getColumnByFieldName($col, $table);
            if (is_null($column)) {
                throw new Exception('Undefined column name in the table:' . $col);
            }
            $pType = self::parsePDOParamType($column['definition']);
            if (is_null($pType)) {
                throw new Exception('Unknown column type for the column:' . $col);
            }
            $st->bindValue($col, $value, $pType);
        }
        if (!$st->execute()) {
            throw new Exception(print_r($st->errorInfo(), true));
        }
    }

    public function attachTableNameAndTypes($table, $values)
    {
        $attachedValues = [];
        foreach ($values as $col => $value) {

            $column = $this->getColumnByFieldName($col, $table);
            if (is_null($column)) {
                throw new Exception('Undefined column name in the table:' . $col);
            }
            $pType = self::parsePDOParamType($column['definition']);
            if (is_null($pType)) {
                throw new Exception('Unknown column type for the column:' . $col);
            }
            $attachedValues[] = [
                'name' => $col,
                'value' => $value,
                'type' => $pType,
            ];
        }
        return [
            'tableName' => $table['tableName'], 
            'values' => $attachedValues
        ];
    }

    public function updateById($tableNameAndValues)
    {
        $el = PHP_EOL;
        $q = '';
        $q .= 'UPDATE ' . $tableNameAndValues['tableName'] . ' SET';
        $cnm = '';
        $c = '';
        foreach ($tableNameAndValues['values'] as $v) {
            $name = $v['name'];
            if ($name === 'id') {
                continue;
            }
            $c .= $cnm . $el . $name . ' = ' . ':' . $name;
            $cnm = ',';
        }
        $q .= $c . $el;
        $q .= 'WHERE id = :id;' . $el;

        $st = $this->pdo->prepare($q);
        if (!$st) {
            throw new Exception(print_r($this->pdo->errorInfo(), true));
        }
        foreach ($tableNameAndValues['values'] as $v) {
            $st->bindValue($v['name'], $v['value'], $v['type']);
        }
        if (!$st->execute()) {
            throw new Exception(print_r($st->errorInfo(), true));
        }
    }

    public function update($table, $values, $where = false, $whereParams = false)
    {
        $el = PHP_EOL;
        $q = '';
        $q .= 'UPDATE ' . $table['tableName'] . ' SET';
        $cnm = '';
        $c = '';
        $v = '';
        foreach ($values as $col => $value) {
            $c .= $cnm . $el . $col . ' = ' . ':' . $col;
            $cnm = ',';
        }
        $q .= $c . $el;
        $q .= 'WHERE ' . $el;
        if ($where) {
            $q .= $where . $el;
            $q .= 'AND ';
        }
        $q .= 'TRUE ' . $el;

        $st = $this->pdo->prepare($q);
        if (!$st) {
            throw new Exception(print_r($this->pdo->errorInfo(), true));
        }
        foreach ($values as $col => $value) {

            $regCol = preg_replace('/__[a-zA-Z0-9]+__$/', '', $col);

            $column = $this->getColumnByFieldName($regCol, $table);
            if (is_null($column)) {
                throw new Exception('Undefined column name in the table:' . $col);
            }
            $pType = self::parsePDOParamType($column['definition']);
            if (is_null($pType)) {
                throw new Exception('Unknown column type for the column:' . $col);
            }
            $st->bindValue($col, $value, $pType);
        }
        if ($whereParams) {
            foreach ($whereParams as $col => $value) {

                $regCol = preg_replace('/__[a-zA-Z0-9]+__$/', '', $col);

                $column = $this->getColumnByFieldName($regCol, $table);
                if (is_null($column)) {
                    throw new Exception('Undefined column name in the table:' . $col);
                }
                $pType = self::parsePDOParamType($column['definition']);
                if (is_null($pType)) {
                    throw new Exception('Unknown column type for the column:' . $col);
                }
                $st->bindValue($col, $value, $pType);
            }
        }
        if (!$st->execute()) {
            throw new Exception(print_r($st->errorInfo(), true));
        }
    }

    public function findOne($table, $sql, $values, $fetch_style = PDO::FETCH_ASSOC)
    {
        $st = $this->pdo->prepare($sql);
        if (!$st) {
            throw new Exception(print_r($this->pdo->errorInfo(), true));
        }

        foreach ($values as $col => $value) {
            $column = $this->getColumnByFieldName($col, $table);
            if (is_null($column)) {
                throw new Exception('Undefined column name in the table:' . $col);
            }
            $pType = self::parsePDOParamType($column['definition']);
            if (is_null($pType)) {
                throw new Exception('Unknown column type for the column:' . $col);
            }
            $st->bindValue($col, $value, $pType);
        }
        if (!$st->execute()) {
            throw new Exception(print_r($st->errorInfo(), true));
        }
        $result = $st->fetchAll($fetch_style);
        if ($result) {
            return $result[0];
        } else {
            return null;
        }
    }

    public function findAll($table, $sql, $values, $fetch_style = PDO::FETCH_ASSOC)
    {
        $st = $this->pdo->prepare($sql);
        if (!$st) {
            throw new Exception(print_r($this->pdo->errorInfo(), true));
        }

        foreach ($values as $col => $value) {
            $column = $this->getColumnByFieldName($col, $table);
            if (is_null($column)) {
                throw new Exception('Undefined column name in the table:' . $col);
            }
            $pType = self::parsePDOParamType($column['definition']);
            if (is_null($pType)) {
                throw new Exception('Unknown column type for the column:' . $col);
            }
            $st->bindValue($col, $value, $pType);
        }
        if (!$st->execute()) {
            throw new Exception(print_r($st->errorInfo(), true));
        }
        return $st->fetchAll($fetch_style);
    }

    public function getTableByTableName($tableName)
    {
        foreach ($this->dbdec_['tables'] as $table) {
            if ($table['tableName'] === $tableName) {
                return $table;
            }
        }
        return null;
    }

    public function getColumnByFieldName($fieldName, $table)
    {
        foreach ($table['columns'] as $column) {
            if ($column['fieldName'] === $fieldName) {
                return $column;
            }
        }
        return null;
    }

    public static function parsePDOParamType($definition)
    {
        if (\preg_match('/^((VAR)?CHAR|(TINY|MEDIUM|LONG)?TEXT)/i', $definition)) {
            return PDO::PARAM_STR;
        } else if (\preg_match('/^((TINY|MEDIUM|LONG|BIG)?INT(EGER)?|(DEC(IMAL)?|NUMERIC|FIXED))/i', $definition)) {
            return PDO::PARAM_INT;
        } else {
            return null;
        }
    }

}
