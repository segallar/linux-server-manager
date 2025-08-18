#!/bin/bash

# Скрипт для тестирования работы кэша

echo "🧪 Тестирование системы кэширования"
echo "==================================="

WEB_ROOT="/var/www/html/linux-server-manager"
CACHE_DIR="$WEB_ROOT/cache"
BASE_URL="http://localhost:81"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Функция для измерения времени загрузки страницы
measure_page_load() {
    local page=$1
    local page_name=$2
    
    echo -n "📄 Тестирую $page_name... "
    
    start_time=$(date +%s%N)
    http_code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL$page")
    end_time=$(date +%s%N)
    
    load_time_ms=$(( (end_time - start_time) / 1000000 ))
    
    if [ "$http_code" = "200" ]; then
        if [ $load_time_ms -lt 1000 ]; then
            echo "✅ ${load_time_ms} ms"
        else
            load_time_s=$((load_time_ms / 1000))
            echo "⚠️ ${load_time_s}.$((load_time_ms % 1000))s"
        fi
        return $load_time_ms
    else
        echo "❌ HTTP $http_code"
        return 999999
    fi
}

# Функция для проверки кэша
check_cache_status() {
    echo ""
    echo "📊 Статус кэша:"
    echo "---------------"
    
    if [ ! -d "$CACHE_DIR" ]; then
        echo "❌ Директория кэша не найдена"
        return
    fi
    
    local total_files=$(find "$CACHE_DIR" -name "*.cache" 2>/dev/null | wc -l)
    local total_size=$(du -sh "$CACHE_DIR" 2>/dev/null | cut -f1)
    
    echo "📁 Файлов в кэше: $total_files"
    echo "💾 Размер кэша: $total_size"
    
    if [ "$total_files" -gt 0 ]; then
        echo ""
        echo "📋 Содержимое кэша:"
        find "$CACHE_DIR" -name "*.cache" -exec ls -lh {} \; 2>/dev/null
    fi
}

# Функция для тестирования кэширования
test_caching() {
    local page=$1
    local page_name=$2
    local cache_key=$3
    
    echo ""
    echo "🧪 Тестирование кэширования для $page_name:"
    echo "----------------------------------------"
    
    # Очищаем кэш для этой страницы
    if [ -f "$CACHE_DIR/$cache_key.cache" ]; then
        rm "$CACHE_DIR/$cache_key.cache"
        echo "🗑️ Очищен кэш для $page_name"
    fi
    
    # Первая загрузка (без кэша)
    echo "🔄 Первая загрузка (без кэша):"
    first_load=$(measure_page_load "$page" "$page_name")
    first_time=$?
    
    # Проверяем, что кэш создался
    sleep 2
    if [ -f "$CACHE_DIR/$cache_key.cache" ]; then
        echo "✅ Кэш создан: $cache_key.cache"
        cache_size=$(ls -lh "$CACHE_DIR/$cache_key.cache" | awk '{print $5}')
        echo "📏 Размер кэша: $cache_size"
    else
        echo "❌ Кэш не создался"
    fi
    
    # Вторая загрузка (с кэшем)
    echo "🔄 Вторая загрузка (с кэшем):"
    second_load=$(measure_page_load "$page" "$page_name")
    second_time=$?
    
    # Сравниваем время
    if [ $first_time -ne 999999 ] && [ $second_time -ne 999999 ]; then
        if [ $second_time -lt $first_time ]; then
            improvement=$(( (first_time - second_time) * 100 / first_time ))
            echo "🎉 Ускорение: ${improvement}% (${first_time}ms → ${second_time}ms)"
        else
            echo "⚠️ Время загрузки не улучшилось (${first_time}ms → ${second_time}ms)"
        fi
    fi
}

# Основное тестирование
echo "🚀 Начинаем тестирование..."

# Тестируем медленные страницы
test_caching "/network/cloudflare" "Cloudflare" "cloudflare_data"
test_caching "/services" "Services" "services_data"
test_caching "/packages" "Packages" "packages_data"

# Показываем финальную статистику кэша
check_cache_status

echo ""
echo "🎯 Тестирование завершено!"
echo "=========================="
echo ""
echo "💡 Для очистки кэша используйте: sudo ./clear-cache.sh"
echo "💡 Для управления кэшем используйте: sudo ./cache-manager.sh"
