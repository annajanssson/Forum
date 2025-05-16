<?php
session_start();

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "forum";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if (isset($_POST["email"], $_POST["pass"])) {
    $email = $conn->real_escape_string($_POST["email"]);
    $pass = $conn->real_escape_string($_POST["pass"]);

    $sql = "SELECT * FROM users WHERE email = '$email' AND passcode = '$pass'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION["email"] = $email;
        $_SESSION["userId"] = $user["userId"];
    } else {
        echo "<p style='color:red;'>Felaktigt email eller lösenord.</p>";
    }
}

if (isset($_SESSION["email"])) {
    // Användare är inloggad
    $email = $_SESSION["email"];
    echo "Välkommen <a href='mailto:$email'>$email</a><br>";

    echo '<form method="post"><input type="submit" name="logout" value="Logga ut"></form><br>';

    if (isset($_POST["action"]) && $_POST["action"] === "newthreadform") {
        echo "<h1>Ny tråd</h1>";
        echo "<form action='addtopicconfirmation.php' method='post'>";
        echo "<input type='hidden' name='email' value='$email'>";
        echo "Rubrik<br>";
        echo "<input type='text' name='header' required><br>";
        echo "Inlägg<br>";
        echo "<textarea name='content' rows='10' cols='50' required></textarea><br>";
        echo "Meddela mig via email vid nya inlägg: ";
        echo "<input type='checkbox' name='subscribe' value='ok' checked><br>";
        echo "<input type='submit' value='Publicera'>";
        echo "</form>";
    } else {
        echo '<form method="post">';
        echo '<input type="hidden" name="action" value="newthreadform">';
        echo '<input type="submit" value="Skapa ny tråd">';
        echo '</form>';
    }
} else {
    // Visa inloggningsformulär
    ?>

    <?php
}

$conn->close();
?>
