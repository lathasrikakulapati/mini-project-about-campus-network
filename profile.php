<?php
session_start();
require 'db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_email'])) {
    echo "<p>You need to <a href='login.php'>login</a> to view your profile.</p>";
    exit();
}

// Fetch user email from session
$user_email = $_SESSION['user_email'];

// Fetch user details from the database
$query = $conn->prepare("SELECT name FROM user WHERE email = ?");
$query->bind_param("s", $user_email);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_name = $user['name'];
} else {
    echo "<p>User not found. Please contact support.</p>";
    exit();
}

// Fetch user scores from the quiz_scores table
$scores_query = $conn->prepare("SELECT score, date FROM quiz_scores WHERE user_email = ?");
$scores_query->bind_param("s", $user_email);
$scores_query->execute();
$scores_result = $scores_query->get_result();

// Prepare data for the graph
$scores = [];
$dates = [];

while ($row = $scores_result->fetch_assoc()) {
    $scores[] = $row['score'];
    $dates[] = $row['date'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .profile-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .profile-container p {
            margin: 10px 0;
            font-size: 16px;
            color: #555;
        }
        .logout-button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #d9534f;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .logout-button:hover {
            background-color: #c9302c;
        }
        canvas {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<header>
        <div class="navbar">
            <img src="logo.png" alt="Campus Network Logo" class="logo">
            <nav>
                <ul>
                    <li><a href="placement-calendar.php">Placement Calendar</a></li>
                    <li><a href="interview-questions.php">Interview Questions</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                    <li><a href="feedback.php">Feedback</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
    <div class="profile-container">
        <?= htmlspecialchars($user_name) ?>!
        <p><strong>Email:</strong> <?= htmlspecialchars($user_email) ?></p>
        <a href="index.html" class="logout-button">Logout</a>
    </div>

    <div class="chart-container">
        <h3>Your Quiz Scores Over Time</h3>
        <canvas id="scoreChart"></canvas>
    </div>
    </main>
    <footer>
        <p>&copy; 2024 Campus Network. All rights reserved.</p>
    </footer>

    <script>
        // Prepare data for the graph
        const labels = <?= json_encode($dates) ?>;
        const data = {
            labels: labels,
            datasets: [{
                label: 'Quiz Scores',
                data: <?= json_encode($scores) ?>,
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        };

        const config = {
            type: 'line', // Line chart
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Quiz Scores Over Time'
                    }
                }
            }
        };

        // Render the chart
        const scoreChart = new Chart(
            document.getElementById('scoreChart'),
            config
        );
    </script>
</body>
</html>
