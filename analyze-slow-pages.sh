#!/bin/bash

# Скрипт для детального анализа медленных страниц

echo "🔍 Детальный анализ медленных страниц"
echo "===================================="

BASE_URL="http://sirocco.romansegalla.online:81"

# Список медленных страниц для анализа
SLOW_PAGES=(
    "/network/cloudflare"
    "/services"
    "/packages"
)

echo "Анализирую медленные страницы..."
echo ""

for page in "${SLOW_PAGES[@]}"; do
    page_name=$(echo "$page" | sed 's|^/||' | sed 's|/| → |g')
    
    echo "📊 Анализ: $page_name"
    echo "URL: $BASE_URL$page"
    echo "----------------------------------------"
    
    # Тест 1: Время загрузки
    echo -n "⏱️  Время загрузки: "
    start_time=$(date +%s%N)
    http_code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL$page")
    end_time=$(date +%s%N)
    load_time_ms=$(( (end_time - start_time) / 1000000 ))
    
    if [ $load_time_ms -lt 1000 ]; then
        echo "${load_time_ms} ms"
    else
        load_time_s=$((load_time_ms / 1000))
        echo "${load_time_s}.$((load_time_ms % 1000))s"
    fi
    
    # Тест 2: Размер ответа
    echo -n "📏 Размер ответа: "
    size=$(curl -s -o /dev/null -w "%{size_download}" "$BASE_URL$page")
    if [ $size -lt 1024 ]; then
        echo "${size} B"
    elif [ $size -lt 1048576 ]; then
        size_kb=$((size / 1024))
        echo "${size_kb} KB"
    else
        size_mb=$((size / 1048576))
        echo "${size_mb} MB"
    fi
    
    # Тест 3: Время до первого байта
    echo -n "🚀 Время до первого байта: "
    ttfb=$(curl -s -o /dev/null -w "%{time_starttransfer}" "$BASE_URL$page")
    ttfb_ms=$(echo "$ttfb * 1000" | bc 2>/dev/null | cut -d. -f1)
    if [ -n "$ttfb_ms" ] && [ "$ttfb_ms" -lt 1000 ]; then
        echo "${ttfb_ms} ms"
    else
        echo "${ttfb}s"
    fi
    
    # Тест 4: Время DNS
    echo -n "🌐 Время DNS: "
    dns_time=$(curl -s -o /dev/null -w "%{time_namelookup}" "$BASE_URL$page")
    dns_ms=$(echo "$dns_time * 1000" | bc 2>/dev/null | cut -d. -f1)
    if [ -n "$dns_ms" ] && [ "$dns_ms" -lt 1000 ]; then
        echo "${dns_ms} ms"
    else
        echo "${dns_time}s"
    fi
    
    # Тест 5: Время подключения
    echo -n "🔌 Время подключения: "
    connect_time=$(curl -s -o /dev/null -w "%{time_connect}" "$BASE_URL$page")
    connect_ms=$(echo "$connect_time * 1000" | bc 2>/dev/null | cut -d. -f1)
    if [ -n "$connect_ms" ] && [ "$connect_ms" -lt 1000 ]; then
        echo "${connect_ms} ms"
    else
        echo "${connect_time}s"
    fi
    
    # Тест 6: Время обработки сервером
    echo -n "⚙️  Время обработки сервером: "
    server_time=$(echo "$ttfb - $connect_time" | bc 2>/dev/null)
    if [ -n "$server_time" ]; then
        server_ms=$(echo "$server_time * 1000" | bc 2>/dev/null | cut -d. -f1)
        if [ -n "$server_ms" ] && [ "$server_ms" -lt 1000 ]; then
            echo "${server_ms} ms"
        else
            echo "${server_time}s"
        fi
    else
        echo "неизвестно"
    fi
    
    echo ""
    
    # Рекомендации для конкретной страницы
    case "$page" in
        "/network/cloudflare")
            echo "💡 Рекомендации для Cloudflare:"
            echo "   - Проверить API вызовы к Cloudflare"
            echo "   - Добавить кэширование результатов"
            echo "   - Оптимизировать запросы к DNS"
            echo "   - Рассмотреть асинхронную загрузку"
            ;;
        "/services")
            echo "💡 Рекомендации для Services:"
            echo "   - Кэшировать статус сервисов"
            echo "   - Оптимизировать системные вызовы"
            echo "   - Использовать параллельные запросы"
            echo "   - Добавить индикатор загрузки"
            ;;
        "/packages")
            echo "💡 Рекомендации для Packages:"
            echo "   - Кэшировать список пакетов"
            echo "   - Оптимизировать запросы к apt/dpkg"
            echo "   - Использовать фоновое обновление"
            echo "   - Добавить пагинацию для больших списков"
            ;;
    esac
    
    echo ""
    echo "========================================"
    echo ""
done

echo "🎯 Общие рекомендации по оптимизации:"
echo "====================================="
echo "1. Добавить кэширование для медленных операций"
echo "2. Использовать асинхронную загрузку данных"
echo "3. Оптимизировать системные вызовы"
echo "4. Добавить индикаторы загрузки"
echo "5. Рассмотреть использование очередей для тяжелых операций"
echo "6. Мониторить производительность регулярно"
