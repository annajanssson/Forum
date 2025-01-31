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
        
        $sql = "SELECT * FROM posts, users WHERE topicid='$topicid' AND user=email";
        $result = $conn->query($sql);
        $nbrposts = $result->num_rows;
        echo "Det finns " . $nbrposts . " inl&auml;gg i denna tr&aring;d:";
        echo "<table class=\"result\">";
        while($row = $result->fetch_assoc()) {
            echo "<tr class=\"result\">";
            echo "<td>" . "Skrivet av ";
            //echo $row["user"] . "<br>";
            echo "<a href=\"mailto:" . $row["user"] . "\" title=\"" . $row["userId"] . "\" >" . $row["user"] . "</a><br>";
            echo $row["time"] . "</td>";
            echo "<td>" . $row["text"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
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
        if ($updates == 1) {
            echo "L&ouml;senord till epostkontot:<br>";
            echo "<input type='password' name='mailpass'><br>";
        }
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
 