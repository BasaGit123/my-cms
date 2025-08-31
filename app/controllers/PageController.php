<?php

// Подключаем базовый контроллер
require_once ROOT . '/app/controllers/Controller.php';

/**
 * Контроллер для отображения статических страниц
 * Обрабатывает публичные запросы к страницам
 */
class PageController extends Controller
{
    /**
     * Отображает страницу по её slug
     * @param string $slug - уникальный идентификатор страницы
     */
    public function index($slug = null)
    {
        // Если slug не указан, возвращаем 404
        if (!$slug) {
            http_response_code(404);
            $GLOBALS['view_name'] = 'blog/404';
            $this->view();
            return;
        }

        // Загружаем модель для работы со страницами
        $pageModel = $this->model('Page');
        
        // Ищем страницу по slug
        $page = $pageModel->findBySlug($slug);
        
        // Делаем данные страницы глобально доступными для хелперов
        $GLOBALS['page_data'] = $page;
        
        // Проверяем, найдена ли страница
        if (!$page) {
            http_response_code(404);
            $GLOBALS['view_name'] = 'blog/404';
            $this->view();
            return;
        }
        
        // Проверяем статус страницы
        switch ($page['status']) {
            case 'published':
                // Опубликованная страница - отображаем
                break;
                
            case 'hidden':
                // Скрытая страница - возвращаем 404
                http_response_code(404);
                $GLOBALS['view_name'] = 'blog/404';
                $this->view();
                return;
                
            case 'draft':
                // Черновик - доступен только для админов
                if (!isset($_SESSION['user'])) {
                    http_response_code(404);
                    $GLOBALS['view_name'] = 'blog/404';
                    $this->view();
                    return;
                }
                break;
                
            default:
                // Неизвестный статус - возвращаем 404
                http_response_code(404);
                $GLOBALS['view_name'] = 'blog/404';
                $this->view();
                return;
        }

        // Подготавливаем и устанавливаем глобальные SEO-переменные
        $seoModel = $this->model('Seo');
        $seoData = $seoModel->generateForPage($page);
        $seoViewData = $this->prepareSeoData($seoData);
        $GLOBALS['page_title'] = $seoViewData['page_title'];
        $GLOBALS['page_description'] = $seoViewData['page_description'];

        // Просто вызываем основной макет.
        // get_content() сам найдет и отобразит нужный шаблон страницы.
        $this->view();
    }
}