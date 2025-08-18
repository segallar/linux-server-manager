#!/bin/bash

# Скрипт для отключения автоматического увеличения patch версии

echo "🔧 Отключение автоматического увеличения patch версии"
echo "=================================================="

if [ -f ".git/hooks/post-commit" ]; then
    mv .git/hooks/post-commit .git/hooks/post-commit.disabled
    echo "✅ Автоматическое увеличение patch версии отключено"
    echo "📁 Файл перемещен в .git/hooks/post-commit.disabled"
else
    echo "ℹ️ Автоматическое увеличение patch версии уже отключено"
fi

echo ""
echo "💡 Для включения снова выполните:"
echo "   mv .git/hooks/post-commit.disabled .git/hooks/post-commit"
