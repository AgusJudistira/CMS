<!DOCTYPE html>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <head>
    <meta charset="UTF-8">
    <title>CMS Backend</title>
    <link rel="stylesheet" type="text/css" href="CMSbackend_002.css" />
  </head>
  <body>
    <h1>Blog administratie</h1>
    <?php
      /* referentiele integriteit wordt met opzet weggelaten om het eventueel
      verwijderen van records makkelijk te maken */
      require_once "dbconnect.php";

      $categoriekeuzemenu = "";

      function get_categories() {
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

      function get_blogsandcats() {
        $link_to_detail = "CMSbackendblogdetail_002.php";
        $db = dbconnect();

        $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.datuminvoer, GROUP_CONCAT(categorienamen.categorienaam SEPARATOR ', ')
                              FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                         LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                              GROUP BY Blogs.titel
                              ORDER BY Blogs.datuminvoer DESC");

        $stmt->execute();
        $stmt->bind_result($id, $titel, $datuminvoer, $category);

        $bloglist = "";

        $bloglist .= "<table>";
        $bloglist .= "<th>Titel</th><th>Datum publicatie</th><th>Categorie</th>";
        while ($stmt->fetch()) {
          $bloglist .= "<tr>";
          $bloglist .= "<td><a href=\"$link_to_detail?id=$id\">$titel</a></td><td>$datuminvoer</td><td>$category</td>";
          $bloglist .= "</tr>";
        }
        $bloglist .= "</table>";

        $stmt->close();
        return $bloglist;
      }


      function insert_blog($blogtitel, $artikel) {

          $db = dbconnect();

          $stmt = $db->prepare("INSERT INTO Blogs (titel, artikel) VALUES (?, ?)");
          $stmt->bind_param("ss", $blogtitel, $artikel);
          $stmt->execute();

          $lastid = mysqli_insert_id($db);


          echo "<p>Blog toegevoegd.</p>";
          return $lastid;
          $stmt->close();
      }

      function insert_category($blog_id, $category_id) {
          $cat_id = number_format($category_id);
          $db = dbconnect();

          $stmt = $db->prepare("INSERT INTO categorietoekenning (id_blog, id_categorie) VALUES (?, ?)");
          $stmt->bind_param("ss", $blog_id, $cat_id);
          $stmt->execute();
          echo "<p>Categorie toegekend.</p>";
          $stmt->close();
      }

      $thisfile = $_SERVER['PHP_SELF'];
      // echo $thisfile;
      $bloglist = get_blogsandcats();
      $categoriekeuzemenu = get_categories();

      if (isset($_POST['submit'])) {
        if (isset($_POST['blogtitel'])) {
          $artikel = $_POST['artikel'];
          $blogtitel = $_POST['blogtitel'];
          $cat_id = $_POST['categorie'];

          $lastid = insert_blog($blogtitel, $artikel);
          insert_category($lastid, $cat_id);

          $bloglist = get_blogsandcats();
        }
      }

      echo $bloglist;
    ?>
    <br />
    <form id="artikelinvoer" method="post" action="<?php echo $thisfile ?>">
      Blogtitel: <input id="blogtitel" name="blogtitel" type="text" value="" required>
      Categorie:
      <select name="categorie">
        <?php
        echo $categoriekeuzemenu;
        ?>
      </select>
    </form>
    <textarea id="editor" rows="5" cols="80" name="artikel" form="artikelinvoer"
    title="Typ '/cg' in om 'Code Gorilla' in te voeren&#013;&#010;
Typ '/ag' in om 'Agus Judistira' in te voeren">
Voer een blog in...</textarea>
    <input id="sendButton" name="submit" type="submit" value="Verstuur" form="artikelinvoer">
    <div id="buffer">
    </div>
    <h3><a href="CMSbackendcategory_002.php">Categorie toevoegen</a></h3>
    <h3><a href="CMSfrontend_002.php">Naar de voorkant</a></h3>

    <script src="CMSbackend_002.js"></script>
  </body>
</html>
