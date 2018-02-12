window.onload = function(){
    //first check if execCommand and contentEditable attribute is supported or not.
    if(document.contentEditable != undefined && document.execCommand != undefined)
   {
       alert("HTML5 Document Editing API Is Not Supported");
    }
    else
    {
        document.execCommand('styleWithCSS', false, true);
    }
}

//underlines the selected text
function underline()
{
    document.execCommand("underline", false, null);
}

function bolden()
{
    document.execCommand("bold", false, null);
}

function italic() {
    document.execCommand("italic", false, null);
}

function insertImage() {
    var img_src = prompt("Geef de link van de afbeelding","link naar de afbeelding");
    document.execCommand("insertImage", false, img_src);
}

function inserthtml() {
    var html_code = prompt("Geef de HTML code","html code");
    document.execCommand("insertHTML", false, html_code);
}
//makes the selected text as hyperlink.
function link()
{
    var url = prompt("Enter the URL");
    document.execCommand("createLink", false, url);
}

//displays HTML of the output
function displayhtml()
{
    //set textContent of pre tag to the innerHTML of editable div. textContent can take any form of text and display it as it is without browser interpreting it. It also preserves white space and new line characters.
    document.getElementsByClassName("htmloutput")[0].textContent = document.getElementById("editor").innerHTML;
}
