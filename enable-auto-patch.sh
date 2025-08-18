#!/bin/bash

# Скрипт для включения автоматического увеличения patch версии

echo "🔧 Включение автоматического увеличения patch версии"
echo "=================================================="

if [ -f ".git/hooks/post-commit.disabled" ]; then
    mv .git/hooks/post-commit.disabled .git/hooks/post-commit
    chmod +x .git/hooks/post-commit
    echo "✅ Автоматическое увеличение patch версии включено"
    echo "📁 Файл перемещен в .git/hooks/post-commit"
else
    echo "ℹ️ Автоматическое увеличение patch версии уже включено"
fi

echo ""
echo "💡 Для отключения выполните:"
echo "   ./disable-auto-patch.sh"
