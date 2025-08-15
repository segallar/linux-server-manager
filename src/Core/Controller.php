<?php

namespace App\Core;

class Controller
{
    public string $layout = 'main';
    public string $action = '';

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public function render($view, $params = [])
    {
        return Application::$app->router->renderView($view, $params);
    }

    public function renderContent($content)
    {
        return Application::$app->router->renderContent($content);
    }

    public function json($data)
    {
        header('Content-Type: application/json');
        return json_encode($data);
    }

    public function redirect($url)
    {
        return Application::$app->response->redirect($url);
    }

    public function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return $this->redirect($referer);
    }

    public function with($key, $value)
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    public function withErrors($errors)
    {
        $_SESSION['errors'] = $errors;
        return $this;
    }

    public function withInput()
    {
        $_SESSION['old'] = $_POST;
        return $this;
    }

    public function old($key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }

    public function hasErrors()
    {
        return isset($_SESSION['errors']);
    }

    public function getErrors()
    {
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        return $errors;
    }

    public function hasError($key)
    {
        return isset($_SESSION['errors'][$key]);
    }

    public function getError($key)
    {
        return $_SESSION['errors'][$key] ?? '';
    }
}
