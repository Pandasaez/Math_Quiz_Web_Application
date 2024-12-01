<!-- Jasper Saez -->
<!-- MATH QUIZ WEB APPLICATION -->

<?php
session_start();

// Check if the user has submitted the settings form
if (isset($_POST['save_settings'])) {
    // Save settings to session
    $_SESSION['settings']['level'] = $_POST['level'];
    $_SESSION['settings']['operator'] = $_POST['operator'];
    $_SESSION['settings']['items'] = $_POST['items'];
    $_SESSION['settings']['max_difference'] = $_POST['max_difference'];

    // Setting the range base of the Level Selected by the user
    if ($_SESSION['settings']['level'] == 1) {
        $_SESSION['settings']['range'] = [1, 10]; 
    } elseif ($_SESSION['settings']['level'] == 2) {
        $_SESSION['settings']['range'] = [11, 100];  
    } else {
        $_SESSION['settings']['range'] = [$_POST['range_min'], $_POST['range_max']];
    }

}

// if the user has clicked the "Start Quiz" button
if (isset($_POST['start_quiz'])) {
    // Initialize the quiz results
    $_SESSION['results'] = ['correct' => 0, 'wrong' => 0];
    $_SESSION['quiz'] = []; // Initialize the quiz array

    // Generate questions based on the user's settings
    for ($i = 0; $i < $_SESSION['settings']['items']; $i++) {
        $num1 = rand($_SESSION['settings']['range'][0], $_SESSION['settings']['range'][1]);
        $num2 = rand($_SESSION['settings']['range'][0], $_SESSION['settings']['range'][1]);
        $correct = $num1 + $num2; // Correct answer for addition

        // Generates the 3 random WRONG answers for the user which 1 correct in total of 4 options
        $choices = [$correct];
        while (count($choices) < 4) {
            $choice = $correct + rand(-$_SESSION['settings']['max_difference'], $_SESSION['settings']['max_difference']);
            if (!in_array($choice, $choices)) {
                $choices[] = $choice;
            }
        }
        shuffle($choices); // Randomize or shuffle the choices

        // Add the question to the session
        $_SESSION['quiz'][] = ['num1' => $num1, 'num2' => $num2, 'correct' => $correct, 'choices' => $choices];
    }

    // Set the first question as the current question
    $_SESSION['current_question'] = 0;
        
    // Redirect to the main page to display the first question
    header("Location: index.php");
    exit;
}

// Check if the user has answered a question
if (isset($_POST['answer'])) {
    $correct = $_SESSION['quiz'][$_SESSION['current_question']]['correct'];
    $answer = $_POST['answer'];

    // Check if the answer is correct
    if ($answer == $correct) {
        $_SESSION['results']['correct']++; // Increment correct count
    } else {
        $_SESSION['results']['wrong']++; // Increment wrong count
    }

    // Move to the next question
    $_SESSION['current_question']++;

    // Check if all questions have been answered
    if ($_SESSION['current_question'] >= count($_SESSION['quiz'])) {
        // Redirect to the result page if quiz is complete
        header("Location: index.php?result=true");
        exit;
    }
}

// Check if the quiz results are to be displayed
if (isset($_GET['result'])) {
    $total_questions = count($_SESSION['quiz']);
    $correct_answers = $_SESSION['results']['correct'];
    $wrong_answers = $_SESSION['results']['wrong'];
    
    // Calculate grade as a percentage
    $grade = ($correct_answers / $total_questions) * 100;

    // Display the results
    echo "<h1>Quiz Completed</h1>";
    echo "<p>Correct: $correct_answers</p>";
    echo "<p>Wrong: $wrong_answers</p>";
    echo "<p>Grade: " . number_format($grade, 1) . "%</p>";
    echo "<a href='index.php'>Restart Quiz</a>";
    exit;
}

// Retrieve the current question
$question = $_SESSION['quiz'][$_SESSION['current_question']] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Math Quiz</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Math Quiz</h1>

    <?php if ($question): ?>
        <div class="card">
            <div class="card-header">
                <h2>Question <?= $_SESSION['current_question'] + 1 ?></h2>
            </div>
            <div class="card-body">
                <!-- Display the question -->
                <p class="lead"><?= $question['num1'] ?> + <?= $question['num2'] ?> = ?</p>
                <form method="post">
                    <!-- Display the answer choices as buttons -->
                    <div class="btn-group-vertical w-100" role="group">
                        <?php foreach ($question['choices'] as $choice): ?>
                            <button type="submit" name="answer" value="<?= $choice ?>" class="btn btn-primary mb-2"><?= $choice ?></button>
                        <?php endforeach; ?>
                    </div>
                </form>
                <!-- Display the current score -->
                <p class="mt-3">Score: Correct <?= $_SESSION['results']['correct'] ?> | Wrong <?= $_SESSION['results']['wrong'] ?></p>
            </div>
        </div>
    <?php else: ?>
        <!-- Display the "Start Quiz" button if no questions are being displayed -->
        <div class="card">
            <div class="card-body text-center">
                <form method="post">
                    <button type="submit" name="start_quiz" class="btn btn-success">Start Quiz</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Settings section for configuring quiz -->
    <div class="card mt-4">
        <div class="card-header">
            <h3>Settings</h3>
        </div>
        <div class="card-body">
            <form method="post">
                <!-- Level settings -->
                <fieldset class="form-group">
                    <legend>Level</legend>
                    <div class="form-check">
                        <input type="radio" name="level" value="1" class="form-check-input" <?= $_SESSION['settings']['level'] == 1 ? 'checked' : '' ?>>
                        <label class="form-check-label">Level 1 (1-10)</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="level" value="2" class="form-check-input" <?= $_SESSION['settings']['level'] == 2 ? 'checked' : '' ?>>
                        <label class="form-check-label">Level 2 (11-100)</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="level" value="3" class="form-check-input" <?= $_SESSION['settings']['level'] == 3 ? 'checked' : '' ?>>
                        <label class="form-check-label">Custom Level</label>
                    </div>
                </fieldset>

                <!-- Operator settings -->
                <fieldset class="form-group">
                    <legend>Operator</legend>
                    <div class="form-check">
                        <input type="radio" name="operator" value="+" class="form-check-input" <?= $_SESSION['settings']['operator'] == '+' ? 'checked' : '' ?>>
                        <label class="form-check-label">Addition</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="operator" value="-" class="form-check-input" <?= $_SESSION['settings']['operator'] == '-' ? 'checked' : '' ?>>
                        <label class="form-check-label">Subtraction</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" name="operator" value="*" class="form-check-input" <?= $_SESSION['settings']['operator'] == '*' ? 'checked' : '' ?>>
                        <label class="form-check-label">Multiplication</label>
                    </div>
                </fieldset>

                <!-- Number of items and max difference inputs -->
                <div class="form-group">
                    <label for="items">Number of Items:</label>
                    <input type="number" name="items" value="<?= $_SESSION['settings']['items'] ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="max_difference">Max Difference:</label>
                    <input type="number" name="max_difference" value="<?= $_SESSION['settings']['max_difference'] ?>" class="form-control">
                </div>

                <!-- Range settings for custom level -->
                <div class="form-group">
                    <label for="range_min">Range Min:</label>
                    <input type="number" name="range_min" value="<?= $_SESSION['settings']['range'][0] ?>" class="form-control">
                </div>

                <div class="form-group">
                    <label for="range_max">Range Max:</label>
                    <input type="number" name="range_max" value="<?= $_SESSION['settings']['range'][1] ?>" class="form-control">
                </div>

                <button type="submit" name="save_settings" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
