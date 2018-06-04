<?php
return [
  'tables' => [
    [
      'tableName' => 'entities',
      'options' => [
      ],
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
      'options' => [
      ],
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
      'index_definitions' => [
      ],
    ],
    //
  ],
];
?>
