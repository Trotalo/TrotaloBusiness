<?php

use MODX\Revolution\Rest\modRestController;
use MODX\Revolution\Rest\modRestServiceRequest;

require_once 'GPTController.php';

class TrotaloEarlyAccessUsr extends GPTController {

  /** @var string $classPrefix */
  public $vloxPrefix;

  /** @var string $classPrefix */
  public $modxPrefix;

  /** @var string $classKey The xPDO class to use */
  public $classKey = 'trotalobusiness\Model\TrEarlyAccessUsr';

  /** @var string $classAlias The alias of the class when used in the getList method */
  public $classAlias = 'TrEarlyAccessUsr';

  /** @var string $defaultSortField The default field to sort by in the getList method */
  public $defaultSortField = 'id';
  /** @var string $defaultSortDirection The default direction to sort in the getList method */
  public $defaultSortDirection = 'ASC';

  public $primaryKeyField = 'id';

  public function __construct(modX $modx, modRestServiceRequest $request, array $config = array()) {
    parent::__construct($modx, $request, $config);
    $isMODX3 = $modx->getVersionData()['version'] >= 3;
    $this->vloxPrefix = $isMODX3 ? 'trotalobusiness\Model\\' : '';
    $this->modxPrefix = $isMODX3 ? 'MODX\Revolution\\' : '';
  }

  public function get() {
    $pk = $this->getProperty($this->primaryKeyField);
    if(!strpos($pk, '?')) {
      //return parent::get();
      return $this->failure($this->modx->lexicon('rest.err_obj_nf', [
        'class_key' => $this->classKey,
      ]));
    } else {
      $operation = substr($pk,strpos($pk, '?') + 1);
      $pk = substr($pk,0, strpos($pk, '?'));
      if (strcmp(strtolower($operation), 'code') === 0) {
        $object = $this->modx->getObject($this->classKey, ['invitation_code' => $pk]);
        if (empty($object)) {
          return $this->failure($this->modx->lexicon('rest.err_obj_nf', [
            'class_key' => $this->classKey,
          ]));
        }
        $objectArray = $object->toArray();

        $afterRead = $this->afterRead($objectArray);
        if ($afterRead !== true && $afterRead !== null) {
          return $this->failure($afterRead === false ? $this->errorMessage : $afterRead);
        }

        return $this->success('', $objectArray);
      }
    }
  }

}