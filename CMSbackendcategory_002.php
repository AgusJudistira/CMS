<!DOCTYPE html>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <head>
    <meta charset="UTF-8">
    <title>CMS Backend - Categorie toevoegen</title>
    <link rel="stylesheet" type="text/css" href="CMSbackend_002.css" />
  </head>
  <body>
    <h1>Categorieen toevoegen</h1>
    <?php

      require_once "dbconnect.php";

      function show_categories() {

        $db = dbconnect();

        $stmt = $db->prepare("SELECT id, categorienaam FROM categorienamen");

        $stmt->execute();
        $stmt->bind_result($id, $categorienaam);
        echo "<table border='1' bordercollapse = 'collapsed'>";
        echo "<th>Category id</th><th>Category name</th>";
        while ($stmt->fetch()) {
          echo "<tr>";
          echo "<td>$id</td><td>$categorienaam</td>";
          echo "</tr>";
        }
        echo "</table>";

      }

      function insert_category($category) {

          $db = dbconnect();

          $stmt = $db->prepare("INSERT INTO categorienamen (categorienaam) VALUES (?)");
          $stmt->bind_param("s", $category);
          $stmt->execute();

          $lastid = mysqli_insert_id($db);

          echo "<p>Categorie toegevoegd.</p>";
          $stmt->close();
      }


      $thisfile = $_SERVER['PHP_SELF'];
      // echo $thisfile;
      show_categories();

      if (isset($_POST['submit'])) {
        if (isset($_POST['categorie'])) {
          $category = $_POST['categorie'];

          insert_category($category);
          show_categories();
        }
      }

    ?>

    <form id="categorieinvoer" method="post" action="<?php echo $thisfile ?>">
      Nieuwe categorie: <input id="categorie" name="categorie" type="text" value="" required>

    </form>

    <input id="sendButton" name="submit" type="submit" value="Toevoegen" form="categorieinvoer">

    <h3><a href="CMSbackend_002.php">Terug naar blogbeheer</a></h3>
  </body>
</html>
