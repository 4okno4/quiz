<?php
session_start();

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'quiz_db';

try {
    // Подключение к базе данных
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Инициализация вопросов при первом заходе на страницу
    if (!isset($_SESSION['questions']) || empty($_SESSION['questions'])) {
        $stmt = $pdo->query("SELECT * FROM questions ORDER BY RAND() LIMIT 5");
        $_SESSION['questions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['answers'] = [];
    }

    // Извлечение текущего вопроса
    $questions = $_SESSION['questions'];
    $currentQuestion = current($questions);

    // Обработка ответа пользователя
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $answer = $_POST['answer'] ?? null;
        $_SESSION['answers'][$currentQuestion['id']] = $answer;

        // Удаление текущего вопроса и переход к следующему
        array_shift($_SESSION['questions']);

        if (empty($_SESSION['questions'])) {
            // Викторина завершена, сохраняем результат
            $correctAnswers = 0;
            foreach ($_SESSION['answers'] as $questionId => $userAnswer) {
                $stmt = $pdo->prepare("SELECT correct_answer FROM questions WHERE id = ?");
                $stmt->execute([$questionId]);
                $question = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($question && $userAnswer === $question['correct_answer']) {
                    $correctAnswers++;
                }
            }

            // Запись результата в таблицу результатов
            $stmt = $pdo->prepare("INSERT INTO results (score) VALUES (?)");
            $stmt->execute([$correctAnswers]);

            // Сохранение результата в сессии
            $_SESSION['last_score'] = $correctAnswers;
            $_SESSION['quiz_completed'] = true;

            // Перенаправление на страницу результатов
            header('Location: results.php');
            exit;
        } else {
            $currentQuestion = current($_SESSION['questions']);
        }
    }
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Викторина</title>
</head>
<body>
    <?php if ($currentQuestion): ?>
        <form method="POST">
            <p><?php echo htmlspecialchars($currentQuestion['question']); ?></p>
            <?php
            // Перемешиваем ответы и показываем только 4 варианта
            $answers = [
                $currentQuestion['answer1'],
                $currentQuestion['answer2'],
                $currentQuestion['answer3'],
                $currentQuestion['answer4']
            ];

            shuffle($answers); // Перемешиваем массив с вариантами ответов
            foreach ($answers as $answer): ?>
                <label>
                    <input type="radio" name="answer" value="<?php echo htmlspecialchars($answer); ?>" required>
                    <?php echo htmlspecialchars($answer); ?>
                </label><br>
            <?php endforeach; ?>
            <button type="submit">Далее</button>
        </form>
    <?php else: ?>
        <p>Все вопросы пройдены.</p>
    <?php endif; ?>
</body>
</html>
