<?php
namespace App\Router;
/**
 * Proudly kanged from: http://upshots.org/php/php-seriously-simple-router
 */
class RegexRouter {
    private array $routes = [];

    public function route(string $pattern, callable $callback): self
    {
        $this->routes[$pattern] = $callback;
        return $this;
    }

    public function execute(string $uri): null
    {
        foreach ($this->routes as $pattern => $callback) {
            if (preg_match($pattern, $uri, $params ) === 1) {
                return call_user_func_array($callback, []);
            }
        }

        //header("HTTP/1.0 404 Not Found");
        echo "route $uri is not found\n";
        return null;
    }
}
