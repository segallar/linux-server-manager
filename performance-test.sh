#!/bin/bash

# Скрипт для тестирования производительности страниц

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

# Функция для измерения времени загрузки
measure_load_time() {
    local url="$1"
    local page_name="$2"
    
    echo -n "Тестирую $page_name... "
    
    # Измеряем время загрузки
    start_time=$(date +%s.%N)
    http_code=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    end_time=$(date +%s.%N)
    
    # Вычисляем время в миллисекундах
    load_time=$(echo "scale=3; ($end_time - $start_time) * 1000" | bc)
    
    # Определяем статус
    if [ "$http_code" = "200" ]; then
        status="✅"
    else
        status="❌"
    fi
    
    # Форматируем время
    if (( $(echo "$load_time < 1000" | bc -l) )); then
        formatted_time="${load_time} ms"
    else
        formatted_time="$(echo "scale=1; $load_time / 1000" | bc) s"
    fi
    
    echo "$status $formatted_time (HTTP $http_code)"
    
    # Сохраняем результат
    echo "$page_name|$load_time|$http_code|$status" >> /tmp/performance_results.txt
}

# Очищаем файл результатов
> /tmp/performance_results.txt

echo "Начинаю тестирование..."
echo ""

# Тестируем каждую страницу
for page in "${PAGES[@]}"; do
    page_name=$(echo "$page" | sed 's|^/||' | sed 's|/| → |g')
    if [ "$page_name" = "" ]; then
        page_name="Главная"
    fi
    
    measure_load_time "$BASE_URL$page" "$page_name"
done

echo ""
echo "📊 Результаты тестирования:"
echo "=========================="

# Сортируем результаты по времени загрузки
sort -t'|' -k2 -n /tmp/performance_results.txt | while IFS='|' read -r page time code status; do
    if (( $(echo "$time < 1000" | bc -l) )); then
        formatted_time="${time} ms"
        performance="🟢 Быстро"
    elif (( $(echo "$time < 3000" | bc -l) )); then
        formatted_time="${time} ms"
        performance="🟡 Средне"
    else
        formatted_time="$(echo "scale=1; $time / 1000" | bc) s"
        performance="🔴 Медленно"
    fi
    
    printf "%-25s | %-10s | %s\n" "$page" "$formatted_time" "$performance"
done

echo ""
echo "📈 Статистика:"
echo "=============="

# Подсчитываем статистику
total_pages=$(wc -l < /tmp/performance_results.txt)
fast_pages=$(grep -c "🟢" /tmp/performance_results.txt 2>/dev/null || echo "0")
medium_pages=$(grep -c "🟡" /tmp/performance_results.txt 2>/dev/null || echo "0")
slow_pages=$(grep -c "🔴" /tmp/performance_results.txt 2>/dev/null || echo "0")

echo "Всего страниц: $total_pages"
echo "Быстрые (< 1s): $fast_pages"
echo "Средние (1-3s): $medium_pages"
echo "Медленные (> 3s): $slow_pages"

# Вычисляем среднее время
avg_time=$(awk -F'|' '{sum+=$2} END {print sum/NR}' /tmp/performance_results.txt 2>/dev/null || echo "0")
if (( $(echo "$avg_time < 1000" | bc -l) )); then
    avg_formatted="${avg_time} ms"
else
    avg_formatted="$(echo "scale=1; $avg_time / 1000" | bc) s"
fi

echo "Среднее время: $avg_formatted"

# Очищаем временный файл
rm -f /tmp/performance_results.txt

echo ""
echo "🎯 Рекомендации:"
echo "================"

if [ "$slow_pages" -gt 0 ]; then
    echo "⚠️  Обнаружены медленные страницы. Рекомендуется оптимизация."
fi

if [ "$fast_pages" -eq "$total_pages" ]; then
    echo "✅ Все страницы загружаются быстро!"
fi

echo "💡 Для детального анализа используйте инструменты разработчика в браузере."
