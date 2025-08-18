<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\Package\PackageService;

class PackageController extends Controller
{
    public function index()
    {
        global $app;
        $cache = $app->cache;
        
        // Пытаемся получить данные из кэша
        $cacheKey = 'packages_data';
        $cachedData = $cache->get($cacheKey);
        
        if ($cachedData !== null) {
            // Используем кэшированные данные
            $stats = $cachedData['stats'];
            $upgradable = $cachedData['upgradable'];
            $unused = $cachedData['unused'];
            $fromCache = true;
        } else {
            try {
                $packageService = new PackageService();
                
                // Получаем данные с обработкой ошибок
                $stats = $this->getPackageStatsSafely($packageService);
                $upgradable = $this->getUpgradablePackagesSafely($packageService);
                $unused = $this->getUnusedPackagesSafely($packageService);
                
                // Сохраняем в кэш на 3 минуты
                $cache->set($cacheKey, [
                    'stats' => $stats,
                    'upgradable' => $upgradable,
                    'unused' => $unused
                ], 180);
                
                $fromCache = false;
            } catch (\Exception $e) {
                // В случае ошибки возвращаем пустые данные
                $stats = [
                    'total_installed' => 0,
                    'upgradable' => 0,
                    'security_updates' => 0,
                    'last_update' => 'Неизвестно'
                ];
                $upgradable = [];
                $unused = [];
                $fromCache = false;
            }
        }

        return $this->render('packages', [
            'title' => 'Управление пакетами',
            'currentPage' => 'packages',
            'stats' => $stats,
            'upgradable' => $upgradable,
            'unused' => $unused,
            'fromCache' => $fromCache
        ]);
    }

    private function getPackageStatsSafely(PackageService $packageService): array
    {
        try {
            return $packageService->getPackageStats();
        } catch (\Exception $e) {
            return [
                'total_installed' => 0,
                'upgradable' => 0,
                'security_updates' => 0,
                'last_update' => 'Неизвестно'
            ];
        }
    }

    private function getUpgradablePackagesSafely(PackageService $packageService): array
    {
        try {
            return $packageService->getUpgradablePackages();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getUnusedPackagesSafely(PackageService $packageService): array
    {
        try {
            return $packageService->getUnusedPackages();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function update()
    {
        try {
            $packageService = new PackageService();
            $result = $packageService->updatePackageList();
            
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка обновления: ' . $e->getMessage()
            ]);
        }
    }

    public function upgradeAll()
    {
        try {
            $packageService = new PackageService();
            $result = $packageService->upgradeAllPackages();
            
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка обновления: ' . $e->getMessage()
            ]);
        }
    }

    public function upgradePackage()
    {
        try {
            $packageName = $this->request->get('package');
            if (!$packageName) {
                return $this->json(['success' => false, 'message' => 'Не указан пакет']);
            }

            $packageService = new PackageService();
            $result = $packageService->upgradePackage($packageName);
            
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка обновления пакета: ' . $e->getMessage()
            ]);
        }
    }

    public function cleanCache()
    {
        try {
            $packageService = new PackageService();
            $result = $packageService->cleanPackageCache();
            
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка очистки кэша: ' . $e->getMessage()
            ]);
        }
    }

    public function autoremove()
    {
        try {
            $packageService = new PackageService();
            $result = $packageService->autoremovePackages();
            
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка удаления пакетов: ' . $e->getMessage()
            ]);
        }
    }

    public function getPackageInfo()
    {
        try {
            $packageName = $this->request->get('package');
            if (!$packageName) {
                return $this->json(['success' => false, 'message' => 'Не указан пакет']);
            }

            $packageService = new PackageService();
            $info = $packageService->getPackageInfo($packageName);
            
            if ($info) {
                return $this->json(['success' => true, 'data' => $info]);
            } else {
                return $this->json(['success' => false, 'message' => 'Пакет не найден']);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка получения информации о пакете: ' . $e->getMessage()
            ]);
        }
    }
}
