<?php 
header('Content-type: text/html');

$html = file_get_contents("index.html");
$html_pieces = explode("<!-- ==xxx== -->", $html);
echo $html_pieces[0]; // skriv första delen av html-sidan

if (count($_POST) > 0) {
    $servername = "localhost"; // OBS: ersätt med rätt namn
    $username = "root";
    $password = "";
    $dbname = "forum";

    // Skapa anslutning
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Kontrollera anslutning
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    else {
        $email = $_POST["email"];
        $userpass = $_POST["userpass"];
        $userId = $_POST["userId"];
        $topicid = $_POST["topicid"];
        $header = $_POST["header"];
        $updates = $_POST["updates"];
        $originator = $_POST["originator"];
        
        echo "Inloggad som <a href=\"mailto:" . $email . "\" title=\"" . $userId . "\" >" . $email . "</a><br>";
        echo "<form action='index.php' method='post'>";
        echo "<input type='hidden' name='email' value='$email'>";
        echo "<input type='hidden' name='pass' value='$userpass'>";
        echo "<input type='submit' name='submit' value='Tillbaka till startsidan'>";
        echo "</form>";
        echo "<h1>" . $header . "</h1>";
        
        // Hämta alla inlägg
        $sql = "SELECT posts.id, posts.user, posts.time, posts.text, posts.likes, users.userId FROM posts JOIN users ON posts.user = users.email WHERE posts.topicid='$topicid'";
        $result = $conn->query($sql);
        $nbrposts = $result->num_rows;
        echo "Det finns " . $nbrposts . " inlägg i denna tråd:";
        echo "<table class=\"result\">";
        while($row = $result->fetch_assoc()) {
            $post_id = $row["id"];
            $user = htmlspecialchars($row["user"], ENT_QUOTES);
            $time = $row["time"];
            $text = nl2br(htmlspecialchars($row["text"], ENT_QUOTES));
            $likes = $row["likes"];
            $userId = $row["userId"];

            echo "<tr class=\"result\">";
            echo "<td>" . "Skrivet av ";
            echo "<a href=\"mailto:" . $user . "\" title=\"" . $userId . "\" >" . $user . "</a><br>";
            echo $time . "</td>";
            echo "<td>" . $text . "</td>";
            echo "<td>Likes: $likes</td>";

            // Gilla-knapp
            if (isset($_SESSION["email"])) {
                echo "<td>
                        <form action='like.php' method='POST'>
                            <input type='hidden' name='post_id' value='$post_id'>
                            <input type='submit' value='Gilla'>
                        </form>
                      </td>";
            } else {
                echo "<td>Logga in för att gilla detta inlägg.</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // Svara på denna tråd:
        echo "Svara på denna tråd:<br>";
        echo "<form action='postconfirmation.php' method='post'>";
        echo "<input type='hidden' name='email' value='$email'>";
        echo "<input type='hidden' name='userpass' value='$userpass'>";
        echo "<input type='hidden' name='dbpass' value='$password'>";
        echo "<input type='hidden' name='userId' value='$userId'>";
        echo "<input type='hidden' name='topicid' value='$topicid'>";
        echo "<input type='hidden' name='updates' value='$updates'>";
        echo "<input type='hidden' name='originator' value='$originator'>";
        echo "<input type='hidden' name='nbrposts' value='$nbrposts'>";
        echo "<input type='hidden' name='header' value='$header'>";
        echo "<textarea name='content' rows='10' cols='50'></textarea><br>";
        echo "<input type='submit' name='submit' value='Publicera'>";
        echo "</form>";
        
        $closed = mysqli_close($conn);
        if ($closed) {
            //echo "Databasuppkopplingen stängd";
        }
        else {
            echo "Lyckades inte stänga databasuppkopplingen";
        }
    }
}
?> 
