// Display how many char. are available
function updateCharCounter(){
    var textarea = $("#textToConvert");
    var maxLength = INPUT_MAX_LENGTH;
    var currentLength = textarea.val().length;

    if (currentLength >= maxLength){
        $("#currentChar").css("color","red");
    }
    else{
        $("#currentChar").css("color","black");
    }
    // update text
    $("#currentChar").html(currentLength);


    // update submit button to allow click, or not
    if (currentLength === 0){
        $("#convert").attr('disabled',true);
    }
    else{
        $("#convert").removeAttr('disabled',false);
    }
}
// Display a loading icon when submit button is clicked
$("#convert").click(getResponse);

function getResponse(){
    var param = $("#textToConvert").val();

    $.get(
        'fetchResponse.php?text=' + param,
        handler,
        'text'
    );
 
    function handler(data){
        if (data === '100'){
            // no request
        }
        else if (data === '200'){
            // no response yet
            $("#loading").css('display',"block");
            getResponse();
        }
        else {
            $("#yodaResponse").html(data);
            $("#yodaBlock").css('display',"block");
            $("#loading").css('display',"none");
        }
    }
    //$("#loading").css('display',"block");
}

// Refresh char counter on load
$(document).ready(function(){
    $("#charCounter").css("display","inline");
    updateCharCounter();
});

// Catch "enter" to fire submit instead of new line
$("#textToConvert").keypress(function (e) {
    if(e.which === 13 && !e.shiftKey) {
        e.preventDefault();
        // submit only if the button is enabled
        if ($("#convert").attr('disabled') !== 'disabled'){
            $("#convert").click();    
        } 
    }
});

// Select all text in textarea (with a timeout to bypass the browser focus)
function selectAll(textArea){
	setTimeout(function(){textArea.select();},10);
}
// Listen to input on textarea to update char counter
var textarea = document.querySelector("#textToConvert");
textarea.addEventListener("input", updateCharCounter);