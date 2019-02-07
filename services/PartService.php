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

    public function isItem($child_id)
    {
        return !is_null($this->pa_->attFindOneBy(['child_id' => $child_id]));
    }

    public function findPart($id)
    {
        return $this->p_->attFindOneBy(['id' => $id]);
    }

    public function findItem($child_id)
    {
        return $this->pa_->attFindOneBy(['child_id' => $child_id]);
    }

    public function findAllArrayOrderByI($parent_id)
    {
        $tableName = $this->pa_->table['tableName'];
        return $this->pa_->attFetchAll("select * from {$tableName} where parent_id = :parent_id order by i", ['parent_id' => $parent_id]);
    }

    public function delete($id)
    {
        $part = $this->findPart($id);
        if (is_null($part)) {
            return false;
        }

        $item = $this->findItem($id);

        if ($part['type'] === 'object') {
            throw new Exception('Not implemented');
        } else if ($part['type'] === 'array') {
            $this->deleteArray($id);
        } else {
            $this->deletePart($id);
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

        $array = $this->findAllArrayOrderByI($parent_id);
        foreach ($array as $item) {
            $this->delete($item['child_id']);
        }

        $this->deletePart($parent_id);

        return true;
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

        $array = $this->findAllArrayOrderByI($parent_id);
        $i = 0;
        foreach ($array as $item) {
            $item['i'] = $i;
            $this->pa_->attUpdateById($item);
            ++$i;
        }

        return true;
    }

}
