<!DOCTYPE html>
<html>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <head>
        <meta charset="UTF-8">
        <title>Account aanmaken</title>
        <link rel="stylesheet" type="text/css" href="CMSfrontend_002.css" />
    </head>
    <body>
        <h1>Een account aanmaken</h1>
        <form id="account-form" method="post" action="<?php $thisfile?>">
            <h3 align='center'>Account gegevens</h3>
            <p>E-mail: <input type="text" name="email" required></p>
            <p>Wachtwoord: <input type="password" name="wachtwoord" required></p>
            <p>Herhaal wachtwoord: <input type="password" name="wachtwoord-check" required></p>
            <p>Voornaam: <input type="text" name="voornaam" required></p>
            <p>Achternaam: <input type="text" name="achternaam" required></p>
            <p><input type="submit" value="Registreren"></p>
        </form>
        <?php
            //error_reporting(E_ALL);
            //ini_set('display_errors', 1);

            require_once "dbconnect.php"; // bestand met de login gegevens voor de database

            $thisfile = $_SERVER['PHP_SELF'];

            if (isset($_POST['email'])) {
                $email = $_POST['email'];
                $wachtwoord = $_POST['wachtwoord'];
                $wachtwoord_check = $_POST['wachtwoord-check'];
                $voornaam = $_POST['voornaam'];
                $achternaam = $_POST['achternaam'];
                if ($wachtwoord == $wachtwoord_check) {
                    account_aanmaken($email, $wachtwoord, $voornaam, $achternaam);
                }
                else {
                    echo "<p>Het tweede wachtwoord komt niet overeen met het eerste.</p>";
                    //header("$thisfile");
                }
            }

            //header("CMSfrontend_002.php");

            function bestaat_al($email) {
                $db = dbconnect();
                
                $stmt = $db->prepare("SELECT email FROM Lezers
                                      WHERE email = '$email';");

                $stmt->execute();
                $stmt->store_result();
                
                return ($stmt->num_rows > 0);
            }

            function account_aanmaken($email, $wachtwoord, $voornaam, $achternaam) {
                
                if (!bestaat_al($email)) {
                    $db = dbconnect();
                    $gecodeerde_wachtwoord = md5($wachtwoord); // sla het wachtwoord gecodeerd op
                    $stmt = $db->prepare("INSERT INTO Lezers (email, wachtwoord, voornaam, achternaam) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $email, $gecodeerde_wachtwoord, $voornaam, $achternaam);
                    $stmt->execute();
        
                    //$lastid = mysqli_insert_id($db);
        
                    echo "<p>Account toegevoegd.</p>";
                    echo "<p>U kunt nu inloggen met de net geregistreerde gegevens.</p>";
                    echo "<h4><a href='CMSfrontend_002.php'>Klik hier om verder te gaan.</a></h4>";
                    //return $lastid;
                    $stmt->close();
                    ?>
                    <script type="text/javascript">
                        //alert("de formulier gaat nu uitgewist worden");
                        document.getElementById('account-form').style.display = "none";
                    </script>
                    <?php
                }
                else {
                    echo "<p>Account met de email $email bestaat al.</p>";
                    //header("$thisfile");
                }
            }
        ?>

    </body>
</html>
