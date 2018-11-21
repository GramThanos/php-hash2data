<?php
    // Include the PHP-Hash2Data library
    include(dirname(__FILE__) . '/../php-hash2data.php');

    // Start or Resume a session
    session_start();

    // Init list of hashes and data
    $map = new Hash2Data();

    $message = false;

    // If form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // If hash exists
        if ($map->exists($_POST['hash'])) {

            // Get answer
            $answer = $map->load($_POST['hash']);
            // Delete it
            $map->delete($_POST['hash']);

            // Check server client's answer
            if (strcmp($_POST['answer'], $answer + '') == 0) {
                $message = 'Correct answer!';
            }
            else {
                $message = 'Wrong answer!';
            }

        }

        // Hash expired or invalid
        else {
            $message = 'Error!';
        }

    }


    // Random question
    $a = rand (1, 9);
    $b = rand (1, 9);
    $answer = $a + $b;

    // Save data on the session
    $hash = $map->save($answer);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>PHP-Hash2Data Example</title>
    </head>
    <body>

        <form method="POST">
            <input type="hidden" name="hash" value="<?=htmlspecialchars($hash);?>"/>
            Solve the math:<br>
            <?=htmlspecialchars($a);?> + <?=htmlspecialchars($b);?> = <input type="number" name="answer" value=""/><input type="submit" value="Submit"/>
        </form>
        <br>
        <?php if ($message) echo $message; ?>

    </body>
</html>
