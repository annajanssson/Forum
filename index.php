<?php
 header('Content-type: text/html');
 
 $html = file_get_contents("index.html");
 $html_pieces = explode("<!-- ==xxx== -->", $html);
 echo $html_pieces[0];
 
 if (count($_POST) > 0) {
     
     $servername = "localhost";
     $username = "root";
     $password = "";
     $dbname = "forum";
     
     $conn = new mysqli($servername, $username, $password, $dbname);
    
     if ($conn->connect_error) {
         die("Connection failed: " . $conn->connect_error);
     } 
     else {
        if (count($_POST) > 1) {
            $email = htmlspecialchars($_POST["email"], ENT_QUOTES);
            $pass = htmlspecialchars($_POST["pass"], ENT_QUOTES);
            $sql = "SELECT * FROM users WHERE email='$email' AND passcode='$pass'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $presentation = "";
                while($row = $result->fetch_assoc()) { // endast en loop, anv.namn+lösen är en unik kombination
                    
                    $presentation = $row["userId"];
                    echo "V&auml;lkommen <a href=\"mailto:" . $email . "\" title=\"" . $presentation . "\" >" . $email . "</a><br>";
                    
                }
                
                // Läs in alla trådar
                $sql = "SELECT topics.id AS id, header, originator, userId, updates FROM topics, users WHERE originator=email";
                $result = $conn->query($sql);
                $nbrrows = $result->num_rows;
                echo "<form action='addtopic.php' method='post'>";
                echo "<input type='hidden' name='email' value='$email'>";
                echo "<input type='hidden' name='userpass' value='$pass'>";
                echo "<input type='hidden' name='dbpass' value='$password'>";
                echo "<input type='hidden' name='presentation' value='$presentation'>";
                echo "<input type='hidden' name='nbrrows' value='$nbrrows'>";
                echo "<input type='submit' name='submit' value='Skapa ny tråd'>";
                echo "</form>";
                if ($nbrrows > 0) {
                    echo "<p>Det finns ";
                    echo $nbrrows;
                    echo " tråd";
                    if ($nbrrows > 1) {
                        echo "ar";
                    }
                    echo ":</p><table class=\"result\"><tr class=\"result\">
                        <th></th>
                        <th>Nr</th>
                        <th>Rubrik</th> 
                        <th>Skapad av</th>
                        <th>Senaste inlägg</th>
                        </tr>";
                    while($row = $result->fetch_assoc()) {
                        echo "<tr class=\"result\"><td>";
                        $topicid = $row["id"];
                        $header = $row["header"];
                        $updates = $row["updates"];
                        $originator = $row["originator"];
                        $userId = $row["userId"];
                        echo "<form action='readtopic.php' method='post'>";
                        echo "<input type='hidden' name='email' value='$email'>";
                        echo "<input type='hidden' name='userpass' value='$pass'>";
                        echo "<input type='hidden' name='userId' value='$userId'>";
                        echo "<input type='hidden' name='header' value='$header'>";
                        echo "<input type='hidden' name='topicid' value='$topicid'>";
                        echo "<input type='hidden' name='updates' value='$updates'>";
                        echo "<input type='hidden' name='originator' value='$originator'>";
                        //echo $topicid;
                        //echo "'>";
                        echo "<input class='result' type='submit' name='submit' value='Läs'>";
                        echo "</form></td><td>";
                        echo $topicid+1;
                        echo "</td><td>";
                        echo $header;
                        echo "</td><td>";
                        echo "<a href=\"mailto:" . $originator . "\" title=\"" . $row["userId"] . "\" >" . $originator . "</a><br>";
                        //echo $row["originator"];
                        echo "</td><td>";
                        $sql = "SELECT MAX(time) AS maxtime FROM posts WHERE topicid='$topicid'";
                        $result2 = $conn->query($sql);
                        while($row = $result2->fetch_assoc()) { // endast en loop, endast ett maxelement
                            echo $row["maxtime"];
                        }
                        
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                else {
                    echo "<p>Det finns ännu inga trådar.</p>";
                }
            } else {
                echo "Login misslyckades!<br>";
            }
            
            
        }
        
        $closed = mysqli_close($conn);
        if ($closed) {
            echo "Databasuppkopplingen stängd";
        }
        else {
            echo "Lyckades inte stänga databasuppkopplingen";
        }
     }


 }


 ?> 

CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId VARCHAR(100) NOT NULL,
    postId INT NOT NULL,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (postId) REFERENCES posts(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE(userId, postId)
);
