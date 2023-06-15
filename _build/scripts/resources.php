<?php
use MODX\Revolution\modAccessPermission;
use MODX\Revolution\modX;
use xPDO\Transport\xPDOTransport;

if ($options[xPDOTransport::PACKAGE_ACTION] === xPDOTransport::ACTION_UNINSTALL) {
  return true;
}

$modx =& $transport->xpdo;
/*$modx = new modX();
$modx->initialize('web');*/

/**
 * @param $parent id of the parent resource
 * @param $name the name of the resource
 * @param $contents the resource contents
 * @param $resGroup the group that the respurce needs permissions
 * @param $template the template that needs to be applied
 * @param $modx
 */
function createResource($parent, $name, $contents, $resGroup, $modx) {
  //first we check if the resource exists
  $resource = $modx->getObject('modResource', ['pagetitle' => $name]);

  $resource_data = array(
    'pagetitle' => $name, // The title of the new resource
    'parent' => $parent, // Assign the new resource to the parent
    'uri' => strtolower($name) . '.html',
    'template' => 0, // The ID of the template to use
    'content' => $contents,
    'published' => 1
  );

  if (empty($resource)) {
    $resource = $modx->newObject('modResource');
  }
  $resource->fromArray($resource_data);
  $resource->save();
  if (!is_null($resGroup)) {
    $resource->joinGroup($resGroup);
  }
  return $resource->get('id');
}

$content = <<<HTML
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <link rel="icon" href="/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach</title>
    <script type="module" crossorigin src="/assets/components/trotalobusiness/7/js/index-a1667597.js"></script>
    <link rel="stylesheet" href="/assets/components/trotalobusiness/7/css/index-d60b0189.css">
  </head>
  <body>
    <div id="q-app" style="min-height: 60rem;"></div>
    
  </body>
</html>
HTML;
$loginId = createResource(0, 'Home', $content, null, $modx);

//Now we need to add the question to the database
$json = <<<JSON
[
	{
		"id": 1,
		"parent_id": 0,
		"question": "Hola, ¿En qué quieres trabajar hoy?",
		"prompt": "Vas a actuar como un experto en desarrollo de negocios, me ayudarás a [[+curr_content]], para tu analisis usaras el modelo LEAN/Agile, y te enfocaras en proveer información clara, con objetivos que cumplan con el modelo SMART, tambien entiendes que es el formato JSON, y generaras las respuestas a las preguntas en este formato, tu objetivo final es generar elementos sobre los cuales pueda tomar accion, como planes de contenido, estrategias de mercadeo, o planes de produccion, toma esto como ideas, pero no te limites en las opciones y planes que sugieras",
		"options": "['crear un nuevo producto/servicio', 'Mejorar un negocio que ya esta funcionando']",
		"ai_generated": 1,
		"api_call": 0,
		"question_type": 2
	},
	{
		"id": 2,
		"parent_id": 1,
		"question": "Cuéntame acerca de tu negocio o idea, es importante que describas quiénes son tus clientes, qué les vendes, y cuál crees que es tu valor agregado",
		"prompt": "Mi negocio es: [[+curr_content]]. Analiza la descripción que te di de mi negocio, y hazme 3 preguntas que me ayuden a mejorar esta definicion, considera elementos como el publico objetivo o la oferta de valor sean claras y sean monetizables, pero no te limites solo a estos 3 elementos y ayudame a indagar elementos claves dentro de mi reto, cada pregunta no puede exceder los 200 caracteres, generalas en formato json, usando un arreglo simple donde cada pregunta va delimitada por \" y solo se usa una coma entre preguntas sin ningun caracter adicional, un ejemplo de la salida es [\"pregunta uno\", \"pregunta b\", \"etc\"]",
		"options": "",
		"ai_generated": 0,
		"api_call": 1,
		"question_type": 1
	},
	{
		"id": 3,
		"parent_id": 2,
		"question": "Tu idea suena muy interesante, por favor revisa las siguientes sugerencias y mejoremos un poco la definición de tu negocio:",
		"prompt": "La respuesta a tus preguntas separadas por - : [[+curr_content]]",
		"options": "",
		"ai_generated": 1,
		"api_call": 0,
		"question_type": 1
	},
	{
		"id": 4,
		"parent_id": 3,
		"question": "Entrando más en materia, cuéntame, ¿Cuál es el mayor reto que enfrentas ahora, por ejemplo, aumentar tus ventas, crear contenido para redes, o crear un plan comercial?",
		"prompt": "Basado en todos los mensajes anteriores, vamos a trabajar en este reto: [[+curr_content]]. Necesito que me des 3 opciones de como podria atacar este reto, uno de los objetivos primarios es estructurar un plan claro que permita vencer el reto. tu respuesta no debe exeder los 300 caracteres, generalas en formato json, usando un arreglo simple donde cada opcion va delimitada por ` y solo se usa una coma entre opciones sin ningun caracter adicional, un ejemplo de la salida es [‘opcion a’, ‘opcion b’, ‘etc’], asegurate que cada opcion sera un objeto independiente en el arreglo",
		"options": "",
		"ai_generated": 0,
		"api_call": 1,
		"question_type": 1
	},
	{
		"id": 6,
		"parent_id": 4,
		"question": "Por favor revisa las estrategias sugeridas, y escoje una para que veamos una plan de acción",
		"prompt": "De las estrategias que me sugeriste vamos a implementar [[+curr_content]]. Ahora necesito que crees un plan semanal para un mes de trabajo, cada plan semanal debe tener un titulo y un párrafo corto donde se describa un OKR para la semana, los planes pueden ser campañas de redes sociales, cambio de imagen, marketing de redes sociales, diseño de producto entre otros. Dentro de cada plan, debes generar un arreglo con cinco actividades a realizar durante cada una de las semanas, debes asegurarte que las actividades no sean repetitivas, y generen valor ofreciendo acciones concretas que ayuden a cumplir las metas.\nPara generar tu respuesta, ten en cuenta todos los elementos de esta conversacion, y busca que tu plan este enfocado en tomar accion, y medir resultados, por ejemplo si se esta diseñando un producto, se debe enfocar la respuesta en llegar a hacer pruebas con el usuario, o si se esta en un plan de marketing, se deben dar pasos concretos en aras de generar ventas. En la respuesta no debes poner saltos de linea, u otros carcateres adicionales, la estructura debe ser como la del json mencionado al inicio de este mensaje, en una sola cadena de caracteres continua que sera leida por javascript\n  La siguiente plantilla JSON es la que debes usar para generar cada uno de los planes de la respuesta\n  [{     nombre: ', //NOmbre de la meta de la semana     \n  \tindicadores: ', //Elementos importantes como nuevas ventas, o pruebas con usuarios, o cualquier elemento significativo     \n  \tactividades: [] // Arreglo de 5 elementos de actividades que la persona deberia realizar diariamente, como hacer posts de redes entre otros\n   },  ] \n Este es un ejemplo de como debes generar la informacion, se explica cada campo, aca solo se muestra un objeto en el arreglo pero debes generar 4 planes completos, uno por cada semana del mes\n",
		"options": "",
		"ai_generated": 1,
		"api_call": 1,
		"question_type": 2
	},
	{
		"id": 7,
		"parent_id": 6,
		"question": "¡Éste es el plan que creemos que te puede dar grandes resultandos en el siguiente mes!",
		"prompt": "Basados en la siguiente actividad: [[+curr_content]], y teniendo en cuenta toda la información que tienes de esta conversacion, quiero que generes un parrafo de maximo 300 caracteres donde me expliques de forma mas amplia la actividad, y como llevarla a cabo, debes evitar ser repetitivo con las frases de tal forma que me logres dar la mayor cantidad de informacion de valor, Luego del parrafo, quiero que me des un ejemplo concreto en forma de lista de 5 elementos, de como se veria la actividad una vez realizada, por ejemplo si estamos hablando de hacer un estudio de mercado, deberia listar que resultados deberiamos obtener, y si por el contrario la actividad tiene alguna relacion con generacion de contenido, dar ejemplos de contenido basados en toda esta conversacion, en esta pregunta debes ser muy creativo y dar informacion interesante que me sirga de guia para hacer la actividad de la mejor forma posible",
		"options": "",
		"ai_generated": 1,
		"api_call": 1,
		"question_type": 4
	},
	{
		"id": 8,
		"parent_id": 7,
		"question": "Y acá, el detalle de una de las tareas que deberías realizar:",
		"prompt": "",
		"options": "",
		"ai_generated": 1,
		"api_call": 0,
		"question_type": 5
	}
]
JSON;

$sanitizedJson = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $json);

// Decode the JSON file
$questions = json_decode($sanitizedJson,true);

foreach ($questions as $question) {
  $newQuestion = $modx->newObject('trotalobusiness\Model\TrQuestions');
  //for each question we set the values from the JSON
  $newQuestion->set('id', $question['id']);
  $newQuestion->set('parent_id', $question['parent_id']);
  $newQuestion->set('question', $question['question']);
  $newQuestion->set('prompt', $question['prompt']);
  $newQuestion->set('options', $question['options']);
  $newQuestion->set('ai_generated', $question['ai_generated']);
  $newQuestion->set('api_call', $question['api_call']);
  $newQuestion->set('question_type', $question['question_type']);

  $newQuestion->save();
}

