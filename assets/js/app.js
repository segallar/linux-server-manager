// Основной JavaScript файл приложения
// jQuery уже подключен через CDN в layout.php

$(document).ready(function() {
    console.log('jQuery подключен и готов к работе!');
    
    // Инициализация бокового меню
    initSidebar();
    
    // Инициализация подменю
    initSubmenus();
    
    // Инициализация активных ссылок
    initActiveLinks();
    
    // Функция для обновления данных в реальном времени
    function updateSystemInfo() {
        $.ajax({
            url: '/api/system-info',
            method: 'GET',
            success: function(data) {
                // Обновляем информацию о системе
                $('#cpu-usage').text(data.cpu + '%');
                $('#memory-usage').text(data.memory + '%');
                $('#disk-usage').text(data.disk + '%');
            },
            error: function(xhr, status, error) {
                console.error('Ошибка при получении данных:', error);
            }
        });
    }
    
    // Обновляем данные каждые 5 секунд (если есть элементы для обновления)
    if ($('#cpu-usage').length > 0) {
        setInterval(updateSystemInfo, 5000);
    }
    
    // Обработка форм с AJAX
    $('form[data-ajax="true"]').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var url = form.attr('action');
        var method = form.attr('method') || 'POST';
        var data = form.serialize();
        
        $.ajax({
            url: url,
            method: method,
            data: data,
            success: function(response) {
                if (response.success) {
                    // Показываем сообщение об успехе
                    showAlert('success', response.message || 'Операция выполнена успешно');
                } else {
                    // Показываем сообщение об ошибке
                    showAlert('danger', response.message || 'Произошла ошибка');
                }
            },
            error: function(xhr, status, error) {
                showAlert('danger', 'Произошла ошибка при выполнении запроса');
            }
        });
    });
    
    // Функция для показа уведомлений
    function showAlert(type, message) {
        var alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.main-content').prepend(alertHtml);
        
        // Автоматически скрываем уведомление через 5 секунд
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    // Глобальная функция для показа уведомлений
    window.showAlert = showAlert;
    
    // Инициализация бокового меню
    function initSidebar() {
        // Переключение бокового меню на мобильных устройствах
        $('#sidebarToggle').on('click', function() {
            $('#sidebar').toggleClass('show');
        });
        
        // Закрытие меню при клике вне его на мобильных устройствах
        $(document).on('click', function(e) {
            if ($(window).width() <= 768) {
                if (!$(e.target).closest('#sidebar, #sidebarToggle').length) {
                    $('#sidebar').removeClass('show');
                }
            }
        });
        
        // Обработка изменения размера окна
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                $('#sidebar').removeClass('show');
            }
        });
    }
    
    // Инициализация подменю
    function initSubmenus() {
        // Обработка кликов по пунктам с подменю
        $('.sidebar-menu .nav-link[data-bs-toggle="collapse"]').on('click', function(e) {
            e.preventDefault();
            
            var target = $($(this).data('bs-target'));
            var icon = $(this).find('.fa-chevron-down');
            
            // Переключаем подменю
            target.toggleClass('show');
            
            // Поворачиваем иконку
            icon.toggleClass('fa-rotate-180');
        });
    }
    
    // Инициализация активных ссылок
    function initActiveLinks() {
        // Определяем текущую страницу
        var currentPath = window.location.pathname;
        
        // Убираем активный класс со всех ссылок
        $('.sidebar-menu .nav-link').removeClass('active');
        
        // Добавляем активный класс к текущей ссылке
        $('.sidebar-menu .nav-link').each(function() {
            var href = $(this).attr('href');
            if (href === currentPath) {
                $(this).addClass('active');
            }
        });
        
        // Обработка кликов по ссылкам в боковом меню
        $('.sidebar-menu .nav-link:not([data-bs-toggle="collapse"])').on('click', function() {
            $('.sidebar-menu .nav-link').removeClass('active');
            $(this).addClass('active');
        });
    }
    
    // Анимации для карточек статистики
    $('.stats-card').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
    
    // Инициализация тултипов Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Инициализация поповеров Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
