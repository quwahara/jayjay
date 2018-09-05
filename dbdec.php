<?php
return [
    'tables' => [
        [
            'tableName' => 'contexts',
            'tableName.singular' => 'context',
            'options' => [],
            'columns' => [
                [
                    'fieldName' => 'status',
                    'definition' => 'VARCHAR(60) CHARACTER SET latin1 NOT NULL'
                ],
            ],
            'index_definitions' => [
            ],
        ],
        [
            'tableName' => 'users',
            'tableName.singular' => 'user',
            'options' => [],
            'columns' => [
                [
                    'fieldName' => 'id',
                    'definition' => 'BIGINT NOT NULL AUTO_INCREMENT UNIQUE'
                ],
                [
                    'fieldName' => 'name',
                    'definition' => 'VARCHAR(60) CHARACTER SET latin1 NOT NULL'
                ],
                [
                    'fieldName' => 'password',
                    'definition' => 'VARCHAR(60) CHARACTER SET latin1 NOT NULL'
                ],
            ],
            'index_definitions' => [
                'UNIQUE(name)'
            ],
        ],
        [
            'tableName' => 'entities',
            'tableName.singular' => 'entity',
            'options' => [],
            'columns' => [
                [
                    'fieldName' => 'id',
                    'definition' => 'BIGINT NOT NULL AUTO_INCREMENT UNIQUE'
                ],
                [
                    'fieldName' => 'entity_name',
                    'definition' => 'VARCHAR(50)  CHARACTER SET latin1 NOT NULL'
                ],
            ],
            'index_definitions' => [
                'UNIQUE(entity_name)'
            ],
        ],
    //
        [
            'tableName' => 'fields',
            'tableName.singular' => 'field',
            'options' => [],
            'columns' => [
                [
                    'fieldName' => 'id',
                    'definition' => 'BIGINT NOT NULL AUTO_INCREMENT UNIQUE'
                ],
                [
                    'fieldName' => 'entity_id',
                    'definition' => 'BIGINT NOT NULL'
                ],
                [
                    'fieldName' => 'field_name',
                    'definition' => 'VARCHAR(50)  CHARACTER SET latin1 NOT NULL'
                ],
                [
                    'fieldName' => 'field_type',
                    'definition' => 'VARCHAR(50)  CHARACTER SET latin1 NOT NULL'
                ],
            ],
            'index_definitions' => [],
        ],
    //
    ],
];
?>
