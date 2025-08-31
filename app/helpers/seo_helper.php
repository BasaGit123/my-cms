<?php
/**
 * SEO Helper Functions
 * Provides simple functions for getting SEO titles and descriptions in templates
 */

/**
 * Получает SEO заголовок для текущей страницы
 * @param string $default Заголовок по умолчанию
 * @return string SEO заголовок страницы
 */
function get_title($default = '') {
    // Комментарий: Используем глобальные переменные, которые устанавливаются в контроллере и шаблоне.
    global $page_title, $settings;
    
    // Если есть специфический заголовок для страницы, используем его.
    if (!empty($page_title)) {
        return htmlspecialchars($page_title);
    }
    
    // Проверяем, что массив настроек существует
    if (empty($settings)) {
        return htmlspecialchars($default);
    }
    
    // Иначе используем заголовок по умолчанию из настроек
    if (!empty($settings['seo']['default_title'])) {
        return htmlspecialchars($settings['seo']['default_title']);
    }
    
    // Резервный вариант - название сайта
    if (!empty($settings['general']['site_name'])) {
        return htmlspecialchars($settings['general']['site_name']);
    }
    
    // Последний резервный вариант
    return htmlspecialchars($default);
}

/**
 * Получает SEO описание для текущей страницы
 * @param string $default Описание по умолчанию
 * @return string SEO описание страницы
 */
function get_description($default = '') {
    // Комментарий: Используем глобальные переменные.
    global $page_description, $settings;
    
    // Если есть специфическое описание для страницы, используем его.
    if (!empty($page_description)) {
        $description = mb_substr($page_description, 0, 160);
        return htmlspecialchars($description);
    }
    
    // Проверяем, что массив настроек существует
    if (empty($settings)) {
        return htmlspecialchars($default);
    }
    
    // Иначе используем описание по умолчанию из настроек
    if (!empty($settings['seo']['default_description'])) {
        $description = mb_substr($settings['seo']['default_description'], 0, 160);
        return htmlspecialchars($description);
    }
    
    // Резервный вариант - описание сайта
    if (!empty($settings['general']['site_description'])) {
        $description = mb_substr($settings['general']['site_description'], 0, 160);
        return htmlspecialchars($description);
    }
    
    // Последний резервный вариант
    return htmlspecialchars($default);
}

/**
 * Генерирует мета-тег robots для текущей страницы
 * @return string Мета-тег robots
 */
function get_robots() {
    // Комментарий: Используем глобальную переменную $page_data, которая должна быть установлена в контроллере.
    global $page_data;

    // Значение по умолчанию, если страница не определена или нет настройки
    $content = 'all';

    // Проверяем, есть ли у страницы настройка индексации
    if (isset($page_data) && !empty($page_data['robots'])) {
        $allowed_values = ['all', 'noindex, follow', 'noindex, nofollow'];
        if (in_array($page_data['robots'], $allowed_values, true)) {
            $content = $page_data['robots'];
        }
    }

    // Если есть GET-параметры в URL, устанавливаем noindex, follow
    if (!empty($_SERVER['QUERY_STRING'])) {
        $content = 'noindex, follow';
    }

    // Возвращаем готовый мета-тег
    return '<meta name="robots" content="' . htmlspecialchars($content) . '">';
}

/**
 * Генерирует все основные мета-теги для SEO (title, description, robots, Open Graph, Canonical)
 * для более удобного использования в шаблонах.
 * @return string HTML-строка с мета-тегами
 */
function get_meta() {
    global $page_data, $view_data; // Добавляем глобальные переменные для доступа к данным страницы/поста/категории

    $output = '';
    $output .= '<title>' . get_title() . '</title>' . PHP_EOL;
    $output .= '<meta name="description" content="' . get_description() . '">' . PHP_EOL;
    $output .= get_robots() . PHP_EOL;

    // Определяем базовый URL
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

    // Определяем канонический URL и URL для Open Graph
    $current_path = strtok($_SERVER['REQUEST_URI'], '?'); // Получаем путь без параметров запроса
    $canonical_url = $base_url . $current_path;

    // Если это статическая страница
    if (isset($page_data) && !empty($page_data['slug'])) {
        $canonical_url = $base_url . '/' . htmlspecialchars($page_data['slug']);
    } 
    // Если это отдельный пост блога
    elseif (isset($view_data['post']) && !empty($view_data['post']['slug'])) {
        $canonical_url = $base_url . '/blog/' . htmlspecialchars($view_data['post']['slug']);
    }
    // Если это страница категории блога
    elseif (isset($view_data['category']) && !empty($view_data['category']['slug'])) {
        $canonical_url = $base_url . '/blog/category/' . htmlspecialchars($view_data['category']['slug']);
    }

    // Open Graph мета-теги
    $output .= '<meta property="og:title" content="' . htmlspecialchars(get_title()) . '">' . PHP_EOL; // Open Graph Title
    $output .= '<meta property="og:description" content="' . htmlspecialchars(get_description()) . '">' . PHP_EOL; // Open Graph Description
    $output .= '<meta property="og:url" content="' . htmlspecialchars($canonical_url) . '">' . PHP_EOL; // Используем канонический URL для og:url
    $output .= '<meta property="og:type" content="website">' . PHP_EOL; // Тип контента по умолчанию
    $output .= '<meta property="og:site_name" content="' . htmlspecialchars(get_site_name()) . '">' . PHP_EOL; // Название сайта
    $output .= '<meta property="og:locale" content="ru_RU">' . PHP_EOL; // Локаль Open Graph

    // Канонический URL
    $output .= '<link rel="canonical" href="' . htmlspecialchars($canonical_url) . '">' . PHP_EOL;

    return $output;
}
