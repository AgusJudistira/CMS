<!DOCTYPE html>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <head>
    <meta charset="UTF-8">
    <title>CMS Frontend</title>
    <link rel="stylesheet" type="text/css" href="CMSfrontend_002.css" />
  </head>
  <body>
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

        $keuzemenu .= "<div><h4><a href=\"$thisfile\">Alle categorieen</a></h4></div>";

        while ($stmt->fetch()) {
          // $keuzemenu .= "<option value='$id'>$categorienaam</option>";
          $keuzemenu .= "<div><a href=\"$thisfile?cat_id=$id\">$categorienaam</a></div>";
        }
        return $keuzemenu;
      }

      // toon blogs gefilterd op een bepaalde categorie
      function get_blogs_catfiltered($id_cat) {
          $db = dbconnect();
          $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.datuminvoer, categorienamen.categorienaam
                                FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                           LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                                WHERE categorietoekenning.id_categorie = $id_cat
                                ORDER BY Blogs.datuminvoer DESC");

          $stmt->execute();
          $stmt->bind_result($id_blog, $titel, $datuminvoer, $categorie);

          $bloglist = "";
          // echo "<div id='rechterkolom'>";
          $bloglist .= "<table>";
          $bloglist .= "<th>Titel</th><th>Datum publicatie</th><th>Categorie</th>";
          while ($stmt->fetch()) {
              $bloglist .= "<tr>";
              $bloglist .= "<td><a href=\"$thisfile?blog_id=$id_blog\">$titel</a></td><td>$datuminvoer</td><td>$categorie</td>";
              $bloglist .= "</tr>";
          }
          $bloglist .= "</table>";

          //echo "</div>";

          $stmt->close();
          return $bloglist;
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
              $bloglist .= "</tr>";
          }
          $bloglist .= "</table>";

          $stmt->close();
          return $bloglist;
      }

      function get_onefullblog($id_blog) {
          // Laat een volle blog zien
          $db = dbconnect();

          $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.artikel, Blogs.datuminvoer, GROUP_CONCAT(categorienamen.categorienaam SEPARATOR ', ')
                                FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                           LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                                WHERE Blogs.id = $id_blog;");

          $stmt->execute();
          $stmt->bind_result($id_blog, $titel, $artikel, $datuminvoer, $categorie);
          $one_blog = "";
          $one_blog .= "<table>";

          while ($stmt->fetch()) {
              $one_blog .= "<th colspan='2'>$titel</th>";
              $one_blog .= "<tr><td>Datum publicatie: $datuminvoer</td><td>Categorie: $categorie</td></tr>";
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

          $commentaren .= "<form id='commentaarinvoer' method='post' action='$thisfile'>";
          $commentaren .= "Naam: <input id='naam' name='naam' type='text' value='anoniem' required>";
          $commentaren .= "</form>";
          $commentaren .= "<textarea id='commentaar' rows='5' cols='80' name='commentaar' form='commentaarinvoer'>";
          $commentaren .= "Voer een commentaar in...</textarea>";
          $commentaren .= "<input id='sendButton' name='submit' type='submit' value='Verstuur' form='commentaarinvoer'>";

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

      $thisfile = $_SERVER['PHP_SELF'];

      $categoriekeuzemenu = get_categories($thisfile);
      $comments = "";
      $link_naar_secties = "<h3><a href=\"CMSbackend_002.php\">Naar administratie aan de achterkant</a></h3>";

      if (isset($_GET['cat_id'])) {
        // als er een lijst van blogs opgevraagd wordt van een bepaalde categorie
        $id_cat = $_GET['cat_id'];
        $bloglist = get_blogs_catfiltered($id_cat);

      } else if (isset($_GET['blog_id'])) {
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
        } else {
            // een overzicht van alle blogs wordt getoond
            $bloglist = get_bloglist($thisfile);
      }

      ?>

      <div id="linkerkolom">
        <?php
          echo $categoriekeuzemenu;
          echo $link_naar_secties;
        ?>
      </div>
      <div id="rechterkolom">
        <?php
          echo $bloglist;
          echo $comments;
        ?>

    <script src="CMSfrontend_002.js"></script>
  </body>
</html>
