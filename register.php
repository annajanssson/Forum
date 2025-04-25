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

    echo "Inloggad som <a href=\"mailto:" . htmlspecialchars($email) . "\" title=\"" . htmlspecialchars($userId) . "\">" . htmlspecialchars($email) . "</a><br>";

    echo "<form action='index.php' method='post'>";
    echo "<input type='hidden' name='email' value='" . htmlspecialchars($email) . "'>";
    echo "<input type='hidden' name='pass' value='" . htmlspecialchars($userpass) . "'>";
    echo "<input type='submit' name='submit' value='Tillbaka till startsidan'>";
    echo "</form>";

    // Kontrollera om användaren redan finns
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Den här e-postadressen är redan registrerad.";
    } else {
        // Skapa hash av lösenordet
        $hashed_pass = password_hash($userpass, PASSWORD_DEFAULT);

        // Lägg in användaren
        $stmt = $conn->prepare("INSERT INTO users (email, passcode) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashed_pass);
        
        if ($stmt->execute()) {
            echo "Användare skapad! <a href='index.html'>Logga in här</a>";
        } else {
            echo "Något gick fel: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
