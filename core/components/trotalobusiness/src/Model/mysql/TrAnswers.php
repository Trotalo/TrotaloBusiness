<?php
namespace trotalobusiness\Model\mysql;

use xPDO\xPDO;

class TrAnswers extends \trotalobusiness\Model\TrAnswers
{

    public static $metaMap = array (
        'package' => 'trotalobusiness\\Model\\',
        'version' => '3.0',
        'table' => 'trotalo_answers',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'question_id' => 0,
            'role' => '',
            'content' => '',
            'ai_content' => '',
            'timestamp' => 'CURRENT_TIMESTAMP',
            'user_id' => 0,
        ),
        'fieldMeta' => 
        array (
            'question_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '11',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'default' => 0,
            ),
            'role' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '20',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'content' => 
            array (
                'dbtype' => 'TEXT',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'ai_content' => 
            array (
                'dbtype' => 'TEXT',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'timestamp' => 
            array (
                'dbtype' => 'datetime',
                'phptype' => 'datetime',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ),
            'user_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '11',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'default' => 0,
            ),
        ),
    );

}
