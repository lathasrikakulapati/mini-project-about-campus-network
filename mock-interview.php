<?php
// Database connection
include('db_connection.php');

// Start the session to access user data like email
session_start();

// Handle form submission (quiz submission)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $score = 0;

    // Fetch questions
    $query = "SELECT * FROM mock_questions";
    $result = $conn->query($query);

    if ($result === false) {
        // Handle query failure
        die("Error fetching questions: " . $conn->error);
    }

    while ($question = $result->fetch_assoc()) {
        $questionId = $question['id'];
        $correctAnswer = $question['answer'];
        $userAnswer = isset($_POST["question_$questionId"]) ? $_POST["question_$questionId"] : '';

        if ($userAnswer === $correctAnswer) {
            $score++;
        }
    }

    // Show the score
    echo "<h2>Your Score: $score / " . $result->num_rows . "</h2>";

    // Ensure user email is available in the session
    if (!isset($_SESSION['user_email'])) {
        // Handle error: User not logged in
        echo "Error: User is not logged in.";
        exit();
    }
    
    // Get user email from session (this assumes the user is logged in)
    $userEmail = $_SESSION['user_email']; // Email from session

    // Insert score into the database
    $date = date('Y-m-d'); // Get current date
    $insertQuery = "INSERT INTO quiz_scores (user_email, score, date) VALUES (?, ?, ?)";
    
    // Prepare and execute the query
    if ($stmt = $conn->prepare($insertQuery)) {
        $stmt->bind_param("sis", $userEmail, $score, $date); // "sis" means string, integer, string
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo "Score saved successfully!";
        } else {
            echo "Failed to save score.";
        }
        
        $stmt->close();
    } else {
        // Handle query preparation failure
        echo "Error: Unable to prepare query.";
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <style>
        /* Global Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            text-align: center;
            color: #4c6ef5;
        }

        h1 {
            margin-top: 30px;
        }

        /* Container for the quiz */
        .quiz-container {
            width: 80%;
            max-width: 900px;
            margin: 30px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Style the questions */
        .question {
            margin-bottom: 20px;
        }

        .question h3 {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 10px;
        }

        .options-container {
            display: flex;
            flex-direction: column;
            margin-left: 20px;
        }

        .options-container label {
            margin-bottom: 8px;
            font-size: 1.1rem;
            color: #555;
        }

        .options-container input {
            margin-right: 10px;
        }

        /* Submit button */
        .submit-btn {
            background-color: #4c6ef5;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: block;
            margin: 20px auto;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #3b56d3;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .quiz-container {
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <h1>Take the Quiz</h1>

    <div class="quiz-container">
        <form method="POST" action="mock-interview.php">
            <?php
            // Fetch questions from the database
            $query = "SELECT * FROM mock_questions";
            $result = $conn->query($query);
            while ($question = $result->fetch_assoc()):
            ?>
                <div class="question">
                    <h3><?php echo htmlspecialchars($question['question']); ?></h3>
                    <div class="options-container">
                        <label>
                            <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo htmlspecialchars($question['option1']); ?>" required>
                            <?php echo htmlspecialchars($question['option1']); ?>
                        </label>
                        <label>
                            <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo htmlspecialchars($question['option2']); ?>">
                            <?php echo htmlspecialchars($question['option2']); ?>
                        </label>
                        <label>
                            <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo htmlspecialchars($question['option3']); ?>">
                            <?php echo htmlspecialchars($question['option3']); ?>
                        </label>
                        <label>
                            <input type="radio" name="question_<?php echo $question['id']; ?>" value="<?php echo htmlspecialchars($question['option4']); ?>">
                            <?php echo htmlspecialchars($question['option4']); ?>
                        </label>
                    </div>
                </div>
            <?php endwhile; ?>
            <button type="submit" class="submit-btn">Submit Quiz</button>
        </form>
    </div>

</body>
</html>
