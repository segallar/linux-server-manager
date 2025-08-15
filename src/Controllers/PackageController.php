<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PackageService;

class PackageController extends Controller
{
    public function index()
    {
        $packageService = new PackageService();
        $stats = $packageService->getPackageStats();
        $upgradable = $packageService->getUpgradablePackages();
        $unused = $packageService->getUnusedPackages();

        return $this->render('packages', [
            'title' => 'Управление пакетами',
            'currentPage' => 'packages',
            'stats' => $stats,
            'upgradable' => $upgradable,
            'unused' => $unused
        ]);
    }

    public function update()
    {
        $packageService = new PackageService();
        $result = $packageService->updatePackageList();
        
        return $this->json($result);
    }

    public function upgradeAll()
    {
        $packageService = new PackageService();
        $result = $packageService->upgradeAllPackages();
        
        return $this->json($result);
    }

    public function upgradePackage()
    {
        $packageName = $this->request->get('package');
        if (!$packageName) {
            return $this->json(['success' => false, 'message' => 'Не указан пакет']);
        }

        $packageService = new PackageService();
        $result = $packageService->upgradePackage($packageName);
        
        return $this->json($result);
    }

    public function cleanCache()
    {
        $packageService = new PackageService();
        $result = $packageService->cleanPackageCache();
        
        return $this->json($result);
    }

    public function autoremove()
    {
        $packageService = new PackageService();
        $result = $packageService->autoremovePackages();
        
        return $this->json($result);
    }

    public function getPackageInfo()
    {
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
    }
}
