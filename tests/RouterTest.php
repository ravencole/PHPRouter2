<?php

namespace phpUnitTutorial\Test;
use Router\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function SERVER_SUPER_GLOBAL_MOCK($_serverSettings)
    {
        foreach ($_serverSettings as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }

    public function GET_SUPER_GLOBAL_MOCK($_get)
    {
        $_GET = $_get;
    }

    public function POST_SUPER_GLOBAL_MOCK($_post)
    {
        $_POST = $_post;
    }

    public function test_Router_Can_Initialize()
    {
        $router = new Router();
    }

    public function test_getRouteList_Can_Return_Routes()
    {
        $router = new Router();
        $this->assertInternalType('array', $router->getRouteList());
    }

    public function test_get_can_add_route_to_routelist()
    {
        $router = new Router();
        $router->get('/', 'Routes@index');
        $this->assertCount(1, $router->getRouteList());
    }

    public function test_get_can_add_multiple_routes()
    {
        $router = new Router();
        $router->get('/', 'Routes@index');
        $router->get('/contact', 'Routes@index');
        $router->get('/about', 'Routes@index');
        $this->assertCount(3, $router->getRouteList());
    }

    public function test_get_throws_exception_when_route_does_not_start_with_backslash()
    {
        $this->setExpectedException('Exception', 'All routes must begin with "/"');
        $router = new Router();
        $router->get('home', 'Routes@index');
    }

    public function test_parseController_can_parse_a_correctly_formatted_controller()
    {
        $route = new Router();
        $route->get('/', 'Routes\Home@index');
        $routeList = $route->getRouteList();

        $this->assertEquals($routeList[0]['controller'], 'Routes\Home');
        $this->assertEquals($routeList[0]['method'], 'index');
    }

    public function test_parseController_can_throw_exception_for_incorrectly_formatted_controller()
    {
        $this->setExpectedException('Exception');
        $route = new Router();
        $route->get('/', 'Routes\Home:index');
    }

    public function test_parseRoute_can_catch_wildcards_at_the_end_of_a_route()
    {
        $router = new Router();
        $router->get('/photos/:id', 'Route\Photos@show');
        $routeList = $router->getRouteList();

        $this->assertEquals($routeList[0]['wildcard'], '/photos/{wildcard}');
    }

    public function test_parseRoute_can_return_false_for_routes_without_wildcards()
    {
        $router = new Router();
        $router->get('/photos', 'Route\Photos@show');
        $routeList = $router->getRouteList();

        $this->assertFalse($routeList[0]['wildcard']);
    }

    public function test_parseRoute_can_catch_multiple_wildcards()
    {
        $router = new Router();
        $router->get('/photos/:id/comments/:id', 'Route\Photos@show');
        $routeList = $router->getRouteList();

        $this->assertEquals($routeList[0]['wildcard'], '/photos/{wildcard}/comments/{wildcard}');
    }

    public function test_get_can_assign_GET_superglobal_to_params_key()
    {
        $this->GET_SUPER_GLOBAL_MOCK(['1','2','3']);

        $router = new Router();
        $router->get('/photos', 'Router\Photos@show');
        $routeList = $router->getRouteList();

        $this->assertEquals($routeList[0]['params'], ['1','2','3']);
    }

    public function test_get_can_assign_REQUEST_METHOD_to_GET()
    {
        $router = new Router();
        $router->get('/photos', 'Router\Photos@show');
        $routeList = $router->getRouteList();

        $this->assertEquals($routeList[0]['request_method'], 'GET');
    }

    public function test_post_can_add_a_correctly_formatted_route_to_routeList()
    {
        $this->POST_SUPER_GLOBAL_MOCK(['4','5','6']);

        $correctPostObject = [
            "uri" => '/photos',
            "wildcard" => false,
            "controller" => 'Router\Photos',
            "method" => 'show',
            "params" => ['4','5','6'],
            "request_method" => 'POST'
        ];

        $router = new Router();
        $router->post('/photos', 'Router\Photos@show');
        $routeList = $router->getRouteList();

        foreach ($routeList[0] as $key => $value) {
            $this->assertEquals($correctPostObject[$key], $value);
        }
    }

    public function test_put_can_add_a_correctly_formatted_route_to_routeList()
    {
        $correctPostObject = [
            "uri" => '/photos',
            "wildcard" => false,
            "controller" => 'Router\Photos',
            "method" => 'edit',
            "params" => null,
            "request_method" => 'PUT'
        ];

        $router = new Router();
        $router->put('/photos', 'Router\Photos@edit');
        $routeList = $router->getRouteList();

        foreach ($routeList[0] as $key => $value) {
            $this->assertEquals($correctPostObject[$key], $value);
        }
    }

    public function test_delete_can_add_a_correctly_formatted_route_to_routeList()
    {
        $correctPostObject = [
            "uri" => '/photos',
            "wildcard" => false,
            "controller" => 'Router\Photos',
            "method" => 'destroy',
            "params" => null,
            "request_method" => 'DELETE'
        ];

        $router = new Router();
        $router->delete('/photos', 'Router\Photos@destroy');
        $routeList = $router->getRouteList();

        foreach ($routeList[0] as $key => $value) {
            $this->assertEquals($correctPostObject[$key], $value);
        }
    }

    public function test_class_can_correctly_format_multiple_routes()
    {
        $this->GET_SUPER_GLOBAL_MOCK(["uno", "deux"]);
        $this->POST_SUPER_GLOBAL_MOCK(["drei", "cztery"]);

        $correctRouteList = [
            [
                "uri" => '/photos',
                "wildcard" => false,
                "controller" => 'Route\Photo',
                "method" => 'index',
                "params" => ["uno", "deux"],
                "request_method" => 'GET'
            ],
            [
                "uri" => '/photos/:id',
                "wildcard" => '/photos/{wildcard}',
                "controller" => 'Route\Photo',
                "method" => 'show',
                "params" => ["uno", "deux"],
                "request_method" => 'GET'
            ],
            [
                "uri" => '/photos',
                "wildcard" => false,
                "controller" => 'Route\Photo',
                "method" => 'create',
                "params" => ["drei","cztery"],
                "request_method" => 'POST'
            ],
            [
                "uri" => '/photos/:id',
                "wildcard" => '/photos/{wildcard}',
                "controller" => 'Route\Photo',
                "method" => 'edit',
                "params" => null,
                "request_method" => 'PUT'
            ],
            [
                "uri" => '/photos/:id',
                "wildcard" => '/photos/{wildcard}',
                "controller" => 'Route\Photo',
                "method" => 'destroy',
                "params" => null,
                "request_method" => 'DELETE'
            ]
        ];

        $router = new Router();
        $router->get('/photos', 'Route\Photo@index');
        $router->get('/photos/:id', 'Route\Photo@show');
        $router->post('/photos', 'Route\Photo@create');
        $router->put('/photos/:id', 'Route\Photo@edit');
        $router->delete('/photos/:id', 'Route\Photo@destroy');

        $routeList = $router->getRouteList();

        for ($i=0; $i < count($routeList); $i++) { 
            foreach ($routeList[$i] as $key => $value) {
                $this->assertEquals($correctRouteList[$i][$key], $value);
            }
        }
    }

    public function test_run_formats_global_variables()
    {
        $this->SERVER_SUPER_GLOBAL_MOCK([
            "REQUEST_METHOD" => 'GET',
            "REQUEST_URI" => '/photos'
        ]);

        $router = new Router();
        $router->run();
        $uri = $router->getRequestUri();
        $method = $router->getRequestMethod();

        $this->assertEquals($uri, '/photos');
        $this->assertEquals($method, 'GET');
    }

    public function test_requestController_can_dispatch_generic_route()
    {
        $this->SERVER_SUPER_GLOBAL_MOCK([
            "REQUEST_METHOD" => 'GET',
            "REQUEST_URI" => '/photos'
        ]);

        $router = new Router();
        $router->get('/photos', 'Router\RouteMock@index');

        $this->assertNull($router->run());
    }

    public function test_requestController_can_dispatch_route_ending_with_a_wildcard()
    {
        $this->SERVER_SUPER_GLOBAL_MOCK([
            "REQUEST_METHOD" => 'GET',
            "REQUEST_URI" => '/photos/comment/123'
        ]); 

        $router = new Router();
        $router->get('/photos/comment/:id', 'Router\RouteMock@index');

        $this->assertNull($router->run());
    }

    public function test_requestController_can_dispatch_route_with_a_center_wildcard()
    {
       $this->SERVER_SUPER_GLOBAL_MOCK([
            "REQUEST_METHOD" => 'GET',
            "REQUEST_URI" => '/photos/ee343/comment'
        ]); 

        $router = new Router();
        $router->get('/photos/:id/comment', 'Router\RouteMock@index');

        $this->assertNull($router->run()); 
    }

    public function test_requestController_can_dispatch_route_with_multiple_wildcards()
    {
        $this->SERVER_SUPER_GLOBAL_MOCK([
            "REQUEST_METHOD" => 'GET',
            "REQUEST_URI" => '/photos/ee343/comment/11235813213455'
        ]); 

        $router = new Router();
        $router->get('/photos/:id/comment/:id', 'Router\RouteMock@index');

        $this->assertNull($router->run());
    }

}



















