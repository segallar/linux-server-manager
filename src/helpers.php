<?php

use App\Core\GitVersion;

/**
 * Получить версию из Git для использования в шаблонах
 */
function getGitVersion(): string
{
    return GitVersion::getVersion();
}

/**
 * Получить полную информацию о версии
 */
function getGitFullVersion(): string
{
    return GitVersion::getFullVersion();
}

/**
 * Получить хеш коммита
 */
function getGitCommitHash(): string
{
    return GitVersion::getCommitHash();
}

/**
 * Получить дату коммита
 */
function getGitCommitDate(): string
{
    return GitVersion::getCommitDate();
}

/**
 * Проверить, есть ли несохраненные изменения
 */
function hasGitUncommittedChanges(): bool
{
    return GitVersion::hasUncommittedChanges();
}

/**
 * Получить ветку
 */
function getGitBranch(): string
{
    return GitVersion::getBranch();
}

/**
 * Получить время выполнения страницы
 */
function getPageExecutionTime(): string
{
    global $app;
    if (isset($app)) {
        return $app->getFormattedExecutionTime();
    }
    return 'unknown';
}

/**
 * Получить время выполнения в секундах
 */
function getPageExecutionTimeSeconds(): float
{
    global $app;
    if (isset($app)) {
        return $app->getExecutionTime();
    }
    return 0.0;
}
