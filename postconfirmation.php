<?php 
header('Content-type: text/html');

// Ladda in startsidan (förutsatt att <!-- ==xxx== --> finns i index.html)
$html = file_get_contents("index.html");
$html_pieces = explode("<!-- ==xxx== -->", $html);
echo $html_pieces[0];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Databasuppgifter
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "forum";

    // Skapa anslutning
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Databasanslutning misslyckades: " . $conn->connect_error);
    }

    // Hämta och sanera användarinformation
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $userpass = htmlspecialchars($_POST["userpass"], ENT_QUOTES);
    $userId = htmlspecialchars($_POST["userId"], ENT_QUOTES);
    $topicid = isset($_POST["topicid"]) ? intval($_POST["topicid"]) : 0;
    $text = isset($_POST["content"]) ? htmlspecialchars($_POST["content"], ENT_QUOTES | ENT_SUBSTITUTE) : "";
    $nbrposts = isset($_POST["nbrposts"]) ? intval($_POST["nbrposts"]) : 0;

    // Visa användarinformation
    echo "Inloggad som <a href=\"mailto:$email\" title=\"$userId\">$email</a><br>";
    echo "<form action='index.php' method='post'>";
    echo "<input type='hidden' name='email' value='$email'>";
    echo "<input type='hidden' name='pass' value='$userpass'>";
    echo "<input type='submit' name='submit' value='Tillbaka till startsidan'>";
    echo "</form>";

    // Förbered och kör SQL för att spara inlägget
    $stmt = $conn->prepare("INSERT INTO posts (text, time, topicId, user) VALUES (?, NOW(), ?, ?)");
    if ($stmt === false) {
        die("Fel vid förberedelse av SQL-fråga: " . $conn->error);
    }

    $stmt->bind_param("sis", $text, $topicid, $email);
    
    if ($stmt->execute()) {
        echo "<p>Inlägget har sparats.</p>";
    } else {
        echo "<p>Fel vid sparning: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<p>Ogiltigt anrop.</p>";
}
?>
