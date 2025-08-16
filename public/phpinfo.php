<?php
// Простой тест PHP
echo "<h1>✅ PHP работает!</h1>";
echo "<p>Версия PHP: " . phpversion() . "</p>";
echo "<p>Время: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Путь к файлу: " . __FILE__ . "</p>";
echo "<p>Корневая директория: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

// Проверяем основные расширения
$extensions = ['json', 'mbstring', 'openssl', 'curl'];
echo "<h2>Проверка расширений:</h2>";
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext загружено<br>";
    } else {
        echo "❌ $ext не загружено<br>";
    }
}

// Проверяем права доступа
echo "<h2>Права доступа:</h2>";
echo "Пользователь PHP: " . get_current_user() . "<br>";
echo "UID: " . posix_getuid() . "<br>";
echo "GID: " . posix_getgid() . "<br>";

// Проверяем возможность записи в лог
if (error_log("Test message", 0)) {
    echo "✅ Запись в лог работает<br>";
} else {
    echo "❌ Запись в лог не работает<br>";
}
?>
