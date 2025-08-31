<?php

/**
 * Базовая модель
 * Содержит общую логику для работы с JSON файлами.
 */
class Model
{
    protected $dataFile; // Путь к файлу с данными
    protected $data;     // Массив с данными

    /**
     * Конструктор загружает данные из файла.
     * @param string $filePath - относительный путь к файлу данных (например, 'data/posts.json')
     */
    public function __construct($filePath)
    {
        // Формируем полный путь к файлу, используя константу ROOT из index.php
        $this->dataFile = ROOT . '/' . $filePath;

        // Проверяем, существует ли файл, и загружаем данные
        if (file_exists($this->dataFile)) {
            $json = file_get_contents($this->dataFile);
            // true в json_decode преобразует объекты в ассоциативные массивы
            $this->data = json_decode($json, true);
        } else {
            // Если файл не найден, инициализируем пустой массив
            // Это предотвратит ошибки, если файл данных еще не создан
            $this->data = [];
        }
    }

    /**
     * Возвращает все данные из файла.
     * @return array
     */
    public function getAll()
    {
        return $this->data;
    }

    /**
     * Находит запись по уникальному идентификатору (ID).
     * @param int $id
     * @return array|null - возвращает запись в виде массива или null, если ничего не найдено
     */
    public function findById($id)
    {
        foreach ($this->data as $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                return $item;
            }
        }
        return null; // Возвращаем null, если элемент не найден
    }

    /**
     * Сохраняет текущее состояние данных ($this->data) в JSON файл.
     * @return bool - true в случае успеха, false в случае ошибки
     */
    protected function save()
    {
        // JSON_PRETTY_PRINT делает файл читаемым для человека, добавляя отступы
        // JSON_UNESCAPED_UNICODE необходим для корректного сохранения кириллических символов
        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Записываем данные в файл
        if (file_put_contents($this->dataFile, $json)) {
            return true;
        }
        return false;
    }
}