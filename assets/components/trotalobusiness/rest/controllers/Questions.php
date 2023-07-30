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

  private function getAiGenerated($object, $userId) {
    if ($object->get('parent_id') === 0) {
      $object->set('questions', $object->get('options'));
    } else {
      $prevAnswer = $this->modx->getObject($this->answersPrefix, ['question_id' => $object->get('parent_id'),
                                                                  'user_id' => $userId]);
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

  public function post()
  {
    $pk = $this->getProperty($this->primaryKeyField);
    $userId = $this->getProperty('userId');
    if(!strpos($pk, '?')) {
      //If its a simple query
      if (empty($pk)) {
        //return $this->getList();
        return $this->failure('Cant get all the messsages, oepration nor permited', null, 500);
      }
      //first we get the question number where the user is
      $query = $this->modx->query("
        SELECT *
        FROM modx_trotalo_answers AS answers
        JOIN modx_trotalo_questions AS questions ON answers.question_id = questions.id
        WHERE answers.user_id = $userId
        AND questions.question_type <> 4
        ORDER BY answers.timestamp DESC
        LIMIT 1;      
      ");
      if (is_null($query)) {
        //throw new Exception("NO global componments");
        //TODO this was changed du the fact that there cannot be global and no need of error
        return $this->failure('Cant find user! please login again', null, 550);
      }

      $list = [];
      while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $list[] = $row;
      }
      if(!empty($list)) {
        $last_question_id = $list[0]["question_id"];
        //and we need to get the next question
        $last_question = $this->modx->getObject($this->classKey, ['parent_id'=> $last_question_id]);
        if (is_null($last_question)) {
          return $this->failure('MIssconfiguration, no next question found', null, 550);
        }
        $pk = $last_question->get('id');

      }

      //we
      $object = $this->modx->getObject($this->classKey, ['id' => $pk]);
      if ($object->get('ai_generated') === 1) {
        //We get the AI answer for the parent's question
        return $this->getAiGenerated($object, $userId);

      } else {
        return $this->read($pk);
      }

    } else {
      //Get the id
      $operation = substr($pk,strpos($pk, '?') + 1);
      //TODO we ignore the provided PK and look for the
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
          return $this->getAiGenerated($object, $userId);

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
      } elseif (strcmp(strtolower($operation), 'curr_question') === 0) {
        $conversation =  parent::getConversation($pk);
        return $this->collection($conversation, count($conversation));
      }


    }

  }

}