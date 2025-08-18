<?php

namespace App\Core;

class Router
{
    public Request $request;
    public Response $response;
    protected array $routes = [];
    private string $rootPath;

    public function __construct(Request $request, Response $response, string $rootPath)
    {
        $this->request = $request;
        $this->response = $response;
        $this->rootPath = $rootPath;
    }

    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    public function resolve()
    {
        $path = $this->request->getPath();
        $method = strtolower($this->request->method());
        
        // Ищем точное совпадение
        $callback = $this->routes[$method][$path] ?? false;
        
        // Если точное совпадение не найдено, ищем с параметрами
        if (!$callback) {
            $callback = $this->findRouteWithParams($method, $path);
        }

        if (!$callback) {
            return $this->renderError("Маршрут не найден: $method $path", 404);
        }

        if (is_string($callback)) {
            return $this->renderView($callback);
        }

        if (is_array($callback)) {
            $controller = new $callback[0]();
            $controller->action = $callback[1];
            $callback[0] = $controller;
        }

        return call_user_func($callback, $this->request, $this->response);
    }

    public function renderView($view, $params = [])
    {
        $viewContent = $this->renderOnlyView($view, $params);
        return $this->renderContent($viewContent);
    }

    public function renderContent($viewContent)
    {
        $layout = 'main'; // Используем дефолтный layout
        ob_start();
        $content = $viewContent;
        include_once $this->rootPath . "/templates/layouts/$layout.php";
        return ob_get_clean();
    }

    public function renderError($message, $code = 500)
    {
        $errorContent = $this->renderOnlyView('_error', [
            'message' => $message,
            'code' => $code
        ]);
        
        return $this->renderContent($errorContent);
    }

    protected function renderOnlyView($view, $params)
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }
        ob_start();
        include_once $this->rootPath . "/templates/$view.php";
        return ob_get_clean();
    }

    /**
     * Поиск маршрута с параметрами
     */
    private function findRouteWithParams(string $method, string $path)
    {
        if (!isset($this->routes[$method])) {
            return false;
        }

        foreach ($this->routes[$method] as $route => $callback) {
            // Проверяем, содержит ли маршрут параметры {param}
            if (strpos($route, '{') !== false) {
                $pattern = $this->convertRouteToPattern($route);
                if (preg_match($pattern, $path, $matches)) {
                    // Извлекаем параметры и добавляем их в request
                    $this->extractParams($route, $path, $matches);
                    return $callback;
                }
            }
        }

        return false;
    }

    /**
     * Конвертирует маршрут с параметрами в регулярное выражение
     */
    private function convertRouteToPattern(string $route): string
    {
        // Заменяем {param} на группы захвата
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    /**
     * Извлекает параметры из URL и добавляет их в request
     */
    private function extractParams(string $route, string $path, array $matches): void
    {
        // Находим все параметры в маршруте
        preg_match_all('/\{([^}]+)\}/', $route, $paramNames);
        
        // Добавляем параметры в request (пропускаем первый элемент - полное совпадение)
        for ($i = 0; $i < count($paramNames[1]); $i++) {
            $paramName = $paramNames[1][$i];
            $paramValue = $matches[$i + 1] ?? '';
            $this->request->setParam($paramName, $paramValue);
        }
    }
}
