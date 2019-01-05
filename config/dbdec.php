<?php
return [
    'tables' => [
        [
            'tableName' => 'tests',
            'tableName.singular' => 'test',
            'options' => [],
            'columns' => [
                [
                    'fieldName' => 'id',
                    'definition' => 'BIGINT NOT NULL AUTO_INCREMENT UNIQUE'
                ],
                [
                    'fieldName' => 'apple',
                    'definition' => 'VARCHAR(60) CHARACTER SET latin1 NOT NULL',
                    // Attributes for input tag
                    'attr' => [
                        'required' => '',   // Turns required attribute on
                        'minlength' => 1,
                        'maxlength' => 60,
                    ]
                ],
                [
                    'fieldName' => 'banana',
                    'definition' => 'VARCHAR(60) CHARACTER SET latin1 NOT NULL',
                    'attr' => [
                        'required' => '',
                        'minlength' => 1,
                        'maxlength' => 60,
                    ]
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
                    'definition' => 'VARCHAR(60) CHARACTER SET latin1 NOT NULL',
                    // Attributes for input tag
                    'attr' => [
                        'required' => '',   // Turns required attribute on
                        'minlength' => 6,
                        'maxlength' => 60,
                    ]
                ],
                [
                    'fieldName' => 'password',
                    'definition' => 'VARCHAR(60) CHARACTER SET latin1 NOT NULL',
                    'attr' => [
                        'required' => '',
                        'minlength' => 8,
                        'maxlength' => 60,
                    ]
                ],
            ],
            'index_definitions' => [
                'UNIQUE(name)'
            ],
        ],
        [
            'tableName' => 'parts',
            'tableName.singular' => 'part',
            'options' => [],
            'columns' => [
                [
                    'fieldName' => 'id',
                    'definition' => 'BIGINT NOT NULL AUTO_INCREMENT UNIQUE'
                ],
                [
                    'fieldName' => 'type',
                    'definition' => 'VARCHAR(50) CHARACTER SET latin1 NOT NULL'
                ],
                [
                    'fieldName' => 'value',
                    'definition' => 'TEXT NOT NULL'
                ],
            ],
            'index_definitions' => [
            ],
        ],
        [
            'tableName' => 'part_objects',
            'tableName.singular' => 'part_object',
            'options' => [],
            'columns' => [
                [
                    'fieldName' => 'id',
                    'definition' => 'BIGINT NOT NULL AUTO_INCREMENT UNIQUE'
                ],
                [
                    'fieldName' => 'parent_id',
                    'definition' => 'BIGINT NOT NULL'
                ],
                [
                    'fieldName' => 'child_id',
                    'definition' => 'BIGINT NOT NULL'
                ],
                [
                    'fieldName' => 'name',
                    'definition' => 'VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL'
                ],
            ],
            'index_definitions' => [
                'UNIQUE(parent_id, child_id)',
                'UNIQUE(parent_id, name)',
            ],
        ],
        [
            'tableName' => 'part_arrays',
            'tableName.singular' => 'part_array',
            'options' => [],
            'columns' => [
                [
                    'fieldName' => 'id',
                    'definition' => 'BIGINT NOT NULL AUTO_INCREMENT UNIQUE'
                ],
                [
                    'fieldName' => 'parent_id',
                    'definition' => 'BIGINT NOT NULL'
                ],
                [
                    'fieldName' => 'child_id',
                    'definition' => 'BIGINT NOT NULL'
                ],
                [
                    'fieldName' => 'i',
                    'definition' => 'BIGINT NOT NULL'
                ],
            ],
            'index_definitions' => [
                'UNIQUE(parent_id, child_id)',
                'UNIQUE(parent_id, i)',
            ],
        ],
        [
            'tableName' => 'objects',
            'tableName.singular' => 'object',
            'options' => [],
            'columns' => [
                [
                    'fieldName' => 'id',
                    'definition' => 'BIGINT NOT NULL AUTO_INCREMENT UNIQUE'
                ],
                [
                    'fieldName' => 'type',
                    'definition' => 'VARCHAR(50) CHARACTER SET latin1 NOT NULL'
                ],
                [
                    'fieldName' => 'i',
                    'definition' => 'INT NOT NULL'
                ],
                [
                    'fieldName' => 'name',
                    'definition' => 'TEXT NOT NULL'
                ],
                [
                    'fieldName' => 'value',
                    'definition' => 'TEXT NOT NULL'
                ],
            ],
            'index_definitions' => [
            ],
        ],
        [
            'tableName' => 'object_graphs',
            'tableName.singular' => 'object_graph',
            'options' => [],
            'columns' => [
                [
                    'fieldName' => 'id',
                    'definition' => 'BIGINT NOT NULL AUTO_INCREMENT UNIQUE'
                ],
                [
                    'fieldName' => 'parent_id',
                    'definition' => 'BIGINT NOT NULL'
                ],
                [
                    'fieldName' => 'child_id',
                    'definition' => 'BIGINT NOT NULL'
                ],
            ],
            'index_definitions' => [
                'UNIQUE(parent_id, child_id)',
            ],
        ],
    //
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
