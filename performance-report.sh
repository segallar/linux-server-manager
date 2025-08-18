#!/bin/bash

# Скрипт для генерации отчета о производительности

echo "📊 Отчет о производительности Linux Server Manager"
echo "================================================="

WEB_ROOT="/var/www/html/linux-server-manager"
CACHE_DIR="$WEB_ROOT/cache"
BASE_URL="http://localhost:81"
REPORT_FILE="$WEB_ROOT/performance-report-$(date +%Y%m%d-%H%M%S).txt"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Функция для измерения времени загрузки
measure_load_time() {
    local page=$1
    local page_name=$2
    local iterations=$3
    
    echo "📄 Тестирование $page_name ($iterations измерений)..."
    
    local times=()
    local errors=0
    
    for ((i=1; i<=$iterations; i++)); do
        start_time=$(date +%s%N)
        http_code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL$page")
        end_time=$(date +%s%N)
        
        load_time_ms=$(( (end_time - start_time) / 1000000 ))
        
        if [ "$http_code" = "200" ]; then
            times+=($load_time_ms)
            echo -n "."
        else
            ((errors++))
            echo -n "E"
        fi
        
        # Небольшая пауза между запросами
        sleep 0.5
    done
    
    echo ""
    
    if [ ${#times[@]} -eq 0 ]; then
        echo "❌ Все запросы завершились с ошибкой"
        return
    fi
    
    # Вычисляем статистику
    local sum=0
    local min=999999
    local max=0
    
    for time in "${times[@]}"; do
        sum=$((sum + time))
        if [ $time -lt $min ]; then min=$time; fi
        if [ $time -gt $max ]; then max=$time; fi
    done
    
    local avg=$((sum / ${#times[@]}))
    local success_rate=$(( (${#times[@]} * 100) / iterations ))
    
    echo "✅ Успешных запросов: ${#times[@]}/$iterations ($success_rate%)"
    echo "📊 Статистика: Среднее=${avg}ms, Мин=${min}ms, Макс=${max}ms"
    
    # Возвращаем среднее время
    echo "$avg"
}

# Функция для проверки кэша
check_cache_status() {
    echo ""
    echo "📁 Статус кэша:"
    echo "---------------"
    
    if [ ! -d "$CACHE_DIR" ]; then
        echo "❌ Директория кэша не найдена"
        return
    fi
    
    local total_files=$(find "$CACHE_DIR" -name "*.cache" 2>/dev/null | wc -l)
    local total_size=$(du -sh "$CACHE_DIR" 2>/dev/null | cut -f1)
    local expired_files=0
    
    if [ "$total_files" -gt 0 ]; then
        echo "📁 Всего файлов: $total_files"
        echo "💾 Общий размер: $total_size"
        
        # Проверяем устаревшие файлы
        expired_files=$(find "$CACHE_DIR" -name "*.cache" -mmin +60 2>/dev/null | wc -l)
        echo "⏰ Устаревших файлов: $expired_files"
        
        echo ""
        echo "📋 Содержимое кэша:"
        find "$CACHE_DIR" -name "*.cache" -exec ls -lh {} \; 2>/dev/null
    else
        echo "📭 Кэш пуст"
    fi
}

# Функция для тестирования с кэшем и без
test_with_and_without_cache() {
    local page=$1
    local page_name=$2
    local cache_key=$3
    local iterations=5
    
    echo ""
    echo "🧪 Тестирование $page_name:"
    echo "=========================="
    
    # Очищаем кэш для этой страницы
    if [ -f "$CACHE_DIR/$cache_key.cache" ]; then
        rm "$CACHE_DIR/$cache_key.cache"
        echo "🗑️ Очищен кэш для $page_name"
    fi
    
    # Тест без кэша
    echo "🔄 Тест без кэша:"
    without_cache_time=$(measure_load_time "$page" "$page_name" $iterations)
    
    # Ждем немного
    sleep 2
    
    # Тест с кэшем
    echo "🔄 Тест с кэшем:"
    with_cache_time=$(measure_load_time "$page" "$page_name" $iterations)
    
    # Сравниваем результаты
    if [ -n "$without_cache_time" ] && [ -n "$with_cache_time" ]; then
        local improvement=0
        if [ $without_cache_time -gt 0 ]; then
            improvement=$(( (without_cache_time - with_cache_time) * 100 / without_cache_time ))
        fi
        
        echo ""
        echo "📈 Результаты сравнения:"
        echo "   Без кэша: ${without_cache_time}ms"
        echo "   С кэшем:  ${with_cache_time}ms"
        
        if [ $improvement -gt 0 ]; then
            echo "   🎉 Ускорение: ${improvement}%"
        else
            echo "   ⚠️ Замедление: ${improvement#-}%"
        fi
    fi
}

# Основное тестирование
echo "🚀 Начинаем тестирование производительности..."
echo ""

# Системная информация
echo "💻 Системная информация:"
echo "======================="
echo "Дата: $(date)"
echo "Система: $(uname -a)"
echo "CPU: $(grep 'model name' /proc/cpuinfo | head -1 | cut -d: -f2 | xargs)"
echo "Память: $(free -h | grep Mem | awk '{print $2}')"
echo ""

# Проверяем статус кэша
check_cache_status

# Тестируем страницы
test_with_and_without_cache "/network/cloudflare" "Cloudflare" "cloudflare_data"
test_with_and_without_cache "/services" "Services" "services_data"
test_with_and_without_cache "/packages" "Packages" "packages_data"

# Тестируем быстрые страницы
echo ""
echo "🧪 Тестирование быстрых страниц:"
echo "================================"

echo "📄 Тестирование главной страницы..."
main_time=$(measure_load_time "/" "Главная" 5)

echo "📄 Тестирование страницы процессов..."
processes_time=$(measure_load_time "/processes" "Processes" 5)

echo "📄 Тестирование страницы системы..."
system_time=$(measure_load_time "/system" "System" 5)

# Финальная статистика
echo ""
echo "📊 Финальная статистика:"
echo "========================"
check_cache_status

echo ""
echo "🎯 Тестирование завершено!"
echo "=========================="
echo ""
echo "💡 Для мониторинга в реальном времени: sudo ./monitor-performance.sh"
echo "💡 Для очистки кэша: sudo ./clear-cache.sh"
echo "💡 Для управления кэшем: sudo ./cache-manager.sh"
