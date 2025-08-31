<?php

/**
 * Модель для обработки SEO-метатегов
 * Предоставляет методы для генерации SEO-данных для разных типов страниц
 */
class Seo
{
    private $settings;
    
    public function __construct()
    {
        // Загружаем настройки сайта
        require_once ROOT . '/app/models/Settings.php';
        $settingsModel = new Settings();
        $this->settings = $settingsModel->getSettings();
    }
    
    /**
     * Генерирует SEO-данные для главной страницы
     * @return array Массив с SEO-данными (title, description)
     */
    public function generateForHomePage()
    {
        return [
            'title' => $this->settings['seo']['default_title'] ?? $this->settings['general']['site_name'],
            'description' => $this->settings['seo']['default_description'] ?? $this->settings['general']['site_description']
        ];
    }
    
    /**
     * Генерирует SEO-данные для страницы блога
     * @return array Массив с SEO-данными (title, description)
     */
    public function generateForBlogPage()
    {
        return [
            'title' => 'Блог - ' . ($this->settings['general']['site_name'] ?? 'Мой сайт'),
            'description' => 'Все записи в блоге ' . ($this->settings['general']['site_name'] ?? 'моего сайта')
        ];
    }
    
    /**
     * Генерирует SEO-данные для поста
     * @param array $post Данные поста
     * @return array Массив с SEO-данными (title, description)
     */
    public function generateForPost($post)
    {
        return [
            'title' => !empty($post['seo_title']) ? $post['seo_title'] : $post['title'],
            'description' => !empty($post['seo_description']) ? $post['seo_description'] : $this->truncateText(strip_tags($post['content']), 160)
        ];
    }
    
    /**
     * Генерирует SEO-данные для категории
     * @param array $category Данные категории
     * @return array Массив с SEO-данными (title, description)
     */
    public function generateForCategory($category)
    {
        return [
            'title' => !empty($category['seo_title']) ? $category['seo_title'] : $category['name'],
            'description' => !empty($category['seo_description']) ? $category['seo_description'] : ($category['description'] ?? '')
        ];
    }
    
    /**
     * Генерирует SEO-данные для статической страницы
     * @param array $page Данные страницы
     * @return array Массив с SEO-данными (title, description)
     */
    public function generateForPage($page)
    {
        return [
            'title' => !empty($page['seo_title']) ? $page['seo_title'] : $page['title'],
            'description' => !empty($page['seo_description']) ? $page['seo_description'] : $this->truncateText(strip_tags($page['content']), 160)
        ];
    }
    
    /**
     * Обрезает текст до указанной длины
     * @param string $text Текст для обрезки
     * @param int $length Максимальная длина текста
     * @return string Обрезанный текст
     */
    private function truncateText($text, $length)
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length);
    }
    
    /**
     * Возвращает настройки сайта
     * @return array Настройки сайта
     */
    public function getSettings()
    {
        return $this->settings;
    }
}