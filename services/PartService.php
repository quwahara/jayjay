<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use \Exception;

class PartService
{
    public $p_;
    public $po_;
    public $pa_;

    public function init($part, $part_object, $part_array)
    {
        $this->p_ = $part;
        $this->po_ = $part_object;
        $this->pa_ = $part_array;
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

    public function findPropertyByName($name)
    {
        return $this->po_->attFindOneBy(['name' => $name]);
    }

    public function findItem($child_id)
    {
        return $this->pa_->attFindOneBy(['child_id' => $child_id]);
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

    public function addPart($type, $value)
    {
        $part = $this->p_->createStruct();
        $part['type'] = $type;
        $part['value'] = $value;

        $id = $this->p_->attInsert($part);
        $part = $this->p_->attFindOneById($id);

        return $part;
    }

    public function addPartObject($parent_id, $child_id, $name)
    {
        $part_object = $this->po_->createStruct();
        $part_object['parent_id'] = $parent_id;
        $part_object['child_id'] = $child_id;
        $part_object['name'] = $name;

        $id = $this->po_->attInsert($part_object);
        $part_object = $this->po_->attFindOneById($id);

        return $part_object;
    }

    public function addPartArray($parent_id, $child_id)
    {
        $part_array = $this->pa_->createStruct();
        $part_array['parent_id'] = $parent_id;
        $part_array['child_id'] = $child_id;
        $part_array['i'] = $this->maxI($parent_id) + 1;

        $id = $this->pa_->attInsert($part_array);
        $part_array = $this->pa_->attFindOneById($id);

        return $part_array;
    }

    public function addNewProperty($parent_id, $name, $type, $value)
    {
        $parent_part = $this->findPart($parent_id);
        if (is_null($parent_part)) {
            return false;
        }

        if ($parent_part['type'] !== 'object') {
            throw new Exception("The id:{$parent_id} was not an object.");
        }

        if (!is_null($this->findPropertyByName($name))) {
            throw new Exception("The object has the name:{$name} of property.");
        }

        $part = $this->addPart($type, $value);

        return $this->addPartObject($parent_id, $part['id'], $name);
    }

    public function addNewItem($parent_id, $type, $value)
    {
        $part = $this->findPart($parent_id);
        if (is_null($part)) {
            return false;
        }

        if ($part['type'] !== 'array') {
            throw new Exception("The id:{$parent_id} was not an array.");
        }

        $part = $this->addPart($type, $value);

        return $this->addPartArray($parent_id, $part['id']);
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
     * Delete part_array.
     * This process goes like:
     * - Delete all array items;
     * - Delete part_array itself.
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
            'select max(i) as i_max from part_arrays '
                . 'where parent_id = :parent_id ',
            ['parent_id' => $parent_id]
        )['i_max'];

        return is_null($maxI) ? -1 : $maxI;
    }

}
