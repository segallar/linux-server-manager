// Основной JavaScript файл для Linux Server Manager

$(document).ready(function() {
    // Инициализация всех компонентов
    initSidebar();
    initSubmenus();
    initActiveLinks();
    initTooltips();
    initModals();
    
    // Автообновление данных (отключено до создания API)
    // if (typeof updateSystemInfo === 'function') {
    //     updateSystemInfo();
    //     setInterval(updateSystemInfo, 5000);
    // }
});

// Инициализация бокового меню
function initSidebar() {
    // Переключение мобильного меню
    $('.navbar-toggler').on('click', function() {
        $('.sidebar').toggleClass('show');
    });
    
    // Закрытие меню при клике вне его
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.sidebar, .navbar-toggler').length) {
            $('.sidebar').removeClass('show');
        }
    });
}

// Инициализация подменю
function initSubmenus() {
    $('.submenu-toggle').on('click', function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        $(target).collapse('toggle');
    });
}

// Инициализация активных ссылок
function initActiveLinks() {
    const currentPath = window.location.pathname;
    
    $('.nav-link').each(function() {
        const href = $(this).attr('href');
        if (href && currentPath === href) {
            $(this).addClass('active');
            $(this).closest('.submenu').addClass('show');
        }
    });
}

// Инициализация тултипов
function initTooltips() {
    $('[data-bs-toggle="tooltip"]').tooltip();
}

// Инициализация модальных окон
function initModals() {
    // Автоматическое закрытие модальных окон при успешном действии
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form').trigger('reset');
        $(this).find('.alert').remove();
    });
}

// Функция показа уведомлений
function showAlert(message, type = 'info', duration = 5000) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Добавляем уведомление в начало основного контента
    $('.main-content').prepend(alertHtml);
    
    // Автоматически скрываем через указанное время
    if (duration > 0) {
        setTimeout(function() {
            $('.alert').first().fadeOut();
        }, duration);
    }
}

// Функция для AJAX запросов
function makeAjaxRequest(url, method = 'GET', data = null) {
    return $.ajax({
        url: url,
        method: method,
        data: data,
        dataType: 'json',
        timeout: 10000
    }).fail(function(xhr, status, error) {
        console.error('AJAX Error:', error);
        showAlert('Ошибка при выполнении запроса', 'danger');
    });
}

// Функция обновления системной информации (отключена до создания API)
function updateSystemInfo() {
    // makeAjaxRequest('/api/system/info').done(function(data) {
    //     if (data.success) {
    //         updateSystemStats(data.data);
    //     }
    // });
    console.log('updateSystemInfo: API не реализован');
}

// Функция обновления статистики системы
function updateSystemStats(stats) {
    if (stats.cpu) $('#cpu-usage').text(stats.cpu + '%');
    if (stats.memory) $('#ram-usage').text(stats.memory + '%');
    if (stats.disk) $('#disk-usage').text(stats.disk + '%');
    if (stats.network) $('#network-status').text(stats.network);
}

// Функции для WireGuard (отключены до создания API)
function viewInterface(interfaceName) {
    showAlert('Функция просмотра интерфейса будет доступна позже', 'info');
    // makeAjaxRequest(`/api/wireguard/interface/${interfaceName}`).done(function(data) {
    //     if (data.success) {
    //         $('#interface-name').text(interfaceName);
    //         $('#interface-details').html(data.html);
    //         $('#interfaceDetailModal').modal('show');
    //     }
    // });
}

function upInterface(interfaceName) {
    if (confirm('Запустить интерфейс ' + interfaceName + '?')) {
        showAlert('Функция запуска интерфейса будет доступна позже', 'info');
        // makeAjaxRequest(`/api/wireguard/interface/${interfaceName}/up`, 'POST').done(function(data) {
        //     if (data.success) {
        //         showAlert('Интерфейс запущен', 'success');
        //         setTimeout(() => location.reload(), 1000);
        //     }
        // });
    }
}

function downInterface(interfaceName) {
    if (confirm('Остановить интерфейс ' + interfaceName + '?')) {
        showAlert('Функция остановки интерфейса будет доступна позже', 'info');
        // makeAjaxRequest(`/api/wireguard/interface/${interfaceName}/down`, 'POST').done(function(data) {
        //     if (data.success) {
        //         showAlert('Интерфейс остановлен', 'success');
        //         setTimeout(() => location.reload(), 1000);
        //     }
        // });
    }
}

function restartInterface(interfaceName) {
    if (confirm('Перезапустить интерфейс ' + interfaceName + '?')) {
        showAlert('Функция перезапуска интерфейса будет доступна позже', 'info');
        // makeAjaxRequest(`/api/wireguard/interface/${interfaceName}/restart`, 'POST').done(function(data) {
        //     if (data.success) {
        //         showAlert('Интерфейс перезапущен', 'success');
        //         setTimeout(() => location.reload(), 1000);
        //     }
        // });
    }
}

function editInterface(interfaceName) {
    showAlert('Функция редактирования интерфейса будет доступна позже', 'info');
    // makeAjaxRequest(`/api/wireguard/interface/${interfaceName}/config`).done(function(data) {
    //     if (data.success) {
    //         $('#edit-interface-name').val(interfaceName);
    //         $('#edit-interface-config').val(data.config);
    //         $('#editInterfaceModal').modal('show');
    //     }
    // });
}

// Функции для форм
function handleFormSubmit(form, successCallback = null) {
    form.on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const url = $(this).attr('action');
        const method = $(this).attr('method') || 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert(response.message || 'Операция выполнена успешно', 'success');
                    if (successCallback) successCallback(response);
                } else {
                    showAlert(response.message || 'Произошла ошибка', 'danger');
                }
            },
            error: function() {
                showAlert('Ошибка при выполнении запроса', 'danger');
            }
        });
    });
}

// Функция для обновления страницы
function refreshPage() {
    location.reload();
}

// Функция для экспорта данных
function exportData(type, data) {
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `export-${type}-${new Date().toISOString().split('T')[0]}.json`;
    a.click();
    window.URL.revokeObjectURL(url);
}

// Функция для копирования в буфер обмена
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showAlert('Скопировано в буфер обмена', 'success', 2000);
    }).catch(function() {
        showAlert('Ошибка при копировании', 'danger');
    });
}

// Функция для форматирования байтов
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Функция для форматирования времени
function formatUptime(seconds) {
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    
    let result = '';
    if (days > 0) result += days + ' дн. ';
    if (hours > 0) result += hours + ' ч. ';
    if (minutes > 0) result += minutes + ' мин.';
    
    return result.trim();
}

// Глобальные функции для использования в HTML
window.showAlert = showAlert;
window.refreshPage = refreshPage;
window.copyToClipboard = copyToClipboard;
window.formatBytes = formatBytes;
window.formatUptime = formatUptime;
