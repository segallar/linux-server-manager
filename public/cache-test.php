<?php
/**
 * Тестирование системы кэширования
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Cache;

// Создаем экземпляр кэша
$cache = new Cache();

// Получаем статистику кэша
$stats = $cache->getStats();

// Функция для тестирования кэша
function testCache($cache, $key, $data, $ttl = 300) {
    $start = microtime(true);
    
    // Пытаемся получить из кэша
    $cached = $cache->get($key);
    
    if ($cached === null) {
        // Кэша нет, сохраняем данные
        $cache->set($key, $data, $ttl);
        $result = 'miss';
        $time = microtime(true) - $start;
    } else {
        // Кэш есть
        $result = 'hit';
        $time = microtime(true) - $start;
    }
    
    return [
        'result' => $result,
        'time' => $time,
        'data_size' => strlen(serialize($data))
    ];
}

// Тестовые данные
$testData = [
    'cloudflare_data' => [
        'tunnels' => ['tunnel1', 'tunnel2', 'tunnel3'],
        'stats' => ['total' => 3, 'active' => 2],
        'isInstalled' => true
    ],
    'services_data' => [
        'stats' => ['total' => 15, 'active' => 12],
        'services' => ['nginx', 'php-fpm', 'mysql']
    ],
    'packages_data' => [
        'stats' => ['total_installed' => 1250, 'upgradable' => 5],
        'upgradable' => ['package1', 'package2'],
        'unused' => ['old-package1']
    ]
];

// Тестируем кэш
$testResults = [];
foreach ($testData as $key => $data) {
    $testResults[$key] = testCache($cache, $key, $data);
}

// Получаем обновленную статистику
$statsAfter = $cache->getStats();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест кэша - Linux Server Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="fas fa-database"></i> Тестирование системы кэширования
                </h1>
                
                <!-- Статистика кэша -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar"></i> Статистика кэша
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-primary"><?= $statsAfter['total_files'] ?></h3>
                                    <p class="text-muted">Файлов в кэше</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-success"><?= $statsAfter['total_size'] ?></h3>
                                    <p class="text-muted">Общий размер</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-info"><?= $statsAfter['hit_rate'] ?>%</h3>
                                    <p class="text-muted">Процент попаданий</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h3 class="text-warning"><?= $statsAfter['expired_files'] ?></h3>
                                    <p class="text-muted">Устаревших файлов</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Результаты тестирования -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-vial"></i> Результаты тестирования
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Ключ кэша</th>
                                        <th>Результат</th>
                                        <th>Время (мс)</th>
                                        <th>Размер данных</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($testResults as $key => $result): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($key) ?></code></td>
                                        <td>
                                            <?php if ($result['result'] === 'hit'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> HIT
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-times"></i> MISS
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= number_format($result['time'] * 1000, 2) ?> ms</td>
                                        <td><?= $result['data_size'] ?> bytes</td>
                                        <td>
                                            <?php if ($result['time'] < 0.01): ?>
                                                <span class="text-success">
                                                    <i class="fas fa-bolt"></i> Быстро
                                                </span>
                                            <?php elseif ($result['time'] < 0.1): ?>
                                                <span class="text-warning">
                                                    <i class="fas fa-clock"></i> Средне
                                                </span>
                                            <?php else: ?>
                                                <span class="text-danger">
                                                    <i class="fas fa-hourglass"></i> Медленно
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Действия -->
                <div class="mt-4">
                    <a href="/" class="btn btn-primary">
                        <i class="fas fa-home"></i> На главную
                    </a>
                    <button onclick="location.reload()" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Обновить тест
                    </button>
                    <a href="/cache-test.php?clear=1" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Очистить кэш
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
