<!DOCTYPE html>
<html>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <head>
    <meta charset="UTF-8">
    <title>CMS Frontend</title>
    <link rel="stylesheet" type="text/css" href="CMSfrontend_002.css" />
  </head>
  <body>
    <form id="user-login" method="post" action="<?php echo $thisfile?>">
        <h3 align='center'>Gebruikers login</h3>
        <p>E-mail: <input type="text" name="email" required></p>
        <p>Wachtwoord: <input type="password" name="wachtwoord" required></p>
        <p><input type="submit" value="Inloggen"></p>
        <p><a href="create_account.php">Account aanmaken</a></p>
        <p><a href="reset_password.php">Wachtwoord vergeten?</a></p>        
    </form>
    <?php $thisfile = $_SERVER['PHP_SELF']; ?>
    <div id="kop">
        <h1>Welkom op mijn blog!</h1>


        <?php
        require_once "dbconnect.php"; // bestand met de login gegevens voor de database

        // stop alle bestaande categorieen in een string voor keuzemenu (klaar voor <select> tag)
        function get_categories($thisfile) {

            $keuzemenu = "";
            $db = dbconnect();

            $stmt = $db->prepare("SELECT id, categorienaam FROM categorienamen");

            $stmt->execute();
            $stmt->bind_result($id, $categorienaam);

            $keuzemenu .= "<div class=\"categorie\" data-value=\"0\"><h4>Alle categorieen</h4></div>";
            //$keuzemenu .= "<div><h4><a href=\"$thisfile\">Alle categorieen</a></h4></div>";

            while ($stmt->fetch()) {
                $keuzemenu .= "<div class=\"categorie\" data-value=\"$id\">$categorienaam</div>";
                //$keuzemenu .= "<div><a href=\"$thisfile?cat_id=$id\">$categorienaam</a></div>";
            }
            return $keuzemenu;
        }

    /*    //toon blogs gefilterd op een bepaalde categorie (deze is al vervangen door een AJAX versie)
        function get_blogs_catfiltered($id_cat) {
            $db = dbconnect();
            $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.datuminvoer, categorienamen.categorienaam
                                FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                            LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                                WHERE categorietoekenning.id_categorie = $id_cat
                                ORDER BY Blogs.datuminvoer DESC;");

            $stmt->execute();
            $stmt->bind_result($id_blog, $titel, $datuminvoer, $categorie);

            $bloglist = "";
            
            $bloglist .= "<table>";
            $bloglist .= "<th>Titel</th><th>Datum publicatie</th><th>Categorie</th>";
            while ($stmt->fetch()) {
                $bloglist .= "<tr>";
                $bloglist .= "<td><a href=\"$thisfile?blog_id=$id_blog\">$titel</a></td><td>$datuminvoer</td><td>$categorie</td>";
                $bloglist .= "</tr>";
            }
            $bloglist .= "</table>";

            $stmt->close();
            return $bloglist;
        }
    */

        function get_monthlist($thisfile) {
            $db = dbconnect();

            $stmt = $db->prepare("SELECT MONTH(datuminvoer), MONTHNAME(datuminvoer), YEAR(datuminvoer), COUNT(*) 
                                FROM Blogs GROUP BY MONTH(datuminvoer)
                                ORDER BY datuminvoer DESC LIMIT 12;");
            //$stmt->bind_param("ss", $blogtitel, $artikel);
            $stmt->execute();
            $stmt->bind_result($maandnummer, $maand, $jaar, $aantal_artikelen);

            $monthlist = "";
            $monthlist .= "<table id='maandpublicatietabel'>"; //border='0' cellspacing='0' cellpadding='0'
            $monthlist .= "<tr><td><b>Publicaties in maand:&nbsp&nbsp</b></td><td><b>Aantal</b></td></tr>";
            while ($stmt->fetch()) {
                if ($aantal_artikelen > 0) {
                    $monthlist .= "<tr class='maandpublicatie' data-value='$maandnummer'>";
                    $monthlist .= "<td>$maand $jaar</td><td align='right'>$aantal_artikelen</td>";
                    $monthlist .= "</tr>";
                }
            }
            $monthlist .= "</table>";

            $stmt->close();
            return $monthlist;
        }


        function get_bloglist($thisfile) {

            $db = dbconnect();

            $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.datuminvoer, GROUP_CONCAT(categorienamen.categorienaam SEPARATOR ', ')
                                FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                            LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                                GROUP BY Blogs.titel
                                ORDER BY Blogs.datuminvoer DESC");
            //$stmt->bind_param("ss", $blogtitel, $artikel);
            $stmt->execute();
            $stmt->bind_result($id_blog, $titel, $datuminvoer, $categorie);

            $bloglist = "";
            $bloglist .= "<table>";
            $bloglist .= "<th>Titel</th><th>Datum publicatie</th><th>Categorie</th>";
            while ($stmt->fetch()) {
                $bloglist .= "<tr>";
                $bloglist .= "<td><a href=\"$thisfile?blog_id=$id_blog\">$titel</a></td><td>$datuminvoer</td><td>$categorie</td>";
                //$bloglist .= "<td>$titel</td><td>$datuminvoer</td><td>$categorie</td>";
                $bloglist .= "</tr>";
            }
            $bloglist .= "</table>";

            $stmt->close();
            return $bloglist;
        }

        function get_onefullblog($id_blog) {
            // Laat een volle blog zien
            $db = dbconnect();

            $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.artikel, Blogs.datuminvoer, Blogs.datumupdate, GROUP_CONCAT(categorienamen.categorienaam SEPARATOR ', ')
                                FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                            LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                                WHERE Blogs.id = $id_blog;");

            $stmt->execute();
            $stmt->bind_result($id_blog, $titel, $artikel, $datuminvoer, $datumupdate, $categorie);
            $one_blog = "";
            $one_blog .= "<table>";

            while ($stmt->fetch()) {
                $one_blog .= "<th colspan='2'><h2>$titel</h2></th>";
                $one_blog .= "<tr><td>Datum publicatie: $datuminvoer<br />Datum update: $datumupdate</td><td>Categorie: $categorie</td></tr>";
                $one_blog .= "<tr>";
                $one_blog .= "<td colspan='2'>$artikel</td>";
                $one_blog .= "</tr>";
            }

            $one_blog .= "</table>";

            $stmt->close();
            return $one_blog;
        }


        function comments_allowed($id_blog) {
            $db = dbconnect();
            $stmt = $db->prepare("SELECT commentaar_toegestaan
                                FROM Blogs
                                WHERE Blogs.id = $id_blog;");

            $stmt->execute();
            $stmt->bind_result($commentaar_toegestaan);
            $stmt->fetch();
            return $commentaar_toegestaan;
        }


        function get_comments($id_blog, $thisfile) {
            // Laat de commentaren bij een blog zien

            $voornaam = $_COOKIE['voornaam'];
            $achternaam = $_COOKIE['achternaam'];


            $db = dbconnect();

            if (!comments_allowed($id_blog)) {
            return "<p>Commentaren zijn voor dit artikel uitgeschakeld.</p>";
            }

            $stmt = $db->prepare("SELECT commentaren.naam, commentaren.commentaar
                                FROM commentaren JOIN Blogs ON Blogs.id = commentaren.id_blog
                                WHERE commentaren.id_blog = $id_blog
                                ORDER BY commentaren.id;");

            $stmt->execute();
            $stmt->bind_result($naam, $commentaar);
            $commentaren = "<p>Commentaren van lezers:</p>";


            while ($stmt->fetch()) {
                $commentaren .= "<p><table>";
                $commentaren .= "<tr><td>Commentaar van: $naam</td></tr>";
                $commentaren .= "<tr><td>$commentaar</td></tr>";
                $commentaren .= "</table></p>";
            }

            if (strlen($voornaam) > 0) {
                $commentaren .= "<form id='commentaarinvoer' method='post' action='$thisfile'>";
                $commentaren .= "<input type='hidden' name='naam' value='$voornaam $achternaam'>";
                $commentaren .= "<div>Naam: $voornaam $achternaam</div>";
                $commentaren .= "</form>";
                $commentaren .= "<textarea id='commentaar' rows='5' cols='80' name='commentaar' form='commentaarinvoer'>";
                $commentaren .= "Voer een commentaar in...</textarea>";
                $commentaren .= "<input id='sendButton' name='submit' type='submit' value='Verstuur' form='commentaarinvoer'>";
            }
            else {
                $commentaren .= "<p><b><i>Log in om commentaren te kunnen geven.</i></b></p>";
            }

            $stmt->close();
            return $commentaren;
        }

        function commentaar_invoeren($id_blog, $naam, $commentaar) {

            $db = dbconnect();

            $stmt = $db->prepare("INSERT INTO commentaren (id_blog, naam, commentaar) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $id_blog, $naam, $commentaar);
            $stmt->execute();
            //echo "<p>Commentaar toegevoegd.</p>";
            $stmt->close();
        }

        function maak_zoek_functie($thisfile) {
            $zoek_formulier = "";
            $zoek_formulier .= "<form id='frontend-zoekform' method='get' action='$thisfile'>";
            $zoek_formulier .= "<b>Artikels opzoeken: </b><input id='zoekstring' name='zoekstring' type='text' size='40'></input>";
            $zoek_formulier .= " <input type='submit' value='Opzoeken'>";
            $zoek_formulier .= "</form><br />";

            return $zoek_formulier;
        }

        function zoek_blogs($zoekstring) {
            $db = dbconnect();

            $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.datuminvoer, GROUP_CONCAT(categorienamen.categorienaam SEPARATOR ', ')
                                FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                        LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                                WHERE Blogs.titel LIKE '%$zoekstring%' OR
                                    Blogs.artikel LIKE '%$zoekstring%' OR
                                    Blogs.inleiding LIKE '%$zoekstring%' OR
                                    Blogs.subtitel LIKE '%$zoekstring%'
                                GROUP BY Blogs.titel
                                ORDER BY Blogs.datuminvoer DESC;");
            //$stmt->bind_param("ss", $blogtitel, $artikel);
            $stmt->execute();
            $stmt->bind_result($id_blog, $titel, $datuminvoer, $categorie);

            $bloglist = "";
            $bloglist .= "<table>";
            $bloglist .= "<th>Titel</th><th>Datum publicatie</th><th>Categorie</th>";
            while ($stmt->fetch()) {
                $bloglist .= "<tr>";
                $bloglist .= "<td><a href=\"$thisfile?blog_id=$id_blog\">$titel</a></td><td>$datuminvoer</td><td>$categorie</td>";
                //$bloglist .= "<td>$titel</td><td>$datuminvoer</td><td>$categorie</td>";
                $bloglist .= "</tr>";
            }
            $bloglist .= "</table>";

            $stmt->close();
            return $bloglist;
        }

        function user_login($email, $wachtwoord) {
            //echo "<p>In user login.</p>";
            $db = dbconnect();

            $coded_password = md5($_POST['wachtwoord']);
            
            /*
            echo "coded password: $coded_password<br />";
            echo "gebruikersnaam: $gebruikersnaam<br />";
            echo "wachtwoord: $wachtwoord<br />";
            */
            $stmt = $db->prepare("SELECT email, voornaam, achternaam FROM Lezers
                                WHERE wachtwoord = '$coded_password' AND
                                        email = '$email';");

            $stmt->execute();
            $stmt->bind_result($email, $voornaam, $achternaam);
            $stmt->store_result();
            $stmt->fetch();

            if ($stmt->num_rows > 0) {
                //cookies zijn 2 uur geldig. m.a.w. na 2 uur wordt de gebruiker automatisch uitgelogd
                setcookie('email',$email, time() + 7200); 
                setcookie('voornaam',$voornaam, time() + 7200);
                setcookie('achternaam',$achternaam, time() + 7200);
                // om de cookies snel te wijzigen
                $_COOKIE['email'] = $email;
                $_COOKIE['voornaam'] = $voornaam;
                $_COOKIE['achternaam'] = $achternaam;
                ?>
                <script type="text/javascript">
                    document.getElementById('user-login').style.display = "none";                  
                </script>
                <?php
            }
            else {
                //echo "<p>Inloggen mislukt. Wachtwoord en/of email niet juist</p>";
                ?>
                <script type="text/javascript">
                    alert("Inloggen mislukt. Wachtwoord en/of email niet juist");
                </script>
                <?php
            }
        }

        function gebruiker_uitloggen() {
            // verwijder de cookies door de geldigheid van een uur geleden in te stellen
            setcookie("email", "", time() - 3600);
            setcookie("voornaam", "", time() - 3600);
            setcookie("achternaam", "", time() - 3600);
            // om de cookies snel te wijzigen
            $_COOKIE['email'] = '';
            $_COOKIE['voornaam'] = '';
            $_COOKIE['achternaam'] = '';
        }

        $categoriekeuzemenu = get_categories($thisfile);
        $zoekfunctie = maak_zoek_functie($thisfile);
        $comments = "";
        $uitlog_button = "";
        $link_naar_secties = "<h3><a href=\"CMSbackend_002.php\">Naar administratie aan de achterkant</a></h3>";
        $maanden = get_monthlist($thisfile);
        echo $zoekfunctie;

        if (isset($_GET['blog_id'])) {
            // als er gefocust wordt op een blog
            $id_blog = $_GET['blog_id'];
            setcookie('blog_id',$id_blog);
            $bloglist = get_onefullblog($id_blog);
            $comments = get_comments($id_blog, $thisfile);
            $link_naar_secties = "<h3><a href=\"CMSfrontend_002.php\">Terug naar overzicht</a></h3>";
            } else if (isset($_POST['commentaar'])) {
                // als er net een commentaar op een blog gesubmit wordt
                $commentaar = $_POST['commentaar'];
                $naam = $_POST['naam'];
                $id_blog = $_COOKIE['blog_id'];

                commentaar_invoeren($id_blog, $naam, $commentaar);
                $bloglist = get_onefullblog($id_blog);
                $comments = get_comments($id_blog, $thisfile);
                $link_naar_secties = "<h3><a href=\"CMSfrontend_002.php\">Terug naar overzicht</a></h3>";
            } else if (isset($_GET['zoekstring'])) {
                $zoekstring = $_GET['zoekstring'];
                $bloglist = zoek_blogs($zoekstring);
            } else if (isset($_POST['email'])) {
                // Inlogformulier net ingevoerd. Nu logingegevens valideren
                $email = $_POST['email'];
                $wachtwoord = $_POST['wachtwoord'];
                $bloglist = get_bloglist($thisfile);
                user_login($email, $wachtwoord);

            } else if (isset($_POST['uitloggen'])) {
                gebruiker_uitloggen();
                $bloglist = get_bloglist($thisfile);
                header("location: $thisfile"); 
                
            } else if (isset($_POST['inloggen'])) {
                // als iemand op de link 'inloggen' heeft geklikt
                ?>
                <script type="text/javascript">
                    document.getElementById('user-login').style.display = "block";
                </script>
                <?php
                $bloglist = get_bloglist($thisfile);
            }
            else {
                // een overzicht van alle blogs wordt getoond
                $bloglist = get_bloglist($thisfile);
        }

        if (isset($_COOKIE['email'])) {
            $voornaam = $_COOKIE['voornaam'];
            $achternaam = $_COOKIE['achternaam'];
            
            $inlog_button = "<h4>$voornaam $achternaam is ingelogd en kan commentaren invoeren.</h4>";
            
            $uitlog_button .= "<form id='user-logout-button' method='post' action='$thisfile'>";
            $uitlog_button .= "<p><input type='submit' name='uitloggen' value='Uitloggen'></p>";
            $uitlog_button .= "</form>";
            
        } else {
            $uitlog_button = "";

            $inlog_button = "<form id='user-login-button' method='post' action='$thisfile'>";
            $inlog_button .= "<p><input type='submit' name='inloggen' value='Inloggen'></p>";
            $inlog_button .= "</form>";
            
        }
        
        ?>
    </div>
    <div id="linkerkolom">
        <?php
        echo $categoriekeuzemenu;
        echo $inlog_button;
        echo $uitlog_button;
        echo $maanden;
        echo $link_naar_secties;
        ?>
    </div>
    <div id="rechterkolom">
        <?php
        echo $bloglist;
        echo $comments;
        ?>
    </div>

    <script src="CMSfrontend_002.js"></script>
  </body>
</html>
