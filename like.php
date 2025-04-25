<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "forum";

// Skapa anslutning
$conn = new mysqli($servername, $username, $password, $dbname);

// Kontrollera anslutning
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Kontrollera om post_id är skickat
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['post_id'])) {
    $post_id = intval($_POST['post_id']);

    // Förbered SQL-sats med prepared statement
    $stmt = $conn->prepare("UPDATE posts SET likes = likes + 1 WHERE id = ?");
    $stmt->bind_param("i", $post_id);

    if ($stmt->execute()) {
        // Lyckades
        $stmt->close();
        $conn->close();
        header("Location: readtopic.php?topicId=" . $_POST['topic_id']); // skicka tillbaka till tråden
        exit;
    } else {
        echo "Fel vid uppdatering: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Ogiltig begäran.";
}

$conn->close();
?>
