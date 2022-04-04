<?php
$server = "127.0.0.1";
$username = "u448707245_vcApp";
$password = "Semens6666";
$dbname = "u448707245_zGQ0L";

// create connection
$conn = new mysqli($server, $username, $password, $dbname);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected succesfully - ";

// get values
$vname = $_POST['vname'];
$nname = $_POST['nname'];
$email = $_POST['email'];
$street = $_POST['street'];
$plz = $_POST['plz'];
$ort = $_POST['ort'];
$tel = $_POST['tel'];
$row = $_POST['row'];
$number = $_POST['number'];
$movies = $_POST['movie'];



// insert values into db
$sql = "INSERT INTO Kunde (Name, Vorname, Email, Strasse, Postleitzahl, Ort, Telefonnummer) VALUES ('$vname','$nname','$email','$street','$plz','$ort','$tel')";
// $newestsql = "INSERT INTO SitzPlatz (Nummer, Besetzt) VALUES ('$number', '1')";

// check if added
if ($conn->query($sql) === TRUE) 
{
    echo "New record created successfully - ";
} 
else 
{
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>

<a href="printDB.php"> Print DB </a>