/**
 * Умный индикатор загрузки с кэшированием
 */
class SmartLoadingIndicator {
    constructor() {
        this.isVisible = false;
        this.startTime = null;
        this.cacheTimeout = 300000; // 5 минут
        this.init();
    }

    init() {
        this.createIndicator();
        this.setupPageTransitions();
        this.hideOnPageLoad();
        this.checkCacheStatus();
    }

    createIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'smart-loading-indicator';
        indicator.innerHTML = `
            <div class="loading-overlay">
                <div class="loading-content">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    <div class="loading-text mt-3">
                        <h5>Загрузка данных...</h5>
                        <p class="loading-message">Пожалуйста, подождите</p>
                        <div class="loading-progress">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                        <small class="loading-time text-muted">0.0s</small>
                        <div class="loading-cache-info mt-2">
                            <small class="text-info">🔄 Обновление данных</small>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Добавляем стили
        const style = document.createElement('style');
        style.textContent = `
            #smart-loading-indicator {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                z-index: 9999;
                display: none;
                align-items: center;
                justify-content: center;
            }
            
            .loading-overlay {
                background: white;
                border-radius: 10px;
                padding: 2rem;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                max-width: 400px;
                width: 90%;
            }
            
            .loading-content {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            
            .loading-text h5 {
                margin-bottom: 0.5rem;
                color: #333;
            }
            
            .loading-message {
                color: #666;
                margin-bottom: 1rem;
            }
            
            .loading-progress {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            .loading-time {
                font-family: monospace;
            }
            
            .loading-cache-info {
                font-size: 0.8rem;
            }
            
            .spinner-border {
                width: 3rem;
                height: 3rem;
            }
            
            .cache-hit {
                color: #28a745 !important;
            }
            
            .cache-miss {
                color: #dc3545 !important;
            }
        `;

        document.head.appendChild(style);
        document.body.appendChild(indicator);
        
        this.element = indicator;
        this.progressBar = indicator.querySelector('.progress-bar');
        this.timeElement = indicator.querySelector('.loading-time');
        this.messageElement = indicator.querySelector('.loading-message');
        this.cacheInfoElement = indicator.querySelector('.loading-cache-info');
    }

    setupPageTransitions() {
        const slowPages = [
            '/network/cloudflare',
            '/services',
            '/packages'
        ];

        // Показываем индикатор при клике на ссылки
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link) {
                const href = link.getAttribute('href');
                if (slowPages.some(page => href === page)) {
                    this.show(href);
                }
            }
        });

        // Показываем индикатор при прямом переходе
        if (slowPages.some(page => window.location.pathname === page)) {
            this.show(window.location.pathname);
        }
    }

    show(pagePath = '') {
        if (this.isVisible) return;

        this.isVisible = true;
        this.startTime = Date.now();
        this.element.style.display = 'flex';

        const messages = {
            '/network/cloudflare': 'Получение данных от Cloudflare API...',
            '/services': 'Проверка статуса системных сервисов...',
            '/packages': 'Загрузка списка пакетов...'
        };

        this.messageElement.textContent = messages[pagePath] || 'Загрузка данных...';

        // Проверяем кэш
        this.checkCacheForPage(pagePath);
        
        this.startProgressAnimation();
        this.updateTime();
    }

    hide() {
        if (!this.isVisible) return;

        this.isVisible = false;
        this.element.style.display = 'none';
        this.stopProgressAnimation();
    }

    checkCacheForPage(pagePath) {
        const cacheKey = `page_${pagePath.replace(/\//g, '_')}`;
        const cached = localStorage.getItem(cacheKey);
        
        if (cached) {
            const cacheData = JSON.parse(cached);
            const now = Date.now();
            
            if (now - cacheData.timestamp < this.cacheTimeout) {
                // Кэш актуален
                this.cacheInfoElement.innerHTML = '<small class="cache-hit">✅ Данные из кэша</small>';
                this.cacheInfoElement.className = 'loading-cache-info mt-2 cache-hit';
            } else {
                // Кэш устарел
                this.cacheInfoElement.innerHTML = '<small class="cache-miss">🔄 Обновление кэша</small>';
                this.cacheInfoElement.className = 'loading-cache-info mt-2 cache-miss';
            }
        } else {
            // Кэша нет
            this.cacheInfoElement.innerHTML = '<small class="cache-miss">🔄 Первая загрузка</small>';
            this.cacheInfoElement.className = 'loading-cache-info mt-2 cache-miss';
        }
    }

    checkCacheStatus() {
        // Показываем информацию о кэше в консоли
        const cacheInfo = {
            totalItems: 0,
            totalSize: 0,
            items: {}
        };

        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('page_')) {
                const value = localStorage.getItem(key);
                const size = new Blob([value]).size;
                
                cacheInfo.totalItems++;
                cacheInfo.totalSize += size;
                cacheInfo.items[key] = {
                    size: size,
                    sizeFormatted: this.formatBytes(size)
                };
            }
        }

        console.log('📊 Кэш статус:', cacheInfo);
    }

    formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    startProgressAnimation() {
        let progress = 0;
        this.progressInterval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress > 85) progress = 85;
            
            this.progressBar.style.width = progress + '%';
        }, 300);
    }

    stopProgressAnimation() {
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }
    }

    updateTime() {
        if (!this.isVisible) return;

        const elapsed = (Date.now() - this.startTime) / 1000;
        this.timeElement.textContent = elapsed.toFixed(1) + 's';

        requestAnimationFrame(() => this.updateTime());
    }

    hideOnPageLoad() {
        window.addEventListener('load', () => {
            setTimeout(() => this.hide(), 300);
        });

        window.addEventListener('beforeunload', () => {
            this.hide();
        });
    }

    // Методы для программного управления
    static show(pagePath = '') {
        if (!window.smartLoadingIndicator) {
            window.smartLoadingIndicator = new SmartLoadingIndicator();
        }
        window.smartLoadingIndicator.show(pagePath);
    }

    static hide() {
        if (window.smartLoadingIndicator) {
            window.smartLoadingIndicator.hide();
        }
    }

    static clearCache() {
        const keysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('page_')) {
                keysToRemove.push(key);
            }
        }
        
        keysToRemove.forEach(key => localStorage.removeItem(key));
        console.log('🗑️ Кэш очищен');
    }
}

// Инициализируем при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    window.smartLoadingIndicator = new SmartLoadingIndicator();
});

// Экспортируем для использования
window.SmartLoadingIndicator = SmartLoadingIndicator;
