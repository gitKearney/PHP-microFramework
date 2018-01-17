<?php

namespace Main\Routers;

/**
 * Proudly kanged from: http://upshots.org/php/php-seriously-simple-router
 */ 
class RegexRouter {

    private $routes = array();
    
    /**
     * @desc 
     * @param string $pattern
     * @param callable $callback
     * @return null
     */
    public function route($pattern, $callback) 
    {
        $this->routes[$pattern] = $callback;
    }
    
    /**
     * @param string $uri
     * @param $appContainer
     * @return array
     */
    public function execute($uri, $appContainer)
    {
        $found = false;
        $arg['di_container'] = $appContainer;

        foreach ($this->routes as $pattern => $callback) {
            $params = [];

            if (preg_match($pattern, $uri, $params) === 1) {
                return call_user_func_array($callback, array_values($arg));
            }
        }

        header("HTTP/1.0 404 Not Found");
    }

}
