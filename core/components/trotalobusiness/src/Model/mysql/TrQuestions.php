<?php
namespace trotalobusiness\Model\mysql;

use xPDO\xPDO;

class TrQuestions extends \trotalobusiness\Model\TrQuestions
{

    public static $metaMap = array (
        'package' => 'trotalobusiness\\Model\\',
        'version' => '3.0',
        'table' => 'trotalo_questions',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'parent_id' => 0,
            'question' => '',
            'prompt' => '',
            'options' => '',
            'ai_generated' => 0,
            'api_call' => 0,
            'question_type' => 0,
            'gpt_function' => '',
        ),
        'fieldMeta' => 
        array (
            'parent_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '11',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'default' => 0,
            ),
            'question' => 
            array (
                'dbtype' => 'TEXT',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'prompt' => 
            array (
                'dbtype' => 'TEXT',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'options' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'ai_generated' => 
            array (
                'dbtype' => 'int',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'default' => 0,
            ),
            'api_call' => 
            array (
                'dbtype' => 'int',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'default' => 0,
            ),
            'question_type' => 
            array (
                'dbtype' => 'int',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'default' => 0,
            ),
            'gpt_function' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '255',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
        ),
    );

}
