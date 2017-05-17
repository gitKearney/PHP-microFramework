<?php

namespace Routers;

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
     *
     */
    public function execute($uri) 
    {
        foreach ($this->routes as $pattern => $callback) {
            if (preg_match($pattern, $uri, $params) === 1) {
                array_shift($params);
                return call_user_func_array($callback, array_values($params));
            }
        }
    }

}
