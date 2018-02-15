<!DOCTYPE html>
<html>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <head>
        <meta charset="UTF-8">
        <title>Wachtwoord wijzigen</title>
        <link rel="stylesheet" type="text/css" href="CMSfrontend_002.css" />
    </head>
    <body>
        <?php
        require_once "dbconnect.php"; // bestand met de login gegevens voor de database

        $thisfile = $_SERVER['PHP_SELF'];
        $email = "Onbekend";

        if (isset($_GET['wwcode'])) {
            //email link is aangeklikt om hier te komen
            $wwcode = $_GET['wwcode'];
            $email = get_email($wwcode);
        }
        
        ?>

        <h1>Wachtwoord wijzigen</h1>
        <form id="pwreset-form" method="post" action="<?php echo $thisfile?>">
            <h3 align='center'>Wachtwoord gegevens</h3>
            <p>E-mail: <?php echo $email ?></p>
            <p><input type="hidden" name="email" value="<?php echo $email?>"></p>
            <p>Nieuw wachtwoord: <input type="password" name="wachtwoord" required></p>
            <p>Herhaal wachtwoord: <input type="password" name="wachtwoord-check" required></p>            
            <p><input type="submit" value="Wachtwoord wijzigen"></p>
        </form>
        <form id="email-form" method="post" action="<?php echo $thisfile?>">
            <h3 align='center'>Email gegevens</h3>
            <p>E-mail: <input type="text" name="email" required></p>
            <p><input type="submit" value="Nieuw wachtwoord instellen"></p>
        </form>
        <?php
        //error_reporting(E_ALL);
        //ini_set('display_errors', 1);

        if (isset($_GET['wwcode'])) {
            //email link is aangeklikt om hier te komen

            show_pwresetform();

        } else if (isset($_POST['wachtwoord'])) {
            // wijzigingsformulier is net gesubmit
            $email = $_POST['email'];
            $wachtwoord = $_POST['wachtwoord'];
            $wachtwoord_check = $_POST['wachtwoord-check'];

            if ($wachtwoord == $wachtwoord_check) {
                wachtwoord_wijzigen($email, $wachtwoord);
                hide_pwresetform();
            }
            else {
                echo "<p>Het tweede wachtwoord komt niet overeen met het eerste.</p>";
                //header("$thisfile");
            }
        } else if (isset($_POST['email'])) {
            // Email is net opgegeven. Stuur email naar het email adres met de link.
            $email = $_POST['email'];
            $wwcode = get_oldpass($email);
            if (strlen($wwcode) > 0) {
                $link = "localhost" . $thisfile. "?wwcode=$wwcode";                
                $boodschap = "Klik $link om uw wachtwoord te kunnen wijzigen.";                
                $headers = 'From: <webmaster@cms.com>' . "\r\n";
                mail($email, "ww reset", $boodschap, $headers);

                hide_emailform();
                echo "<h4><p>Een email met een link om uw wachtwoord te veranderen is net verstuurd.</p></h4>";
                echo "<h4><p>Klik op de link om uw wachtwoord opnieuw in te stellen.</p></h4>";
                echo "<h4><a href='CMSfrontend_002.php'>Klik hier om terug te gaan.</a></h4>";
            } else {
                echo "<h4>Er is geen account met '$email' als email-adres.</h4>";
                echo "<h4><a href='CMSfrontend_002.php'>Klik hier om terug te gaan.</a></h4>";
            }
        } else {
            // Gebruiker weet wachtwoord niet meer en komt hier eerst terecht. 
            // Vraag email-adres op
            show_emailform();

        }

        function show_pwresetform() {
            ?>
            <script type="text/javascript">
                document.getElementById('pwreset-form').style.display = "block";
            </script>
            <?php
        }

        function get_email($wwcode) {
            $db = dbconnect();
            
            $stmt = $db->prepare("SELECT email FROM Lezers
                                    WHERE wachtwoord = '$wwcode';");

            $stmt->bind_result($email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->fetch();
            
            return ($email);
        }

        function hide_pwresetform() {
            ?>
            <script type="text/javascript">                
                document.getElementById('pwreset-form').style.display = "none";
            </script>
            <?php
        }

        function show_emailform() {
            ?>
            <script type="text/javascript">
                document.getElementById('email-form').style.display = "block";
            </script>
            <?php
        }

        function hide_emailform() {
            ?>
            <script type="text/javascript">
                document.getElementById('email-form').style.display = "none";
            </script>
            <?php
        }

        function get_oldpass($email) {
            $db = dbconnect();
            
            $stmt = $db->prepare("SELECT wachtwoord FROM Lezers
                                    WHERE email = '$email';");

            $stmt->bind_result($wachtwoord);
            $stmt->execute();
            $stmt->store_result();
            $stmt->fetch();

            if ($stmt->num_rows > 0) {
                return $wachtwoord;
            } else {
                return "";
            }                
        }

        function bestaat_al($email) {
            $db = dbconnect();
            
            $stmt = $db->prepare("SELECT email FROM Lezers
                                    WHERE email = '$email';");

            $stmt->execute();
            $stmt->store_result();
            
            return ($stmt->num_rows > 0);
        }

        function wachtwoord_wijzigen($email, $wachtwoord) {
            
            if (bestaat_al($email)) {
                $db = dbconnect();
                $gecodeerde_wachtwoord = md5($wachtwoord); // sla het wachtwoord gecodeerd op
                $stmt = $db->prepare("UPDATE Lezers SET wachtwoord='$gecodeerde_wachtwoord'
                                        WHERE email='$email'");
                $stmt->bind_param("ssss", $email, $gecodeerde_wachtwoord);
                $stmt->execute();
    
                echo "<p>Wachtwoord gewijzigd.</p>";
                echo "<p>U kunt nu inloggen met de net geregistreerde gegevens.</p>";
                echo "<h4><a href='CMSfrontend_002.php'>Klik hier om verder te gaan.</a></h4>";
                
                $stmt->close();
            }
            else {                    
                echo "<p>Account met de email $email bestaat niet.</p>";
                echo "<p>Er is niets gewijzigd.</p>";
                echo "<h4><a href='CMSfrontend_002.php'>Klik hier om verder te gaan.</a></h4>";                    
            }
        }
        ?>

    </body>
</html>
