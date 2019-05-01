<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use \Exception;
use \PDO;
use \PDOStatement;

class DAObject
{
    const DEFAULT_SQL_TRACE = false;
    const DEFAULT_RANDOM_ID_ENABLED = true;

    /**
     * I was going to be the range of randomId 
     * between 1000000000000000000 and 1999999999999999999.
     * I chose the range to be:
     *  - Number of digits are as same as +9223372036854775807, and
     *  - First digit is 1
     * The +9223372036854775807 is max of 8 bytes.
     * But I could not select and delete the record with the ids in the range,
     * if I could insert it.
     * 
     * I have narrowed range
     * between 1000000000001 and 9999999999999.
     * I could select and delete the record with the id in the range.
     * 
     * References for integer data types in databases:
     * https://dev.mysql.com/doc/refman/8.0/en/integer-types.html
     * https://www.postgresql.org/docs/11/datatype-numeric.html
     * https://www.sqlite.org/datatype3.html
     */

    //                          The number of digits are 13.
    //                            x123x123x123x
    const DEFAULT_RANDOM_ID_MIN = 1000000000001;
    const DEFAULT_RANDOM_ID_MAX = 9999999999999;

    public $sqlTrace;
    public $pdo;
    public $table;
    public $subtables;
    public $randomIdEnabled;
    public $randomIdMin;
    public $randomIdMax;


    public static function isNumber($definition)
    {
        return \preg_match('/^((TINY|MEDIUM|LONG|BIG)?INT(EGER)?|(DEC(IMAL)?|NUMERIC|FIXED|DOUBLE))/i', $definition);
    }

    public static function parsePDOParamType($definition)
    {
        if (\preg_match('/^((VAR)?CHAR|(TINY|MEDIUM|LONG)?TEXT|DOUBLE)/i', $definition)) {
            return PDO::PARAM_STR;
        } else if (\preg_match('/^((TINY|MEDIUM|LONG|BIG)?INT(EGER)?|(DEC(IMAL)?|NUMERIC|FIXED))/i', $definition)) {
            return PDO::PARAM_INT;
        } else {
            return null;
        }
    }

    public function init(PDO $pdo, array $table, array $subtables = []): self
    {
        $this->sqlTrace = self::DEFAULT_SQL_TRACE;
        $this->randomIdEnabled = self::DEFAULT_RANDOM_ID_ENABLED;
        $this->randomIdMin = self::DEFAULT_RANDOM_ID_MIN;
        $this->randomIdMax = self::DEFAULT_RANDOM_ID_MAX;

        $this->pdo = $pdo;
        $this->table = $table;
        $this->subtables = $subtables;
        return $this;
    }

    public function setSqlTrace(bool $sqlTrace): self
    {
        $this->sqlTrace = $sqlTrace;
        return $this;
    }

    public function setRandomIdEnabled(bool $randomIdEnabled): self
    {
        $this->randomIdEnabled = $randomIdEnabled;
        return $this;
    }

    public function setRandomIdMin(int $randomIdMin): self
    {
        $this->randomIdMin = $randomIdMin;
        return $this;
    }

    public function setRandomIdMax(int $randomIdMax): self
    {
        $this->randomIdMax = $randomIdMax;
        return $this;
    }

    public function randomId(): int
    {
        return mt_rand($this->randomIdMin, $this->randomIdMax);
    }

    public function getAttrsAll()
    {
        $attrs = [];
        foreach ($this->table['columns'] as $column) {
            $fieldName = $column['fieldName'];
            $attrs[$fieldName] = $this->getAttrByFieldName($fieldName);
        }
        if (count($attrs) > 0) {
            return $attrs;
        } else {
            return new \stdClass();
        }
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

    public function createStruct(): array
    {
        $struct = [];
        foreach ($this->table['columns'] as $column) {
            $struct[$column['fieldName']] = self::isNumber($column['definition']) ? 0 : '';
        }
        return $struct;
    }

    public function getColumnByFieldName(string $fieldName, array $tables = []): array
    {
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
        $pType = self::parsePDOParamType($column['definition']);
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
        $sql = [];

        $sql[] = "select * from {$this->table['tableName']} where TRUE";

        foreach ($nameValueTypes as $nvt) {
            $sql[] = "and {$nvt['name']} = :{$nvt['name']}";
        }

        return $this->fetchAll(implode(PHP_EOL,  $sql), $nameValueTypes, $fetch_style);
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
        $st = $this->execute($sql, $nameValueTypes);

        return $st->fetchAll($fetch_style);
    }

    public function attInsert($nameVsValues)
    {
        return $this->insert($this->attachTypes($nameVsValues));
    }

    public function insert($setNameValueTypes)
    {
        if ($this->randomIdEnabled) {
            foreach ($setNameValueTypes as &$nvt) {
                if ($nvt['name'] === 'id') {
                    $nvt['value'] = $this->randomId();
                    break;
                }
            }
            unset($nvt);
        }

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
        return $this->updateById($this->attachTypes($nameVsValues));
    }

    public function updateById($nameValueTypes)
    {
        $setNameValueTypes = [];
        $whereNameValueTypes = [];
        $id = null;
        foreach ($nameValueTypes as $nvt) {
            if ($nvt['name'] === 'id') {
                $whereNameValueTypes[] = $nvt;
                $id = $nvt['value'];
            } else {
                $setNameValueTypes[] = $nvt;
            }
        }

        if (count($whereNameValueTypes) === 0) {
            throw new Exception("Not found Id in params");
        }

        $this->updateBy($setNameValueTypes, $whereNameValueTypes);
        return $id;
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
        $sql = [];

        $sql[] = "DELETE FROM {$this->table['tableName']}";

        $sql[] = "where true";
        if ($nameValueTypes) {
            foreach ($nameValueTypes as $nvt) {
                $sql[] = "and {$nvt['name']} = :{$nvt['name']}";
            }
        }

        $this->execute(implode(PHP_EOL,  $sql), $nameValueTypes);
    }

    public function createTableDDL(bool $enableIfNotExists = false): string
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

        $s .= implode(',' . $el, $defs);
        $s .= $el . ');' . $el;

        return $s;
    }

    public function dropTableDDL(bool $enableIfExists = false): string
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

    public function execute($sql, $nameValueTypes = null): PDOStatement
    {
        $st = $this->pdo->prepare($sql);

        if ($nameValueTypes) {
            foreach ($nameValueTypes as $nvt) {
                $st->bindValue($nvt['name'], $nvt['value'], $nvt['type']);
            }
        }

        if ($this->sqlTrace) {
            error_log($st->queryString);
        }

        $st->execute();

        return $st;
    }
}
