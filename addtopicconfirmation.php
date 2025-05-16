<?php 
header('Content-type: text/html');

// Ladda in HTML-huvudet
$html = file_get_contents("index.html");
$html_pieces = explode("<!-- ==xxx== -->", $html);
echo $html_pieces[0];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email"], $_POST["userpass"], $_POST["presentation"], $_POST["header"], $_POST["content"])) {

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "forum";

    // Anslut till databasen
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Hämta och sanera indata
    $email = htmlspecialchars($_POST["email"], ENT_QUOTES);
    $userpass = htmlspecialchars($_POST["userpass"], ENT_QUOTES);
    $presentation = htmlspecialchars($_POST["presentation"], ENT_QUOTES);
    $nbrrows = isset($_POST["nbrrows"]) ? intval($_POST["nbrrows"]) : 0;
    $subscribe = isset($_POST["subscribe"]) ? 1 : 0;

    $header = htmlspecialchars($_POST["header"], ENT_QUOTES | ENT_SUBSTITUTE);
    $content = htmlspecialchars($_POST["content"], ENT_QUOTES | ENT_SUBSTITUTE);

    echo "Inloggad som <a href=\"mailto:$email\" title=\"$presentation\">$email</a><br>";

    echo "<form action='index.php' method='post'>";
    echo "<input type='hidden' name='email' value='$email'>";
    echo "<input type='hidden' name='pass' value='$userpass'>";
    echo "<input type='submit' name='submit' value='Tillbaka till startsidan'>";
    echo "</form>";

    // Skapa ny tråd (topic)
    $stmt = $conn->prepare("INSERT INTO topics (header, originator, updates) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die("Förberedelse av topic-fråga misslyckades: " . $conn->error);
    }
    $stmt->bind_param("ssi", $header, $email, $subscribe);
    $stmt->execute();
    $topic_id = $conn->insert_id;
    $stmt->close();

    // Lägg till första inlägget i tråden
    $stmt2 = $conn->prepare("INSERT INTO posts (topicid, time, user, text) VALUES (?, NOW(), ?, ?)");
    if ($stmt2 === false) {
        die("Förberedelse av post-fråga misslyckades: " . $conn->error);
    }
    $stmt2->bind_param("iss", $topic_id, $email, $content);
    $stmt2->execute();
    $stmt2->close();

    echo "<p>Inlägget har sparats.</p>";

    $conn->close();

} else {
    echo "<p>Fel: Något gick fel med formulärdata.</p>";
}
?>