<?php
header('Content-type: text/html');

// Hämta HTML-sidan
$html = file_get_contents('index.html');
$html_pieces = explode('<!-- ==xxx== -->', $html);
echo $html_pieces[0];

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'forum';

// Skapa anslutning
$conn = new mysqli($servername, $username, $password, $dbname);

// Kontrollera anslutning
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Hantera kommentar-post (om något kommenteras)
if (isset($_POST['comment_content']) && isset($_POST['topicid']) && isset($_POST['email'])) {
    $comment = $_POST['comment_content'];
    $topicid = $_POST['topicid'];
    $email = $_POST['email'];
    $time = date('Y-m-d H:i:s');

    // Lägg in kommentar i posts
    $stmt = $conn->prepare("INSERT INTO posts (user, time, text, topicid, likes) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param('ssss', $email, $time, $comment, $topicid);
    $stmt->execute();
    $stmt->close();

    // Efter insättning gör en redirect för att undvika dubblett vid siduppdatering
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

if (count($_POST) > 0) {
    $email = $_POST['email'];
    $userpass = $_POST['userpass'] ?? '';
    $userId = $_POST['userId'];
    $topicid = $_POST['topicid'];
    $header = $_POST['header'];
    $updates = $_POST['updates'];
    $originator = $_POST['originator'];

    echo "Inloggad som <a href='mailto:$email' title='$userId'>$email</a><br>";
    echo "<form action='index.php' method='post'>";
    echo "<input type='hidden' name='email' value='$email'>";
    echo "<input type='hidden' name='pass' value='$userpass'>";
    echo "<input type='submit' name='submit' value='Tillbaka till startsidan'>";
    echo "</form>";
    echo "<h1>$header</h1>";

    // Hämta alla inlägg
    $stmt = $conn->prepare('SELECT posts.id, posts.user, posts.time, posts.text, posts.likes, users.userId FROM posts JOIN users ON posts.user = users.email WHERE posts.topicid=?');
    $stmt->bind_param('s', $topicid);
    $stmt->execute();
    $result = $stmt->get_result();
    $nbrposts = $result->num_rows;

    echo "Det finns $nbrposts inlägg i denna tråd:";
    echo "<table class='result'>";

    while ($row = $result->fetch_assoc()) {
        $post_id = $row['id'];
        $user = htmlspecialchars($row['user'], ENT_QUOTES);
        $time = $row['time'];
        $text = nl2br(htmlspecialchars($row['text'], ENT_QUOTES));
        $likes = $row['likes'];
        $userId = $row['userId'];

        echo "<tr class='result'>";
        echo "<td>Skrivet av <a href='mailto:$user' title='$userId'>$user</a><br>$time</td>";
        echo "<td>$text</td>";
        echo "<td id='likes_$post_id'>Likes: $likes</td>";

        // Gilla-knapp med AJAX
        echo "<td>
                <button onclick='likePost($post_id)'>Gilla</button>
              </td>";

        echo "</tr>";
    }

    echo "</table>";

    // Kommentarformulär
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

}

$conn->close();
?>

<script>
function likePost(postId) {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'like_id=' + postId
    })
    .then(response => response.text())
    .then(data => {
        const likeElement = document.getElementById('likes_' + postId);
        const currentLikes = parseInt(likeElement.textContent.replace('Likes: ', ''));
        likeElement.textContent = 'Likes: ' + (currentLikes + 1);
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?php
// Hantera AJAX-förfrågan för att uppdatera likes
if (isset($_POST['like_id'])) {
    $postId = $_POST['like_id'];

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    $stmt = $conn->prepare('UPDATE posts SET likes = likes + 1 WHERE id = ?');
    $stmt->bind_param('i', $postId);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    exit();
}
?>
