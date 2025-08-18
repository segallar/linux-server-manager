# 💻 Стандарты разработки Linux Server Manager

## 🎯 Общие принципы

### 📖 Философия кода
- **Читаемость** - код должен быть понятным и самодокументируемым
- **Поддерживаемость** - легко изменять и расширять
- **Безопасность** - защита от уязвимостей
- **Производительность** - оптимальная работа
- **Тестируемость** - возможность написания тестов

---

## 📝 Стиль кодирования

### 🎯 Именование

#### 📁 Файлы и директории
```php
// ✅ Правильно
src/
├── Controllers/
│   ├── DashboardController.php
│   ├── SystemController.php
│   └── NetworkController.php
├── Services/
│   ├── SystemService.php
│   ├── NetworkService.php
│   └── FirewallService.php
└── Core/
    ├── Application.php
    ├── Router.php
    └── Controller.php

// ❌ Неправильно
src/
├── controllers/
│   ├── dashboard_controller.php
│   └── system_controller.php
├── services/
│   └── system_service.php
└── core/
    └── application.php
```

#### 🏷️ Классы
```php
// ✅ Правильно - PascalCase
class DashboardController extends Controller
class SystemService
class NetworkInterface
class FirewallRule

// ❌ Неправильно
class dashboardController
class system_service
class networkinterface
class firewall_rule
```

#### 🔧 Методы
```php
// ✅ Правильно - camelCase
public function getSystemInfo(): array
public function updateFirewallRule(): bool
public function isOnline(): bool
public function formatBytes(): string

// ❌ Неправильно
public function get_system_info()
public function UpdateFirewallRule()
public function is_online()
public function formatbytes()
```

#### 📊 Переменные
```php
// ✅ Правильно - camelCase
$systemInfo = [];
$cpuUsage = 0;
$isOnline = true;
$maxRetries = 3;

// ❌ Неправильно
$system_info = [];
$CPUUsage = 0;
$is_online = true;
$maxretries = 3;
```

#### 🔒 Константы
```php
// ✅ Правильно - UPPER_SNAKE_CASE
class SystemService
{
    private const MAX_RETRIES = 3;
    private const TIMEOUT_SECONDS = 30;
    private const DEFAULT_MEMORY_LIMIT = '256M';
    
    public const STATUS_ONLINE = 'online';
    public const STATUS_OFFLINE = 'offline';
}

// ❌ Неправильно
class SystemService
{
    private const maxRetries = 3;
    private const timeout_seconds = 30;
    public const statusOnline = 'online';
}
```

### 📋 Структура файла

#### 📝 Заголовок файла
```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\SystemService;
use App\Services\NetworkService;

/**
 * Контроллер для управления системной информацией
 * 
 * @package App\Controllers
 * @author Команда Linux Server Manager
 * @version 1.0.0
 */
class SystemController extends Controller
{
    // Код класса...
}
```

#### 🏗️ Структура класса
```php
class ExampleController extends Controller
{
    // 1. Константы
    private const MAX_RETRIES = 3;
    public const STATUS_ACTIVE = 'active';
    
    // 2. Свойства (сначала public, потом private/protected)
    public string $title;
    private SystemService $systemService;
    protected array $config;
    
    // 3. Конструктор
    public function __construct()
    {
        $this->systemService = new SystemService();
        $this->config = $this->loadConfig();
    }
    
    // 4. Публичные методы
    public function index(): string
    {
        return $this->render('system/index', [
            'title' => 'System Information',
            'data' => $this->getSystemData()
        ]);
    }
    
    // 5. Защищенные методы
    protected function getSystemData(): array
    {
        return $this->systemService->getSystemInfo();
    }
    
    // 6. Приватные методы
    private function loadConfig(): array
    {
        return [
            'timeout' => 30,
            'retries' => self::MAX_RETRIES
        ];
    }
}
```

### 🎨 Форматирование

#### 📏 Отступы и пробелы
```php
// ✅ Правильно - 4 пробела
class Example
{
    public function method(): void
    {
        if ($condition) {
            $result = $this->process($data);
            return $result;
        }
    }
}

// ❌ Неправильно - табы или 2 пробела
class Example
{
  public function method(): void
  {
    if ($condition) {
      $result = $this->process($data);
      return $result;
    }
  }
}
```

#### 🔗 Операторы
```php
// ✅ Правильно - пробелы вокруг операторов
$result = $a + $b;
$condition = $x > 0 && $y < 100;
$array = ['key' => 'value', 'another' => 'data'];

// ❌ Неправильно
$result=$a+$b;
$condition=$x>0&&$y<100;
$array=['key'=>'value','another'=>'data'];
```

#### 📋 Массивы
```php
// ✅ Правильно - многострочные массивы
$config = [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'app_db'
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600
    ]
];

// ✅ Короткие массивы в одну строку
$simple = ['a', 'b', 'c'];
$assoc = ['key' => 'value'];

// ❌ Неправильно
$config = ['database' => ['host' => 'localhost', 'port' => 3306, 'name' => 'app_db'], 'cache' => ['enabled' => true, 'ttl' => 3600]];
```

### 📝 Комментарии

#### 📖 Документация методов
```php
/**
 * Получить информацию о системе
 * 
 * @return array Массив с информацией о системе
 * @throws \RuntimeException Если не удалось получить информацию
 * 
 * @example
 * $info = $this->getSystemInfo();
 * // Возвращает: ['os' => 'Linux', 'kernel' => '5.4.0', ...]
 */
public function getSystemInfo(): array
{
    // Реализация...
}
```

#### 💭 Встроенные комментарии
```php
// ✅ Хорошие комментарии
// Проверяем доступность сети
if (!$this->isOnline()) {
    return ['status' => 'offline'];
}

// Получаем информацию о CPU из /proc/cpuinfo
$cpuInfo = file_get_contents('/proc/cpuinfo');

// ❌ Плохие комментарии
// Получаем данные
$data = $this->getData();

// Проверяем условие
if ($condition) {
    // Делаем что-то
    $this->doSomething();
}
```

---

## 🏗️ Архитектурные принципы

### 📋 MVC архитектура

#### 🎯 Контроллеры
```php
/**
 * Контроллер должен быть тонким
 * - Только обработка запросов
 * - Делегирование бизнес-логики сервисам
 * - Возврат ответов
 */
class SystemController extends Controller
{
    private SystemService $systemService;
    
    public function __construct()
    {
        $this->systemService = new SystemService();
    }
    
    public function index(): string
    {
        $systemInfo = $this->systemService->getSystemInfo();
        
        return $this->render('system/index', [
            'title' => 'System Information',
            'systemInfo' => $systemInfo
        ]);
    }
    
    public function getStats(): string
    {
        $stats = $this->systemService->getStats();
        
        return $this->json($stats);
    }
}
```

#### 🔧 Сервисы
```php
/**
 * Сервисы содержат бизнес-логику
 * - Валидация данных
 * - Обработка бизнес-правил
 * - Взаимодействие с внешними системами
 */
class SystemService
{
    /**
     * Получить статистику системы
     */
    public function getStats(): array
    {
        return [
            'cpu' => $this->getCpuStats(),
            'memory' => $this->getMemoryStats(),
            'disk' => $this->getDiskStats(),
            'network' => $this->getNetworkStats()
        ];
    }
    
    private function getCpuStats(): array
    {
        // Реализация получения статистики CPU
    }
}
```

### 🔒 Безопасность

#### 🛡️ Валидация входных данных
```php
/**
 * Всегда валидируйте входные данные
 */
public function updateConfig(array $data): bool
{
    // ✅ Валидация данных
    if (!isset($data['timeout']) || !is_numeric($data['timeout'])) {
        throw new \InvalidArgumentException('Invalid timeout value');
    }
    
    if ($data['timeout'] < 1 || $data['timeout'] > 3600) {
        throw new \InvalidArgumentException('Timeout must be between 1 and 3600 seconds');
    }
    
    // Безопасная обработка
    return $this->saveConfig($data);
}
```

#### 🔐 Санитизация данных
```php
/**
 * Санитизируйте данные перед выводом
 */
public function render(string $template, array $data): string
{
    // ✅ Санитизация данных
    $sanitizedData = array_map(function($value) {
        if (is_string($value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }, $data);
    
    return $this->renderTemplate($template, $sanitizedData);
}
```

### ⚡ Производительность

#### 💾 Кэширование
```php
/**
 * Используйте кэширование для тяжелых операций
 */
class SystemService
{
    private Cache $cache;
    
    public function getSystemInfo(): array
    {
        $cacheKey = 'system_info';
        
        // Проверяем кэш
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Получаем данные
        $data = $this->fetchSystemInfo();
        
        // Сохраняем в кэш на 5 минут
        $this->cache->set($cacheKey, $data, 300);
        
        return $data;
    }
}
```

#### 🔄 Оптимизация запросов
```php
/**
 * Минимизируйте количество системных вызовов
 */
class ProcessService
{
    public function getProcesses(): array
    {
        // ✅ Один вызов для получения всех процессов
        $output = shell_exec('ps aux 2>/dev/null');
        $lines = explode("\n", $output);
        
        $processes = [];
        foreach ($lines as $line) {
            if (trim($line)) {
                $processes[] = $this->parseProcessLine($line);
            }
        }
        
        return $processes;
    }
}
```

---

## 🧪 Тестирование

### 📋 Unit тесты
```php
/**
 * Каждый публичный метод должен иметь тест
 */
class SystemServiceTest extends TestCase
{
    private SystemService $service;
    
    protected function setUp(): void
    {
        $this->service = new SystemService();
    }
    
    public function testGetCpuInfoReturnsValidData(): void
    {
        $cpuInfo = $this->service->getCpuInfo();
        
        $this->assertIsArray($cpuInfo);
        $this->assertArrayHasKey('usage', $cpuInfo);
        $this->assertArrayHasKey('cores', $cpuInfo);
        $this->assertGreaterThanOrEqual(0, $cpuInfo['usage']);
        $this->assertLessThanOrEqual(100, $cpuInfo['usage']);
    }
    
    public function testGetMemoryInfoReturnsValidData(): void
    {
        $memoryInfo = $this->service->getMemoryInfo();
        
        $this->assertIsArray($memoryInfo);
        $this->assertArrayHasKey('total', $memoryInfo);
        $this->assertArrayHasKey('used', $memoryInfo);
        $this->assertArrayHasKey('usage_percent', $memoryInfo);
    }
}
```

### 🔄 Integration тесты
```php
/**
 * Тестирование взаимодействия компонентов
 */
class SystemControllerTest extends TestCase
{
    public function testIndexReturnsValidResponse(): void
    {
        $controller = new SystemController();
        $response = $controller->index();
        
        $this->assertIsString($response);
        $this->assertStringContainsString('System Information', $response);
    }
}
```

---

## 🚨 Обработка ошибок

### 📝 Исключения
```php
/**
 * Используйте специфичные исключения
 */
class SystemService
{
    public function getSystemInfo(): array
    {
        try {
            $cpuInfo = file_get_contents('/proc/cpuinfo');
            
            if ($cpuInfo === false) {
                throw new \RuntimeException('Unable to read CPU information');
            }
            
            return $this->parseCpuInfo($cpuInfo);
        } catch (\Exception $e) {
            // Логируем ошибку
            error_log("Error getting system info: " . $e->getMessage());
            
            // Возвращаем безопасные данные по умолчанию
            return [
                'error' => 'Unable to get system information',
                'timestamp' => time()
            ];
        }
    }
}
```

### 📊 Логирование
```php
/**
 * Используйте структурированное логирование
 */
class Logger
{
    public function log(string $level, string $message, array $context = []): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];
        
        error_log(json_encode($logEntry));
    }
}
```

---

## 📋 Чек-лист качества кода

### 📝 Перед коммитом
- [ ] Код следует единому стилю
- [ ] Все методы имеют документацию
- [ ] Нет неиспользуемого кода
- [ ] Все переменные имеют осмысленные имена
- [ ] Нет магических чисел (используйте константы)
- [ ] Методы не превышают 50 строк
- [ ] Классы не превышают 500 строк

### 🔒 Безопасность
- [ ] Все входные данные валидируются
- [ ] Данные санитизируются перед выводом
- [ ] Нет SQL-инъекций
- [ ] Нет XSS-уязвимостей
- [ ] Используются подготовленные запросы
- [ ] Чувствительные данные не логируются

### ⚡ Производительность
- [ ] Используется кэширование где возможно
- [ ] Минимизировано количество системных вызовов
- [ ] Нет N+1 проблем
- [ ] Оптимизированы запросы к базе данных
- [ ] Используется пагинация для больших списков

### 🧪 Тестирование
- [ ] Написаны unit тесты для новых методов
- [ ] Покрытие тестами > 80%
- [ ] Все тесты проходят
- [ ] Написаны integration тесты для API

---

## 🛠️ Инструменты

### 📊 Статический анализ
```bash
# PHP_CodeSniffer для проверки стиля
composer require --dev squizlabs/php_codesniffer

# PHPStan для статического анализа
composer require --dev phpstan/phpstan

# Psalm для анализа типов
composer require --dev vimeo/psalm
```

### 🔧 Конфигурация
```json
// phpcs.xml
{
    "ruleset": "PSR12",
    "fileExtensions": ["php"],
    "ignorePatterns": ["vendor/*", "tests/*"]
}
```

---

**📅 Последнее обновление**: 2025-01-16  
**🏷️ Версия документа**: 1.0.0  
**📝 Автор**: Команда Linux Server Manager
