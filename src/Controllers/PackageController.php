<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PackageService;

class PackageController extends Controller
{
    public function index()
    {
        try {
            $packageService = new PackageService();
            
            // Получаем данные с обработкой ошибок
            $stats = $this->getPackageStatsSafely($packageService);
            $upgradable = $this->getUpgradablePackagesSafely($packageService);
            $unused = $this->getUnusedPackagesSafely($packageService);

            return $this->render('packages', [
                'title' => 'Управление пакетами',
                'currentPage' => 'packages',
                'stats' => $stats,
                'upgradable' => $upgradable,
                'unused' => $unused
            ]);
        } catch (\Exception $e) {
            // В случае ошибки возвращаем пустые данные
            return $this->render('packages', [
                'title' => 'Управление пакетами',
                'currentPage' => 'packages',
                'stats' => [
                    'total_installed' => 0,
                    'upgradable' => 0,
                    'security_updates' => 0,
                    'last_update' => 'Неизвестно'
                ],
                'upgradable' => [],
                'unused' => [],
                'error' => 'Ошибка загрузки данных пакетов: ' . $e->getMessage()
            ]);
        }
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
