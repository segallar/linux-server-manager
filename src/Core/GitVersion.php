<?php

namespace App\Core;

class GitVersion
{
    private static $version = null;
    private static $commitHash = null;
    private static $commitDate = null;

    /**
     * Получить версию из Git
     */
    public static function getVersion(): string
    {
        if (self::$version === null) {
            self::$version = self::extractVersion();
        }
        return self::$version;
    }

    /**
     * Получить хеш коммита
     */
    public static function getCommitHash(): string
    {
        if (self::$commitHash === null) {
            self::$commitHash = self::extractCommitHash();
        }
        return self::$commitHash;
    }

    /**
     * Получить дату коммита
     */
    public static function getCommitDate(): string
    {
        if (self::$commitDate === null) {
            self::$commitDate = self::extractCommitDate();
        }
        return self::$commitDate;
    }

    /**
     * Получить полную информацию о версии
     */
    public static function getFullVersion(): string
    {
        $version = self::getVersion();
        $hash = self::getCommitHash();
        $date = self::getCommitDate();
        
        return "v{$version} ({$hash} - {$date})";
    }

    /**
     * Извлечь версию из Git тегов
     */
    private static function extractVersion(): string
    {
        $rootPath = dirname(dirname(__DIR__));
        
        // Пытаемся получить последний тег
        $tag = self::executeGitCommand($rootPath, 'describe --tags --abbrev=0 2>/dev/null');
        
        if ($tag) {
            // Убираем 'v' из начала тега если есть
            return ltrim($tag, 'v');
        }
        
        // Если нет тегов, используем количество коммитов
        $commitCount = self::executeGitCommand($rootPath, 'rev-list --count HEAD');
        if ($commitCount) {
            return "0.0.{$commitCount}";
        }
        
        return '0.0.0';
    }

    /**
     * Извлечь хеш коммита
     */
    private static function extractCommitHash(): string
    {
        $rootPath = dirname(dirname(__DIR__));
        $hash = self::executeGitCommand($rootPath, 'rev-parse --short HEAD');
        
        return $hash ?: 'unknown';
    }

    /**
     * Извлечь дату коммита
     */
    private static function extractCommitDate(): string
    {
        $rootPath = dirname(dirname(__DIR__));
        $date = self::executeGitCommand($rootPath, 'log -1 --format=%cd --date=short');
        
        return $date ?: date('Y-m-d');
    }

    /**
     * Выполнить Git команду
     */
    private static function executeGitCommand(string $path, string $command): ?string
    {
        if (!is_dir($path . '/.git')) {
            return null;
        }
        
        $output = [];
        $returnCode = 0;
        
        exec("cd {$path} && git {$command} 2>/dev/null", $output, $returnCode);
        
        if ($returnCode === 0 && !empty($output)) {
            return trim($output[0]);
        }
        
        return null;
    }

    /**
     * Проверить, есть ли несохраненные изменения
     */
    public static function hasUncommittedChanges(): bool
    {
        $rootPath = dirname(dirname(__DIR__));
        
        if (!is_dir($rootPath . '/.git')) {
            return false;
        }
        
        $output = [];
        exec("cd {$rootPath} && git status --porcelain 2>/dev/null", $output);
        
        return !empty($output);
    }

    /**
     * Получить ветку
     */
    public static function getBranch(): string
    {
        $rootPath = dirname(dirname(__DIR__));
        $branch = self::executeGitCommand($rootPath, 'rev-parse --abbrev-ref HEAD');
        
        return $branch ?: 'unknown';
    }
}
