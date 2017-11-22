// shows an error in netbeans but it's ok: https://netbeans.org/bugzilla/show_bug.cgi?id=226477
const INPUT_MAX_LENGTH = 140;

// Updates the display of how many char. are available
function updateCharCounter(){
    var textarea = $('#textToConvert');
    var currentLength = textarea.val().length;

    // change color if limit is reached
    if (currentLength >= INPUT_MAX_LENGTH){
        $('#currentChar').css('color','red');
    }
    else{
        $('#currentChar').css('color','#212529');
    }
    // update text
    $('#currentChar').html(currentLength);

    // update submit button to disable it if input is empty
    if (currentLength === 0){
        disableButton();
    }
    else{
        enableButton();
    }
}
// Bind the clic event to the handler
$('#convert').click(submitRequest);

// Callback when the convert button is clicked
function submitRequest(){
    disableButton();
    var param = $('#textToConvert').val();
    var counter = 0;
    if (param.length > INPUT_MAX_LENGTH){
        param = param.substring(0, INPUT_MAX_LENGTH);
    }
    
    GETrequest();
    // repeat GET request every 2 seconds
    // until we get a response or we repeat it a certain amount of time
    var tid = setInterval(GETrequest, 2000);
    
    // Perform a GET request on a local script
    // which looks for a response in the session file
    function GETrequest(){
        if (counter <= 3){
            $.get(
                'scripts/fetchResponse.php?text=' + param,
                handler,
                'text'
            );
            counter++;
        }
        // timeout
        else{
            hideLoading();			
            displayYoda('Une perturbation dans la Force, à me connecter m\'empêche. Réessayer plus tard, tu dois.');
			scrollToYoda();
            clearInterval(tid);
            enableButton();
            selectAll($('#textToConvert'));
        }
        
        function handler(data){
            // request is empty
            if (data === '100'){               
                clearInterval(tid);
                hideYoda();
                $('#textToConvert').val('');
                updateCharCounter();               
            }
            // no response yet
            else if (data === '200'){
                // 
                displayLoading();
                hideYoda();
            }
            // display response
            else {             
                clearInterval(tid);
                hideLoading();               
                displayYoda(data);               
                enableButton();
                scrollToYoda();
                selectAll($('#textToConvert'));
            }
        }
    }    
}

// Shows "loading screen"
function displayLoading(){
    $('#loading').css('display','block');
}
// Hide "loading screen"
function hideLoading(){
    $('#loading').css('display','none');
}
// Displays Yoda block with the message passed in parameter
function displayYoda(message){
    randomizeImage();
    $('#yodaResponse').html(message);
    $('#yodaBlock').css('display','block');
}
// Hides Yoda block
function hideYoda(){
    $('#yodaBlock').css('display','none');
}
// Updates max length text and attribute if we changed the const.
function updateMaxLength(){
    $('#textToConvert').attr('maxLength',INPUT_MAX_LENGTH);
    $('#maxChar').html(INPUT_MAX_LENGTH);
}
// Enables submit button
function enableButton(){
    $('#convert').removeAttr('disabled',false);
}
// Disables submit button
function disableButton(){
    $('#convert').attr('disabled',true);
}
// Display a different yoda image
// hardcoded, but not vital, so...
function randomizeImage(){
    function randomIntFromInterval(min,max){
        return Math.floor(Math.random()*(max-min+1)+min);
    }
    var imageNumber = randomIntFromInterval(1, 4);
    var src = 'images/yoda-0' + imageNumber + '.png';
    $('#yoda').attr('src', src);
}
// Scroll to the Yoda text so that small screens shows up the response
// without the need to scroll manually
function scrollToYoda(){
	$('html, body').animate({
        scrollTop: $("#yodaResponse").offset().top
    }, 1000);
}
// Returns current year for the footer
function displayCurrentYear(){
    var d = new Date();
    $('#currentYear').html(d.getFullYear());
}

// Catch enter key to fire submit instead of new line
// Shift + enter still allows the user to enter a new line
$('#textToConvert').keypress(function (e) {
    if(e.which === 13 && !e.shiftKey) {
        e.preventDefault();
        // submit only if the button is enabled
        if ($('#convert').attr('disabled') !== 'disabled'){
            submitRequest();
        } 
    }
});

// Select all text in textarea (with a timeout to bypass the browser focus)
function selectAll(textArea){
    setTimeout(function(){textArea.select();},10);
}
// Listen to input on textarea to update char counter
var textarea = document.querySelector('#textToConvert');
textarea.addEventListener('input', updateCharCounter);

// Page load
$(document).ready(function(){
    updateMaxLength();
    updateCharCounter();
    displayCurrentYear();   
});