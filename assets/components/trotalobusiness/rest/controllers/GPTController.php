<?php

use MODX\Revolution\Rest\modRestController;
use MODX\Revolution\Rest\modRestServiceRequest;
use Orhanerday\OpenAi\OpenAi;

class GPTController extends modRestController{

  /** @var string $classPrefix */
  public $open_ai_key;
  private $open_ai;

  public function __construct(modX $modx,modRestServiceRequest $request,array $config = array()){
    parent::__construct($modx, $request, $config);
    $this->open_ai_key = getenv('OPENAI_API_KEY');
    $this->open_ai = new OpenAi($this->open_ai_key);
  }


  public function getConversation($questionId, $userId): array {
    $query = $this->modx->query("
      WITH RECURSIVE category_path (id, question, prompt, parent_id) AS
      (
        SELECT id, question, prompt, parent_id
          FROM modx_trotalo_questions
          WHERE id = $questionId
        UNION ALL
        SELECT c.id, c.question, c.prompt, c.parent_id
          FROM category_path AS cp JOIN modx_trotalo_questions AS c
            ON cp.parent_id = c.id
      )
      SELECT cat.*, answers.content, answers.ai_content 
        FROM category_path cat
        left join modx_trotalo_answers answers ON
          cat.id = answers.question_id
          and answers.user_id = $userId
        order by id;
      ");
    if (is_null($query)) {
      //throw new Exception("NO global componments");
      //TODO this was changed du the fact that there cannot be global and no need of error
      return '';
    }

        $list = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
      $list[] = $row;
    }
    $messages = [];
    foreach ($list as $key=>$answer){
      //we store the content and the ai_content on separate variables
      $message = [];
      $content = $answer['content'];
      $ai_content = $answer['ai_content'];

      $prev_ai_content = '';
      if ($key === 0) {
        $message['role'] = 'system';
      } else {
        $message['role'] = 'user';
        $prev_ai_content = $list[$key-1]['ai_content'];

      }
      $prompt = $answer['prompt'];
      $chunk = $this->modx->newObject('MODX\Revolution\modChunk', array('name' => $key));
      $chunk->setCacheable(false);
      $properties = [ 'curr_content' => $content, 'prev_ai_content' => $prev_ai_content , 'prev_ai_content' =>$prev_ai_content ];
      $output = $chunk->process($properties, $prompt);
      $message['content'] = $output;
      //finally we che
      $messages[] = $message;
      $localAiContent = $list[$key]['ai_content'];
      if ($localAiContent !== null && trim($localAiContent) !== '') {
        //there's ai content
        $message = [];
        $message['role'] = 'assistant';
        $message['content'] = $localAiContent;
        $messages[] = $message;
      }


    }
    //now with the list completed, we build the message
    return $messages;
  }

  public function chat($messages) {
    $chat = $this->open_ai->chat([
      'model' => 'gpt-3.5-turbo',
      'messages' => $messages,
      'temperature' => 0.5
    ]);
    return json_decode($chat, true);

  }

}