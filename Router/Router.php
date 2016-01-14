<?php

namespace Router;

class Router
{
    private $routeList;
    private $request_uri;
    private $request_method;

    public function __construct()
    {
        $this->routeList = array();
    }

    public function get($_route, $_controller)
    {
        $this->buildRouteObject($_route, $_controller, 'GET');
    }

    public function post($_route, $_controller)
    {
        $this->buildRouteObject($_route, $_controller, 'POST');
    }

    public function put($_route, $_controller)
    {
        $this->buildRouteObject($_route, $_controller, 'PUT');
    }

    public function delete($_route, $_controller)
    {
        $this->buildRouteObject($_route, $_controller, 'DELETE');
    }

    public function getRouteList()
    {
        return $this->routeList;
    }

    public function getRequestUri()
    {
        return $this->request_uri;
    }

    public function getRequestMethod()
    {
        return $this->request_method;
    }

    public function run()
    {
        $this->request_uri = $_SERVER["REQUEST_URI"];
        $this->request_method = $_SERVER["REQUEST_METHOD"];

        return $this->requestController();
    }

    public function requestController()
    {   
        for ($i=0; $i < count($this->routeList); $i++) { 
            $route = $this->routeList[$i];

            if ($route["uri"] === $this->request_uri && $route["request_method"] === $this->request_method) {
                return $this->dispatchRoute($route);
            }

            if ($route["wildcard"] !== false) {
                $findWildCardsRE = "%({wildcard\\})%";  
                $wildcardSub = "((\d|\w)*)"; 
                $wildcardRE = preg_replace($findWildCardsRE, $wildcardSub, $route["wildcard"]);

                $re = "%".$wildcardRE."%"; 

                if (preg_match_all($re, $this->request_uri, $matches)) {
                    return $this->dispatchRoute($route, $matches);
                }
            }
        }

        return false;

    }

    private function dispatchRoute($_route, $_params = null)
    {
        $call = new $_route["controller"]();
        $call->$_route["method"]($_params);
    }

    private function buildRouteObject($_route, $_controller, $_request_method)
    {
        if ($this->routeBeginsWithBackslash($_route)) {

            $formattedController = $this->formatController($_controller);
            $wildcard = $this->checkForWildcards($_route);

            switch ($_request_method) {
                case 'POST':
                    $params = $_POST;
                    break;
                case 'GET':
                    $params = $_GET;
                    break;
                default:
                    $params = null;
            }

            $this->routeList[] = [
                "uri" => $_route,
                "wildcard" => $wildcard,
                "controller" => $formattedController['controller'],
                "method" => $formattedController["method"],
                "params" => $params,
                "request_method" => $_request_method
            ];   
        }
    }

    private function formatController($_controller)
    {
        if (!strpos($_controller, '@')) {
            throw new \Exception("Improperly Formatted Controller");
        }

        $re = "%(\\S*)@(\\S*)%"; 
         
        $controller = preg_replace($re, "$1", $_controller, 1);
        $method = preg_replace($re, "$2", $_controller, 1);

        $formattedController = [
            'controller' => $controller,
            'method' => $method
        ];

        return $formattedController;
    }

    private function routeBeginsWithBackslash($_route)
    {
        if (substr($_route, 0, 1) !== '/') {
            throw new \Exception('All routes must begin with "/"');
        }
        return true;
    }

    private function checkForWildcards($_route)
    {
        $re = "/(:(\\w|\\d)*)/"; 
        $substitute = "{wildcard}"; 
         
        if (preg_match($re, $_route)) {
            return preg_replace($re, $substitute, $_route);
        }
        
        return false;
    }
}