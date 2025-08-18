<?php

namespace App\Core;

class Request
{
    public function getPath()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        
        if ($position === false) {
            return $path;
        }
        
        return substr($path, 0, $position);
    }

    public function method()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function isGet()
    {
        return $this->method() === 'get';
    }

    public function isPost()
    {
        return $this->method() === 'post';
    }

    public function getBody()
    {
        $data = [];

        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $data[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if ($this->isPost()) {
            foreach ($_POST as $key => $value) {
                $data[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $data;
    }

    public function get($key, $default = null)
    {
        $body = $this->getBody();
        return $body[$key] ?? $default;
    }

    public function post($key, $default = null)
    {
        if ($this->isPost()) {
            return $_POST[$key] ?? $default;
        }
        return $default;
    }

    public function all()
    {
        return $this->getBody();
    }

    public function has($key)
    {
        $body = $this->getBody();
        return isset($body[$key]);
    }

    public function only($keys)
    {
        $body = $this->getBody();
        $result = [];
        
        foreach ($keys as $key) {
            if (isset($body[$key])) {
                $result[$key] = $body[$key];
            }
        }
        
        return $result;
    }

    public function except($keys)
    {
        $body = $this->getBody();
        
        foreach ($keys as $key) {
            unset($body[$key]);
        }
        
        return $body;
    }

    public function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Установить параметр (используется роутером для параметров из URL)
     */
    public function setParam(string $key, string $value): void
    {
        $_GET[$key] = $value;
    }
}
