#!/bin/bash

# Скрипт для мониторинга производительности в реальном времени

echo "📊 Мониторинг производительности Linux Server Manager"
echo "=================================================="

WEB_ROOT="/var/www/html/linux-server-manager"
CACHE_DIR="$WEB_ROOT/cache"
BASE_URL="http://localhost:81"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Функция для измерения времени загрузки
measure_load_time() {
    local page=$1
    local page_name=$2
    
    start_time=$(date +%s%N)
    http_code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL$page")
    end_time=$(date +%s%N)
    
    load_time_ms=$(( (end_time - start_time) / 1000000 ))
    
    if [ "$http_code" = "200" ]; then
        echo "$load_time_ms"
    else
        echo "ERROR"
    fi
}

# Функция для показа статистики кэша
show_cache_stats() {
    local total_files=$(find "$CACHE_DIR" -name "*.cache" 2>/dev/null | wc -l)
    local total_size=$(du -sh "$CACHE_DIR" 2>/dev/null | cut -f1)
    
    echo "📁 Кэш: $total_files файлов, $total_size"
}

# Функция для показа текущего времени
show_time() {
    date '+%H:%M:%S'
}

# Основной цикл мониторинга
echo "🔄 Запуск мониторинга (Ctrl+C для остановки)..."
echo ""

# Заголовок таблицы
printf "%-8s | %-12s | %-10s | %-10s | %-10s | %-15s\n" "Время" "Cloudflare" "Services" "Packages" "Главная" "Кэш"
printf "%-8s-|-%-12s-|-%-10s-|-%-10s-|-%-10s-|-%-15s\n" "--------" "------------" "----------" "----------" "----------" "---------------"

# Счетчики для статистики
cloudflare_times=()
services_times=()
packages_times=()
main_times=()

# Обработка Ctrl+C
trap 'echo ""; echo "📊 Финальная статистика:"; show_final_stats; exit 0' INT

# Функция для показа финальной статистики
show_final_stats() {
    echo "=================================================="
    
    if [ ${#cloudflare_times[@]} -gt 0 ]; then
        echo "🌐 Cloudflare:"
        show_page_stats "${cloudflare_times[@]}"
    fi
    
    if [ ${#services_times[@]} -gt 0 ]; then
        echo "🔧 Services:"
        show_page_stats "${services_times[@]}"
    fi
    
    if [ ${#packages_times[@]} -gt 0 ]; then
        echo "📦 Packages:"
        show_page_stats "${packages_times[@]}"
    fi
    
    if [ ${#main_times[@]} -gt 0 ]; then
        echo "🏠 Главная:"
        show_page_stats "${main_times[@]}"
    fi
}

# Функция для показа статистики страницы
show_page_stats() {
    local times=("$@")
    local count=${#times[@]}
    
    if [ $count -eq 0 ]; then
        echo "  Нет данных"
        return
    fi
    
    # Вычисляем статистику
    local sum=0
    local min=999999
    local max=0
    
    for time in "${times[@]}"; do
        if [ "$time" != "ERROR" ]; then
            sum=$((sum + time))
            if [ $time -lt $min ]; then min=$time; fi
            if [ $time -gt $max ]; then max=$time; fi
        fi
    done
    
    local avg=$((sum / count))
    
    printf "  Среднее: %dms, Мин: %dms, Макс: %dms, Измерений: %d\n" $avg $min $max $count
}

# Основной цикл
while true; do
    current_time=$(show_time)
    
    # Измеряем время загрузки страниц
    cloudflare_time=$(measure_load_time "/network/cloudflare" "Cloudflare")
    services_time=$(measure_load_time "/services" "Services")
    packages_time=$(measure_load_time "/packages" "Packages")
    main_time=$(measure_load_time "/" "Главная")
    
    # Сохраняем результаты для статистики
    if [ "$cloudflare_time" != "ERROR" ]; then
        cloudflare_times+=($cloudflare_time)
    fi
    if [ "$services_time" != "ERROR" ]; then
        services_times+=($services_time)
    fi
    if [ "$packages_time" != "ERROR" ]; then
        packages_times+=($packages_time)
    fi
    if [ "$main_time" != "ERROR" ]; then
        main_times+=($main_time)
    fi
    
    # Показываем статистику кэша
    cache_info=$(show_cache_stats)
    
    # Форматируем вывод
    printf "%-8s | %-12s | %-10s | %-10s | %-10s | %-15s\n" \
        "$current_time" \
        "${cloudflare_time}ms" \
        "${services_time}ms" \
        "${packages_time}ms" \
        "${main_time}ms" \
        "$cache_info"
    
    # Ждем 30 секунд перед следующим измерением
    sleep 30
done
