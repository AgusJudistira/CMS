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
          GLOBAL $titel, $artikel, $datuminvoer, $datumupdate, $categorie;
          $db = dbconnect();

          $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.artikel, Blogs.datuminvoer, Blogs.datumupdate, GROUP_CONCAT(categorienamen.categorienaam SEPARATOR ', ')
                                FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                           LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                                WHERE Blogs.id = $blog_id;");

          $stmt->execute();
          $stmt->bind_result($id, $titel, $artikel, $datuminvoer, $datumupdate, $categorie);

          /* fetch values */
          $one_blog = "";
          $one_blog .= "<table>";
          //$one_blog .= "<th>Titel</th><th>Datum publicatie</th><th>Categorie</th>";
          while ($stmt->fetch()) {
            $one_blog .= "<th colspan='2'>$titel</th>";
            $one_blog .= "<tr><td>Datum publicatie: $datuminvoer</td><td>Categorie: $categorie</td></tr>";
            $one_blog .= "<tr><td colspan='2'>$artikel</td></tr>";
          }
          $one_blog .= "</table>";

          $stmt->close();
          return $one_blog;
      }

      function blog_bijwerken($blog_id, $titel, $artikel) {
          $db = dbconnect();
          $datumupdate = date("y-m-d H:m:s");

          $stmt = $db->prepare("UPDATE Blogs SET titel='$titel', artikel='$artikel', datumupdate='$datumupdate'
                                WHERE id = $blog_id");

          $stmt->execute();

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

      function get_comments($blog_id) {
          // Laat de commentaren bij een blog zien
          $db = dbconnect();

          $stmt = $db->prepare("SELECT commentaren.id, commentaren.naam, commentaren.commentaar
                                FROM commentaren JOIN Blogs ON Blogs.id = commentaren.id_blog
                                WHERE commentaren.id_blog = $blog_id
                                ORDER BY commentaren.id;");

          $stmt->execute();
          $stmt->bind_result($commentaar_id, $naam, $commentaar);
          $commentaren = "<p>Commentaren van lezers:</p>";

          $commentaren .= "<form id='commentaren-form' method='post' action='$thisfile'>";
          while ($stmt->fetch()) {
              $commentaren .= "<p><table>";
              $commentaren .= "<tr><td>Commentaar van: $naam</td><td>";
              $commentaren .= "<input id='deleteButton' name='verwijder' type='submit' value='Verwijder (#$commentaar_id)' form='commentaren-form'></td></tr>";
              $commentaren .= "<tr><td colspan='2'>$commentaar</td></tr>";
              $commentaren .= "</table></p>";
          }
          $commentaren .= "</form>";
          $stmt->close();
          return $commentaren;
      }

      function verwijder_commentaar($commentaar_id) {
          $db = dbconnect();

          $stmt = $db->prepare("DELETE FROM commentaren
                                WHERE commentaren.id = $commentaar_id;");

          $stmt->execute();
          $stmt->close();
      }


      function comments_allowed($blog_id) {
          $db = dbconnect();
          $stmt = $db->prepare("SELECT commentaar_toegestaan
                                FROM Blogs
                                WHERE id = $blog_id;");

          $stmt->execute();
          $stmt->bind_result($commentaar_toegestaan);
          $stmt->fetch();
          //echo "<p>in comments_allowed($blog_id)- commentaar_toegestaan: $commentaar_toegestaan</p>";
          return $commentaar_toegestaan;
      }


      function get_comments_allowed($blog_id, $thisfile) {
          //echo "in get_comments_allowed";

          if (comments_allowed($blog_id)) {

              $input_tag = "<input type=\"radio\" name=\"commentaar_toegestaan\" value=\"1\" checked=\"checked\"> Commentaar toegestaan<br>" .
                           "<input type=\"radio\" name=\"commentaar_toegestaan\" value=\"0\"> Commentaar uitgeschakeld<br>";

              //$input_tag = "<p><input type=\"checkbox\" name=\"commentaar_toegestaan\" value=\"0\" checked=\"checked\">Commentaren toegestaan</p>";
              //$input_tag = "<p><input id=\"commentaar_toegestaan\" type=\"checkbox\" name=\"commentaar_toegestaan\" value=\"0\" checked=\"checked\">Commentaren toegestaan</p>";

          } else {

              $input_tag = "<input type=\"radio\" name=\"commentaar_toegestaan\" value=\"1\"> Commentaar toegestaan<br>" .
                           "<input type=\"radio\" name=\"commentaar_toegestaan\" value=\"0\" checked=\"checked\"> Commentaar uitgeschakeld<br>";
              //$input_tag = "<p><input id=\"commentaar_toegestaan\" type=\"checkbox\" name=\"commentaar_toegestaan\" value=\"1\">Commentaren toegestaan</p>";

          }
          $comment_allowance_form = "<form id=\"comment-checkbox\" name=\"comment-checkbox\" method=\"post\" action=\"$thisfile\" >";
          $comment_allowance_form .= $input_tag;
          $comment_allowance_form .= "</form>";

          return $comment_allowance_form;
      }

      function commentaar_toegestaan_bijwerken($blog_id, $checkbox_value) {
          $db = dbconnect();
          /*
          echo "UPDATE Blogs SET commentaar_toegestaan = $checkbox_value
                                WHERE id = $blog_id";
          */
          $stmt = $db->prepare("UPDATE Blogs SET commentaar_toegestaan=$checkbox_value
                                WHERE id = $blog_id");

          $stmt->execute();

          //echo "<p>Commentaar toegestaan bijgewerkt.</p>";

          $stmt->close();
      }

      $titel = "";
      $artikel = "";

      if (isset($_GET['id'])) {
        $blog_id = $_GET['id'];
        setcookie('blog_id',$blog_id);
      }
      else {
        $blog_id = $_COOKIE['blog_id'];
      }

      if (isset($_POST['verwijder'])) {
        $commentaar_id = filter_var($_POST['verwijder'], FILTER_SANITIZE_NUMBER_INT);
        //echo "Commentaar id: $commentaar_id zal verwijderd worden";
        verwijder_commentaar($commentaar_id);
      }

      /*
      echo "\$_POST['commentaar_toegestaan']: " . $_POST['commentaar_toegestaan'] . "<br />";
      echo "\$_POST['comment-checkbox']: " . $_POST['commentaar_checkbox'] . "<br />";
      */

      if (isset($_POST['commentaar_toegestaan'])) {
          $checkbox_value = $_POST['commentaar_toegestaan'];
          commentaar_toegestaan_bijwerken($blog_id, $checkbox_value);
      } else if (isset($_POST['submit'])) {

                $artikel = $_POST['artikel'];
                $titel = $_POST['blogtitel'];
                $cat_id = $_POST['categorie'];

                blog_bijwerken($blog_id, $titel, $artikel);
                categorie_toevoegen($blog_id, $cat_id);
      }

      $cat_keuze_menu = get_categories($blog_id);
      $blog_details = get_one_blog($blog_id);
      $commentaren = get_comments($blog_id);
      $commentaar_toegestaan = get_comments_allowed($blog_id, $thisfile);
    ?>
    <div id="linkerkolom">
        <h1>Blog details</h1>
        <h3><a href="CMSbackend_002.php">Terug naar blog administratie</a></h3>
    </div>
    <div id="rechterkolom">
      <?php
        //echo $blog_details;
        //echo "<br />";
      ?>
    <p>

      <button onclick="underline()" style="font-size:24px"><i class="material-icons">format_underlined</i></button>
      <button onclick="bolden()" style="font-size:18px"><i class="material-icons">format_bold</i></button>
      <button onclick="italic()" style="font-size:18px"><i class="material-icons">format_italic</i></button>
      <button onclick="insertImage()" style="font-size:18px"><i class="material-icons">insert_photo</i></button>
      <button onclick="link()" style="font-size:18px"><i class="material-icons">insert_link</i></button>
      <!-- <button onclick="link()">Link</button> -->
      <button onclick="displayhtml()" style="font-size:18px">Toon HTML</button>
    </p>

    <table>
    <form id="artikelinvoer" method="post" action="<?php echo $thisfile; ?>" onsubmit="javascript: return verwerkArtikel();">
        <tr><td>Blogtitel: <input id="blogtitel" name="blogtitel" type="text" value="<?php echo $titel; ?>" title="Typ '/cg' in om 'Code Gorilla' in te voeren&#013;&#010;
Typ '/ag' in om 'Agus Judistira' in te voeren&#013;&#010;
Typ '/nl' in om 'Nederland' in te voeren&#013;&#010;
Typ '/mvg' in om 'Met vriendelijke groet' in te voeren" required>
            <td>Categorieen: <?php echo $categorie ?></td>
            <tr>
                <td>Datuminvoer: <?php echo $datuminvoer ?><br />Datumupdate: <?php echo $datumupdate ?></td>
                <td>Categorie toevoegen:
                    <select name="categorie">
                        <?php echo $cat_keuze_menu ?>
                    </select>
                </td>
            </tr>
    </form>
        <tr><td colspan='2'>
    <!-- Make it content editable attribute true so that we can edit inside the div tag and also enable execCommand to edit content inside it.-->
            <div id="editor" contenteditable="true" spellcheck="false" title="Typ '/cg' in om 'Code Gorilla' in te voeren&#013;&#010;
Typ '/ag' in om 'Agus Judistira' in te voeren&#013;&#010;
Typ '/nl' in om 'Nederland' in te voeren&#013;&#010;
Typ '/mvg' in om 'Met vriendelijke groet' in te voeren">
                <?php echo $artikel ?>
            </div>
        </td></tr>
        <input id="hidden" type="hidden" name="artikel" value="<?php $artikel ?>" form="artikelinvoer">
    </table>
    <p>
        <input id="sendButton" name="submit" type="submit" value="Verstuur" form="artikelinvoer">
    </p>
    <div class="codeoutput">
        <!-- <pre> tags reserves whitespace and newline characters. -->
        <p class="htmloutput">
        </p>
    </div>

    <?php
    echo $commentaar_toegestaan;
    echo $commentaren;
    ?>
    <!--
      <textarea id="editor" rows="5" cols="80" name="artikel" form="artikelinvoer"
      title="Typ '/cg' in om 'Code Gorilla' in te voeren&#013;&#010;
Typ '/ag' in om 'Agus Judistira' in te voeren&#013;&#010;
Typ '/nl' in om 'Nederland' in te voeren&#013;&#010;
Typ '/mvg' in om 'Met vriendelijke groet' in te voeren">

</textarea>

</div> -->

    <script src="CMSbackend_002.js"></script>
    <script src="wysiwyg-editor.js"></script>
  </body>
</html>
