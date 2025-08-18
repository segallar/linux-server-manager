#!/bin/bash

# Скрипт для тестирования на сервере после рефакторинга
# Запускается на сервере для проверки работоспособности

echo "🧪 Тестирование Linux Server Manager на сервере"
echo "=============================================="
echo ""

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Проверяем, что мы в правильной директории
if [ ! -f "composer.json" ]; then
    echo -e "${RED}❌ composer.json не найден. Убедитесь, что вы в корневой директории проекта.${NC}"
    exit 1
fi

echo -e "${BLUE}📁 Текущая директория: $(pwd)${NC}"
echo ""

# 1. Проверяем PHP версию
echo -e "${YELLOW}1. Проверка PHP версии...${NC}"
PHP_VERSION=$(php -v | head -1 | cut -d' ' -f2 | cut -d'.' -f1,2)
echo "PHP версия: $PHP_VERSION"

if [[ $(echo "$PHP_VERSION >= 7.4" | bc -l) -eq 1 ]]; then
    echo -e "${GREEN}✅ PHP версия подходит${NC}"
else
    echo -e "${RED}❌ PHP версия должна быть 7.4 или выше${NC}"
    exit 1
fi
echo ""

# 2. Проверяем Composer
echo -e "${YELLOW}2. Проверка Composer...${NC}"
if command -v composer &> /dev/null; then
    echo -e "${GREEN}✅ Composer установлен${NC}"
    composer --version
else
    echo -e "${RED}❌ Composer не установлен${NC}"
    exit 1
fi
echo ""

# 3. Устанавливаем зависимости
echo -e "${YELLOW}3. Установка зависимостей...${NC}"
if composer install --no-dev --optimize-autoloader; then
    echo -e "${GREEN}✅ Зависимости установлены${NC}"
else
    echo -e "${RED}❌ Ошибка установки зависимостей${NC}"
    exit 1
fi
echo ""

# 4. Запускаем тестовый скрипт
echo -e "${YELLOW}4. Запуск тестового скрипта...${NC}"
if [ -f "test-server.php" ]; then
    if php test-server.php; then
        echo -e "${GREEN}✅ Тестовый скрипт выполнен успешно${NC}"
    else
        echo -e "${RED}❌ Ошибка выполнения тестового скрипта${NC}"
        exit 1
    fi
else
    echo -e "${RED}❌ test-server.php не найден${NC}"
    exit 1
fi
echo ""

# 5. Проверяем права доступа
echo -e "${YELLOW}5. Проверка прав доступа...${NC}"
if [ -w "logs" ]; then
    echo -e "${GREEN}✅ Директория logs доступна для записи${NC}"
else
    echo -e "${YELLOW}⚠️ Директория logs недоступна для записи${NC}"
    mkdir -p logs
    chmod 777 logs
    echo -e "${GREEN}✅ Директория logs создана и настроена${NC}"
fi

if [ -w "cache" ]; then
    echo -e "${GREEN}✅ Директория cache доступна для записи${NC}"
else
    echo -e "${YELLOW}⚠️ Директория cache недоступна для записи${NC}"
    mkdir -p cache
    chmod 777 cache
    echo -e "${GREEN}✅ Директория cache создана и настроена${NC}"
fi
echo ""

# 6. Проверяем веб-сервер
echo -e "${YELLOW}6. Проверка веб-сервера...${NC}"
if command -v nginx &> /dev/null; then
    echo -e "${GREEN}✅ Nginx установлен${NC}"
    nginx -v
elif command -v apache2 &> /dev/null; then
    echo -e "${GREEN}✅ Apache установлен${NC}"
    apache2 -v
else
    echo -e "${YELLOW}⚠️ Веб-сервер не обнаружен${NC}"
fi
echo ""

# 7. Проверяем PHP-FPM
echo -e "${YELLOW}7. Проверка PHP-FPM...${NC}"
if command -v php-fpm &> /dev/null; then
    echo -e "${GREEN}✅ PHP-FPM установлен${NC}"
    php-fpm -v
elif systemctl is-active --quiet php*-fpm; then
    echo -e "${GREEN}✅ PHP-FPM запущен${NC}"
else
    echo -e "${YELLOW}⚠️ PHP-FPM не обнаружен${NC}"
fi
echo ""

# 8. Проверяем доступность приложения
echo -e "${YELLOW}8. Проверка доступности приложения...${NC}"
if [ -f "public/index.php" ]; then
    echo -e "${GREEN}✅ public/index.php найден${NC}"
    
    # Проверяем синтаксис
    if php -l public/index.php; then
        echo -e "${GREEN}✅ Синтаксис public/index.php корректен${NC}"
    else
        echo -e "${RED}❌ Ошибка синтаксиса в public/index.php${NC}"
        exit 1
    fi
else
    echo -e "${RED}❌ public/index.php не найден${NC}"
    exit 1
fi
echo ""

# 9. Проверяем версию
echo -e "${YELLOW}9. Проверка версии...${NC}"
if [ -f ".git/HEAD" ]; then
    CURRENT_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
    echo -e "${GREEN}✅ Текущая версия: $CURRENT_VERSION${NC}"
else
    echo -e "${YELLOW}⚠️ Git репозиторий не найден${NC}"
fi
echo ""

# 10. Проверяем конфигурацию
echo -e "${YELLOW}10. Проверка конфигурации...${NC}"
if [ -f ".env" ]; then
    echo -e "${GREEN}✅ .env файл найден${NC}"
else
    echo -e "${YELLOW}⚠️ .env файл не найден, создаем из примера...${NC}"
    if [ -f "env.example" ]; then
        cp env.example .env
        echo -e "${GREEN}✅ .env файл создан из env.example${NC}"
    else
        echo -e "${RED}❌ env.example не найден${NC}"
    fi
fi
echo ""

# 11. Проверяем шаблоны
echo -e "${YELLOW}11. Проверка шаблонов...${NC}"
if [ -d "templates" ]; then
    TEMPLATE_COUNT=$(find templates -name "*.php" | wc -l)
    echo -e "${GREEN}✅ Найдено $TEMPLATE_COUNT шаблонов${NC}"
else
    echo -e "${RED}❌ Директория templates не найдена${NC}"
fi
echo ""

# 12. Проверяем статические файлы
echo -e "${YELLOW}12. Проверка статических файлов...${NC}"
if [ -d "public/assets" ]; then
    ASSET_COUNT=$(find public/assets -type f | wc -l)
    echo -e "${GREEN}✅ Найдено $ASSET_COUNT статических файлов${NC}"
else
    echo -e "${YELLOW}⚠️ Директория public/assets не найдена${NC}"
fi
echo ""

# 13. Проверяем логи
echo -e "${YELLOW}13. Проверка логов...${NC}"
if [ -d "logs" ]; then
    LOG_COUNT=$(find logs -name "*.log" | wc -l)
    echo -e "${GREEN}✅ Найдено $LOG_COUNT лог файлов${NC}"
else
    echo -e "${YELLOW}⚠️ Директория logs не найдена${NC}"
fi
echo ""

# 14. Финальная проверка
echo -e "${YELLOW}14. Финальная проверка...${NC}"
echo -e "${BLUE}📊 Статистика проекта:${NC}"
echo "   - Контроллеры: $(find src/Controllers -name "*.php" | wc -l)"
echo "   - Сервисы: $(find src/Services -name "*.php" | wc -l)"
echo "   - Интерфейсы: $(find src/Interfaces -name "*.php" | wc -l)"
echo "   - Исключения: $(find src/Exceptions -name "*.php" | wc -l)"
echo "   - Абстракции: $(find src/Abstracts -name "*.php" | wc -l)"
echo ""

echo -e "${GREEN}🎉 Тестирование на сервере завершено успешно!${NC}"
echo -e "${BLUE}💡 Следующие шаги:${NC}"
echo "   1. Настройте веб-сервер (Nginx/Apache)"
echo "   2. Настройте PHP-FPM"
echo "   3. Настройте SSL сертификат"
echo "   4. Настройте файрвол"
echo "   5. Запустите приложение"
echo ""
echo -e "${BLUE}📝 Для развертывания используйте:${NC}"
echo "   sudo ./deploy.sh"
echo ""
echo -e "${BLUE}🔄 Для автоматических обновлений:${NC}"
echo "   sudo ./auto-update.sh"
