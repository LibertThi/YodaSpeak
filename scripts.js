// Display how many char. are available
function updateCharCounter(){
	var textarea = document.querySelector("#idTextToConvert");
	var maxLength = INPUT_MAX_LENGTH;
	var currentLength = textarea.value.length;

	if (currentLength >= maxLength){
		document.getElementById('currentChar').style.color = "red";
	}
	else{
		document.getElementById('currentChar').style.color = "black";
	}
	// update text
	$("#currentChar").html(currentLength);
	
	
	// update submit button to allow click, or not
	if (currentLength === 0){
		$("#submit").attr('disabled',true);
	}
	else{
		$("#submit").removeAttr('disabled',false);
	}
}
// Display a loading icon when submit button is clicked
$("#submit").click(function(){
	document.getElementById('loading').style.display = "block";
});

// Refresh char counter on load
$(document).ready(function(){
        document.getElementById('charCounter').style.display = "inline";
	updateCharCounter();
});

// Catch "enter" to fire submit instead of new line
$("#idTextToConvert").keypress(function (e) {
	if(e.which === 13 && !e.shiftKey) {
		e.preventDefault();
		// submit only if the button is enabled
		if ($("#submit").attr('disabled') != 'disabled'){
			$("#submit").click();    
		} 
	}
});

// Select all text in textarea (with a timeout to bypass the browser focus)
function selectAll(textArea){
	setTimeout(function(){textArea.select();},10);
}
// Listen to input on textarea to update char counter
var textarea = document.querySelector("#idTextToConvert");
textarea.addEventListener("input", updateCharCounter);