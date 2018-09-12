<?php
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Uri;
use Slim\Http\RequestBody;
require './vendor/autoload.php';

// empty class definitions for phpunit to mock.
class mockQuery {
  public function fetchAll(){}
    public function fetch(){}
    };
    class mockDb {
      public function query(){}
        public function exec(){}
        }

        class BuildsTest extends TestCase
        {
          protected $app;
          protected $db;

          // execute setup code before each test is run
          public function setUp()
          {
            $this->db = $this->createMock('mockDb');
            $this->app = (new choadis\Slim\App($this->db))->get();
          }

          public function testDeleteBuild() {
            $query = $this->createMock('mockQuery');
            $this->db->method('exec')->willReturn(true);
            $env = Environment::mock([
              'REQUEST_METHOD' => 'DELETE',
              'REQUEST_URI'    => '/builds/1',
            ]);
            $req = Request::createFromEnvironment($env);
            $this->app->getContainer()['request'] = $req;

            // actually run the request through the app.
            $response = $this->app->run(true);
            // assert expected status code and body
            $this->assertSame(200, $response->getStatusCode());
          }

          public function testGetBuild() {

            // test successful request
            $resultString = '{"id":"1","name":"1080p high","cpu":"Ryzen 5 2600","gpu":"gtx 1060 6g"}';
            $query = $this->createMock('mockQuery');
            $query->method('fetch')->willReturn(json_decode($resultString, true));
            $this->db->method('query')->willReturn($query);
            $env = Environment::mock([
              'REQUEST_METHOD' => 'GET',
              'REQUEST_URI'    => '/builds/1',
            ]);
            $req = Request::createFromEnvironment($env);
            $this->app->getContainer()['request'] = $req;

            // actually run the request through the app.
            $response = $this->app->run(true);
            // assert expected status code and body
            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame($resultString, (string)$response->getBody());
          }
          public function testGetBuildFailed() {
            $query = $this->createMock('mockQuery');
            $query->method('fetch')->willReturn(false);
            $this->db->method('query')->willReturn($query);
            $env = Environment::mock([
              'REQUEST_METHOD' => 'GET',
              'REQUEST_URI'    => '/builds/1',
            ]);
            $req = Request::createFromEnvironment($env);
            $this->app->getContainer()['request'] = $req;

            // actually run the request through the app.
            $response = $this->app->run(true);
            // assert expected status code and body
            $this->assertSame(404, $response->getStatusCode());
            $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());
          }

          public function testUpdateBuild() {
            // expected result string
            $resultString = '{"id":"1","name":"Budget Build","cpu":"Ryzen 5 2400G","gpu":"none"}';

            // mock the query class & fetchAll functions
            $query = $this->createMock('mockQuery');
            $query->method('fetch')
            ->willReturn(json_decode($resultString, true)
          );
          $this->db->method('query')
          ->willReturn($query);
          $this->db->method('exec')
          ->willReturn(true);

          // mock the request environment.  (part of slim)
          $env = Environment::mock([
            'REQUEST_METHOD' => 'PUT',
            'REQUEST_URI'    => '/builds/1',
          ]);
          $req = Request::createFromEnvironment($env);
          $requestBody = ["name" =>  "Budget Build", "cpu" => "Ryzen 5 2400G", "gpu" => "none"];
          $req =  $req->withParsedBody($requestBody);
          $this->app->getContainer()['request'] = $req;

          // actually run the request through the app.
          $response = $this->app->run(true);
          // assert expected status code and body
          $this->assertSame(200, $response->getStatusCode());
          $this->assertSame($resultString, (string)$response->getBody());
        }

        // test Build update failed due to invalid fields
        public function testUpdateBuildFailed() {
          // expected result string
          $resultString = '{"id":"1","name":"Budget Build","cpu":"Ryzen 5 2400G","gpu":"none"}';

          // mock the query class & fetchAll functions
          $query = $this->createMock('mockQuery');
          $query->method('fetch')
          ->willReturn(json_decode($resultString, true)
        );
        $this->db->method('query')
        ->willReturn($query);
        $this->db->method('exec')
        ->will($this->throwException(new PDOException()));

        // mock the request environment.  (part of slim)
        $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/builds/1',
        ]);
        $req = Request::createFromEnvironment($env);
        $requestBody = ["name" =>  "Budget Build", "cpu" => "Ryzen 5 2400G", "gpu" => "none"];
        $req =  $req->withParsedBody($requestBody);
        $this->app->getContainer()['request'] = $req;

        // actually run the request through the app.
        $response = $this->app->run(true);
        // assert expected status code and body
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('{"status":400,"message":"Invalid data provided to update"}', (string)$response->getBody());
      }

      // test Build update failed due to persn not found
      public function testUpdateBuildNotFound() {
        // expected result string
        $resultString = '{"id":"1","name":"Budget Build","cpu":"Ryzen 5 2400G","gpu":"none"}';

        // mock the query class & fetchAll functions
        $query = $this->createMock('mockQuery');
        $query->method('fetch')->willReturn(false);
        $this->db->method('query')
        ->willReturn($query);
        $this->db->method('exec')
        ->will($this->throwException(new PDOException()));

        // mock the request environment.  (part of slim)
        $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/builds/1',
        ]);
        $req = Request::createFromEnvironment($env);
        $requestBody = ["name" =>  "Budget Build", "cpu" => "Ryzen 5 2400G", "gpu" => "none"];
        $req =  $req->withParsedBody($requestBody);
        $this->app->getContainer()['request'] = $req;

        // actually run the request through the app.
        $response = $this->app->run(true);
        // assert expected status code and body
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());

      }
        }
