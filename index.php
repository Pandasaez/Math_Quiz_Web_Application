<?php
session_start();

if (isset($_POST['save_settings'])) {
    $_SESSION['settings']['level'] = $_POST['level'];
    $_SESSION['settings']['operator'] = $_POST['operator'];
    $_SESSION['settings']['items'] = $_POST['items'];
    $_SESSION['settings']['max_difference'] = $_POST['max_difference'];

    // Set the level onm which one the user wants
    if ($_SESSION['settings']['level'] == 1) {
        $_SESSION['settings']['range'] = [1, 10]; 
    } elseif ($_SESSION['settings']['level'] == 2) {
        $_SESSION['settings']['range'] = [11, 100];  
    } else {
       
        $_SESSION['settings']['range'] = [$_POST['range_min'], $_POST['range_max']];
    }

    header("Location: index.php");
    exit;
}

if (isset($_POST['start_quiz'])) {
    $_SESSION['results'] = ['correct' => 0, 'wrong' => 0];
    $_SESSION['quiz'] = [];
    for ($i = 0; $i < $_SESSION['settings']['items']; $i++) {
        $num1 = rand($_SESSION['settings']['range'][0], $_SESSION['settings']['range'][1]);
        $num2 = rand($_SESSION['settings']['range'][0], $_SESSION['settings']['range'][1]);
        $correct = $num1 + $num2;

        // Generate choices
        $choices = [$correct];
        while (count($choices) < 4) {
            $choice = $correct + rand(-$_SESSION['settings']['max_difference'], $_SESSION['settings']['max_difference']);
            if (!in_array($choice, $choices)) {
                $choices[] = $choice;
            }
        }
        shuffle($choices);

        $_SESSION['quiz'][] = ['num1' => $num1, 'num2' => $num2, 'correct' => $correct, 'choices' => $choices];
    }
    $_SESSION['current_question'] = 0;
    header("Location: index.php");
    exit;
}

if (isset($_POST['answer'])) {
    $correct = $_SESSION['quiz'][$_SESSION['current_question']]['correct'];
    $answer = $_POST['answer'];
    if ($answer == $correct) {
        $_SESSION['results']['correct']++;
    } else {
        $_SESSION['results']['wrong']++;
    }
    $_SESSION['current_question']++;
    if ($_SESSION['current_question'] >= count($_SESSION['quiz'])) {
        header("Location: index.php?result=true");
        exit;
    }
}

if (isset($_GET['result'])) {
    echo "<h1>Quiz Completed</h1>";
    echo "<p>Correct: {$_SESSION['results']['correct']}</p>";
    echo "<p>Wrong: {$_SESSION['results']['wrong']}</p>";
    echo "<a href='index.php'>Restart Quiz</a>";
    exit;
}

$question = $_SESSION['quiz'][$_SESSION['current_question']] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Math Quiz</title>
</head>
<body>
<h1>Math Quiz</h1>

<?php if ($question): ?>
    <h2>Question <?= $_SESSION['current_question'] + 1 ?></h2>
    <p><?= $question['num1'] ?> + <?= $question['num2'] ?> = ?</p>
    <form method="post">
        <?php foreach ($question['choices'] as $choice): ?>
            <button type="submit" name="answer" value="<?= $choice ?>"><?= $choice ?></button>
        <?php endforeach; ?>
    </form>
    <p>Score: Correct <?= $_SESSION['results']['correct'] ?> | Wrong <?= $_SESSION['results']['wrong'] ?></p>
<?php else: ?>
    <form method="post">
        <button type="submit" name="start_quiz">Start Quiz</button>
    </form>
<?php endif; ?>

<!-- Settings -->
<div id="settings">
    <h3>Settings</h3>
    <form method="post">
        <fieldset>
            <legend>Level</legend>
            <input type="radio" name="level" value="1" <?= $_SESSION['settings']['level'] == 1 ? 'checked' : '' ?>> Level 1 (1-10)<br>
            <input type="radio" name="level" value="2" <?= $_SESSION['settings']['level'] == 2 ? 'checked' : '' ?>> Level 2 (11-100)<br>
            <input type="radio" name="level" value="3" <?= $_SESSION['settings']['level'] == 3 ? 'checked' : '' ?>> Custom Level<br>
        </fieldset>

        <fieldset>
            <legend>Operator</legend>
            <input type="radio" name="operator" value="+" <?= $_SESSION['settings']['operator'] == '+' ? 'checked' : '' ?>> Addition<br>
            <input type="radio" name="operator" value="-" <?= $_SESSION['settings']['operator'] == '-' ? 'checked' : '' ?>> Subtraction<br>
            <input type="radio" name="operator" value="*" <?= $_SESSION['settings']['operator'] == '*' ? 'checked' : '' ?>> Multiplication<br>
        </fieldset>

        <label for="items">Number of Items:</label>
        <input type="number" name="items" value="<?= $_SESSION['settings']['items'] ?>"><br>

        <label for="max_difference">Max Difference:</label>
        <input type="number" name="max_difference" value="<?= $_SESSION['settings']['max_difference'] ?>"><br>

        <label for="range_min">Range Min:</label>
        <input type="number" name="range_min" value="<?= $_SESSION['settings']['range'][0] ?>"><br>

        <label for="range_max">Range Max:</label>
        <input type="number" name="range_max" value="<?= $_SESSION['settings']['range'][1] ?>"><br>

        <button type="submit" name="save_settings">Save Settings</button>
    </form>
</div>

</body>
</html>
