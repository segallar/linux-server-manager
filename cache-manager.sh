#!/bin/bash

# Скрипт для управления кэшем приложения

echo "🗄️ Менеджер кэша Linux Server Manager"
echo "===================================="

WEB_ROOT="/var/www/html/linux-server-manager"
CACHE_DIR="$WEB_ROOT/cache"

# Проверяем, что скрипт запущен с правами root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Этот скрипт должен быть запущен с правами root (sudo)"
    exit 1
fi

# Функция для показа статистики кэша
show_cache_stats() {
    echo "📊 Статистика кэша:"
    echo "-------------------"
    
    if [ ! -d "$CACHE_DIR" ]; then
        echo "❌ Директория кэша не найдена"
        return
    fi
    
    local total_files=$(find "$CACHE_DIR" -name "*.cache" 2>/dev/null | wc -l)
    local total_size=$(du -sh "$CACHE_DIR" 2>/dev/null | cut -f1)
    local expired_files=0
    local valid_files=0
    
    if [ "$total_files" -gt 0 ]; then
        echo "📁 Всего файлов: $total_files"
        echo "💾 Общий размер: $total_size"
        
        # Проверяем устаревшие файлы
        while IFS= read -r -d '' file; do
            if [ -f "$file" ]; then
                # Простая проверка на устаревшие файлы (старше 1 часа)
                if [ $(find "$file" -mmin +60 2>/dev/null | wc -l) -gt 0 ]; then
                    ((expired_files++))
                else
                    ((valid_files++))
                fi
            fi
        done < <(find "$CACHE_DIR" -name "*.cache" -print0 2>/dev/null)
        
        echo "✅ Актуальных файлов: $valid_files"
        echo "⏰ Устаревших файлов: $expired_files"
        
        # Показываем самые большие файлы
        echo ""
        echo "📏 Самые большие файлы кэша:"
        find "$CACHE_DIR" -name "*.cache" -exec ls -lh {} \; 2>/dev/null | head -5
    else
        echo "📭 Кэш пуст"
    fi
}

# Функция для очистки кэша
clear_cache() {
    echo "🧹 Очистка кэша..."
    
    if [ -d "$CACHE_DIR" ]; then
        local deleted_count=$(find "$CACHE_DIR" -name "*.cache" -delete 2>/dev/null | wc -l)
        echo "✅ Удалено файлов: $deleted_count"
    else
        echo "📭 Директория кэша не найдена"
    fi
}

# Функция для очистки устаревших файлов
cleanup_expired() {
    echo "🧹 Очистка устаревших файлов..."
    
    if [ -d "$CACHE_DIR" ]; then
        local deleted_count=$(find "$CACHE_DIR" -name "*.cache" -mmin +60 -delete 2>/dev/null | wc -l)
        echo "✅ Удалено устаревших файлов: $deleted_count"
    else
        echo "📭 Директория кэша не найдена"
    fi
}

# Функция для тестирования производительности
test_performance() {
    echo "⚡ Тестирование производительности после очистки кэша..."
    echo ""
    
    # Тестируем медленные страницы
    local slow_pages=(
        "/network/cloudflare"
        "/services"
        "/packages"
    )
    
    for page in "${slow_pages[@]}"; do
        page_name=$(echo "$page" | sed 's|^/||' | sed 's|/| → |g')
        echo -n "Тестирую $page_name... "
        
        start_time=$(date +%s%N)
        http_code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:81$page")
        end_time=$(date +%s%N)
        
        load_time_ms=$(( (end_time - start_time) / 1000000 ))
        
        if [ $load_time_ms -lt 1000 ]; then
            echo "✅ ${load_time_ms} ms"
        else
            load_time_s=$((load_time_ms / 1000))
            echo "⚠️ ${load_time_s}.$((load_time_ms % 1000))s"
        fi
    done
}

# Главное меню
while true; do
    echo ""
    echo "🎯 Выберите действие:"
    echo "1) Показать статистику кэша"
    echo "2) Очистить весь кэш"
    echo "3) Очистить устаревшие файлы"
    echo "4) Тестировать производительность"
    echo "5) Выход"
    
    read -p "Выберите вариант (1-5): " choice
    
    case $choice in
        1)
            show_cache_stats
            ;;
        2)
            clear_cache
            show_cache_stats
            ;;
        3)
            cleanup_expired
            show_cache_stats
            ;;
        4)
            test_performance
            ;;
        5)
            echo "👋 До свидания!"
            exit 0
            ;;
        *)
            echo "❌ Неверный выбор"
            ;;
    esac
done
