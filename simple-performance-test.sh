#!/bin/bash

# Упрощенный скрипт для тестирования производительности страниц

echo "⚡ Тестирование производительности Linux Server Manager"
echo "=================================================="

# Базовый URL
BASE_URL="http://sirocco.romansegalla.online:81"

# Список страниц для тестирования
PAGES=(
    "/"
    "/system"
    "/processes"
    "/services"
    "/packages"
    "/network/ssh"
    "/network/port-forwarding"
    "/network/wireguard"
    "/network/cloudflare"
    "/network/routing"
)

echo "Начинаю тестирование..."
echo ""

# Заголовок таблицы
printf "%-25s | %-12s | %-8s | %s\n" "Страница" "Время загрузки" "HTTP" "Статус"
echo "----------------------------------------------------------------"

# Тестируем каждую страницу
for page in "${PAGES[@]}"; do
    page_name=$(echo "$page" | sed 's|^/||' | sed 's|/| → |g')
    if [ "$page_name" = "" ]; then
        page_name="Главная"
    fi
    
    echo -n "Тестирую $page_name... "
    
    # Измеряем время загрузки
    start_time=$(date +%s%N)
    http_code=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    end_time=$(date +%s%N)
    
    # Вычисляем время в миллисекундах
    load_time_ms=$(( (end_time - start_time) / 1000000 ))
    
    # Определяем статус
    if [ "$http_code" = "200" ]; then
        status="✅"
    else
        status="❌"
    fi
    
    # Форматируем время
    if [ $load_time_ms -lt 1000 ]; then
        formatted_time="${load_time_ms} ms"
        performance="🟢"
    elif [ $load_time_ms -lt 3000 ]; then
        formatted_time="${load_time_ms} ms"
        performance="🟡"
    else
        load_time_s=$((load_time_ms / 1000))
        formatted_time="${load_time_s}.$((load_time_ms % 1000))s"
        performance="🔴"
    fi
    
    printf "%-25s | %-12s | %-8s | %s\n" "$page_name" "$formatted_time" "$http_code" "$performance"
done

echo ""
echo "📊 Легенда:"
echo "🟢 Быстро (< 1s) | 🟡 Средне (1-3s) | 🔴 Медленно (> 3s)"
echo ""
echo "💡 Для детального анализа используйте инструменты разработчика в браузере."
