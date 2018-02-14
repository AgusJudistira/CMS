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
    <form id="login-form" method="post" action="<?php $thisfile?>">
            <h3 align='center'>Administrator login</h3>
            <p>Gebruikersnaam: <input type="text" name="gebruikersnaam" required></p>
            <p>Wachtwoord: <input type="password" name="wachtwoord" required></p>
            <p><input type="submit" value="Inloggen"></p>
    </form>
    <?php
      session_start();
      
      $thisfile = $_SERVER['PHP_SELF'];
      $uitloggen = "";
    
      $editor = "<p>";

      $editor .= "<button onclick='underline()' style='font-size:18px'><i class='material-icons'>format_underlined</i></button>";
      $editor .= "<button onclick='bolden()' style='font-size:18px'><i class='material-icons'>format_bold</i></button>";
      $editor .= "<button onclick='italic()' style='font-size:18px'><i class='material-icons'>format_italic</i></button>";
      $editor .= "<button onclick='insertImage()' style='font-size:18px'><i class='material-icons'>insert_photo</i></button>";

      $editor .= "<button onclick='link()' style='font-size:18px'><i class='material-icons'>insert_link</i></button>";
      
      $editor .= "<button onclick='inserthtml()' style='font-size:18px'>HTML invoegen</button>";
      $editor .= "<button onclick='displayhtml()' style='font-size:18px'>Toon HTML</button>";
      $editor .= "</p>";

      $editor .= "<form id='artikelinvoer' method='post' action='<?php echo $thisfile; ?>' onsubmit='javascript: return verwerkArtikel();'>";
      $editor .= "Blogtitel: <input id='blogtitel' name='blogtitel' type='text' value='' title=";
      $editor .= "\"'Typ '/cg' in om 'Code Gorilla' in te voeren&#013;&#010;";
      $editor .= "Typ '/ag' in om 'Agus Judistira' in te voeren&#013;&#010;";
      $editor .= "Typ '/nl' in om 'Nederland' in te voeren&#013;&#010;";
      $editor .= "Typ '/mvg' in om 'Met vriendelijke groet' in te voeren\" required>";
      $editor .= "Categorie:";
      $editor .= "<select name='categorie'>";
      $editor .= $categoriekeuzemenu;
      $editor .= "</select>";
      $editor .= "</form>";
      $editor .= "<div id='editor' contenteditable='true' spellcheck='false' title=\"Typ '/cg' in om 'Code Gorilla' in te voeren&#013;&#010;";
      $editor .= "Typ '/ag' in om 'Agus Judistira' in te voeren&#013;&#010;";
      $editor .= "Typ '/nl' in om 'Nederland' in te voeren&#013;&#010;";
      $editor .= "Typ '/mvg' in om 'Met vriendelijke groet' in te voeren\">";
      $editor .= "<p><br />Voer hier een nieuwe blog in...<br /><br /></p>";
      $editor .= "</div>";
      $editor .= "<input id='hidden' type='hidden' name='artikel' value='$artikel' form='artikelinvoer'>";
      $editor .= "<div>";
      $editor .= "<p><input type='radio' name='commentaar_toegestaan' value='1' checked='checked' form='artikelinvoer'>Commentaar toegestaan</input></p>";
      $editor .= "<p><input type='radio' name='commentaar_toegestaan' value='0' form='artikelinvoer'>Commentaar uitgeschakeld</input></p>";
      $editor .= "</div>";
      $editor .= "<div style='display: inline-flex'>";
      $editor .= "<input id='sendButton' name='submit' type='submit' value='Blog invoeren' form='artikelinvoer'>";
      $editor .= "</div>";

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

      function insert_blog($blogtitel, $artikel, $commentaar_toegestaan) {

          $db = dbconnect();

          $stmt = $db->prepare("INSERT INTO Blogs (titel, artikel, commentaar_toegestaan) VALUES (?, ?, ?)");
          $stmt->bind_param("sss", $blogtitel, $artikel, $commentaar_toegestaan);
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

      function inloggen() {
          $keuzemenu = "";
          $db = dbconnect();

          $gebruikersnaam = $_POST['gebruikersnaam'];
          $coded_password = md5($_POST['wachtwoord']);
          $wachtwoord = $_POST['wachtwoord'];
          /*
          echo "coded password: $coded_password<br />";
          echo "gebruikersnaam: $gebruikersnaam<br />";
          echo "wachtwoord: $wachtwoord<br />";
          */
          $stmt = $db->prepare("SELECT id, gebruikersnaam FROM Administrators
                                WHERE wachtwoord = '$coded_password' AND
                                      gebruikersnaam = '$gebruikersnaam';");

          $stmt->execute();
          $stmt->bind_result($id, $gebruikersnaam);
          $stmt->store_result();

          //echo "number of rows: $stmt->num_rows";
          if ($stmt->num_rows > 0) {
              $_SESSION['gebruikersnaam'] = $gebruikersnaam;
              ?>
              <script type="text/javascript">
                  document.getElementById('login-form').style.display = "none";                  
              </script>
              <?php
          }          
      }

      function uitloggen() {
          session_unset();
          session_destroy();
      }

      if (isset($_POST['uitloggen'])) {
          uitloggen();
          $uitloggen = "";
      }

      if (isset($_POST['gebruikersnaam'])) {
          inloggen();
      }

      if (!isset($_SESSION['gebruikersnaam'])) {
          $editor = "";
        ?>
          <script type="text/javascript">
              
              document.getElementById('login-form').style.display = "block";
              //alert('nog niet ingelogd');
          </script>
        <?php          
      }
      else {
        ?>
          <script type="text/javascript">
              document.getElementById('login-form').style.display = "none";
          </script>
        <?php
        if (isset($_POST['submit'])) {
          if (isset($_POST['blogtitel'])) {
            $artikel = $_POST['artikel'];
            $blogtitel = $_POST['blogtitel'];
            $cat_id = $_POST['categorie'];
            $commentaar_toegestaan = $_POST['commentaar_toegestaan'];

            $lastid = insert_blog($blogtitel, $artikel, $commentaar_toegestaan);
            insert_category($lastid, $cat_id);

            //$bloglist = get_blogsandcats();

          }
        }
        $uitloggen .= "<form id='uitloggen-form' method='post' action='$thisfile'>";
        $uitloggen .= "<p><input type='submit' name='uitloggen' value='Uitloggen'></p>";
        $uitloggen .= "</form>";
        $bloglist = get_blogsandcats();
        $categoriekeuzemenu = get_categories();
      }

    ?>

    <div class="container">
      <div id="linkerkolom">
        <?php //echo "wachtwoord:" . md5('medLey'); ?>
        <h3><a href="CMSbackendcategory_002.php">Categorie toevoegen</a></h3>
        <h3><a href="CMSfrontend_002.php">Naar de voorkant</a></h3>
        <?php echo $uitloggen; ?>
      </div>
      <div id="rechterkolom">
        <?php echo $bloglist; ?>
        <?php echo $editor; ?>
      </div>
    
      <div id="hidden-h2" style="display: none"> 
        <h2>nodig</h2>
      </div>
      <div id="comment-checkbox">
      </div>
    </div>
    <script src="CMSbackend_002.js"></script>
    <script src="wysiwyg-editor.js"></script>
  </body>
</html>
