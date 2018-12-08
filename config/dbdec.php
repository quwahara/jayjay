<?php
return [
    'tables' => [
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
