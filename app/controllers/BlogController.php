<?php

// Подключаем базовый контроллер
require_once ROOT . '/app/controllers/Controller.php';

/**
 * Контроллер для блога
 * Отвечает за отображение списка постов, категорий и отдельных постов
 */
class BlogController extends Controller
{
    /**
     * Показывает список всех опубликованных постов блога
     */
    public function index()
    {
        // Загружаем модель для работы с блогом
        $blogModel = $this->model('Blog');
        
        // Получаем все опубликованные посты
        $posts = $blogModel->getPublishedPosts();
        
        // Подготавливаем SEO-данные
        $seoModel = $this->model('Seo');
        $seoData = $seoModel->generateForBlogPage();
        $seoViewData = $this->prepareSeoData($seoData);
        $GLOBALS['page_title'] = $seoViewData['page_title'];
        $GLOBALS['page_description'] = $seoViewData['page_description'];

        // Устанавливаем глобальные переменные для get_content()
        $GLOBALS['view_name'] = 'blog/index';
        $GLOBALS['view_data'] = ['posts' => $posts];
        
        // Вызываем основной макет
        $this->view();
    }

    /**
     * Показывает отдельный пост по его slug
     * @param string $slug - уникальный идентификатор поста в URL
     */
    public function post($slug)
    {
        if (!$slug) {
            header("Location: /blog");
            exit();
        }

        $blogModel = $this->model('Blog');
        $post = $blogModel->getPostBySlug($slug);
        
        if ($post && $post['status'] !== 'published' && !isset($_SESSION['user'])) {
            $post = null;
        }

        if (!$post) {
            http_response_code(404);
            $GLOBALS['view_name'] = 'blog/404';
            $this->view();
            return;
        }
        
        // Подготавливаем SEO-данные
        $seoModel = $this->model('Seo');
        $seoData = $seoModel->generateForPost($post);
        $seoViewData = $this->prepareSeoData($seoData);
        $GLOBALS['page_title'] = $seoViewData['page_title'];
        $GLOBALS['page_description'] = $seoViewData['page_description'];

        // Устанавливаем глобальные переменные для get_content()
        $GLOBALS['view_name'] = 'blog/post';
        $GLOBALS['view_data'] = ['post' => $post];
        
        $this->view();
    }

    /**
     * Показывает посты определенной категории
     * @param string $categorySlug - slug категории
     */
    public function category($categorySlug)
    {
        if (!$categorySlug) {
            header("Location: /blog");
            exit();
        }

        $blogModel = $this->model('Blog');
        $posts = $blogModel->getPostsByCategory($categorySlug);
        
        if (empty($posts)) {
            http_response_code(404);
            $GLOBALS['view_name'] = 'blog/404';
            $this->view();
            return;
        }

        $category = [
            'name' => ucfirst(str_replace('-', ' ', $categorySlug)),
            'slug' => $categorySlug
        ];
        
        // Подготавливаем SEO-данные
        $seoModel = $this->model('Seo');
        $seoData = $seoModel->generateForCategory($category);
        $seoViewData = $this->prepareSeoData($seoData);
        $GLOBALS['page_title'] = $seoViewData['page_title'];
        $GLOBALS['page_description'] = $seoViewData['page_description'];

        // Устанавливаем глобальные переменные для get_content()
        $GLOBALS['view_name'] = 'blog/category';
        $GLOBALS['view_data'] = [
            'posts' => $posts,
            'category' => $category
        ];
        
        $this->view();
    }
}