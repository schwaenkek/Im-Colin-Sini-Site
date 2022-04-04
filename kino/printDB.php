<?php
$server = "127.0.0.1";
$username = "u448707245_vcApp";
$password = "Semens6666";
$dbname = "u448707245_zGQ0L";

// create connection
$conn = new mysqli($server, $username, $password, $dbname);

// check connection
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
// echo "Connected succesfully <br>";

// select values
$sql = "SELECT Name, Vorname, Email, Strasse, Postleitzahl, Ort, Telefonnummer FROM Kunde";
$result = $conn->query($sql);

if ($result->num_rows > 0) 
{
    // output data of each row
    echo "KUNDEN". " <br>";
    while($row = $result->fetch_assoc()) 
    {
      echo "Name: " . $row["Vorname"]. " " . $row["Name"]. " | Adresse: " . $row["Strasse"]. " " .$row["Postleitzahl"]. " " .$row["Ort"]. " | Kontakt: " .$row["Email"]. " " .$row["Telefonnummer"]. " <br>";
    }
} 
else 
{
    echo "0 results";
}

$filmsql = "SELECT Reihennummer FROM Reihe";
$result = $conn->query($filmsql);

if ($result->num_rows > 0) 
{
    // output data of each row
    echo "REIHE". "<br>";
    while($row = $result->fetch_assoc()) 
    {
      // echo "Film: " .$row["Titel"]. " | Dauer: " .$row["Dauer"]. " Minuten". " <br>";
      echo "Reihe: " .$row["Reihennummer"]. " <br>";
    }
} 
else 
{
    echo "0 results";
}

$fullsql = "SELECT k.Vorname, k.Name, Bestellungsdaten.Bestellungsdaten, Bestellungsdaten.Reservationsart, SitzPlatz.Nummer FROM Kunde as k INNER JOIN Reservation ON k.Kunde = Reservation.Kunde INNER JOIN Bestellungsdaten ON Reservation.Bestellungsdaten = Bestellungsdaten.Bestellungsdaten INNER JOIN SitzPlatz ON Bestellungsdaten.Sitzplatz = SitzPlatz.Sitzplatz";
$result = $conn->query($fullsql);

if ($result->num_rows > 0) 
{
    // output data of each row
    echo "BESTELLUNGSDATEN". " <br>";
    while($row = $result->fetch_assoc()) 
    {
      echo "Name: " . $row["Vorname"]. " " . $row["Name"]. " | Bestellung: " . $row["Bestellungsdaten"]. " " .$row["Reservationsart"]. " | Sitz: " .$row["Nummer"]. " <br>";
    }
} 
else 
{
    echo "0 results";
}


$conn->close();
?>