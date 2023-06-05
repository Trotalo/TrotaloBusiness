<?php


use MODX\Revolution\Rest\modRestController;
use MODX\Revolution\Rest\modRestServiceRequest;

require_once 'GPTController.php';

class TrotaloQuestions extends GPTController {

  /** @var string $classPrefix */
  public $vloxPrefix;

  /** @var string $classPrefix */
  public $modxPrefix;

  /** @var string $classKey The xPDO class to use */
  public $classKey = 'trotalobusiness\Model\TrQuestions';

  /** @var string $classAlias The alias of the class when used in the getList method */
  public $classAlias  = 'TrQuestions';

  /** @var string $defaultSortField The default field to sort by in the getList method */
  public $defaultSortField = 'id';
  /** @var string $defaultSortDirection The default direction to sort in the getList method */
  public $defaultSortDirection = 'ASC';

  public $primaryKeyField = 'id';

  public $answersPrefix = 'trotalobusiness\Model\TrAnswers';

  public function __construct(modX $modx,modRestServiceRequest $request,array $config = array()){
    parent::__construct($modx, $request, $config);
    $isMODX3 = $modx->getVersionData()['version'] >= 3;
    $this->vloxPrefix = $isMODX3 ? 'Vlox\Model\\' : '';
    $this->modxPrefix = $isMODX3 ? 'MODX\Revolution\\' : '';
  }

  private function getAiGenerated($object) {
    if ($object->get('parent_id') === 0) {
      $object->set('questions', $object->get('options'));
    } else {
      $prevAnswer = $this->modx->getObject($this->answersPrefix, ['question_id' => $object->get('parent_id')]);
      if (is_null($prevAnswer)) {
        throw new Exception('Needed answer not found, please contact support');
      }
      $prompt = $object->get('question');
      //here we process the question tags and return it
      $chunk = $this->modx->newObject('MODX\Revolution\modChunk', array('name' => $prevAnswer->get('id')));
      //$chunk->setCacheable(false);
      //$properties = [ 'prev_ai_content' => $prevAnswer->get('ai_content')];
      $output = $chunk->process([], $prompt);
      $object->set('questions', $prevAnswer->get('ai_content'));
      //$object->set('json_object', $prevAnswer->get('ai_content'));
    }
    $objectArray = $object->toArray();
    return $this->success('', $objectArray);
  }

  public function get()
  {
    $pk = $this->getProperty($this->primaryKeyField);
    if(!strpos($pk, '?')) {
      //If its a simple query
      if (empty($pk)) {
        //return $this->getList();
        return $this->failure('Cant get all the messsages, oepration nor permited', null, 500);
      }
      //no we get the question number where the user is
      $object = $this->modx->getObject($this->classKey, ['id' => $pk]);
      if ($object->get('ai_generated') === 1) {
        //We get the AI answer for the parent's question
        return $this->getAiGenerated($object);

      } else {
        return $this->read($pk);
      }

    } else {
      //Get the id
      $operation = substr($pk,strpos($pk, '?') + 1);
      $pk = substr($pk,0, strpos($pk, '?'));
      if (strcmp(strtolower($operation), 'next') === 0) {
        $object = $this->modx->getObject($this->classKey, ['parent_id' => $pk]);
        if (empty($object)) {
          return $this->failure($this->modx->lexicon('rest.err_obj_nf', [
            'class_key' => $this->classKey,
          ]));
        }
        if ($object->get('ai_generated') === 1) {
          //We get the AI answer for the parent's question
          return $this->getAiGenerated($object);

        } else {
          $objectArray = $object->toArray();

          $afterRead = $this->afterRead($objectArray);
          if ($afterRead !== true && $afterRead !== null) {
            return $this->failure($afterRead === false ? $this->errorMessage : $afterRead);
          }

          return $this->success('', $objectArray);
        }

      } elseif (strcmp(strtolower($operation), 'all') === 0) {
        $conversation =  parent::getConversation($pk);
        return $this->collection($conversation, count($conversation));

      }


    }

  }

}