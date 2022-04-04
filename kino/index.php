<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kino Projekt</title>
    <link rel="stylesheet" href="style.css">
    <script src="_script/main.js"></script>
  </head>
  <header>
  <center> Kino Reservation <center>
  </header>
  <main>


  <!-- <select name="owner">
  <?php 
  // $server = "127.0.0.1";
  // $username = "u448707245_vcApp";
  // $password = "Semens6666";
  // $dbname = "u448707245_zGQ0L";
  
  // // create connection
  // $conn = new mysqli($server, $username, $password, $dbname);
  // $sql = mysqli_query($conn, "SELECT Titel FROM Film");
  // while ($row = $sql->fetch_assoc()){
  // echo "<option value=\"owner1\">" . $row['Titel'] . "</option>";
  // }
  ?>
  </select> -->

      <p></p>
      <center><img src="saal.png"></center>
      <p></p>
      <center>

      <a href="printMovies.php"> <button> Show Movies </button> </a>

      <form name="reservationForm" action="inputDB.php" method="POST">
        <!-- input contact dates -->
        <p> Kunde </p> <br>
        <label for="vname">Vorname:</label>
        <input type="text" id="vname" name="vname">
        <label for="nname">Nachname:</label>
        <input type="text" id="nname" name="nname"> 
        <label for="email">Email:</label> 
        <input type="email" id="email" name="email"> <br>
        <label for="street">Strasse:</label>
        <input type="text" id="street" name="street"> 
        <label for="plz">Postleitzahl:</label>
        <input type="text" id="plz" name="plz">
        <label for="ort">Ort:</label>
        <input type="text" id="ort" name="ort"> <br>
        <label for="tel">Telefonnummer:</label> 
        <input type="tel" id="tel" name="tel">

        <!-- input movie dates -->
        <p> Vorstellung </p> 
        <label for="movie">Film:</label>
        <select name="owner" id="movie">
        <?php 
        $server = "127.0.0.1";
        $username = "u448707245_vcApp";
        $password = "Semens6666";
        $dbname = "u448707245_zGQ0L";
  
        // create connection
        $conn = new mysqli($server, $username, $password, $dbname);
        $sql = mysqli_query($conn, "SELECT Titel, Dauer FROM Film");
        while ($row = $sql->fetch_assoc()){
        echo "<option value=\"owner1\">" . $row['Titel'] . " | " . $row['Dauer'] . "</option>";
        }
        ?>
        </select>
        <label for="row">Reihe:</label>
        <input type="text" id="row" name="row">
        <label for="seat">Nummer:</label>
        <input type="text" id="number" name="number"><br>
        <br>

        <!-- buttons to add to DB / print DB -->

        <button href="javascript: submitform()" style="cursor: pointer;"> Add </button>
      </form>
        <a href="printDB.php"> <button> Print </button> </a>


        <!-- Script to submit the inputs -->
        <script type="text/javascript">
              function submitform()
              {
                document.reservationForm.submit();
              }
        </script>
      </center>

      <center><p> Copyrighted by us! </p></center>
  </main>