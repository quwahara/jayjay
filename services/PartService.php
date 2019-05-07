<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use \Exception;

class PartService
{
    const DEFAULT_ROOT_ID = 1;

    public $rootId;

    /** parts DAObject */
    public $p_;
    /** part_properties DAObject */
    public $r_;
    /** part_items DAObject */
    public $i_;

    public function init($part, $part_properties, $part_item)
    {
        $this->rootId = self::DEFAULT_ROOT_ID;
        $this->p_ = $part;
        $this->r_ = $part_properties;
        $this->i_ = $part_item;
        return $this;
    }

    public function setRootId(int $rootId): self
    {
        $this->rootId = $rootId;
        return $this;
    }

    // public function isObject($parent_id)
    // {
    //     return !is_null($this->r_->attFindOneBy(['parent_id' => $parent_id]));
    // }

    // public function isArray($parent_id)
    // {
    //     return !is_null($this->i_->attFindOneBy(['parent_id' => $parent_id]));
    // }

    public function isProperty($child_id)
    {
        return !is_null($this->r_->attFindOneBy(['child_id' => $child_id]));
    }

    public function isItem($child_id)
    {
        return !is_null($this->i_->attFindOneBy(['child_id' => $child_id]));
    }

    public function dump()
    {
        $sql = "select * from {$this->p_->table['tableName']} where id <> :id";

        return [
            'parts' => $this->p_->attFetchAll($sql, ['id' => $this->rootId]),
            'part_properties' => $this->r_->attFindAllBy([]),
            'part_items' => $this->i_->attFindAllBy([]),
        ];
    }

    public function findPart($id)
    {
        return $this->p_->attFindOneBy(['id' => $id]);
    }

    public function findProperty($child_id)
    {
        return $this->r_->attFindOneBy(['child_id' => $child_id]);
    }

    public function findPropertyByParentIdAndName($parent_id, $name)
    {
        return $this->r_->attFindOneBy(['parent_id' => $parent_id, 'name' => $name]);
    }

    public function findItem($child_id)
    {
        return $this->i_->attFindOneBy(['child_id' => $child_id]);
    }

    public function findItemByParentIdAndI($parent_id, $i)
    {
        return $this->i_->attFindOneBy(['parent_id' => $parent_id, 'i' => $i]);
    }

    public function findPartSet($id)
    {
        return [
            'part' => $this->findPart($id),
            'property' => $this->findProperty($id),
            'item' => $this->findItem($id),
        ];
    }

    public function findAllGlobals()
    {
        return $this->p_->attFetchAll(
            'select p.* '
                . 'from parts p '
                . ' left outer join part_items i '
                . '     on p.id = i.child_id '
                . ' left outer join part_properties r '
                . '     on p.id = r.child_id '
                . 'where i.child_id is null '
                . 'and r.child_id is null '
                . ' ',
            []
        );
    }

    public function findAllPropertiesOrderByName($parent_id)
    {
        $tableName = $this->r_->table['tableName'];
        return $this->r_->attFetchAll("select * from {$tableName} where parent_id = :parent_id order by name", ['parent_id' => $parent_id]);
    }

    public function findAllItemsOrderByI($parent_id)
    {
        $tableName = $this->i_->table['tableName'];
        return $this->i_->attFetchAll("select * from {$tableName} where parent_id = :parent_id order by i", ['parent_id' => $parent_id]);
    }

    public function findPartAndChildren($id)
    {
        $part = $this->findPart($id);
        if (is_null($part)) {
            return null;
        }
        $results = [
            'part' => $part,
            'part_properties' => null,
            'part_items' => null
        ];
        if ($part['type'] === 'object') {
            $part_properties = $this->findAllPropertiesOrderByName($part['id']);
            $part_property_results = [];
            foreach ($part_properties as $part_property) {
                $part_property_results[$part_property['name']] = $this->findPartAndChildren($part_property['child_id']);
            }
            $results['part_properties'] =  $part_property_results;
        } else if ($part['type'] === 'array') {
            $part_items = $this->findAllItemsOrderByI($part['id']);
            $part_item_results = [];
            foreach ($part_items as $part_item) {
                $part_item_results[$part_item['i']] = $this->findPartAndChildren($part_item['child_id']);
            }
            $results['part_items'] = $part_item_results;
        }

        return $results;
    }

    public function findRoot()
    {
        return $this->findPart($this->rootId);
    }

    /**
     * Get part value. The value contains child values if it has.
     *
     * @param int $id
     * @param array $opts   addPseudoProperty:  Add '___' property if an object has no properties.
     *                                          It is to prevent to encode to JSON array by json_encode if the array was empty.
     *                      addObjectId: add id of part if the type of part is object.
     * @return mixed
     */
    public function get(int $id, array $opts = [])
    {
        $part = $this->findPart($id);
        if (is_null($part)) {
            return null;
        }

        $opts = array_merge([
            'addPseudoProperty' => true,
            'pseudoPropertyName' => '___',
            'addObjectId' => true,
            'objectIdName' => '___id',
        ], $opts);

        if ($part['type'] === 'string') {
            return $part['value_string'];
        }

        if ($part['type'] === 'number') {
            return $part['value_number'];
        }

        if ($part['type'] === 'object') {
            $properties = $this->findAllPropertiesOrderByName($part['id']);
            $object = [];
            if ($opts['addObjectId']) {
                $object[$opts['objectIdName']] = $part['id'];
            }
            foreach ($properties as $property) {
                $object[$property['name']] = $this->get($property['child_id']);
            }
            if (empty($object) && $opts['addPseudoProperty']) {
                $object[$opts['pseudoPropertyName']] = null;
            }
            return $object;
        }

        if ($part['type'] === 'array') {
            $items = $this->findAllItemsOrderByI($part['id']);
            $array = [];
            foreach ($items as $item) {
                $array[$item['i']] = $this->get($item['child_id']);
            }
            return $array;
        }

        throw new Exception("The type is invalid. type:{$part['type']}");
    }

    /**
     * Query part, part_properties and part_items that are drawn by path
     * 
     * Example: '#123456/name/[3]'
     * 
     * - '/' is delimiter
     * - '#123456' specifies the id of part
     * - 'name' specifies the name of property
     * - '[3]' specifies the index of item
     *
     * @param string $path
     * @return mixed returns part id if the query was succeded otherwise null
     */
    public function queryId(string $path, array $opts = [])
    {
        if (!is_string($path) || empty($path)) {
            return null;
        }

        $ps = explode('/', $path);

        if (mb_substr($path, 0, 1) === '/') {
            $parent_id = $this->rootId;
            array_shift($ps);
        } else {
            $parent_id = null;
        }

        foreach ($ps as $p) {

            // query by Id
            if (preg_match('/\A#[0-9]+\z/u', $p)) {

                $part = $this->findPart(intVal(substr($p, 1)));

                if (is_null($part)) {
                    return null;
                }

                $parent_id = $part['id'];

                // query by index of item
            } else if (preg_match('/\A\[[0-9]+\]\z/u', $p)) {

                if (is_null($parent_id)) {
                    return null;
                }

                $item = $this->findItemByParentIdAndI($parent_id, intVal(substr($p, mb_strlen($p) - 2)));

                if (is_null($item)) {
                    return null;
                }

                $parent_id = $item['child_id'];

                // query by name of property
            } else {

                if (is_null($parent_id)) {
                    return null;
                }

                $property = $this->findPropertyByParentIdAndName($parent_id, $p);

                if (is_null($property)) {
                    return null;
                }

                $parent_id = $property['child_id'];
            }
        }

        if (is_null($parent_id)) {
            return null;
        }

        return $parent_id;
    }

    public function query(string $path, array $opts = [])
    {
        $id = $this->queryId($path, $opts);

        if (is_null($id)) {
            return null;
        }

        return $this->get($id);
    }

    public function load($dump)
    {
        $randomIdEnabled = $this->i_->randomIdEnabled;
        $this->i_->setRandomIdEnabled(false);
        $itemCount = 0;
        foreach ($dump['part_items'] as $item) {
            $insertId = $this->i_->attInsert($item);
            if ($insertId) {
                ++$itemCount;
            }
        }
        $this->i_->setRandomIdEnabled($randomIdEnabled);

        $randomIdEnabled = $this->r_->randomIdEnabled;
        $this->r_->setRandomIdEnabled(false);
        $propertyCount = 0;
        foreach ($dump['part_properties'] as $property) {
            $insertId = $this->r_->attInsert($property);
            if ($insertId) {
                ++$propertyCount;
            }
        }
        $this->r_->setRandomIdEnabled($randomIdEnabled);

        $randomIdEnabled = $this->p_->randomIdEnabled;
        $this->p_->setRandomIdEnabled(false);
        $partCount = 0;
        foreach ($dump['parts'] as $part) {
            $insertId = $this->p_->attInsert($part);
            if ($insertId) {
                ++$partCount;
            }
        }
        $this->p_->setRandomIdEnabled($randomIdEnabled);

        return [
            'part' => $partCount,
            'property' => $propertyCount,
            'item' => $itemCount,
        ];
    }

    public function addPart($type, $value_string, $value_number)
    {
        $part = $this->p_->createStruct();
        $part['type'] = $type;
        $part['value_string'] = null;
        $part['value_number'] = null;

        if ($type === 'string') {
            $part['value_string'] = $value_string;
        }
        if ($type === 'number') {
            $part['value_number'] = $value_number;
        }

        $id = $this->p_->attInsert($part);
        $part = $this->p_->attFindOneById($id);

        return $part;
    }

    public function setPart($part)
    {
        $id = $this->p_->attUpdateById($part);
        $part = $this->p_->attFindOneById($id);
        return $part;
    }

    public function addRoot()
    {
        $part = $this->p_->createStruct();

        $part['id'] = $this->rootId;
        $part['type'] = 'object';
        $part['value_string'] = null;
        $part['value_number'] = null;

        $randomIdEnabled = $this->p_->randomIdEnabled;
        $this->p_->setRandomIdEnabled(false);

        $id = $this->p_->attInsert($part);

        $this->p_->setRandomIdEnabled($randomIdEnabled);

        $part = $this->p_->attFindOneById($id);

        return $part;
    }

    public function addPartObject($parent_id, $child_id, $name)
    {
        $part_property = $this->r_->createStruct();
        $part_property['parent_id'] = $parent_id;
        $part_property['child_id'] = $child_id;
        $part_property['name'] = $name;

        $id = $this->r_->attInsert($part_property);
        $part_property = $this->r_->attFindOneById($id);

        return $part_property;
    }

    public function setPartObject($part_property)
    {
        $id = $this->r_->attUpdateById($part_property);
        $part_property = $this->r_->attFindOneById($id);

        return $part_property;
    }

    public function addPartArray($parent_id, $child_id)
    {
        $part_item = $this->i_->createStruct();
        $part_item['parent_id'] = $parent_id;
        $part_item['child_id'] = $child_id;
        $part_item['i'] = $this->maxI($parent_id) + 1;

        $id = $this->i_->attInsert($part_item);
        $part_item = $this->i_->attFindOneById($id);

        return $part_item;
    }

    public function setPartArray($part_item)
    {
        $id = $this->i_->attUpdateById($part_item);
        $part_item = $this->i_->attFindOneById($id);

        return $part_item;
    }

    public function addNewProperty($parent_id, $name, $type, $value_string, $value_number)
    {
        $parent_part = $this->findPart($parent_id);
        if (is_null($parent_part)) {
            return false;
        }

        if ($parent_part['type'] !== 'object') {
            throw new Exception("The id:{$parent_id} was not an object.");
        }

        if (!is_null($this->findPropertyByParentIdAndName($parent_id, $name))) {
            throw new Exception("The object has the name:{$name} of property.");
        }

        $part = $this->addPart($type, $value_string, $value_number);

        return $this->addPartObject($parent_id, $part['id'], $name);
    }

    public function setPrimitiveValueToProperty($parent_id, $name, $value)
    {
        $newPart = $this->putPrimitiveValueToProperty($parent_id, $name, $value);

        if (is_null($newPart)) {
            throw new Exception("The property was not found.");
        }

        return $newPart;
    }

    public function putPrimitiveValueToProperty($parent_id, $name, $value)
    {
        $property = $this->findPropertyByParentIdAndName($parent_id, $name);

        if (is_null($property)) {
            return null;
        }

        $part = $this->findPart($property['child_id']);
        if (is_null($part)) {
            return null;
        }

        if ($part['type'] === 'string') {
            $part['value_string'] = $value;
        } else if ($part['type'] === 'number') {
            $part['value_number'] = $value;
        }

        return $this->setPart($part);
    }

    public function setProperty($property, $part)
    {
        $parent_part = $this->findPart($property['parent_id']);
        if (is_null($parent_part)) {
            return false;
        }

        if ($parent_part['type'] !== 'object') {
            throw new Exception("The id:{$property['parent_id']} was not an object.");
        }
        $property_for_validation = $this->findPropertyByParentIdAndName($property['parent_id'], $property['name']);
        if (!is_null($property_for_validation) && $property_for_validation['child_id'] !== $property['child_id']) {
            throw new Exception("The object has the name:{$property['name']} of property.");
        }

        $this->setPart($part);
        return $this->setPartObject($property);
    }

    public function addNewItem($parent_id, $type, $value_string, $value_number)
    {
        $part = $this->findPart($parent_id);
        if (is_null($part)) {
            return false;
        }

        if ($part['type'] !== 'array') {
            throw new Exception("The id:{$parent_id} was not an array.");
        }

        $part = $this->addPart($type, $value_string, $value_number);

        return $this->addPartArray($parent_id, $part['id']);
    }

    public function setItem($item, $part)
    {
        $parent_part = $this->findPart($item['parent_id']);
        if (is_null($parent_part)) {
            return false;
        }

        if ($parent_part['type'] !== 'array') {
            throw new Exception("The id:{$item['parent_id']} was not an array.");
        }

        $this->setPart($part);

        return $this->setPartArray($item);
    }

    /**
     * Clone a part from original
     *
     * @param mixed $parent_id parent_id that is of parent for cloned part. This value must be false that was called by boolvalu() if new part belongs to global.
     * @param [string] $name Name for property if parent is part_property unless this value is null
     * @param [array] $original_part_and_children Original for cloning. this value is assumed to be results of findPartAndChildren() method
     * @return integer returns new part id
     */
    public function clone($parent_id, $name, $original_part_and_children): int
    {
        if (is_null($original_part_and_children)) {
            throw new Exception("\$original_part_and_children was null.");
        }

        $part = &$original_part_and_children['part'];
        $type = $part['type'];
        $value_string = $part['value_string'];
        $value_number = $part['value_number'];

        if (!boolval($parent_id)) {
            $new_part = $this->addPart($type, $value_string, $value_number);
            $new_part_id = $new_part['id'];
        } else {
            $parent_part = $this->findPart($parent_id);
            if (is_null($parent_part)) {
                throw new Exception("Parent part was not found for the id:{$parent_id}.");
            }
            if ($parent_part['type'] === 'object') {
                $new_part_property = $this->addNewProperty($parent_id, $name, $type, $value_string, $value_number);
                $new_part_id = $new_part_property['child_id'];
            } else if ($parent_part['type'] === 'array') {
                $new_part_item = $this->addNewItem($parent_id, $type, $value_string, $value_number);
                $new_part_id = $new_part_item['child_id'];
            }
        }

        if ($type === 'object') {
            foreach ($original_part_and_children['part_properties'] as $name => $part_property) {
                $this->clone($new_part_id, $name, $part_property);
            }
        } else if ($type === 'array') {
            foreach ($original_part_and_children['part_items'] as $part_item) {
                $this->clone($new_part_id, null, $part_item);
            }
        }

        return $new_part_id;
    }

    /**
     * Clone a part by part id
     *
     * @param mixed $parent_id parent_id that is of parent for cloned part. This value must be false that was called by boolvalu() if new part belongs to global.
     * @param mixed $name Name for property if parent is part_property unless this value is null
     * @param mixed $part_id id of original part
     * @return integer returns new part id
     */
    public function cloneById($parent_id, $name, $part_id): int
    {
        $partAndChildren = $this->findPartAndChildren($part_id);
        return $this->clone($parent_id, $name, $partAndChildren);
    }

    public function delete($id)
    {
        $part = $this->findPart($id);
        if (is_null($part)) {
            return false;
        }

        $property = $this->findProperty($id);

        $item = $this->findItem($id);

        if ($part['type'] === 'object') {
            $this->deleteObject($id);
        } else if ($part['type'] === 'array') {
            $this->deleteArray($id);
        } else {
            $this->deletePart($id);
        }

        if (!is_null($property)) {
            $this->deleteProperty($property['child_id'], $property['parent_id']);
        }

        if (!is_null($item)) {
            $this->deleteItem($item['child_id'], $item['parent_id']);
        }

        return true;
    }

    public function deletePart($id)
    {
        $this->p_->attDeleteBy(['id' => $id]);
    }

    public function deleteObject($parent_id)
    {
        $part = $this->findPart($parent_id);
        if (is_null($part)) {
            return false;
        }

        if ($part['type'] !== 'object') {
            throw new Exception("The id:{$parent_id} was not an object.");
        }

        $properties = $this->findAllPropertiesOrderByName($parent_id);
        foreach ($properties as $property) {
            $this->delete($property['child_id']);
        }

        $this->deletePart($parent_id);

        return true;
    }

    /**
     * Delete part_item.
     * This process goes like:
     * - Delete all array items;
     * - Delete part_item itself.
     * - Delete part itself.
     *
     * @param [type] $parent_id
     * @return void
     */
    public function deleteArray($parent_id)
    {
        $part = $this->findPart($parent_id);
        if (is_null($part)) {
            return false;
        }

        if ($part['type'] !== 'array') {
            throw new Exception("The id:{$parent_id} was not an array.");
        }

        $items = $this->findAllItemsOrderByI($parent_id);
        foreach ($items as $item) {
            $this->delete($item['child_id']);
        }

        $this->deletePart($parent_id);

        return true;
    }

    public function deleteProperty($child_id, $parent_id)
    {
        if (!$this->isProperty($child_id)) {
            throw new Exception("The id:{$child_id} was not a property of object.");
        }

        // Delete the property in the object.
        $this->r_->attDeleteBy(['parent_id' => $parent_id, 'child_id' => $child_id]);
    }

    /**
     * Delete an item in the array.
     * This method doesn't delete parts record of item.
     * This process goes like:
     * - Delete the item in the array.
     * - Reorder i of the array that the item belonged to.
     *
     * @param [type] $id
     * @return void
     */
    public function deleteItem($child_id, $parent_id)
    {
        if (!$this->isItem($child_id)) {
            throw new Exception("The id:{$child_id} was not an item of array.");
        }

        // Delete the item in the array.
        $this->i_->attDeleteBy(['parent_id' => $parent_id, 'child_id' => $child_id]);

        return $this->reorderI($parent_id);
    }

    public function deleteAll()
    {
        $this->i_->attDeleteBy([]);
        $this->r_->attDeleteBy([]);
        $this->p_->attDeleteBy([]);
    }

    public function reorderI($parent_id)
    {
        $part = $this->findPart($parent_id);
        if (is_null($part)) {
            return false;
        }

        if ($part['type'] !== 'array') {
            throw new Exception("The id:{$parent_id} was not an array.");
        }

        $array = $this->findAllItemsOrderByI($parent_id);
        $i = 0;
        foreach ($array as $item) {
            $item['i'] = $i;
            $this->i_->attUpdateById($item);
            ++$i;
        }

        return true;
    }

    public function maxI($parent_id)
    {
        $maxI = $this->i_->attFetchOne(
            'select max(i) as i_max from part_items '
                . 'where parent_id = :parent_id ',
            ['parent_id' => $parent_id]
        )['i_max'];

        return is_null($maxI) ? -1 : $maxI;
    }

    public function path($id)
    {
        $targetId = $id;
        $part = $this->findPart($targetId);
        $points = [];
        while ($part) {

            $set = array_merge($this->r_->createStruct(), $this->i_->createStruct(), $part);
            $set['i'] = null;

            $part_property = $this->findProperty($targetId);
            if ($part_property) {
                $set = array_merge($set, $part_property);
                $set['id'] = $targetId;
                $set['sub_type'] = 'property';
                array_unshift($points, $set);
                $targetId = $part_property['parent_id'];
                $part = $this->findPart($targetId);
                continue;
            }

            $part_item = $this->findItem($targetId);
            if ($part_item) {
                $set = array_merge($set, $part_item);
                $set['id'] = $targetId;
                $set['sub_type'] = 'item';
                array_unshift($points, $set);
                $targetId = $part_item['parent_id'];
                $part = $this->findPart($targetId);
                continue;
            }

            $set['sub_type'] = 'root';
            array_unshift($points, $set);
            break;
        }
        return $points;
    }
}
