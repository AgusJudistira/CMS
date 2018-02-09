<!DOCTYPE html>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <head>
    <meta charset="UTF-8">
    <title>CMS Backend</title>
    <link rel="stylesheet" type="text/css" href="CMSbackend_002.css" />
    <link rel="stylesheet" type="text/css" href="wysiwyg-editor.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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


    ?>
    <div class="row">
      <div id="linkerkolom">
        <h3><a href="CMSbackendcategory_002.php">Categorie toevoegen</a></h3>
        <h3><a href="CMSfrontend_002.php">Naar de voorkant</a></h3>
      </div>
      <div id="rechterkolom">
        <?php echo $bloglist; ?>
        <p>

          <button onclick="underline()" style="font-size:24px"><i class="material-icons">format_underlined</i></button>
          <button onclick="bolden()" style="font-size:18px"><i class="material-icons">format_bold</i></button>
          <button onclick="italic()" style="font-size:18px"><i class="material-icons">format_italic</i></button>
          <button onclick="insertImage()" style="font-size:18px"><i class="material-icons">insert_photo</i></button>
          <button onclick="link()" style="font-size:18px"><i class="material-icons">insert_link</i></button>
          <!-- <button onclick="link()">Link</button> -->
          <button onclick="displayhtml()" style="font-size:18px">Toon HTML</button>
        </p>

        <form id="artikelinvoer" method="post" action="<?php echo $thisfile; ?>" onsubmit="javascript: return verwerkArtikel();">
        Blogtitel: <input id="blogtitel" name="blogtitel" type="text" value="" title=
        "Typ '/cg' in om 'Code Gorilla' in te voeren&#013;&#010;
  Typ '/ag' in om 'Agus Judistira' in te voeren&#013;&#010;
  Typ '/nl' in om 'Nederland' in te voeren&#013;&#010;
  Typ '/mvg' in om 'Met vriendelijke groet' in te voeren" required>
        Categorie:
          <select name="categorie">
            <?php
            echo $categoriekeuzemenu;
            ?>
          </select>
        </form>

        <div id="editor" contenteditable="true" spellcheck="false" title="Typ '/cg' in om 'Code Gorilla' in te voeren&#013;&#010;
Typ '/ag' in om 'Agus Judistira' in te voeren&#013;&#010;
Typ '/nl' in om 'Nederland' in te voeren&#013;&#010;
Typ '/mvg' in om 'Met vriendelijke groet' in te voeren">
            <p><br />Voer hier een nieuwe blog in...<br /><br /></p>
        </div>
        <input id="hidden" type="hidden" name="artikel" value="<?php $artikel ?>" form="artikelinvoer">

        <input id="sendButton" name="submit" type="submit" value="Verstuur" form="artikelinvoer">
    </div>
  </div>
    <!-- <div id="buffer">
    </div> -->

    <script src="CMSbackend_002.js"></script>
    <script src="wysiwyg-editor.js"></script>
  </body>
</html>
