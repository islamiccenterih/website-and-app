<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

final class Router
{
    /** @var array<int, array{method:string,pattern:string,handler:mixed,middleware:array}> */
    private array $routes = [];

    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function add(string $method, string $path, mixed $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $this->compile($path),
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $method = strtoupper($method);
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        $uri = rawurldecode($uri);
        $uri = '/' . ltrim($uri, '/');
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && !($route['method'] === 'GET' && $method === 'HEAD')) {
                continue;
            }
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($route['middleware'] as $mw) {
                if ($mw === 'admin') {
                    Auth::requireRole('admin');
                    $path = current_path();
                    if ($path !== '/admin/logout' && !AdminAccess::canPath($path)) {
                        AdminAccess::deny();
                    }
                } elseif ($mw === 'student') {
                    Auth::requireRole('student');
                } elseif ($mw === 'guest-admin') {
                    Auth::guestOnly('admin');
                } elseif ($mw === 'guest-student') {
                    Auth::guestOnly('student');
                } elseif ($mw === 'panel') {
                    $currentRole = $_SESSION['auth']['role'] ?? '';
                    if ($currentRole !== 'admin' && $currentRole !== 'student') {
                        if (str_starts_with(current_path(), '/api/')) {
                            json_response(['ok' => false, 'error' => 'Please sign in to continue.'], 401);
                        }
                        flash('error', 'Please sign in to continue.');
                        redirect('/student/login');
                    }
                    if ($currentRole === 'admin' && str_starts_with(current_path(), '/api/live-class') && !AdminAccess::canModule('live-classes')) {
                        json_response(['ok' => false, 'error' => 'You do not have access to live classes.'], 403);
                    }
                } elseif ($mw instanceof Closure) {
                    $mw();
                }
            }

            return $this->invoke($route['handler'], $params);
        }

        http_response_code(404);
        $controller = new \App\Controllers\PublicSite\ErrorController();
        return $controller->notFound();
    }

    private function compile(string $path): string
    {
        $path = rtrim($path, '/') ?: '/';
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $regex . '$#u';
    }

    private function invoke(mixed $handler, array $params): mixed
    {
        if ($handler instanceof Closure) {
            return $handler(...array_values($params));
        }
        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $instance = is_object($class) ? $class : new $class();
            return $instance->{$method}(...array_values($params));
        }
        throw new \RuntimeException('Invalid route handler.');
    }
}
