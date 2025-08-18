<?php
/**
 * Тестовый скрипт для проверки всех компонентов после рефакторинга
 * Запускается на сервере для проверки работоспособности
 */

echo "🧪 Тестирование Linux Server Manager после рефакторинга\n";
echo "=====================================================\n\n";

// 1. Проверяем загрузку автозагрузчика
echo "1. Проверка автозагрузчика...\n";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Автозагрузчик загружен успешно\n";
} catch (Exception $e) {
    echo "❌ Ошибка загрузки автозагрузчика: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Проверяем загрузку основных классов
echo "\n2. Проверка основных классов...\n";
$classes = [
    'App\Core\Application',
    'App\Core\Router',
    'App\Core\Controller',
    'App\Core\Request',
    'App\Core\Response',
    'App\Core\Cache',
    'App\Core\GitVersion'
];

foreach ($classes as $class) {
    try {
        if (class_exists($class)) {
            echo "✅ $class - загружен\n";
        } else {
            echo "❌ $class - не найден\n";
        }
    } catch (Exception $e) {
        echo "❌ $class - ошибка: " . $e->getMessage() . "\n";
    }
}

// 3. Проверяем загрузку новых контроллеров
echo "\n3. Проверка новых контроллеров...\n";
$controllers = [
    'App\Controllers\Network\NetworkViewController',
    'App\Controllers\Network\SSHTunnelApiController',
    'App\Controllers\Network\PortForwardingApiController',
    'App\Controllers\Network\WireGuardController',
    'App\Controllers\Network\CloudflareController'
];

foreach ($controllers as $controller) {
    try {
        if (class_exists($controller)) {
            echo "✅ $controller - загружен\n";
        } else {
            echo "❌ $controller - не найден\n";
        }
    } catch (Exception $e) {
        echo "❌ $controller - ошибка: " . $e->getMessage() . "\n";
    }
}

// 4. Проверяем загрузку новых сервисов
echo "\n4. Проверка новых сервисов...\n";
$services = [
    'App\Services\Network\NetworkService',
    'App\Services\Network\NetworkRoutingService',
    'App\Services\Network\NetworkMonitoringService',
    'App\Services\Network\SSHTunnelService',
    'App\Services\Network\PortForwardingService',
    'App\Services\WireGuard\WireGuardService',
    'App\Services\Cloudflare\CloudflareService'
];

foreach ($services as $service) {
    try {
        if (class_exists($service)) {
            echo "✅ $service - загружен\n";
        } else {
            echo "❌ $service - не найден\n";
        }
    } catch (Exception $e) {
        echo "❌ $service - ошибка: " . $e->getMessage() . "\n";
    }
}

// 5. Проверяем загрузку интерфейсов
echo "\n5. Проверка интерфейсов...\n";
$interfaces = [
    'App\Interfaces\NetworkViewControllerInterface',
    'App\Interfaces\NetworkRoutingServiceInterface',
    'App\Interfaces\NetworkMonitoringServiceInterface',
    'App\Interfaces\SSHTunnelServiceInterface',
    'App\Interfaces\PortForwardingServiceInterface',
    'App\Interfaces\WireGuardServiceInterface',
    'App\Interfaces\CloudflareServiceInterface'
];

foreach ($interfaces as $interface) {
    try {
        if (interface_exists($interface)) {
            echo "✅ $interface - загружен\n";
        } else {
            echo "❌ $interface - не найден\n";
        }
    } catch (Exception $e) {
        echo "❌ $interface - ошибка: " . $e->getMessage() . "\n";
    }
}

// 6. Проверяем загрузку абстрактных классов
echo "\n6. Проверка абстрактных классов...\n";
$abstracts = [
    'App\Abstracts\BaseService'
];

foreach ($abstracts as $abstract) {
    try {
        if (class_exists($abstract)) {
            echo "✅ $abstract - загружен\n";
        } else {
            echo "❌ $abstract - не найден\n";
        }
    } catch (Exception $e) {
        echo "❌ $abstract - ошибка: " . $e->getMessage() . "\n";
    }
}

// 7. Проверяем загрузку исключений
echo "\n7. Проверка исключений...\n";
$exceptions = [
    'App\Exceptions\ServiceException',
    'App\Exceptions\ValidationException'
];

foreach ($exceptions as $exception) {
    try {
        if (class_exists($exception)) {
            echo "✅ $exception - загружен\n";
        } else {
            echo "❌ $exception - не найден\n";
        }
    } catch (Exception $e) {
        echo "❌ $exception - ошибка: " . $e->getMessage() . "\n";
    }
}

// 8. Проверяем версию
echo "\n8. Проверка версии...\n";
try {
    $version = App\Core\GitVersion::getVersion();
    $fullVersion = App\Core\GitVersion::getFullVersion();
    echo "✅ Версия: $version\n";
    echo "✅ Полная версия: $fullVersion\n";
} catch (Exception $e) {
    echo "❌ Ошибка получения версии: " . $e->getMessage() . "\n";
}

// 9. Проверяем создание экземпляров (без выполнения методов)
echo "\n9. Проверка создания экземпляров...\n";
try {
    $app = new App\Core\Application(__DIR__);
    echo "✅ Application создан успешно\n";
} catch (Exception $e) {
    echo "❌ Ошибка создания Application: " . $e->getMessage() . "\n";
}

// 10. Проверяем роутер
echo "\n10. Проверка роутера...\n";
try {
    $request = new App\Core\Request();
    $response = new App\Core\Response();
    $router = new App\Core\Router($request, $response, __DIR__);
    echo "✅ Router создан успешно\n";
} catch (Exception $e) {
    echo "❌ Ошибка создания Router: " . $e->getMessage() . "\n";
}

echo "\n🎉 Тестирование завершено!\n";
echo "📝 Если все проверки прошли успешно, рефакторинг выполнен корректно.\n";
echo "🌐 Теперь можно развернуть на сервере и протестировать функциональность.\n";
