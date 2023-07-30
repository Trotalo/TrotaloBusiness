<?php
namespace trotalobusiness\Model\mysql;

use xPDO\xPDO;

class TrConversations extends \trotalobusiness\Model\TrConversations
{

    public static $metaMap = array (
        'package' => 'trotalobusiness\\Model\\',
        'version' => '3.0',
        'table' => 'trotalo_conversations',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'answer_id' => 0,
            'user_id' => 0,
            'conversation' => '',
        ),
        'fieldMeta' => 
        array (
            'answer_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '11',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'default' => 0,
            ),
            'user_id' => 
            array (
                'dbtype' => 'int',
                'precision' => '11',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'default' => 0,
            ),
            'conversation' => 
            array (
                'dbtype' => 'TEXT',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
        ),
    );

}
