/**
 * Индикатор загрузки для медленных страниц
 */
class LoadingIndicator {
    constructor() {
        this.isVisible = false;
        this.startTime = null;
        this.init();
    }

    init() {
        // Создаем элемент индикатора
        this.createIndicator();
        
        // Показываем индикатор при переходе на медленные страницы
        this.setupPageTransitions();
        
        // Скрываем индикатор когда страница загружена
        this.hideOnPageLoad();
    }

    createIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'loading-indicator';
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
                    </div>
                </div>
            </div>
        `;

        // Добавляем стили
        const style = document.createElement('style');
        style.textContent = `
            #loading-indicator {
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
            
            .spinner-border {
                width: 3rem;
                height: 3rem;
            }
        `;

        document.head.appendChild(style);
        document.body.appendChild(indicator);
        
        this.element = indicator;
        this.progressBar = indicator.querySelector('.progress-bar');
        this.timeElement = indicator.querySelector('.loading-time');
        this.messageElement = indicator.querySelector('.loading-message');
    }

    setupPageTransitions() {
        // Список медленных страниц
        const slowPages = [
            '/network/cloudflare',
            '/services',
            '/packages'
        ];

        // Показываем индикатор при клике на ссылки к медленным страницам
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link) {
                const href = link.getAttribute('href');
                if (slowPages.some(page => href === page)) {
                    this.show(href);
                }
            }
        });

        // Показываем индикатор при прямом переходе на медленные страницы
        if (slowPages.some(page => window.location.pathname === page)) {
            this.show(window.location.pathname);
        }
    }

    show(pagePath = '') {
        if (this.isVisible) return;

        this.isVisible = true;
        this.startTime = Date.now();
        this.element.style.display = 'flex';

        // Устанавливаем сообщение в зависимости от страницы
        const messages = {
            '/network/cloudflare': 'Получение данных от Cloudflare API...',
            '/services': 'Проверка статуса системных сервисов...',
            '/packages': 'Загрузка списка пакетов...'
        };

        this.messageElement.textContent = messages[pagePath] || 'Загрузка данных...';

        // Запускаем анимацию прогресса
        this.startProgressAnimation();
        
        // Запускаем обновление времени
        this.updateTime();
    }

    hide() {
        if (!this.isVisible) return;

        this.isVisible = false;
        this.element.style.display = 'none';
        this.stopProgressAnimation();
    }

    startProgressAnimation() {
        let progress = 0;
        this.progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            
            this.progressBar.style.width = progress + '%';
        }, 200);
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
        // Скрываем индикатор когда страница полностью загружена
        window.addEventListener('load', () => {
            setTimeout(() => this.hide(), 500);
        });

        // Скрываем индикатор при навигации
        window.addEventListener('beforeunload', () => {
            this.hide();
        });
    }

    // Метод для программного показа/скрытия
    static show(pagePath = '') {
        if (!window.loadingIndicator) {
            window.loadingIndicator = new LoadingIndicator();
        }
        window.loadingIndicator.show(pagePath);
    }

    static hide() {
        if (window.loadingIndicator) {
            window.loadingIndicator.hide();
        }
    }
}

// Инициализируем индикатор при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    window.loadingIndicator = new LoadingIndicator();
});

// Экспортируем для использования в других скриптах
window.LoadingIndicator = LoadingIndicator;
