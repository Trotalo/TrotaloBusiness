<?php

use PHPUnit\Framework\TestCase;
require_once ('../controllers/Answers.php');
include_once '/opt/project/www/html/www/html/core/model/modx/modx.class.php';

class TrotaloAnswersTest extends TestCase {

  public function testGetConversation() {
    $modx = new modX();
    $modx->initialize('web');

    $answers = new TrotaloAnswers($modx, null, null);
    $answers->getConversation(2);
  }

}
