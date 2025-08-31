<?php
/**
 * Template Helper Functions
 * Функции для подключения частей шаблона (header, footer).
 */

/**
 * Загружает и возвращает HTML-код для шапки сайта.
 * - Приоритет 1: Имя, переданное в функцию (например, 'admin' для get_header('admin')).
 * - Приоритет 2: Шаблон, указанный в настройках страницы (в поле header_template).
 * - Приоритет 3: Шаблон, указанный в настройках главной страницы.
 * - Приоритет 4: Шаблон по умолчанию (header.php).
 *
 * @param string|null $name Название специфического шаблона шапки.
 * @return string HTML-код шапки.
 */
function get_header($name = null) {
    global $settings, $page_title, $page_description, $view_name;
    
    $template_name = '';

    // 1. Прямое указание имени (например, для админки) имеет высший приоритет.
    if (!is_null($name)) {
        $template_name = "header-{$name}";
    } else {
        // 2. Проверяем, задан ли для страницы индивидуальный шаблон хедера.
        $page_specific_header = $GLOBALS['page_data']['header_template'] ?? null;

        if ($page_specific_header && $page_specific_header !== 'default') {
            $template_name = "header-{$page_specific_header}";
        } 
        // 3. Для главной страницы проверяем настройки главной страницы
        elseif ($view_name === 'home/index' && isset($settings['homepage']['header_template'])) {
            error_log("DEBUG: get_header - view_name: " . $view_name);
            error_log("DEBUG: get_header - settings['homepage']['header_template']: " . ($settings['homepage']['header_template'] ?? 'N/A'));
            $homepage_header = $settings['homepage']['header_template'];
            if ($homepage_header && $homepage_header !== 'default') {
                $template_name = "header-{$homepage_header}";
            } else {
                $template_name = 'header';
            }
            error_log("DEBUG: get_header - final template_name: " . $template_name);
        } else {
            // 4. Если нет, используем хедер по умолчанию.
            $template_name = 'header';
        }
    }

    $template_path = ROOT . "/app/views/partials/{$template_name}.php";

    if (file_exists($template_path)) {
        ob_start();
        require $template_path;
        return ob_get_clean();
    }

    // Если указанный шаблон не найден, пытаемся использовать шаблон по умолчанию
    $default_template_path = ROOT . "/app/views/partials/header.php";
    if (file_exists($default_template_path)) {
        ob_start();
        require $default_template_path;
        return ob_get_clean();
    }

    return "<!-- Template part not found: {$template_path} -->";
}

/**
 * Загружает и возвращает HTML-код для подвала сайта.
 * Логика аналогична get_header().
 *
 * @param string|null $name Название специфического шаблона подвала.
 * @return string HTML-код подвала.
 */
function get_footer($name = null) {
    global $settings, $view_name;
    
    $template_name = '';

    // 1. Прямое указание имени.
    if (!is_null($name)) {
        $template_name = "footer-{$name}";
    } else {
        // 2. Индивидуальный шаблон из настроек страницы.
        $page_specific_footer = $GLOBALS['page_data']['footer_template'] ?? null;

        if ($page_specific_footer && $page_specific_footer !== 'default') {
            $template_name = "footer-{$page_specific_footer}";
        } 
        // 3. Для главной страницы проверяем настройки главной страницы
        elseif ($view_name === 'home/index' && isset($settings['homepage']['footer_template'])) {
            error_log("DEBUG: get_footer - view_name: " . $view_name);
            error_log("DEBUG: get_footer - settings['homepage']['footer_template']: " . ($settings['homepage']['footer_template'] ?? 'N/A'));
            $homepage_footer = $settings['homepage']['footer_template'];
            if ($homepage_footer && $homepage_footer !== 'default') {
                $template_name = "footer-{$homepage_footer}";
            } else {
                $template_name = 'footer';
            }
            error_log("DEBUG: get_footer - final template_name: " . $template_name);
        } else {
            // 4. Подвал по умолчанию.
            $template_name = 'footer';
        }
    }

    $template_path = ROOT . "/app/views/partials/{$template_name}.php";

    if (file_exists($template_path)) {
        ob_start();
        require $template_path;
        return ob_get_clean();
    }

    // Если указанный шаблон не найден, пытаемся использовать шаблон по умолчанию
    $default_template_path = ROOT . "/app/views/partials/footer.php";
    if (file_exists($default_template_path)) {
        ob_start();
        require $default_template_path;
        return ob_get_clean();
    }

    return "<!-- Template part not found: {$template_path} -->";
}