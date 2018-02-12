var editor = document.getElementById('editor');
var blogtitel = document.getElementById('blogtitel');
var buffer = "";
var afkortingen = [['/cg','Code Gorilla'],
                   ['/ag', 'Agus Judistira'],
                   ['/mvg', 'Met vriendelijke groet,'],
                   ['/nl', 'Nederland']];

var commentAllowanceForm = document.getElementById('comment-checkbox');

window.onload = Initialize();

function Initialize() {
  //alert("initialized");
  document.getElementById("blogtitel").style.cssText = document.getElementsByTagName("H2")[0].style.cssText;
}

commentAllowanceForm.onchange = function(ev) {
    //ev.preventDefault();
    this.submit();
}

function verwerkArtikel() {
  document.getElementById("hidden").value = document.getElementById("editor").innerHTML;
  return true;
}

function getCaretPosition(editableDiv) {
  var caretPos = 0,
    sel, range;
  if (window.getSelection) {
    sel = window.getSelection();
    if (sel.rangeCount) {
      range = sel.getRangeAt(0);
      if (range.commonAncestorContainer.parentNode == editableDiv) {
        caretPos = range.endOffset;
      }
    }
  } else if (document.selection && document.selection.createRange) {
    range = document.selection.createRange();
    if (range.parentElement() == editableDiv) {
      var tempEl = document.createElement("span");
      editableDiv.insertBefore(tempEl, editableDiv.firstChild);
      var tempRange = range.duplicate();
      tempRange.moveToElementText(tempEl);
      tempRange.setEndPoint("EndToEnd", range);
      caretPos = tempRange.text.length;
    }
  }
  return caretPos;
}

editor.onkeyup = function(e) {

  if (e.keyCode == 191) {
    buffer += "/";
  }
  else {
    buffer += String.fromCharCode(e.keyCode).toLowerCase();
  }
/*
  console.log("buffer = "+buffer);
  console.log("Cursor pos:"+this.selectionStart);
*/
  var startPos = getCaretPosition(this);
  console.log("startPos="+startPos);

  for (var i=0; i < afkortingen.length; i++) {
    //console.log("afkortingen.length:"+afkortingen.length);
    var afkorting = afkortingen[i][0];
    var voluit = afkortingen[i][1];
    if (buffer.endsWith(afkorting)) {
      alert('afkorting gevonden');
      var startPos = getCaretPosition(this);
      //var startPos = this.selectionStart;
      console.log("startPos="+startPos);
      //

      //this.value = this.value.replace(afkorting, voluit);
      this.innerHTML = this.innerHTML.replace(afkorting, voluit);
/*
      console.log("afkorting:"+afkorting);
      console.log("afkorting.length:"+afkorting.length);
      console.log("voluit:"+voluit);
      console.log("voluit.length:"+voluit.length);
*/
      this.selectionEnd = startPos + voluit.length - afkorting.length;
      break;
    }
  }

  if (buffer.length > 7) { //een afkorting mag max 7 karakters lang zijn
    buffer = buffer.substring(buffer.length - 7);
  }
}

blogtitel.onkeyup = function(e) {
  if (e.keyCode == 191) {
    buffer += "/";
  }
  else {
    buffer += String.fromCharCode(e.keyCode).toLowerCase();
  }

  var startPos = this.selectionStart;

  for (var i=0; i < afkortingen.length; i++) {
    //console.log("afkortingen.length:"+afkortingen.length);
    var afkorting = afkortingen[i][0];
    var voluit = afkortingen[i][1];
    if (buffer.endsWith(afkorting)) {
      //alert('afkorting gevonden');
      var startPos = this.selectionStart;

      this.value = this.value.replace(afkorting, voluit);
      console.log("afkorting:"+afkorting);
      console.log("afkorting.length:"+afkorting.length);
      console.log("voluit:"+voluit);
      console.log("voluit.length:"+voluit.length);
      this.selectionEnd = startPos + voluit.length - afkorting.length;
      break;
    }
  }

  if (buffer.length > 3) {
    buffer = buffer.substring(buffer.length - 3);
  }
}




