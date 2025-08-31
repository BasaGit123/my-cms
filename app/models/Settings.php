<?php

class Settings
{
    private $settingsFile;
    private $data;

    public function __construct()
    {
        $this->settingsFile = ROOT . '/data/settings.json';
        if (file_exists($this->settingsFile)) {
            $this->data = json_decode(file_get_contents($this->settingsFile), true);
        } else {
            // Значения по умолчанию, если файл по какой-то причине отсутствует
            $this->data = [
                'general' => [
                    'site_name' => 'Мой Блог',
                    'site_description' => '',
                    'posts_per_page' => 10
                ],
                'seo' => [
                    'default_title' => 'Мой Блог',
                    'default_description' => 'Простой и быстрый блог на PHP без базы данных.'
                ],
                'security' => [
                    'admin_email' => '',
                    'max_login_attempts' => 5,
                    'lockout_time_minutes' => 15
                ],
                // Добавляем секцию для настроек главной страницы
                'homepage' => [
                    'home_banner_title' => 'Добро пожаловать на наш сайт',
                    'home_banner_subtitle' => 'Лучшие решения для вашего бизнеса',
                    'home_banner_image' => '',
                    'home_show_banner' => true,
                    'featured_text' => 'Мы предлагаем лучшие услуги на рынке',
                    'show_featured' => true,
                    'header_template' => 'default',
                    'footer_template' => 'default'
                ]
            ];
        }
    }

    /**
     * Возвращает все настройки
     * @return array
     */
    public function getSettings()
    {
        return $this->data;
    }

    /**
     * Сохраняет новые настройки
     * @param array $newData
     * @return bool
     */
    public function saveSettings($newData)
    {
        // array_replace_recursive позволяет обновить только переданные поля,
        // не затрагивая остальные настройки в файле.
        $this->data = array_replace_recursive($this->data, $newData);
        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($this->settingsFile, $json) !== false;
    }
}