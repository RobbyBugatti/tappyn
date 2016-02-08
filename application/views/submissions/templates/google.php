<div class='facebook-form-wrapper'>
    <div class='form-row'>
       <?php echo form_input(
       	array('name' => 'headline',
       	'value' => '',
       	'placeholder' =>
       		($contest->objective == 'website_clicks' ? "*Captivating Headling Here" : 
       		($contest->objective == 'app_installs' ? "*Captivating Headling Here" :
       		($contest->objective == 'engagement' ? "*Captivating Headling Here" : "Enter a headline"))), 
       		'type' => 'text',
       		'id' => 'headline'));?>
       	<div class='input-count'><span id='headline_span'>0</span> of 25 characters</div>
    </div>
    <div class='form-row'>
       <?php echo form_textarea(
       	array('name' => 'text',
       	'value' => '',
       	'placeholder' =>
       		($contest->objective == 'website_clicks' ? "Describe why people should visit this website!" :
       		($contest->objective == 'app_installs' ? "Describe why people should install this app!" :
       		($contest->objective == 'engagement' ? "Create compelling content this business could supply" : "Tell a bit more. Be Clear!"))),
       	'type' => 'text',
       	'id' => 'text',
       	'rows' => "3"));?>
       	<div class='input-count'><span id='text_span'>0</span> of 250 characters</div>
    </div>
</div>

<script>
$('#headline').keyup(function(){
	var wordlength = $('#headline').val().length;
	$("#headline_span").html(wordlength);
});
$('#text').keyup(function(){
	var wordlength = $('#text').val().length;
	$("#text_span").html(wordlength);
});
</script>
