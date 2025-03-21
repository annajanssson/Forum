<?php 
header('Content-type: text/html');

$html = file_get_contents("index.html");
$html_pieces = explode("<!-- ==xxx== -->", $html);
echo $html_pieces[0]; // skriv första delen av html-sidan

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $servername = "localhost"; // Uppdatera vid behov
    $username = "root";
    $password = "";
    $dbname = "forum";

    // Skapa anslutning
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Kontrollera anslutning
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Hämta och validera POST-data
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $userpass = $_POST["userpass"];
    $userId = $_POST["userId"];
    $topicid = intval($_POST["topicid"]);
    $text = htmlspecialchars($_POST["content"], ENT_QUOTES, 'UTF-8');
    $nbrposts = intval($_POST["nbrposts"]);

    echo "Inloggad som <a href=\"mailto:" . htmlspecialchars($email) . "\" title=\"" . htmlspecialchars($userId) . "\">" . htmlspecialchars($email) . "</a><br>";
    
    echo "<form action='index.php' method='post'>";
    echo "<input type='hidden' name='email' value='" . htmlspecialchars($email) . "'>";
    echo "<input type='hidden' name='pass' value='" . htmlspecialchars($userpass) . "'>";
    echo "<input type='submit' name='submit' value='Tillbaka till startsidan'>";
    echo "</form>";

    // Förbered och kör SQL
    $stmt = $conn->prepare("INSERT INTO posts (text, time, topicId, user) VALUES (?, now(), ?, ?)");
    $stmt->bind_param("sis", $text, $topicid, $email);

    if ($stmt->execute()) {
        echo "Inlägget har sparats.<br>";
    } else {
        echo "Fel vid sparning: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
