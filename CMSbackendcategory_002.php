<!DOCTYPE html>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <head>
    <meta charset="UTF-8">
    <title>CMS Backend - Categorie toevoegen</title>
    <link rel="stylesheet" type="text/css" href="CMSbackend_002.css" />
  </head>
  <body>
    <?php
      require_once "dbconnect.php";

      function get_categories() {

        $db = dbconnect();

        $stmt = $db->prepare("SELECT id, categorienaam FROM categorienamen");

        $stmt->execute();
        $stmt->bind_result($id, $categorienaam);
        $output = "<table>";
        $output .= "<th>Category id</th><th>Category name</th>";

        while ($stmt->fetch()) {
          $output .= "<tr>";
          $output .= "<td>$id</td><td>$categorienaam</td>";
          $output .= "</tr>";
        }

        $output .= "</table>";

        return $output;
      }

      function insert_category($category) {

          $db = dbconnect();

          $stmt = $db->prepare("INSERT INTO categorienamen (categorienaam) VALUES (?)");
          $stmt->bind_param("s", $category);
          $stmt->execute();

          $lastid = mysqli_insert_id($db);

          // echo "<p>Categorie toegevoegd.</p>";
          $stmt->close();
      }

      $thisfile = $_SERVER['PHP_SELF'];
      // echo $thisfile;
      $categories = get_categories();

      if (isset($_POST['submit'])) {
        if (isset($_POST['categorie'])) {
          $category = $_POST['categorie'];

          insert_category($category);
          $categories = get_categories();
        }
      }

    ?>
    <div id="kop">
      <h1>Categorieen toevoegen</h1>
    </div>
    <div id="rechterkolom">
      <?php
      echo $categories;
      ?>
      <p>
        <form id="categorieinvoer" method="post" action="<?php echo $thisfile ?>">
          Nieuwe categorie: <input id="categorie" name="categorie" type="text" value="" required>
          <input id="sendButton" name="submit" type="submit" value="Toevoegen" form="categorieinvoer">
        </form>
      </p>

    </div>
    <div id = "linkerkolom">
      <h3><a href="CMSbackend_002.php">Terug naar blogbeheer</a></h3>
    </div>
  </body>
</html>
