<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'campus_network1');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle question submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $question = $conn->real_escape_string($_POST['question']);
    $email = $_SESSION['email'];

    // Insert question into the database
    $stmt = $conn->prepare("INSERT INTO faq (question, user_email) VALUES (?, ?)");
    $stmt->bind_param("ss", $question, $email);
    if ($stmt->execute()) {
        echo "<p style='color: green;'>Question posted successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
    }
    $stmt->close();
}

// Retrieve all questions
$faq_query = "SELECT * FROM faq ORDER BY id DESC";
$faq_result = $conn->query($faq_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Page</title>
    <link rel="stylesheet" href="faqstyle.css">
    <style>
        /* Styles omitted for brevity; same as before */
        .answer-button {
            margin-top: 10px;
            padding: 5px 10px;
            font-size: 0.9rem;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .answer-button:hover {
            background-color: #0056b3;
        }
        .answers-container {
            display: none;
            margin-top: 15px;
            padding-left: 15px;
        }
    </style>
    <script>
        function toggleAnswers(id) {
            const answersContainer = document.getElementById(`answers-${id}`);
            if (answersContainer.style.display === "none") {
                answersContainer.style.display = "block";
            } else {
                answersContainer.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FAQ Section</h1>
        </div>
        <form method="POST" class="question-form">
            <textarea name="question" placeholder="Type your question here..." rows="4" cols="50" required></textarea><br>
            <button type="submit">Post Question</button>
        </form>

        <div class="faq-list">
            <?php
            if ($faq_result->num_rows > 0) {
                while ($row = $faq_result->fetch_assoc()) {
                    $faq_id = $row['id'];

                    // Get the count of answers for this question
                    $answer_count_query = "SELECT COUNT(*) AS answer_count FROM faq_answers WHERE faq_id = $faq_id";
                    $answer_count_result = $conn->query($answer_count_query);
                    $answer_count = $answer_count_result->fetch_assoc()['answer_count'];
                    ?>
                    <div class="faq-item">
                        <p class="question"><?php echo htmlspecialchars($row['question']); ?></p>
                        <p><strong><?php echo $answer_count; ?> Answers</strong></p>
                        <button class="answer-button" onclick="toggleAnswers(<?php echo $faq_id; ?>)">View Answers</button>

                        <div id="answers-<?php echo $faq_id; ?>" class="answers-container">
                            <ul class="answer-list">
                                <?php
                                $answer_query = "SELECT * FROM faq_answers WHERE faq_id = $faq_id";
                                $answer_result = $conn->query($answer_query);

                                if ($answer_result->num_rows > 0) {
                                    while ($answer_row = $answer_result->fetch_assoc()) {
                                        echo "<li><strong>" . htmlspecialchars($answer_row['user_email']) . ":</strong> " . htmlspecialchars($answer_row['answer']) . "</li>";
                                    }
                                } else {
                                    echo "<li>No answers yet.</li>";
                                }
                                ?>
                            </ul>
                        </div>

                        <!-- Answer submission form -->
                        <form method="POST" class="answer-form">
                            <textarea name="answer" placeholder="Type your answer here..." rows="2" cols="50" required></textarea><br>
                            <button type="submit" name="answer_question" value="<?php echo $faq_id; ?>">Submit Answer</button>
                        </form>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='no-questions'>No questions posted yet.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
<?php
// Handle answer submission
if (isset($_POST['answer_question'])) {
    $answer = $conn->real_escape_string($_POST['answer']);
    $faq_id = intval($_POST['answer_question']);
    $user_email = $_SESSION['email'];

    // Insert answer into the database
    $stmt = $conn->prepare("INSERT INTO faq_answers (faq_id, answer, user_email) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $faq_id, $answer, $user_email);
    if ($stmt->execute()) {
        header('Location: faq.php'); // Refresh the page to display the new answer
        exit();
    } else {
        echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
    }
    $stmt->close();
}

$conn->close();
?>
