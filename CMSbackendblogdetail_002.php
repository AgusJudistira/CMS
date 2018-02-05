<!DOCTYPE html>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <head>
    <meta charset="UTF-8">
    <title>CMS Backend</title>
    <link rel="stylesheet" type="text/css" href="CMSbackend_002.css" />
  </head>
  <body>
    <h1>Blog details</h1>
    <?php
      require_once "dbconnect.php";
      $thisfile = $_SERVER['PHP_SELF'];

      function get_categories($blog_id) {
        $keuzemenu = "";
        $db = dbconnect();

        $stmt = $db->prepare("SELECT id, categorienaam FROM categorienamen");

        $stmt->execute();
        $stmt->bind_result($id, $categorienaam);
        while ($stmt->fetch()) {
          $keuzemenu .= "<option value='$id'>$categorienaam</option>";
        }
        //echo $keuzemenu;
        return $keuzemenu;
      }


      function get_one_blog($blog_id) {
          //$blog_id = number_format($blog_id);
          GLOBAL $titel, $artikel;
          $db = dbconnect();

          $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.artikel, Blogs.datuminvoer, GROUP_CONCAT(categorienamen.categorienaam SEPARATOR ', ')
                                FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                           LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                                WHERE Blogs.id = $blog_id;");

          $stmt->execute();
          $stmt->bind_result($id, $titel, $artikel, $datuminvoer, $category);

          /* fetch values */
          $one_blog = "";
          $one_blog .= "<table>";
          $one_blog .= "<th>Titel</th><th>Datum publicatie</th><th>Categorie</th>";
          while ($stmt->fetch()) {
            $one_blog .= "<tr>";
            $one_blog .= "<td>$titel</td><td>$datuminvoer</td><td>$category</td>";
            $one_blog .= "</tr>";
            $one_blog .= "<tr><td colspan=\"3\">$artikel</td></tr>";
          }
          $one_blog .= "</table>";

          $stmt->close();
          return $one_blog;
      }

      function blog_bijwerken($blog_id, $titel, $artikel) {
          $db = dbconnect();

          $stmt = $db->prepare("UPDATE Blogs SET titel='$titel', artikel='$artikel'
                                WHERE id = $blog_id");
          //$stmt->bind_param("ss", $blogtitel, $artikel);
          $stmt->execute();

          //$lastid = mysqli_insert_id($db);

          echo "<p>Blog bijgewerkt.</p>";
          //return $lastid;
          $stmt->close();
      }

      function categorie_nogniet_toegevoegd($blog_id, $category_id) {
          $db = dbconnect();

          $stmt = $db->prepare("SELECT id_blog, id_categorie FROM categorietoekenning
                                WHERE (id_blog = $blog_id) AND (id_categorie = $category_id);");
          $stmt->execute();
          $stmt->store_result();

          return ($stmt->num_rows == 0);
      }


      function categorie_toevoegen($blog_id, $category_id) {
          $cat_id = number_format($category_id);
          $db = dbconnect();

          if (categorie_nogniet_toegevoegd($blog_id, $cat_id)) {
            echo "<p>Categorie is nog niet toegevoegd. Nu toevoegen dus</p>";
            $stmt = $db->prepare("INSERT INTO categorietoekenning (id_blog, id_categorie) VALUES (?, ?)");
            $stmt->bind_param("ss", $blog_id, $cat_id);
            $stmt->execute();
            echo "<p>Categorie toegevoegd.</p>";
            $stmt->close();
          }
          else {
            echo "<p>Categorie hoort al bij dit artikel.</p>";
          }
      }


      $titel = "";
      $artikel = "";

      if (isset($_GET['id'])) {
        $blog_id = $_GET['id'];
        //echo "cookie blog id: $blog_id";
        setcookie('blog_id',$blog_id);
      }
      else {
        $blog_id = $_COOKIE['blog_id'];
        //echo "cookie blog id: $blog_id";
      }

      if (isset($_POST['submit'])) {
        $artikel = $_POST['artikel'];
        $titel = $_POST['blogtitel'];
        $cat_id = $_POST['categorie'];

        blog_bijwerken($blog_id, $titel, $artikel);
        categorie_toevoegen($blog_id, $cat_id);
      }

      //echo "<p>Blog id voor get_categories: $blog_id</p>";
      $cat_keuze_menu = get_categories($blog_id);
      //echo "categories created";
      $blog_details = get_one_blog($blog_id);
      echo $blog_details;
      echo "<br />";

      echo "<form id=\"artikelinvoer\" method=\"post\" action=\"$thisfile\">";
      echo "Blogtitel: <input id=\"blogtitel\" name=\"blogtitel\" type=\"text\" value=\"$titel\" required>";
      echo "Categorie:";
      echo "<select name=\"categorie\">";
      echo $cat_keuze_menu;
      echo "</select>";
      echo "</form>";
      echo "<textarea rows=\"5\" cols=\"80\" name=\"artikel\" form=\"artikelinvoer\">";
      echo $artikel;
      echo "</textarea>";
      echo "<input id=\"sendButton\" name=\"submit\" type=\"submit\" value=\"Verstuur\" form=\"artikelinvoer\">";
    ?>

    <h3><a href="CMSbackend_002.php">Terug naar blog administratie</a></h3>

    <script src="CMSbackend_002.js"></script>
  </body>
</html>
