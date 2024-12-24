<?php
session_start();

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'quiz_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Если викторина завершена, то сохраняем результат
    if (isset($_SESSION['quiz_completed']) && $_SESSION['quiz_completed']) {
        // Получаем только 5 вопросов для подсчета правильных ответов
        $stmt = $pdo->query("SELECT id, correct_answer FROM questions LIMIT 5");
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $userAnswers = $_SESSION['answers'] ?? [];
        $correctAnswers = 0;

        // Подсчёт правильных ответов
        foreach ($questions as $question) {
            $id = $question['id'];
            $correctAnswer = $question['correct_answer'];

            if (isset($userAnswers[$id]) && $userAnswers[$id] === $correctAnswer) {
                $correctAnswers++;
            }
        }

        // Запись результата в базу данных
        $stmt = $pdo->prepare("INSERT INTO results (score) VALUES (?)");
        $stmt->execute([$correctAnswers]);

        // Запоминаем последний результат в сессии
        $_SESSION['last_score'] = $correctAnswers;

        // Сбрасываем флаг завершённости викторины для возможности прохождения заново
        unset($_SESSION['quiz_completed']);
    }

    // Получаем все результаты из базы данных для построения распределения
    $stmt = $pdo->query("SELECT score, COUNT(*) as count FROM results GROUP BY score");
    $resultsDistribution = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Подготовка данных для графика
    $chartData = [];
    // Перебор возможных результатов от 0 до 5 (в викторине всего 5 вопросов)
    for ($i = 0; $i <= 5; $i++) {
        $chartData[] = [
            'label' => "$i правильных ответов",
            'count' => $resultsDistribution[$i] ?? 0
        ];
    }

} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Ваш результат: <?php echo $_SESSION['last_score'] ?? 0; ?> правильных ответов</h1>

    <canvas id="resultsChart" width="400" height="200"></canvas>
    <script>
        const ctx = document.getElementById('resultsChart').getContext('2d');
        const chartData = <?php echo json_encode($chartData); ?>;

        const labels = chartData.map(data => data.label);
        const counts = chartData.map(data => data.count);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Количество участников',
                    data: counts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Количество: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
