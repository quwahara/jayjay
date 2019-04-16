<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use \Exception;

class PartService
{
    public $p_;
    public $po_;
    public $pa_;

    public function init($part, $part_properties, $part_item)
    {
        $this->p_ = $part;
        $this->po_ = $part_properties;
        $this->pa_ = $part_item;
        return $this;
    }

    // public function isObject($parent_id)
    // {
    //     return !is_null($this->po_->attFindOneBy(['parent_id' => $parent_id]));
    // }

    // public function isArray($parent_id)
    // {
    //     return !is_null($this->pa_->attFindOneBy(['parent_id' => $parent_id]));
    // }

    public function isProperty($child_id)
    {
        return !is_null($this->po_->attFindOneBy(['child_id' => $child_id]));
    }

    public function isItem($child_id)
    {
        return !is_null($this->pa_->attFindOneBy(['child_id' => $child_id]));
    }

    public function findPart($id)
    {
        return $this->p_->attFindOneBy(['id' => $id]);
    }

    public function findProperty($child_id)
    {
        return $this->po_->attFindOneBy(['child_id' => $child_id]);
    }

    public function findPropertyByParentIdAndName($parent_id, $name)
    {
        return $this->po_->attFindOneBy(['parent_id' => $parent_id, 'name' => $name]);
    }

    public function findItem($child_id)
    {
        return $this->pa_->attFindOneBy(['child_id' => $child_id]);
    }

    public function findPartSet($id)
    {
        return [
            'part' => $this->findPart($id),
            'property' => $this->findProperty($id),
            'item' => $this->findItem($id),
        ];
    }

    public function findAllPropertiesOrderByName($parent_id)
    {
        $tableName = $this->po_->table['tableName'];
        return $this->po_->attFetchAll("select * from {$tableName} where parent_id = :parent_id order by name", ['parent_id' => $parent_id]);
    }

    public function findAllItemsOrderByI($parent_id)
    {
        $tableName = $this->pa_->table['tableName'];
        return $this->pa_->attFetchAll("select * from {$tableName} where parent_id = :parent_id order by i", ['parent_id' => $parent_id]);
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
            $part_propertys = $this->findAllPropertiesOrderByName($part['id']);
            $part_property_results = [];
            foreach ($part_propertys as $part_property) {
                $part_property_results[$part_property['name']] = $this->findPartAndChildren($part_property['child_id']);
            }
            $results['part_propertys'] =  $part_property_results;
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

    public function addPartObject($parent_id, $child_id, $name)
    {
        $part_property = $this->po_->createStruct();
        $part_property['parent_id'] = $parent_id;
        $part_property['child_id'] = $child_id;
        $part_property['name'] = $name;

        $id = $this->po_->attInsert($part_property);
        $part_property = $this->po_->attFindOneById($id);

        return $part_property;
    }

    public function setPartObject($part_property)
    {
        $id = $this->po_->attUpdateById($part_property);
        $part_property = $this->po_->attFindOneById($id);

        return $part_property;
    }

    public function addPartArray($parent_id, $child_id)
    {
        $part_item = $this->pa_->createStruct();
        $part_item['parent_id'] = $parent_id;
        $part_item['child_id'] = $child_id;
        $part_item['i'] = $this->maxI($parent_id) + 1;

        $id = $this->pa_->attInsert($part_item);
        $part_item = $this->pa_->attFindOneById($id);

        return $part_item;
    }

    public function setPartArray($part_item)
    {
        $id = $this->pa_->attUpdateById($part_item);
        $part_item = $this->pa_->attFindOneById($id);

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
            } else if ($parent_part['type'] === 'object') {
                $new_part_item = $this->addNewItem($parent_id, $type, $value_string, $value_number);
                $new_part_id = $new_part_item['child_id'];
            }
        }

        if ($type === 'object') {
            foreach ($original_part_and_children['part_propertys'] as $name => $part_property) {
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
        $this->po_->attDeleteBy(['parent_id' => $parent_id, 'child_id' => $child_id]);
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
        $this->pa_->attDeleteBy(['parent_id' => $parent_id, 'child_id' => $child_id]);

        return $this->reorderI($parent_id);
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
            $this->pa_->attUpdateById($item);
            ++$i;
        }

        return true;
    }

    public function maxI($parent_id)
    {
        $maxI = $this->pa_->attFetchOne(
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

            $set = array_merge($this->po_->createStruct(), $this->pa_->createStruct(), $part);
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

            $set['sub_type'] = 'global';
            array_unshift($points, $set);
            break;
        }
        return $points;
    }
}
