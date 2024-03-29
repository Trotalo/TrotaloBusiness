<?php


use MODX\Revolution\Rest\modRestController;
use MODX\Revolution\Rest\modRestServiceRequest;

require_once 'GPTController.php';

class TrotaloAnswers extends GPTController {

  /** @var string $classPrefix */
  public $vloxPrefix;

  /** @var string $classPrefix */
  public $modxPrefix;

  /** @var string $classKey The xPDO class to use */
  public $classKey = 'trotalobusiness\Model\TrAnswers';

  /** @var string $classAlias The alias of the class when used in the getList method */
  public $classAlias  = 'TrAnswers';

  /** @var string $defaultSortField The default field to sort by in the getList method */
  public $defaultSortField = 'id';
  /** @var string $defaultSortDirection The default direction to sort in the getList method */
  public $defaultSortDirection = 'ASC';

  public $primaryKeyField = 'id';

  public function __construct(modX $modx,modRestServiceRequest $request,array $config = array()){
    parent::__construct($modx, $request, $config);
    $isMODX3 = $modx->getVersionData()['version'] >= 3;
    $this->vloxPrefix = $isMODX3 ? 'trotalobusiness\Model\\' : '';
    $this->modxPrefix = $isMODX3 ? 'MODX\Revolution\\' : '';
  }

  public function post()
  {
    $properties = $this->getProperties();
    $questionId = $properties['question_id'];
    $userId = $properties['user_id'];
    //we get the question
    $questionObject = $this->modx->getObject('trotalobusiness\Model\TrQuestions', ['id' => $questionId]);
    //we check if we need to call openAI api
    if ($questionObject->get("api_call") === 1) {
      //call openAI api and store content
      $properties = $this->getProperties();
      $this->object = $this->modx->newObject($this->classKey);
      $this->object->fromArray($properties);
      $this->object->save();
      //now we retrieve the conversation!
      $conversation = parent::getConversation($questionId, $userId);
      //after getting the conversation up to this point, we call openAI API
      $AIResponse = parent::chat($conversation, $questionObject->get('gpt_function'));
      if (array_key_exists('error', $AIResponse)){
        //removes the answer if the connetion failed!
        $this->object->remove();
        throw new Exception('Errors accessing openAI API: ' . $AIResponse['error']['message'], 500);
      }
      $aiMsg = strlen($questionObject->get('gpt_function')) > 0
                          ? $AIResponse['choices'][0]['message']['function_call']['arguments']
                          : $AIResponse["choices"][0]["message"]["content"];


      $this->object->set('ai_content', $aiMsg);



      $answerId = $this->object->save();
      //then we store the conversatin
      $convDB = $this->modx->newObject('trotalobusiness\Model\TrConversations');
      $convDB->set('answer_id', $answerId);
      $convDB->set('conversation', json_encode($conversation));
      $convDB->set('user_id', $userId);
      $convDB->save();

      $objectArray = $this->object->toArray();
      $this->afterPost($objectArray);

      return $this->success('', $objectArray);

    } else {
      parent::post();
    }
  }

  public function put()
  {
    $userId = $this->getProperty('userId');
    if(is_null($userId) || empty($userId)) {
      return $this->failure('MIssing parameters!', null, 500);
    }
    $this->modx->removeCollection($this->classKey, array("`user_id` = {$userId}"));
    $this->success('All answers were deleted');
  }

  public function get()
  {
    $pk = $this->getProperty($this->primaryKeyField);
    if (empty($pk)) {
      return $this->getList();
    }

    return $this->read($pk);
  }

}