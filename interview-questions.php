<?php
// Database connection
$host = 'localhost';
$db = 'campus_network1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Handle AJAX requests for company-specific data
if (isset($_GET['company'])) {
    $company = $_GET['company'];

    // Fetch round types and questions for the selected company
    $stmt = $pdo->prepare("SELECT round_types, questions FROM interview_questions WHERE company_name = :company");
    $stmt->execute(['company' => $company]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    if ($result) {
        foreach ($result as $row) {
            $round = trim($row['round_types']);
            $question = trim($row['questions']);

            if (!isset($data[$round])) {
                $data[$round] = [];
            }
            $data[$round][] = $question;
        }
    }

    echo json_encode($data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Questions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 20px;
        }

        h1 {
            text-align: center;
        }

        #company-buttons, #round-buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        #questions {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 10px 0;
            background: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
    <script>
        // Fetch and display rounds and questions for a company
        function showQuestions(company) {
            fetch(`interview-questions.php?company=${encodeURIComponent(company)}`)
                .then(response => response.json())
                .then(data => {
                    const questionsDiv = document.getElementById('questions');
                    questionsDiv.innerHTML = ''; // Clear existing questions

                    if (Object.keys(data).length > 0) {
                        for (const round in data) {
                            const roundDiv = document.createElement('div');
                            const roundTitle = document.createElement('h3');
                            roundTitle.textContent = `Round: ${round}`;
                            roundDiv.appendChild(roundTitle);

                            const questionList = document.createElement('ul');
                            data[round].forEach(question => {
                                const questionItem = document.createElement('li');
                                questionItem.textContent = question;
                                questionList.appendChild(questionItem);
                            });
                            roundDiv.appendChild(questionList);

                            questionsDiv.appendChild(roundDiv);
                        }
                    } else {
                        questionsDiv.innerHTML = '<p>No data available for this company.</p>';
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }
    </script>
</head>
<body>
    <h1>Interview Questions</h1>

    <!-- Company Buttons -->
    <div id="company-buttons">
        <?php
        // Fetch and display company buttons
        $stmt = $pdo->query("SELECT DISTINCT company_name FROM interview_questions");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<button onclick="showQuestions(\'' . htmlspecialchars($row['company_name']) . '\')">' . htmlspecialchars($row['company_name']) . '</button>';
        }
        ?>
    </div>

    <!-- Questions -->
    <div id="questions">
        <p>Select a company to view questions by round.</p>
    </div>
</body>
</html>
