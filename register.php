<?php
header('Content-type: text/html');

$html = file_get_contents("index.html");
$html_pieces = explode("<!-- ==xxx== -->", $html);
echo $html_pieces[0]; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "forum";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Anslutningsfel: " . $conn->connect_error);
    }

    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $userpass = $_POST["pass"];

    echo "Inloggad som <a href=\"mailto:" . htmlspecialchars($email) . "\">" . htmlspecialchars($email) . "</a><br>";

    echo "<form action='index.php' method='post'>";
    echo "<input type='hidden' name='email' value='" . htmlspecialchars($email) . "'>";
    echo "<input type='hidden' name='pass' value='" . htmlspecialchars($userpass) . "'>";
    echo "<input type='submit' name='submit' value='Tillbaka till startsidan'>";
    echo "</form>";

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Den här e-postadressen är redan registrerad.";
    } else {
        $hashed_pass = password_hash($userpass, PASSWORD_DEFAULT);

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
