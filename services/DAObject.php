<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use \Exception;
use \PDO;
use Services\DAService;

class DAObject
{
    public $da;
    public $tableName;
    public $table;

    public function init($da, $tableName)
    {
        $this->da = $da;
        $this->tableName = $tableName;
        $this->table = $da->getTableByTableName($tableName);
        return $this;
    }

    public function createModel()
    {
        $model = [];
        foreach ($this->table['columns'] as $column) {
            $model[$column['fieldName']] = DAService::isNumber($column['definition']) ? 0 : '';
        }
        return $model;
    }

    public function getColumnByFieldName($fieldName)
    {
        foreach ($this->table['columns'] as $column) {
            if ($column['fieldName'] === $fieldName) {
                return $column;
            }
        }
        return null;
    }

    public function attachType($name, $value)
    {
        $column = $this->getColumnByFieldName($name);
        if (is_null($column)) {
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

    public function attachTypes($nameVsValues)
    {
        $nameValueTypes = [];
        foreach ($nameVsValues as $name => $value) {
            $nameValueTypes[] = $this->attachType($name, $value);
        }
        return $nameValueTypes;
    }

    public function findAllBy($nameValueTypes, $fetch_style = PDO::FETCH_ASSOC)
    {
        $el = PHP_EOL;
        $sql = "select * from {$this->table['tableName']} where TRUE {$el}";
        
        foreach ($nameValueTypes as $nvt) {
            $sql .= "and {$nvt['name']} = :{$nvt['name']}{$el}";
        }
        
        $pdo = $this->da->pdo;
        $st = $pdo->prepare($sql);
        if (!$st) {
            throw new Exception(print_r($pdo->errorInfo(), true));
        }

        foreach ($nameValueTypes as $nvt) {
            $st->bindValue($nvt['name'], $nvt['value'], $nvt['type']);
        }

        if (!$st->execute()) {
            throw new Exception(print_r($st->errorInfo(), true));
        }

        return $st->fetchAll($fetch_style);
    }
    
    public function attFindAllBy($nameVsValues, $fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->findAllBy($this->attachTypes($nameVsValues), $fetch_style);
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
    
    public function attFindOneBy($nameVsValues, $fetch_style = PDO::FETCH_ASSOC)
    {
        return $this->findOneBy($this->attachTypes($nameVsValues), $fetch_style);
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

        $pdo = $this->da->pdo;
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
    
    public function attUpdateById($nameVsValues)
    {
        $this->updateById($this->attachTypes($nameVsValues));
    }
    
}
