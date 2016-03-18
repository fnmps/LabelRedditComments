<html>
<link rel="stylesheet" href="js/jquery-ui.css">
<link rel="stylesheet" href="css/labeling.css">
<script src="js/jquery-1.12.0.min.js"></script>
<script src="js/jquery-ui.js"></script>

<body>
<?php

require_once("reddit/reddit.php");
$reddit = new reddit();
$author = $reddit->getUser()->name;
$code = $_GET['code'];

session_start();
$config['sess_use_database'] = TRUE;
$config['sess_match_ip'] = TRUE;
$_SESSION['author'] = $author;

?>

<script>

//IMPORTANT - LABELS AND THEIR NAMES MUST BE IN THE SAME INDEX!!!

//The labels that are inserted into the database
var labels = 
	[ 
		"#####",    //LABEL1         
		"#####",    //LABEL2         
		"#####",  	//LABEL3         
		"#####"     //LABEL4
					// etc.    
	]

//The labels that are shown to the user
var labelnames = 
	[      
		"#####",    //LABELNAME1
		"#####",    //LABELNAME2
		"#####",   	//LABELNAME3  
		"#####"    	//LABELNAME4
					// etc.
    ]


var author = <?php echo json_encode($author) ?>; // change this to the redditor you want the user to label, if not self
var code = "<?php echo $code ?>";
var data = [];
var current_index = 0;
var commentSegments = [];
var segmentAnswers = [];
var segmentsAnswered = 0;

$(function() {
	$( document ).tooltip();
});

window.onload = function(){
	
	$('.button').prop('disabled', true);
	
	$( "#progressbar" ).progressbar({
		value: false,
	});
	$('#progress-label').text( "Loading your comments... this might take a few minutes." );

	//Fetch comments from database
	$.ajax({ 
			url: 'database.php',
        	data: {action: 'getComments', author: author, code: code},
        	type: 'POST',
        	dataType: 'json',
        	success: function(output) {
                     data = output;
                     size = data.length;
                     segmentAnswers = new Array(size);
                     commentSegments = new Array(size)
                     for (i=0; i < size; i++){
                    	 segmentAnswers[i] = [];
                    	 commentSegments[i] = [];
                     }
                     if(size > 0){
                    	 start();
                     }else{
                    	 $( "#progressbar" ).progressbar({
                    			value: 100,
                    	 });
                    	 $('#permalinkDiv').css('display', 'none');
                    	 $('#progress-label').text( "All comments labeled!" );
                     }
            },
	    error: function(xhr, status, error){
			alert("An error occurred while trying to fetch your comments! Please try again.");
			$('#progress-label').text( "An error ocurred! Please try again later." );
			window.location.replace('index.html');
	    }
	});
}

/**
 * Initialize interface
 */
function start(){
	$( "#progressbar" ).progressbar({
		value: 1,
		max: size,
	    change: function() {
	      $('#progress-label').text( $( "#progressbar" ).progressbar( "value" ) + " of " + size );
	    }
	});
	createCommentForm();
	$( "#permalink" ).attr("href", data[current_index].permalink + "?context=3");
	$('#progress-label').text( $( "#progressbar" ).progressbar( "value" ) + " of " + size );
	$('.answer').prop('disabled', false);
    $('#nextButton').prop('disabled', false);
}

/**
 * Segments a full comment into sentences
 * Note that it is not perfect, segments wrongly where there are words like
 * "Mr.", "Mrs.", "Dr." ,etc,  and a few other non basic segments
 *
 * @param {comment} Text to be segmented
 * @return {array} an array of sentences
 */
function segmentCommentIntoSentences(comment){

	comment = comment.replace(/^>.*\n/g , ''); // remove citations
	if(comment.length == 0){
		next();
		return;
	}
        var urls = comment.match(/(https?:\/\/)?((([a-z\d]([a-z\d-]*[a-z\d])*)\.)+[a-z]{2,}|((\d{1,3}\.){3}\d{1,3}))(\:\d+)?(\/[-a-zA-Z\d%_.~+]*)*(\?[;&a-zA-Z\d%_.~+=-]*)?(\#[-a-zA-Z\d_]*)?/g);
	if(urls != null){
		for (i = 0; i < urls.length; i++){
			comment = comment.replace(urls[i], " <<<--URL"+ i + "-->>>");
		}
	}
	var parenthesis = comment.match(/(\[.*])|(\(.*\))|(\".*\")/g);
        if(parenthesis != null){
                for (i = 0; i < parenthesis.length; i++){
                        comment = comment.replace(parenthesis[i], " <<<--PARENTHESIS"+ i + "-->>>");
                }
        }

        var tmpSentences = comment.match(/[^\.!\?\n]+([\.!\?\n]+|$)/g);
        var sentences = [];
        var ind = 0;
        var ind_par = 0;
        tmpSentences.forEach(function(sentence){
                sentence = sentence.trim();
                if(sentence.length !== 0){
                        while(sentence.search(/<<<--PARENTHESIS[0-9]+-->>/) != -1){
                                sentence = sentence.replace(/\n/g, '').replace("<<<--PARENTHESIS" + ind_par +"-->>>",  parenthesis[ind_par]);
                                ind_par++;
                        }
                        while(sentence.search(/<<<--URL[0-9]+-->>/) != -1){
                                sentence = sentence.replace(/\n/g, '').replace("<<<--URL" + ind +"-->>>",  urls[ind]);
                                ind++;
                        }
                        sentences.push(sentence);
                        
                }
        });
        return sentences;
}


/**
 * Creates the form with the text and buttons to label that text
 *
 */
function createCommentForm(){
	var comment = data[current_index].comment;

	sentences = segmentCommentIntoSentences(comment);
		
	if(segmentAnswers[current_index].length != sentences.length)
		segmentAnswers[current_index] = new Array(sentences.length);
	
	var form = $('#form');
	form.empty();
	
	for (index = 0; index < sentences.length; index++){
		var comment = sentences[index];

		commentSegments[current_index][index] = comment;
		
		var subFormDiv = jQuery(document.createElement('div'));
		subFormDiv.attr("id", "segment" + index);
		subFormDiv.addClass("segment");

		var textDiv = jQuery(document.createElement('div'));
		textDiv.addClass("comment");
		textDiv.text(comment);

		var answersDiv = jQuery(document.createElement('div'));

		for(i = 0; i < labels.length; i++){
			var answer = jQuery(document.createElement('button'))
			answer.attr("id", "answer"+i);
			answer.addClass("answer button");
			answer.attr("onClick", "submit(this,"+ index + ")");
			answer.val(labels[i]);
			answer.text (labelnames[i]);
			
			answersDiv.append(answer);	
		}

		subFormDiv.append(textDiv, answersDiv);
		form.append(subFormDiv);

	}

	hightlightAnswers();
}


/**
 * Returns to the previous comment
 *
 */
function back(){
	current_index--;
	if(current_index >= 0){
		createCommentForm();
		$( "#progressbar" ).progressbar( "option", "value", current_index+1 );
		$('#nextButton').prop('disabled', false);
		if(current_index == 0)
			$('#backButton').prop('disabled', true);
	}
}

/**
 * Skips to the next comment
 *
 */
function next(){
	current_index++;
	
	if(current_index < size){
		hightlightAnswers();
		createCommentForm();
		$( "#permalink" ).attr("href", data[current_index].permalink + "?context=3");
		$( "#progressbar" ).progressbar( "option", "value", current_index+1 );
		$('#backButton').prop('disabled', false);
	}else{
		current_index--;
		$('#nextButton').prop('disabled', true);
		alert("No more comments! Thank you for participating ;)");
	}
}

/**
 * Inserts the sentence segment with its respective label into the database
 *
 * @param {el} HTML element of subform for the sentence to be inserted
 * @param {segment_id} The id of the segment sentence
 */
function submit(el, segment_id){
	segment = commentSegments[current_index][segment_id];
	jQuery.ajax({
	    type: "POST",
	    url: 'database.php',
	    data: {action: 'submitAnswer', id: data[current_index].id, segment_id: segment_id, segment: segment, author: author, answer: el.value},
	    element: el,
	    segment_index: segment_id,
	    success: function (data) {
	    	segmentAnswers[current_index][this.segment_index] = this.element.value;
	    	segmentsAnswered++;
		$( "#" + this.element.parentNode.parentNode.id ).animate({
   			opacity: 0.25,
			left: "+=50",
			height: "hide"
		}, 500, function() {
			this.style.display="none";
			if(segmentsAnswered >= segmentAnswers[current_index].length){
                                segmentsAnswered = 0;
                        	next();
                        }
		})
		},
		error: function(){
			alert("Something went wrong! Please, try again later.");
		}
	});

}

function hightlightAnswers(){
	answers = segmentAnswers[current_index];
	$('.answer').css('border-color', '')
	for(i=0; i < answers.length; i++){
		answer = answers[i];
		for(j = 0; j < labels.length; j++){
			if (answer == label[i]){
				$('#segment'+ i + ' #answer' + j).css('border-color', 'green');
				break;
			}
		}
	}
}

</script>

<div style="text-align: center">

	<div id="definition">
		<strong>Irony</strong> (http://www.merriam-webster.com/dictionary/ironic):
		<br>: using words that mean the opposite of what you really think 
        <br>: strange or funny because something (such as a situation) is different from what you expected
    </div>
    
	<div id="progressbar"><div id="progress-label"></div></div>
	
	<div>	
		<div style="text-align:center; margin-top:10px;">
			<button id="backButton" style="float:left" class="button" type="button" onclick="back()">Back</button>
			<button id="nextButton" style="float:right" class="button" type="button" onclick="next()">Next</button>
		</div>
	</div>
	
	<div id="permalinkDiv">Need context? <a id="permalink" style="text-align: center;" target="_blank">permalink</a></div>
	
	<div id="form">

	</div> 

	

</div>

</body>
</html>

