<?php
namespace choadis\Slim;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
// use \Tuupola\Middleware\HttpBasicAuthentication as basicAuth;
require './vendor/autoload.php';

class App
{

   private $app;
   public function __construct($db) {
     
     $config['db']['host']   = 'localhost';
     $config['db']['user']   = 'root';
     $config['db']['pass']   = 'root';
     $config['db']['dbname'] = 'apidb';

     $app = new \Slim\App(['settings' => $config]);

     $container = $app->getContainer();
     $container['db'] = $db;

     $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
         $name = $args['name'];
         $response->getBody()->write("Hello, $name");

         return $response;
     });
     $app->get('/builds', function (Request $request, Response $response) {
         $builds = $this->db->query('SELECT * from builds')->fetchAll();
         $jsonResponse = $response->withJson($builds);
         return $jsonResponse;
     });
     $app->get('/builds/{id}', function (Request $request, Response $response, array $args) {
         $id = $args['id'];

         $build = $this->db->query('SELECT * from builds where id='.$id)->fetch();

         if($build){
           $response =  $response->withJson($build);
         } else {
           $errorData = array('status' => 404, 'message' => 'not found');
           $response = $response->withJson($errorData, 404);
         }
         return $response;

     });
     $app->post('/builds', function (Request $request, Response $response) {

         // check that build exists
         // $build = $this->db->query('SELECT * from builds where name='.$id)->fetch();
         // if(!$build){
         //   $errorData = array('status' => 404, 'message' => 'not found');
         //   $response = $response->withJson($errorData, 404);
         //   return $response;
         // }

         // build query string
         $createString = "INSERT INTO builds ";
         $fields = $request->getParsedBody();
         $keysArray = array_keys($fields);
         $last_key = end($keysArray);
         $values = '(';
         $fieldNames = '(';
         foreach($fields as $field => $value) {
           $values = $values . "'"."$value"."'";
           $fieldNames = $fieldNames . "$field";
           if ($field != $last_key) {
             // conditionally add a comma to avoid sql syntax problems
             $values = $values . ", ";
             $fieldNames = $fieldNames . ", ";
           }
         }
         $values = $values . ')';
         $fieldNames = $fieldNames . ') VALUES ';
         $createString = $createString . $fieldNames . $values . ";";
         // execute query
         try {
           $this->db->exec($createString);
         } catch (\PDOException $e) {
           var_dump($e);
           $errorData = array('status' => 400, 'message' => 'Invalid data provided to create build');
           return $response->withJson($errorData, 400);
         }
         // return updated record
         $build = $this->db->query('SELECT * from builds ORDER BY id desc LIMIT 1')->fetch();
         $jsonResponse = $response->withJson($build);

         return $jsonResponse;
     });
     $app->put('/builds/{id}', function (Request $request, Response $response, array $args) {
         $id = $args['id'];
         // $this->logger->addInfo("PUT /builds/".$id);

         // check that build exists
         $build = $this->db->query('SELECT * from builds where id='.$id)->fetch();
         if(!$build){
           $errorData = array('status' => 404, 'message' => 'not found');
           $response = $response->withJson($errorData, 404);
           return $response;
         }

         // build query string
         $updateString = "UPDATE builds SET ";
         $fields = $request->getParsedBody();
         $keysArray = array_keys($fields);
         $last_key = end($keysArray);
         foreach($fields as $field => $value) {
           $updateString = $updateString . "$field = '$value'";
           if ($field != $last_key) {
             // conditionally add a comma to avoid sql syntax problems
             $updateString = $updateString . ", ";
           }
         }
         $updateString = $updateString . " WHERE id = $id;";

         // execute query
         try {
           $this->db->exec($updateString);
         } catch (\PDOException $e) {
           $errorData = array('status' => 400, 'message' => 'Invalid data provided to update');
           return $response->withJson($errorData, 400);
         }
         // return updated record
         $build = $this->db->query('SELECT * from builds where id='.$id)->fetch();
         $jsonResponse = $response->withJson($build);

         return $jsonResponse;
     });

     $app->delete('/builds/{id}', function (Request $request, Response $response, array $args) {
       $id = $args['id'];

       $deleteSuccessful = $this->db->exec('DELETE FROM builds where id='.$id);
       if($deleteSuccessful){
         $response = $response->withStatus(200);
       } else {
         $errorData = array('status' => 404, 'message' => 'not found');
         $response = $response->withJson($errorData, 404);
       }
       return $response;
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
