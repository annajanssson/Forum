<?php 
session_start();
header('Content-type: text/html');

// Ladda in början av HTML-sidan
$html = file_get_contents("index.html");
$html_pieces = explode("<!-- ==xxx== -->", $html);
echo $html_pieces[0];

// Kontrollera att nödvändig POST-data finns
if (isset($_POST["email"], $_POST["userpass"], $_POST["presentation"], $_POST["nbrrows"])) {
    
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "forum";

    // Anslut till databasen
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Kontrollera anslutning
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Hämta och skydda indata
    $email = htmlspecialchars($_POST["email"], ENT_QUOTES);
    $userpass = htmlspecialchars($_POST["userpass"], ENT_QUOTES);
    $presentation = htmlspecialchars($_POST["presentation"], ENT_QUOTES);
    $nbrrows = intval($_POST["nbrrows"]);

    // Visa användarinformation
    echo "Inloggad som <a href=\"mailto:$email\" title=\"$presentation\">$email</a><br>";

    // Tillbaka till startsidan-formulär
    echo "<form action='index.php' method='post'>";
    echo "<input type='hidden' name='email' value='$email'>";
    echo "<input type='hidden' name='pass' value='$userpass'>";
    echo "<input type='submit' name='submit' value='Tillbaka till startsidan'>";
    echo "</form>";

    // Formulär för ny tråd
    echo "<h1>Ny tråd</h1>";
    echo "<form action='addtopicconfirmation.php' method='post'>";
    echo "<input type='hidden' name='email' value='$email'>";
    echo "<input type='hidden' name='userpass' value='$userpass'>";
    echo "<input type='hidden' name='presentation' value='$presentation'>";
    echo "<input type='hidden' name='nbrrows' value='$nbrrows'>";
    echo "Rubrik<br>";
    echo "<input type='text' name='header' required><br>";
    echo "Inlägg<br>";
    echo "<textarea name='content' rows='10' cols='50' required></textarea><br>";
    echo "Meddela mig via email vid nya inlägg: ";
    echo "<input type='checkbox' name='subscribe' value='ok' checked><br>";
    echo "<input type='submit' name='submit' value='Publicera'>";
    echo "</form>";

    // Stäng anslutning
    $conn->close();

} else {
    echo "<p>Felaktig åtkomst. Vänligen logga in via startsidan.</p>";
}
?>
