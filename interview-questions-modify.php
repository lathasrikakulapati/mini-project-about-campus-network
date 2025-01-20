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

// Handle form submission for adding or updating an entry
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? null;
    $company = htmlspecialchars(trim($_POST['company']));
    $roundTypes = htmlspecialchars(trim($_POST['roundTypes']));
    $questions = htmlspecialchars(trim($_POST['questions']));

    if ($id) {
        // Update existing entry
        $stmt = $pdo->prepare("UPDATE interview_questions 
            SET company_name = :company, round_types = :roundTypes, questions = :questions 
            WHERE id = :id");
        $stmt->execute([
            'company' => $company,
            'roundTypes' => $roundTypes,
            'questions' => $questions,
            'id' => $id,
        ]);
        $message = "Details updated successfully!";
    } else {
        // Insert new entry
        $stmt = $pdo->prepare("INSERT INTO interview_questions (company_name, round_types, questions) 
            VALUES (:company, :roundTypes, :questions)");
        $stmt->execute([
            'company' => $company,
            'roundTypes' => $roundTypes,
            'questions' => $questions,
        ]);
        $message = "Details added successfully!";
    }
}

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Delete entry from the database
    $stmt = $pdo->prepare("DELETE FROM interview_questions WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $message = "Details deleted successfully!";
}

// Fetch all entries grouped by company and round type
$stmt = $pdo->query("SELECT company_name, round_types, questions FROM interview_questions ORDER BY company_name, round_types");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create an array indexed by round types
$questionsByRound = [];
foreach ($data as $entry) {
    $roundType = $entry['round_types'];
    $question = $entry['questions'];

    if (!isset($questionsByRound[$roundType])) {
        $questionsByRound[$roundType] = [];
    }
    $questionsByRound[$roundType][] = $question;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Interview Questions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #4caf50;
            margin-top: 50px;
        }

        .message {
            text-align: center;
            font-weight: bold;
            color: green;
            margin-top: 20px;
        }

        form {
            width: 70%;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        form input,
        form textarea,
        form button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
            background-color: #4caf50;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .details-list {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .round-group {
            margin-bottom: 20px;
        }

        .round-group h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .round-group ul {
            list-style: disc;
            padding-left: 20px;
        }

        .round-group ul li {
            margin: 5px 0;
        }
    </style>
</head>
<body>

<h1>Admin - Modify Interview Questions</h1>

<?php if (isset($message)) : ?>
    <p class="message"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="POST">
    <input type="hidden" id="id" name="id">
    <label for="company">Company Name:</label>
    <input type="text" id="company" name="company" required>

    <label for="roundTypes">Types of Rounds (e.g., Technical, HR):</label>
    <input type="text" id="roundTypes" name="roundTypes" required>

    <label for="questions">Questions Asked in the Round:</label>
    <textarea id="questions" name="questions" rows="5" required></textarea>

    <button type="submit">Save Details</button>
</form>

<div class="details-list">
    <h2>Interview Questions Grouped by Round Types</h2>
    <?php foreach ($questionsByRound as $roundType => $questions): ?>
        <div class="round-group">
            <h3><?php echo htmlspecialchars($roundType); ?></h3>
            <ul>
                <?php foreach ($questions as $question): ?>
                    <li><?php echo htmlspecialchars($question); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
