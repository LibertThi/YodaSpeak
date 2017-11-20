// shows an error in netbeans but it's ok: https://netbeans.org/bugzilla/show_bug.cgi?id=226477
const INPUT_MAX_LENGTH = 140;

// Display how many char. are available
function updateCharCounter(){
    var textarea = $('#textToConvert');
    var currentLength = textarea.val().length;

    if (currentLength >= INPUT_MAX_LENGTH){
        $('#currentChar').css('color','red');
    }
    else{
        $('#currentChar').css('color','#212529');
    }
    // update text
    $('#currentChar').html(currentLength);


    // update submit button to allow click, or not
    if (currentLength === 0){
        disableButton();
    }
    else{
        enableButton();
    }
}
// Bind the clic event to the handler
$('#convert').click(getResponse);

function getResponse(){
    disableButton();
    var param = $('#textToConvert').val();
    var counter = 0;
    if (param.length > INPUT_MAX_LENGTH){
        param = param.substring(0, INPUT_MAX_LENGTH);
    }
    
    GETrequest();
    var tid = setInterval(GETrequest, 2000);
    
    function GETrequest(){
        if (counter <= 3){
            $.get(
                'scripts/fetchResponse.php?text=' + param,
                handler,
                'text'
            );
            counter++;
        }
        else{
            hideLoading();
            displayYoda('Une perturbation dans la Force, à me connecter m\'empêche. Réessayer plus tard, tu dois.');
            clearInterval(tid);
            enableButton();
            selectAll($('#textToConvert'));
        }
        
        function handler(data){
            if (data === '100'){
                // no request
                hideYoda();
                $('#textToConvert').val('');
                updateCharCounter();
                clearInterval(tid);
            }
            else if (data === '200'){
                // no response yet
                displayLoading();
                hideYoda();
            }
            else {
                // show response
                hideLoading();               
                displayYoda(data);
                clearInterval(tid);
                enableButton();
                selectAll($('#textToConvert'));
            }
        }
    }    
}

function displayLoading(){
    $('#loading').css('display','block');
}

function hideLoading(){
    $('#loading').css('display','none');
}

function displayYoda(message){
    randomizeImage();
    $('#yodaResponse').html(message);
    $('#yodaBlock').css('display','block');
}

function hideYoda(){
    $('#yodaBlock').css('display','none');
}

function updateMaxLength(){
    $('#textToConvert').attr('maxLength',INPUT_MAX_LENGTH);
    $('#maxChar').html(INPUT_MAX_LENGTH);
}

function enableButton(){
    $('#convert').removeAttr('disabled',false);
}

function disableButton(){
    $('#convert').attr('disabled',true);
}

function randomizeImage(){
    function randomIntFromInterval(min,max){
        return Math.floor(Math.random()*(max-min+1)+min);
    }
    var imageNumber = randomIntFromInterval(1, 4);
    var src = 'images/yoda-0' + imageNumber + '.png';
    $('#yoda').attr('src', src);
}

function displayCurrentYear(){
    var d = new Date();
    $('#currentYear').html(d.getFullYear());
}

// Catch "enter" to fire submit instead of new line
$('#textToConvert').keypress(function (e) {
    if(e.which === 13 && !e.shiftKey) {
        e.preventDefault();
        // submit only if the button is enabled
        if ($('#convert').attr('disabled') !== 'disabled'){
            $('#convert').click();    
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

// On page load...
$(document).ready(function(){
    updateMaxLength();
    updateCharCounter();
    displayCurrentYear();   
});