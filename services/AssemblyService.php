<?php
namespace Services;

require_once __DIR__ . '/../vendor/autoload.php';

use \Exception;

class AssemblyService
{
    /** PartService */
    public $part;

    public function init($partService)
    {
        $this->part = $partService;
        return $this;
    }

    public function getSchemaNameAndDescriptions($recordId, array $opts = [])
    {
        $path = $this->part->path($recordId);
        if (empty($path)) {
            return null;
        }

        if (count($path) < 3) {
            throw new Exception("The path was invlid.");
        }

        if ($path[2]['name'] !== 'schema') {
            throw new Exception("The path was invlid.");
        }

        $name = $path[3]['name'];

        $opts = array_merge([
            'addObjectId' => false,
        ], $opts);

        $descriptions = $this->getDescriptions($name, $opts);
        if (is_null($descriptions)) {
            throw new Exception("The descriptions was not found.");
        }

        return [
            'name' => $name,
            'descriptions' => $descriptions,
        ];
    }

    public function getDescriptions($schemaName, array $opts = [])
    {
        $opts = array_merge([
            'addObjectId' => false,
        ], $opts);

        $descriptions = $this->part->query("/system/schema/{$schemaName}/descriptions", $opts);

        return $descriptions;
    }


    /**
     * Transform descriptions to Attrs, Columns and Struct
     *
     * @param array $descriptions
     * @return array
     */
    public function toACS(array $descriptions, array $opts = []): array
    {
        $opts = array_merge([
            'addPseudoProperty' => true,
            'pseudoPropertyName' => '___',
        ], $opts);


        $attrs = [];

        foreach ($descriptions['columns'] as $col) {
            $attrs[$col['name']] = $col['attr'];
        }

        if (empty($attrs) && $opts['addPseudoProperty']) {
            $attrs[$opts['pseudoPropertyName']] = null;
        }


        $struct = [];
        foreach ($descriptions['columns'] as $col) {
            $struct[$col['name']] = '';
        }

        if (empty($struct) && $opts['addPseudoProperty']) {
            $struct[$opts['pseudoPropertyName']] = null;
        }

        $violation_set_structs = [];
        $violation_set_data = [];
        foreach ($descriptions['columns'] as $col) {
            $violation_set_structs[$col['name']] = [
                'violations[]' => [
                    'type' => '',
                    'value' => '',
                    'violation' => '',
                    'params[]' => [
                        'name' => '',
                        'value' => '',
                    ],
                    'message' => '',
                ]
            ];
            $violation_set_data[$col['name']] = [
                'violations' => []
            ];
        }

        if (empty($violation_set_structs) && $opts['addPseudoProperty']) {
            $violation_set_structs[$opts['pseudoPropertyName']] = null;
            $violation_set_data[$opts['pseudoPropertyName']] = null;
        }

        return [
            'attrs' => $attrs,
            'columns' => $descriptions['columns'],
            'struct' => $struct,
            'violation_set_structs' => $violation_set_structs,
            'violation_set_data' => $violation_set_data,
        ];
    }
}
