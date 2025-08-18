#!/bin/bash

# Скрипт для проверки ссылок в документации
echo "🔍 Проверка ссылок в документации..."

# Проверяем все .md файлы в docs/
for file in *.md; do
    if [ -f "$file" ]; then
        echo "📄 Проверяем файл: $file"
        
        # Ищем ссылки на другие .md файлы
        grep -o '\[.*\]([^)]*\.md)' "$file" | while read -r link; do
            # Извлекаем путь из ссылки
            path=$(echo "$link" | sed 's/.*(\([^)]*\))/\1/')
            
            # Проверяем существование файла
            if [ -f "$path" ]; then
                echo "  ✅ $link"
            else
                echo "  ❌ $link (файл не найден: $path)"
            fi
        done
    fi
done

echo "✅ Проверка завершена!"
