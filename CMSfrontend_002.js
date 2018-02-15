var categorieen = document.getElementsByClassName('categorie');
var maanden = document.getElementsByClassName('maandpublicatie');
var rechterkolom = document.getElementById('rechterkolom');

//alert('categorieen.length ='+categorieen.length);

for (var i = 0; i < maanden.length; i++) {
    maanden[i].onclick = function() {
        var maandnummer = this.getAttribute('data-value');
        getBloglistWithMonth(maandnummer);
    }
}

for (var i = 0; i < categorieen.length; i++) {
    categorieen[i].onclick = function() {
        var cat_id = this.getAttribute('data-value');
        getBloglistWithCat(cat_id);
    }
}

function getBloglistWithCat(cat_id) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {

      if (this.readyState == 4 && this.status == 200) {
          rechterkolom.innerHTML = this.responseText;
      }
    };

    xhttp.open("GET", "getBloglistWithCat.php?cat_id="+cat_id, true);
    xhttp.send();
}

function getBloglistWithMonth(maandnummer) {
    //alert('maandnummer:'+maandnummer);
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {

      if (this.readyState == 4 && this.status == 200) {
          rechterkolom.innerHTML = this.responseText;
      }
    };

    xhttp.open("GET", "getBloglistWithMonth.php?monthnumber="+maandnummer, true);
    xhttp.send();
}