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

      $thisfile = $_SERVER['PHP_SELF'];

      $categoriekeuzemenu = ""; // om categorie keuzemenu html tags in te bewaren

      // stop alle bestaande categorieen in een string voor keuzemenu (klaar voor <select> tag)
      function get_categories($thisfile) {

        $keuzemenu = "";
        $db = dbconnect();

        $stmt = $db->prepare("SELECT id, categorienaam FROM categorienamen");

        $stmt->execute();
        $stmt->bind_result($id, $categorienaam);

        $keuzemenu .= "<div><a href=\"$thisfile\">Alle categorieen</a></div>";

        while ($stmt->fetch()) {
          // $keuzemenu .= "<option value='$id'>$categorienaam</option>";
          $keuzemenu .= "<div><a href=\"$thisfile?cat_id=$id\">$categorienaam</a></div>";
        }
        return $keuzemenu;
      }

      // toon blogs gefilterd op een bepaalde categorie
      function show_blogs_catfiltered($id_cat) {
          $db = dbconnect();
          $stmt = $db->prepare("SELECT Blogs.titel, Blogs.datuminvoer, categorienamen.categorienaam
                                FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                           LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                                WHERE categorietoekenning.id_categorie = $id_cat
                                ORDER BY Blogs.datuminvoer DESC");

          $stmt->execute();
          $stmt->bind_result($titel, $datuminvoer, $categorie);

          $bloglist = "";
          // echo "<div id='rechterkolom'>";
          $bloglist .= "<table>";
          $bloglist .= "<th>Titel</th><th>Datum publicatie</th><th>Categorie</th>";
          while ($stmt->fetch()) {
            $bloglist .= "<tr>";
            $bloglist .= "<td>$titel</td><td>$datuminvoer</td><td>$categorie</td>";
            $bloglist .= "</tr>";
          }
          $bloglist .= "</table>";

          //echo "</div>";

          $stmt->close();
          return $bloglist;
      }


      function show_bloglist() {

          $db = dbconnect();

          $stmt = $db->prepare("SELECT Blogs.titel, Blogs.datuminvoer, GROUP_CONCAT(categorienamen.categorienaam SEPARATOR ', ')
                                FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                           LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                                GROUP BY Blogs.titel
                                ORDER BY Blogs.datuminvoer DESC");
          //$stmt->bind_param("ss", $blogtitel, $artikel);
          $stmt->execute();
          $stmt->bind_result($titel, $datuminvoer, $categorie);

          // echo "<div id='rechterkolom'>";
          $blogist = "";
          $bloglist .= "<table>";
          $bloglist .= "<th>Titel</th><th>Datum publicatie</th><th>Categorie</th>";
          while ($stmt->fetch()) {
            $bloglist .= "<tr>";
            $bloglist .= "<td>$titel</td><td>$datuminvoer</td><td>$categorie</td>";
            $bloglist .= "</tr>";
          }
          $bloglist .= "</table>";
          // echo "</div>";

          $stmt->close();
          return $bloglist;
      }


      $categoriekeuzemenu = get_categories($thisfile);

      if (isset($_GET['cat_id'])) {
        $id_cat = $_GET['cat_id'];
        $bloglist = show_blogs_catfiltered($id_cat);
      } else {
          $bloglist = show_bloglist();
      }
      ?>

      <div id="linkerkolom">
        <?php
          echo $categoriekeuzemenu;
        ?>
      </div>
      <div id="rechterkolom">
        <?php
          echo $bloglist;
        ?>
        <h3><a href="CMSbackend_002.php">Naar administratie aan de achterkant</a></h3>
      </div>
        <!-- <form id="categoriekeuze" method="post" action="">
          <br />Filter blogs op categorie: <select name="categorie"> -->

      <!--    </select>
          </form>
        <input id="sendButton" name="submit" type="submit" value="Verstuur" form="categoriekeuze"> -->


    <script src="CMSfrontend_002.js"></script>
  </body>
</html>
