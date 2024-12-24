<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'quiz_db';

try {
    // Подключение к MySQL
    $pdo = new PDO("mysql:host=$host", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание базы данных
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");

    // Создание таблицы вопросов
    $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        answer1 VARCHAR(255) NOT NULL,
        answer2 VARCHAR(255) NOT NULL,
        answer3 VARCHAR(255) NOT NULL,
        answer4 VARCHAR(255) NOT NULL,
        answer5 VARCHAR(255) NOT NULL,
        answer6 VARCHAR(255) NOT NULL,
        correct_answer VARCHAR(255) NOT NULL
    )");

    // Создание таблицы результатов
    $pdo->exec("CREATE TABLE IF NOT EXISTS results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        score INT NOT NULL
    )");

    // Заполнение таблицы вопросов
    $questions = [
        ['Что такое HTML?', 'Язык программирования', 'Языка разметки гипертекстов', 'Платформа для разработки приложений', 'Система управления базами данных', 'Язык для создания стилей', 'Протокол передачи данных', 'Языка разметки гипертекстов'],
        ['Какой из следующих языков является языком программирования, который часто используется для создания интерактивных элементов веб-страниц?', 'CSS', 'HTML', 'JavaScript', 'SQL', 'XML', 'PHP', 'JavaScript'],
        ['Что такое CSS?', 'Язык для сертификации систем безопасности', 'Язык-разметки', 'Язык для оформления внешнего вида документов HTML', 'Язык для работы с базами данных', 'Язык для создания API', 'Язык программирования', 'Язык для оформления внешнего вида документов HTML'],
        ['Какой метод отправки данных на сервер с использованием формы на веб-странице чаще всего применяется?', 'GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'GET'],
        ['Какой из следующих типов баз данных часто используется в веб-программировании?', 'MongoDB', 'Markdown', 'JDBC', 'HTML5', 'XML', 'FTP', 'MongoDB'],
        ['Какой из следующих протоколов используется для передачи данных по вебу?', 'FTP', 'HTTP', 'SMTP', 'TCP', 'UDP', 'SSH', 'HTTP'],
        ['Какой из следующих языков часто используется для разработки серверной части вэб-приложений?', 'JavaScript', 'HTML', 'CSS', 'PHP', 'Markdown', 'XML', 'PHP']
    ];

    $stmt = $pdo->prepare("INSERT INTO questions (question, answer1, answer2, answer3, answer4, answer5, answer6, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($questions as $q) {
        $stmt->execute($q);
    }

    echo "База данных и таблицы успешно созданы!";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
