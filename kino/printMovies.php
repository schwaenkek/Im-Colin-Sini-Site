<?php
        $server = "127.0.0.1";
        $username = "u448707245_vcApp";
        $password = "Semens6666";
        $dbname = "u448707245_zGQ0L";
  
        // create connection
        $conn = new mysqli($server, $username, $password, $dbname);
        $sql = "SELECT Titel, Dauer FROM Film";

        $result = $conn->query($sql);

          if ($result->num_rows > 0) 
            {
                // output data of each row
                echo "FILME". " <br>";
                while($row = $result->fetch_assoc()) 
                    {
                        echo "Titel: " . $row["Titel"]. " | Dauer: " . $row["Dauer"]. " Minuten". " <br>";
                    }
            } 
          else 
            {
            echo "0 results";
            }
?>