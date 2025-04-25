<?php
session_start();
header('Content-type: text/html');

$html = file_get_contents("index.html");
$html_pieces = explode("<!-- ==xxx== -->", $html);
echo $html_pieces[0];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email"]) && isset($_POST["pass"])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "forum";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Hämta inloggningsuppgifter
    $email = htmlspecialchars($_POST["email"], ENT_QUOTES);
    $pass = htmlspecialchars($_POST["pass"], ENT_QUOTES);

    // Säker SQL med prepared statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND passcode=?");
    $stmt->bind_param("ss", $email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION["email"] = $user["email"];
        $_SESSION["userId"] = $user["userId"];

        echo "Välkommen <a href=\"mailto:" . $user["email"] . "\" title=\"" . $user["userId"] . "\">" . $user["email"] . "</a><br>";

        // Visa befintliga trådar
        $sql = "SELECT topics.id AS id, header, originator, userId, updates 
                FROM topics 
                JOIN users ON originator = email";
        $result = $conn->query($sql);
        $nbrrows = $result->num_rows;

        echo "<form action='addtopic.php' method='post'>";
        echo "<input type='hidden' name='email' value='$email'>";
        echo "<input type='hidden' name='userpass' value='$pass'>";
        echo "<input type='hidden' name='nbrrows' value='$nbrrows'>";
        echo "<input type='submit' name='submit' value='Skapa ny tråd'>";
        echo "</form>";

        if ($nbrrows > 0) {
            echo "<p>Det finns $nbrrows tråd" . ($nbrrows > 1 ? "ar" : "") . ":</p>";
            echo "<table class=\"result\">
                    <tr class=\"result\">
                        <th></th>
                        <th>Nr</th>
                        <th>Rubrik</th> 
                        <th>Skapad av</th>
                        <th>Senaste inlägg</th>
                    </tr>";

            while ($row = $result->fetch_assoc()) {
                $topicid = $row["id"];
                $header = $row["header"];
                $originator = $row["originator"];
                $userId = $row["userId"];
                $updates = $row["updates"];

                echo "<tr class=\"result\"><td>";
                echo "<form action='readtopic.php' method='post'>";
                echo "<input type='hidden' name='email' value='$email'>";
                echo "<input type='hidden' name='userpass' value='$pass'>";
                echo "<input type='hidden' name='userId' value='$userId'>";
                echo "<input type='hidden' name='header' value='$header'>";
                echo "<input type='hidden' name='topicid' value='$topicid'>";
                echo "<input type='hidden' name='updates' value='$updates'>";
                echo "<input type='hidden' name='originator' value='$originator'>";
                echo "<input class='result' type='submit' name='submit' value='Läs'>";
                echo "</form></td><td>";
                echo $topicid + 1;
                echo "</td><td>";
                echo $header;
                echo "</td><td>";
                echo "<a href=\"mailto:$originator\" title=\"$userId\">$originator</a>";
                echo "</td><td>";

                // Visa senaste inlägg
                $stmt2 = $conn->prepare("SELECT MAX(time) AS maxtime FROM posts WHERE topicid=?");
                $stmt2->bind_param("i", $topicid);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $maxtime = $result2->fetch_assoc()["maxtime"] ?? "Inget inlägg";
                echo $maxtime;

                echo "</td></tr>";
                $stmt2->close();
            }

            echo "</table>";
        } else {
            echo "<p>Det finns ännu inga trådar.</p>";
        }
    } else {
        echo "Login misslyckades!<br>";
    }

    $stmt->close();
    $conn->close();
}
?>
