<?php
namespace choadis\slimClient;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

require './vendor/autoload.php';

class App
{
   private $app;
   private const SCRIPT_INCLUDE = '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
   <script
     src="https://code.jquery.com/jquery-3.3.1.min.js"
     integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
     crossorigin="anonymous"></script>
   </head>
   <script src=".public/script.js"></script>';


   public function __construct() {

     $app = new \Slim\App(['settings' => $config]);

     $container = $app->getContainer();

     $container['renderer'] = new PhpRenderer("./templates");

     function makeApiRequest($path){
       $ch = curl_init();

       //Set the URL that you want to GET by using the CURLOPT_URL option.
       curl_setopt($ch, CURLOPT_URL, "http://localhost/Slim/$path");
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

       $response = curl_exec($ch);
       return json_decode($response, true);
     }
     $app->get('/builds', function (Request $request, Response $response, array $args) {
       $responseRecords = makeApiRequest('builds');

       $templateVariables = [
           "title" => "Builds",
           "tableRows" => $tableRows,
           "responseRecords" => $responseRecords
       ];
       return $this->renderer->render($response, "/builds.html", $templateVariables);
     });
     $app->get('/builds/add', function(Request $request, Response $response) {
       $templateVariables = [
         "type" => "new",
         "title" => "Add build"
       ];
       return $this->renderer->render($response, "/buildsForm.html", $templateVariables);

     });

     $app->get('/builds/{id}', function (Request $request, Response $response, array $args) {
         $id = $args['id'];
         $responseRecords = makeApiRequest('builds/'.$id);
         $body = "<h1>Name: ".$responseRecords['name']."</h1>";
         $body = $body . "<h2>Cpu: ".$responseRecords['cpu']."</h2>";
         $body = $body . "<h3>Gpu: ".$responseRecords['gpu']."</h3>";
         $response->getBody()->write($body);
         return $response;
     });
     $app->get('/builds/{id}/edit', function (Request $request, Response $response, array $args) {
         $id = $args['id'];
         $responseRecord = makeApiRequest('builds/'.$id);
         $templateVariables = [
           "type" => "edit",
           "title" => "Edit build",
           "id" => $id,
           "person" => $responseRecord
         ];
         return $this->renderer->render($response, "/buildsEditForm.html", $templateVariables);

     });
     $this->app = $app;
   }

   /**
    * Get an instance of the application.
    *
    * @return \Slim\App
    */
   public function get()
   {
       return $this->app;
   }
 }
