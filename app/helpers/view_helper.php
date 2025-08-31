<?php
/**
 * View Helper Functions
 * Функции-помощники для шаблонов.
 */

/**
 * "Умная" функция для отображения основного контента страницы.
 * Анализирует глобальный контекст и решает, какой шаблон загрузить.
 * - Если задан $GLOBALS['page_data'], рендерит шаблон страницы из /app/views/pages/.
 * - Если задан $GLOBALS['view_name'], рендерит указанный шаблон из /app/views/.
 * - В крайнем случае, для совместимости, вернет $GLOBALS['content'].
 * @return string HTML-код основного контента.
 */
function get_content() {
    // Приоритет 1: Рендеринг статической страницы
    if (isset($GLOBALS['page_data'])) {
        $page_data = $GLOBALS['page_data'];
        $template_name = $page_data['template'] ?? 'default';
        $template_path = ROOT . "/app/views/pages/{$template_name}.php";

        if (file_exists($template_path)) {
            // Делаем данные страницы доступными как переменные внутри шаблона
            extract($page_data);
            $page = $page_data; // А также как единый объект $page

            ob_start();
            require $template_path;
            return ob_get_clean();
        } else {
            // Запасной вариант, если файл шаблона страницы не найден
            return "<!-- Ошибка: шаблон страницы не найден: {$template_path} -->";
        }
    } 
    // Приоритет 2: Рендеринг обычного представления (блог, главная и т.д.)
    else if (isset($GLOBALS['view_name'])) {
        $view_name = $GLOBALS['view_name'];
        $view_data = $GLOBALS['view_data'] ?? [];
        $template_path = ROOT . "/app/views/{$view_name}.php";

        if (file_exists($template_path)) {
            // Делаем данные из контроллера доступными как переменные
            extract($view_data);

            ob_start();
            require $template_path;
            return ob_get_clean();
        } else {
            return "<!-- Ошибка: файл представления не найден: {$template_path} -->";
        }
    }
    // Приоритет 3: Совместимость со старым методом (если вдруг где-то остался)
    else if (isset($GLOBALS['content'])) {
        return $GLOBALS['content'];
    }

    return '<!-- Не найден контент для отображения -->';
}

/**
 * Возвращает название сайта из настроек.
 * @param string $default Название по умолчанию, если не найдено в настройках.
 * @return string Название сайта.
 */
function get_site_name($default = 'Мой сайт') {
    global $settings;
    
    // Получаем название из настроек, если оно есть
    $site_name = $settings['general']['site_name'] ?? $default;
    
    return htmlspecialchars($site_name);
}