<?php
require_once "dbconnect.php"; // bestand met de login gegevens voor de database
//$thisfile = $_SERVER['PHP_SELF'];

if (isset($_GET['cat_id'])) {
    $id_cat = $_GET['cat_id'];
} else {
    $id_cat = '0';
}

$output = "";
$output .= get_blogs_catfiltered($id_cat, $thisfile);
echo $output;


function get_blogs_catfiltered($id_cat, $thisfile) {
    
    $db = dbconnect();

    if ($id_cat == '0') {
        $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.datuminvoer, GROUP_CONCAT(categorienamen.categorienaam SEPARATOR ', ')
                              FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                         LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                              GROUP BY Blogs.titel
                              ORDER BY Blogs.datuminvoer DESC;");
    }
    else {
        $stmt = $db->prepare("SELECT Blogs.id, Blogs.titel, Blogs.datuminvoer, categorienamen.categorienaam
                              FROM Blogs LEFT JOIN categorietoekenning ON Blogs.id = categorietoekenning.id_blog
                                         LEFT JOIN categorienamen ON categorietoekenning.id_categorie = categorienamen.id
                              WHERE categorietoekenning.id_categorie = $id_cat
                              ORDER BY Blogs.datuminvoer DESC;");
    }

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
?>
