<?php

// Подключаем базовый контроллер
require_once ROOT . '/app/controllers/Controller.php';

/**
 * Контроллер главной страницы
 */
class HomeController extends Controller
{
    /**
     * Метод по умолчанию. Показывает главную страницу сайта.
     */
    public function index()
    {
        // Загружаем модель для работы с постами для отображения статистики
        $blogModel = $this->model('Blog');
        
        // Получаем все опубликованные посты для показа последних записей
        $publishedPosts = $blogModel->getPublishedPosts();
        
        // Получаем дополнительные данные для статистики
        $allPosts = $blogModel->getAllPosts();
        $categories = $blogModel->getAllPostCategories();
        
        // Подготавливаем SEO-данные для главной страницы
        $seoModel = $this->model('Seo');
        $seoData = $seoModel->generateForHomePage();
        $seoViewData = $this->prepareSeoData($seoData);
        $GLOBALS['page_title'] = $seoViewData['page_title'];
        $GLOBALS['page_description'] = $seoViewData['page_description'];

        // Загружаем настройки сайта для главной страницы
        $settingsModel = $this->model('Settings');
        $settings = $settingsModel->getSettings();

        // Готовим данные для передачи в шаблон
        $viewData = [
            'publishedPosts' => $publishedPosts,
            'allPosts' => $allPosts,
            'categories' => $categories,
            'settings' => $settings  // Передаем настройки как часть данных представления
        ];

        // Устанавливаем глобальные переменные для хелпера get_content()
        $GLOBALS['view_name'] = 'home/index';
        $GLOBALS['view_data'] = $viewData;
        $GLOBALS['settings'] = $settings;
        
        // Вызываем основной макет
        $this->view();
    }
}