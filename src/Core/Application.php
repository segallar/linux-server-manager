<?php

namespace App\Core;

class Application
{
    public Router $router;
    public Request $request;
    public Response $response;
    public string $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response, $this->rootPath);
        
        // Устанавливаем таймауты для предотвращения 504 ошибок
        $this->setTimeouts();
    }

    private function setTimeouts(): void
    {
        // Увеличиваем лимиты выполнения
        set_time_limit(300);
        ini_set('max_execution_time', '300');
        ini_set('memory_limit', '256M');
        
        // Отключаем ограничения на ввод
        ini_set('max_input_time', '300');
        ini_set('post_max_size', '50M');
        ini_set('upload_max_filesize', '50M');
    }

    public function run()
    {
        try {
            // Проверяем, что запрос не превышает лимиты
            if ($this->request->isPost() && $_SERVER['CONTENT_LENGTH'] > 50 * 1024 * 1024) {
                $this->response->setStatusCode(413);
                echo json_encode(['error' => 'Request too large']);
                return;
            }

            $result = $this->router->resolve();
            echo $result;
        } catch (\Exception $e) {
            // Логируем ошибку
            error_log("Application error: " . $e->getMessage());
            
            // Возвращаем ошибку клиенту
            $this->response->setStatusCode(500);
            if ($this->request->isAjax()) {
                echo json_encode(['error' => 'Internal server error']);
            } else {
                echo "Internal server error";
            }
        }
    }
}
