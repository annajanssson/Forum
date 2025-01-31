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
        $nbrrows = $_POST["nbrrows"];


        echo "Inloggad som <a href=\"mailto:" . $email . "\" title=\"" . $presentation . "\" >" . $email . "</a><br>";
        echo "<form action='index.php' method='post'>";
        echo "<input type='hidden' name='email' value='$email'>";
        echo "<input type='hidden' name='pass' value='$userpass'>";
        echo "<input type='submit' name='submit' value='Tillbaka till startsidan'>";
        echo "</form>";
        echo "<h1>Ny tråd</h1>";
        echo "<form action='addtopicconfirmation.php' method='post'>";
        echo "<input type='hidden' name='email' value='$email'>";
        echo "<input type='hidden' name='userpass' value='$userpass'>";
        echo "<input type='hidden' name='dbpass' value='$password'>";
        echo "<input type='hidden' name='presentation' value='$presentation'>";
        echo "<input type='hidden' name='nbrrows' value='$nbrrows'>";
        echo "Rubrik<br>";
        echo "<input type='text' name='header'><br>";
        echo "Inlägg<br>";
        echo "<textarea name='content' rows='10' cols='50'></textarea><br>";
        echo "Meddela mig via email vid nya inlägg: ";
        echo "<input type='checkbox' name='subscribe' value='ok' checked><br>";
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
 