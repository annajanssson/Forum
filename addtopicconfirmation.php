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
         
     // Create connection
     $conn = new mysqli($servername, $username, $password, $dbname);
    
     // Check connection
     if ($conn->connect_error) {
         die("Connection failed: " . $conn->connect_error);
     }
     else {
        $email = $_POST["email"];
        $userpass = $_POST["userpass"];
        $presentation = $_POST["presentation"];
        $nbrrows = intval($_POST["nbrrows"]);
        echo "Inloggad som <a href=\"mailto:" . $email . "\" title=\"" . $presentation . "\" >" . $email . "</a><br>";
        echo "<form action='index.php' method='post'>";
        echo "<input type='hidden' name='email' value='$email'>";
        echo "<input type='hidden' name='pass' value='$userpass'>";
        echo "<input type='submit' name='submit' value='Tillbaka till startsidan'>";
        echo "</form>";
        
        
        
        $subscribe = TRUE; // hade varit bättre att ha en boolean-variabel, men av någon anledning funkar inte detta med min databas
        if ($_POST["subscribe"]) {
            $subscribe = FALSE;
        }
        $header = htmlspecialchars($_POST["header"], ENT_QUOTES);
        $header = str_replace('<', '&lt;', $header);
        $header = str_replace('>', '&gt;', $header);
        $header = str_replace('å', '&aring;', $header);
        $header = str_replace('ä', '&auml;', $header);
        $header = str_replace('ö', '&ouml;', $header);
        $header = str_replace('Å', '&Aring;', $header);
        $header = str_replace('Ä', '&Auml;', $header);
        $header = str_replace('Ö', '&Ouml;', $header);
        $text = htmlspecialchars($_POST["content"], ENT_QUOTES);
        $text = str_replace('<', '&lt;', $text);
        $text = str_replace('>', '&gt;', $text);
        $text = str_replace('å', '&aring;', $text);
        $text = str_replace('ä', '&auml;', $text);
        $text = str_replace('ö', '&ouml;', $text);
        $text = str_replace('Å', '&Aring;', $text);
        $text = str_replace('Ä', '&Auml;', $text);
        $text = str_replace('Ö', '&Ouml;', $text);
        
        //echo "Nbrrows:" . $nbrrows . "<br>";

        $stmt = $conn->prepare("INSERT INTO topics (id, header, originator, updates) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $nbrrows, $header, $email, $subscribe);
        
        $stmt->execute();
        $stmt->close();
        $sql = "SELECT * FROM posts";
        $result = $conn->query($sql);
        $nbrposts = $result->num_rows;
        $stmt2 = $conn->prepare("INSERT INTO posts (id, topicid, time, user, text) VALUES (?, ?, now(), ?, ?)");
        $stmt2->bind_param("iiss", $nbrposts, $nbrrows, $email, $text);
        $stmt2->execute();
        $stmt2->close();
        echo "Inlägget har sparats.";
        
        
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
 