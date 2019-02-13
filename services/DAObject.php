<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use \Exception;
use \PDO;
use Services\DAService;

class DAObject
{
    public $pdo;
    public $table;
    public $subtables;

    public function init(PDO $pdo, array $table, array $subtables = [])
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->subtables = $subtables;
        return $this;
    }

    public function getAttrsAll()
    {
        $attrs = [];
        foreach ($this->table['columns'] as $column) {
            $fieldName = $column['fieldName'];
            $attrs[$fieldName] = $this->getAttrByFieldName($fieldName);

            // $attr = $this->getAttrByFieldName($fieldName);
            // // Empty PHP array turns to empty JS array by json_encode().
            // // Not empty assoc PHP array turns to JS Object.
            // // Attr is expected to be JS Object.
            // // This method doesn't returns empty PHP array to avoid producing empty JS array.
            // if (count($attr) > 0) {
            //     $attrs[$fieldName] = $this->getAttrByFieldName($fieldName);
            // }
        }
        if (count($attrs) > 0) {
            return $attrs;
        } else {
            return new \stdClass();
        }
    }

    public function eliminateByFieldName(array $ar)
    {
        $ar2 = [];
        foreach ($ar as $name => $value) {
            $column = $this->getColumnByFieldName($name);
            if (!empty($column)) {
                $ar[$name] = $value;
            }
        }
        return $ar2;
    }

    public function getAttrByFieldName(string $fieldName)
    {
        $column = $this->getColumnByFieldName($fieldName);
        $attr = !empty($column) && array_key_exists('attr', $column) ? $column['attr'] : [];
        if (count($attr) > 0) {
            return $attr;
        } else {
            return new \stdClass();
        }
    }

    public function createStruct() : array
    {
        $struct = [];
        foreach ($this->table['columns'] as $column) {
            $struct[$column['fieldName']] = DAService::isNumber($column['definition']) ? 0 : '';
        }
        return $struct;
    }

    public function getColumnByFieldName(string $fieldName, array $tables = []) : array
    {
        $targetTables;
        if (!empty($table)) {
            $targetTables = $tables;
        } else {
            $targetTables = array_merge([$this->table], $this->subtables);
        }
        foreach ($targetTables as $table) {
            foreach ($table['columns'] as $column) {
                if ($column['fieldName'] === $fieldName) {
                    return $column;
                }
            }
        }
        return [];
    }

    public function attachTypes($nameVsValues)
    {
        $nameValueTypes = [];
        foreach ($nameVsValues as $name => $value) {
            $nameValueTypes[] = $this->attachType($name, $value);
        }
        return $nameValueTypes;
    }

    public function attachType($name, $value)
    {
        $column = $this->getColumnByFieldName($name);
        if (empty($column)) {
            throw new Exception('Undefined column name in the table:' . $name);
        }
        $pType = DAService::parsePDOParamType($column['definition']);
        if (is_null($pType)) {
            throw new Exception('Unknown column type for the column:' . $name);
        }
        return [
            'name' => $name,
            'value' => $value,
            'type' => $pType,
        ];
    }

    public function attFindOneById($id, $fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->findOneBy($this->attachTypes(['id' => $id]), $fetch_style);
    }

    public function attFindOneBy($nameVsValues, $fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->findOneBy($this->attachTypes($nameVsValues), $fetch_style);
    }

    public function findOneBy($nameValueTypes, $fetch_style = PDO::FETCH_ASSOC)
    {
        $results = $this->findAllBy($nameValueTypes, $fetch_style);
        if ($results) {
            return $results[0];
        } else {
            return null;
        }
    }

    public function attFindAllBy($nameVsValues, $fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->findAllBy($this->attachTypes($nameVsValues), $fetch_style);
    }

    public function findAllBy($nameValueTypes, $fetch_style = PDO::FETCH_ASSOC)
    {
        $el = PHP_EOL;
        $sql = "select * from {$this->table['tableName']} where TRUE {$el}";

        foreach ($nameValueTypes as $nvt) {
            $sql .= "and {$nvt['name']} = :{$nvt['name']}{$el}";
        }

        return $this->fetchAll($sql, $nameValueTypes, $fetch_style);
    }

    public function attFetchOne($sql, $nameVsValues, $fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->fetchOne($sql, $this->attachTypes($nameVsValues), $fetch_style);
    }

    public function fetchOne($sql, $nameValueTypes, $fetch_style = PDO::FETCH_ASSOC)
    {
        $results = $this->fetchAll($sql, $nameValueTypes, $fetch_style);
        if ($results) {
            return $results[0];
        } else {
            return null;
        }
    }

    public function attFetchAll($sql, $nameVsValues, $fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->fetchAll($sql, $this->attachTypes($nameVsValues), $fetch_style);
    }

    public function fetchAll($sql, $nameValueTypes, $fetch_style = PDO::FETCH_ASSOC)
    {
        $st = $this->pdo->prepare($sql);
        foreach ($nameValueTypes as $nvt) {
            $st->bindValue($nvt['name'], $nvt['value'], $nvt['type']);
        }
        $st->execute();
        return $st->fetchAll($fetch_style);
    }

    public function attInsert($nameVsValues)
    {
        return $this->insert($this->attachTypes($nameVsValues));
    }

    public function insert($setNameValueTypes)
    {
        $el = PHP_EOL;
        $sql = "INSERT INTO {$this->table['tableName']} ({$el}";

        $cnm = '';
        foreach ($setNameValueTypes as $nvt) {
            $sql .= "{$cnm} {$nvt['name']}{$el}";
            $cnm = ',';
        }

        $sql .= ") VALUES ({$el}";

        $cnm = '';
        foreach ($setNameValueTypes as $nvt) {
            $sql .= "{$cnm} :{$nvt['name']}{$el}";
            $cnm = ',';
        }

        $sql .= "){$el}";

        $pdo = $this->pdo;
        $st = $pdo->prepare($sql);

        foreach ($setNameValueTypes as $nvt) {
            $st->bindValue($nvt['name'], $nvt['value'], $nvt['type']);
        }

        $st->execute();
        return $pdo->lastInsertId();
    }

    public function attUpdateById($nameVsValues)
    {
        $this->updateById($this->attachTypes($nameVsValues));
    }

    public function updateById($nameValueTypes)
    {
        $setNameValueTypes = [];
        $whereNameValueTypes = [];
        foreach ($nameValueTypes as $nvt) {
            if ($nvt['name'] === 'id') {
                $whereNameValueTypes[] = $nvt;
            } else {
                $setNameValueTypes[] = $nvt;
            }
        }

        if (count($whereNameValueTypes) === 0) {
            throw new Exception("Not found Id in params");
        }

        $this->updateBy($setNameValueTypes, $whereNameValueTypes);
    }

    public function updateBy($setNameValueTypes, $whereNameValueTypes = null)
    {
        $el = PHP_EOL;
        $sql = "UPDATE {$this->table['tableName']} SET {$el}";

        $cnm = '';
        foreach ($setNameValueTypes as $nvt) {
            $sql .= "{$cnm} {$nvt['name']} = :{$nvt['name']}{$el}";
            $cnm = ',';
        }

        $sql .= "where true{$el}";
        if ($whereNameValueTypes) {
            foreach ($whereNameValueTypes as $nvt) {
                $sql .= "and {$nvt['name']} = :{$nvt['name']}{$el}";
            }
        }

        $pdo = $this->pdo;
        $st = $pdo->prepare($sql);
        if (!$st) {
            throw new Exception(print_r($pdo->errorInfo(), true));
        }

        foreach ($setNameValueTypes as $nvt) {
            $st->bindValue($nvt['name'], $nvt['value'], $nvt['type']);
        }

        if ($whereNameValueTypes) {
            foreach ($whereNameValueTypes as $nvt) {
                $st->bindValue($nvt['name'], $nvt['value'], $nvt['type']);
            }
        }

        if (!$st->execute()) {
            throw new Exception(print_r($st->errorInfo(), true));
        }
    }

    public function attDeleteById($id)
    {
        $this->attDeleteBy(['id' => $id]);
    }

    public function attDeleteBy($nameVsValues)
    {
        $this->deleteBy($this->attachTypes($nameVsValues));
    }

    public function deleteBy($nameValueTypes = null)
    {
        $el = PHP_EOL;
        $sql = "DELETE FROM {$this->table['tableName']} {$el}";

        $sql .= "where true{$el}";
        if ($nameValueTypes) {
            foreach ($nameValueTypes as $nvt) {
                $sql .= "and {$nvt['name']} = :{$nvt['name']}{$el}";
            }
        }

        $pdo = $this->pdo;
        $st = $pdo->prepare($sql);

        if ($nameValueTypes) {
            foreach ($nameValueTypes as $nvt) {
                $st->bindValue($nvt['name'], $nvt['value'], $nvt['type']);
            }
        }

        $st->execute();
    }

    public function createTableDDL(bool $enableIfNotExists = false) : string
    {
        $el = PHP_EOL;
        $s = '';
        $s .= 'CREATE TABLE';
        if ($enableIfNotExists) {
            $s .= ' IF NOT EXISTS';
        }
        $s .= " {$this->table['tableName']} (" . $el;

        $defs = [];
        foreach ($this->table['columns'] as $column) {
            $defs[] = "{$column['fieldName']} {$column['definition']}";
        }
        if (array_key_exists('index_definitions', $this->table)) {
            foreach ($this->table['index_definitions'] as $def) {
                $defs[] = $def;
            }
        }

        $s .= implode($defs, ',' . $el);
        $s .= $el . ');' . $el;

        return $s;
    }

    public function dropTableDDL(bool $enableIfExists = false) : string
    {
        $el = PHP_EOL;
        $s = '';
        $s .= 'DROP TABLE';
        if ($enableIfExists) {
            $s .= ' IF EXISTS';
        }
        $s .= " {$this->table['tableName']};" . $el;
        return $s;
    }

    public function execute($sql)
    {
        $this->pdo->prepare($sql)->execute();
    }

}
