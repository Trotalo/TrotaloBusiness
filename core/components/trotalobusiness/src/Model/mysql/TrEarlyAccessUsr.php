<?php
namespace trotalobusiness\Model\mysql;

use xPDO\xPDO;

class TrEarlyAccessUsr extends \trotalobusiness\Model\TrEarlyAccessUsr
{

    public static $metaMap = array (
        'package' => 'trotalobusiness\\Model\\',
        'version' => '3.0',
        'table' => 'trotalo_early_access_user',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
        'fields' => 
        array (
            'invitation_code' => '',
            'name' => '',
            'phone_number' => '',
            'email' => '',
            'generated' => 0,
        ),
        'fieldMeta' => 
        array (
            'invitation_code' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '200',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'name' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '200',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'phone_number' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '20',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'email' => 
            array (
                'dbtype' => 'varchar',
                'precision' => '400',
                'phptype' => 'string',
                'null' => false,
                'default' => '',
            ),
            'generated' => 
            array (
                'dbtype' => 'int',
                'precision' => '1',
                'attributes' => 'unsigned',
                'phptype' => 'integer',
                'default' => 0,
            ),
        ),
    );

}
