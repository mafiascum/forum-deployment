<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Replacement Request Generator</title>
<link rel="stylesheet" type="text/css" href="<?php echo(getenv('PHPBB_FORUM_SERVER_PROTOCOL') . getenv('FORUM_FQDN')) ?>/styles/prosilver/theme/stylesheet.css" />

<style type="text/css">

body, select, input {
	font-family:sans-serif;
	font-size:13px;
}

.blankerror {
	display: none;
	position: absolute;
	top: 0%;
	left: 0%;
	width: 100%;
	height: 100%;
	background-color: black;
	z-index:1001;
	-moz-opacity: 0.8;
	opacity:.80;
	filter: alpha(opacity=80);
}

.blankmessage {
	display: none;
	position: absolute;
	top: 0;
	left: 0;
	width: 0;
	height: 0;
	background-color: white;
	z-index:1002;
	overflow: auto;
	vertical-align: middle;
}
.col1{
width: 170px;
float:left;
margin-top:3px;
}

.col2{
width: 170px;
float:left;
}

.col3{
width: auto;
float:left;
font-style: italic;
}

form {
	min-width:500px;
}

.panel{
margin:10px 20px 0px 20px;
padding:20px 20px;
}

.clear{
	clear:both;
}
.error{
	color:red;
	font-weight:bold;
	display:none;
	margin-top:3px;
}
h1 {
	margin-left:20px;
	color: #115098;
	font-size: 2.1em;
}
h2 {
	font-weight:bold;
	margin:8px 0px;
}

h3 {
	margin-left: 60px;
}

h4 {
	margin:14px 70px;
	font-style: italic;
}
#bbcode{
	width:66%;
}
#output{
	margin-top:0px;
	padding:20px 5px;
	display:none; /* changes when they click the generate button */
}
.inneroutput{
	border-top:2px;
	border-color:white;
	border-style:solid;
	padding:0px 15px;
}

#preview{
	font-size:13px;
	line-height:135%;
	margin-left:55px;
	width:66%;
}

strong {
	font-weight:bold;
}
em{
	font-style:italic;
}

select{
	width:158px;
}

label {
	font-weight :bold;
	margin: 4px 0;
}

#submit{
	font-weight:bold;
	font-size:133%;
	margin-top:10px;
}

</style>
</head>

<body>
<h1><a href="<?php echo(getenv('PHPBB_FORUM_SERVER_PROTOCOL') . getenv('FORUM_FQDN')) ?>">mafiascum.net</a> Replacement Form</h1>

<form name="postgen" method="post" defaultbutton="submit">
<h3><em>All fields are required.</em></h3>
	<div class="panel bg2">
		<label class="col1" for="month">Month:</label>
		<div class="col2">
				<select id="month">
					<option id="January" value="January">January</option>
					<option id="February" value="February">February</option>
					<option id="March" value="March">March</option>
					<option id="April" value="April">April</option>
					<option id="May" value="May">May</option>
					<option id="June" value="June">June</option>
					<option id="July" value="July">July</option>
					<option id="August" value="August">August</option>
					<option id="September" value="September">September</option>
					<option id="October" value="October">October</option>
					<option id="November" value="November">November</option>
					<option id="December" value="December">December</option>
				</select>
		</div>
		<div class="col3">
			<span class="error" id="montherror">You must select a month.</span>
		</div>
		<div class="clear"></div>
		
		<label class="col1" for="day">Date:</label>
		<div class="col2">
			<input type="text" id="day" name="day" maxlength="2"/>
		</div>
		<div class="col3">
			<span class="error" id="dateerror">You must enter a numerical date.</span>
		</div>
		<div class="clear"></div>
		
		<label class="col1" for="gametype">Game Type:</label>
		<div class="col2">		
				  <select name="gametype" onchange="large_check(this);">
					  <option value=""></option>
					  <option value="Open">Open</option>
					  <option value="Micro">Micro</option>
					  <option value="Mini">Mini Normal</option>
					  <option value="Mini Theme">Mini Theme</option>
					  <option value="New York">Large Normal</option>
				    <option value="LT">Large Theme</option>
				  </select>
		</div>
		<div class="col3">
				  <span class="error" id="gametypeerror">You must select a game type.</span>
		</div>
		<div class="clear"></div>
		
		<label class="col1" for="gamenum">Game Number:</label>
		<div class="col2">
				  <input id="gamenum" type="text" name="gamenum" maxlength="4" />
		</div>
		<div class="col3">
				  <span class="error" id="gamenumerror">You must enter a numerical game number.</span>
		</div>
		<div class="clear"></div>
		
		<label class="col1" for="gamename">Game Title:</label>
		<div class="col2">
				  <input type="text" name="gamename"/>
		</div>
		<div class="col3">
				  <span class="error" id="gamenameerror">You must enter a game name.</span>
		</div>
		<div class="clear"></div>
		
		<label class="col1" for="gamelink">Link to Game:</label>
		<div class="col2">
				  <input type="text" name="gamelink"/>
		</div>
		<div class="col3">
				  <span class="error" id="gamelinkerror">You must enter a game link.</span>
		</div>
		<div class="clear"></div>
		
		<label class="col1" for="modname">Moderator:</label>
		<div class="col2">
				  <input type="text" name="modname"/>
		</div>
		<div class="col3">
				  <span class="error" id="modnameerror">You must enter the moderator's exact username.</span>
		</div>
		<div class="clear"></div>
		
		<label class="col1" for="phase">Phase:</label>
		<div class="col2">
				  <select name="phase" onchange="pregame_check(this);">
					  <option value=""></option>
					  <option value="Day">Day</option>
					  <option value="Night">Night</option>
					  <option value="Pregame">Pregame</option>
				  </select>
		</div>
		<div class="col3">
				  <span class="error" id="phaseerror">You must enter the game phase.</span>
		</div>
		<div class="clear"></div>
		
		<label class="col1" for="phasenum">Phase Number:</label>
		<div class="col2">
				  <input type="text" id="phasenum" name="phasenum" maxlength="2"/>
		</div>
		<div class="col3">
				  <span class="error" id="phasenumerror">You must enter the phase number.</span>
		</div>
		<div class="clear"></div>
		
		<label class="col1" for="pagenum">Number of Pages:</label>
		<div class="col2">
				  <input type="text" id="pagenum" name="pagenum" maxlength="3"/>
		</div>
		<div class="col3">
				  <span class="error" id="pagenumerror">You must enter the page number.</span>
		</div>
		<div class="clear"></div>
		
		<label class="col1" for="repname">Departing Player:</label>
		<div class="col2">
				  <input type="text" name="repname"/>
		</div>
		<div class="col3">
				  <span class="error" id="repnameerror">You must enter the username of the person getting replaced.</span>
		</div>
		<div class="clear"></div>
		
		<input type="button" value="Generate" id="submit" onclick="generate_bbc()">
		<div class="clear"></div>
	</div>
</form>
<div id="output" class="panel bg2">
	<div class="inneroutput">
		<h3>Preview:</h2>
		<div id="preview"></div>
		
		<h3>BBCode:</h2>
		<textarea name="textarea" id="bbcode" rows="4" columns="80" onClick="this.select();"></textarea>
	</div>
<h4><a href="<?php echo(getenv('PHPBB_FORUM_SERVER_PROTOCOL') . getenv('FORUM_FQDN')) ?>/posting.php?mode=reply&f=4&t=70776">Return to Replacement Thread <em>(reply screen)</em></a></h4>

</div>

<script type="text/javascript">
// Set default value for the month field.
// Create all the months.
var dropJanuary = document.getElementById('January');
var dropFebruary = document.getElementById('February');
var dropMarch = document.getElementById('March');
var dropApril = document.getElementById('April');
var dropMay = document.getElementById('May');
var dropJune = document.getElementById('June');
var dropJuly = document.getElementById('July');
var dropAugust = document.getElementById('August');
var dropSeptember = document.getElementById('September');
var dropOctober = document.getElementById('October');
var dropNovember = document.getElementById('November');
var dropDecember = document.getElementById('December');

// Find the current month.
var currentTime = new Date();
var month = currentTime.getMonth();

var monthNames = [ "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December" ];
    
// console.log(month+', '+monthNames[month]+', '+currentTime.getDate(),document.getElementById('month'))

var fieldMonth = document.getElementById('month')
fieldMonth.value = monthNames[month]

// Find and set the default value for the day field.
var fieldDay = document.getElementById('day');
fieldDay.value = currentTime.getDate();

function validateField(value, error, isnumeric){
	if ((value==null || value=="")||(isnumeric && isNaN(value))){
		error.style.display="block";
		return 1;
	}
	else{
		error.style.display="none";
		return 0;
	}
}

function validateForm()
{
	var noerrors = 0;
	// Find the form.
	var oForm = document.forms["postgen"];

	// Find the values of the form's elements and store them as variables.
	var monthlist = oForm.elements['month'].value;
	var dayfield = oForm.elements['day'].value;
	var gametypelist = oForm.elements['gametype'].value;
	var gamenumfield = oForm.elements['gamenum'].value;
	var gamenamefield = oForm.elements['gamename'].value;
	var modnamefield = oForm.elements['modname'].value;
	var phaselist = oForm.elements['phase'].value;
	var phasenumfield = oForm.elements['phasenum'].value;
	var pagenumfield = oForm.elements['pagenum'].value;
	var repnamefield = oForm.elements['repname'].value;
	var gamelinkfield = oForm.elements['gamelink'].value;

	noerrors += validateField(monthlist, document.getElementById('montherror'), false);
	noerrors += validateField(dayfield, document.getElementById('dateerror'), false);
	noerrors += validateField(gametypelist, document.getElementById('gametypeerror'), false);

	if (phaselist != "Pregame"){

		noerrors += validateField(phasenumfield, document.getElementById('phasenumerror'), true);
		noerrors += validateField(pagenumfield, document.getElementById('pagenumerror'), true);
	}
	if (gametypelist != "LT"){
	
		noerrors += validateField(gamenumfield, document.getElementById('gamenumerror'), true);
	}
	if (gametypelist == "LT" || gametypelist == "Mini Theme" || gametypelist == "" || gametypelist == "Micro")
	{
		noerrors += validateField(gamenamefield, document.getElementById('gamenameerror'), false);
	}
	noerrors += validateField(modnamefield, document.getElementById('modnameerror'), false);
	noerrors += validateField(phaselist, document.getElementById('phaseerror'), false);
	noerrors += validateField(repnamefield, document.getElementById('repnameerror'), false);
	noerrors += validateField(gamelinkfield, document.getElementById('gamelinkerror'), false);
	
	return noerrors == 0;
}

// Disable unnecessary elements for requests in Pregame.
function pregame_check(type){
	// Find the game phase.
	var gamephase = type.value;

	// Find the elements to be disabled.
	var phasenum = document.getElementById('phasenum');
	var pagenum = document.getElementById('pagenum');

	if (gamephase == 'Pregame'){
		phasenum.disabled=true;
		pagenum.disabled=true;
	}
	else {
		phasenum.disabled=false;
		pagenum.disabled=false;
	}
}

// Disable unnecessary elements for Large Theme requests.
function large_check(type){
	// Find the type of game.
	var gametype = type.value;

	// Find the elements to be disabled.
	var gamenum = document.getElementById('gamenum');

	if (gametype == 'LT'){
		gamenum.disabled=true;
	}
	else {
		gamenum.disabled=false;
	}
}

function generate_bbc(){
	if (!validateForm()){
		return false;
	}
// Find the form.
var oForm = document.forms["postgen"];

// Find the values of the form's elements and store them as variables.
var monthlist = oForm.elements['month'].value;
var dayfield = oForm.elements['day'].value;
var gametypelist = oForm.elements['gametype'].value;
var gamenumfield = oForm.elements['gamenum'].value;
var gamenamefield = oForm.elements['gamename'].value;
var modnamefield = oForm.elements['modname'].value;
var phaselist = oForm.elements['phase'].value;
var phasenumfield = oForm.elements['phasenum'].value;
var pagenumfield = oForm.elements['pagenum'].value;
var repnamefield = oForm.elements['repname'].value;
var gamelinkfield = oForm.elements['gamelink'].value;
var themegame = gametypelist + ' ' + gamenumfield + ': ';

var phasestuff = phaselist + ' ' + phasenumfield + ', ' + pagenumfield + ' page';

if (pagenumfield > '1'){
	var phasestuff = phasestuff + 's';
}

if (phaselist == 'Pregame'){
	var phasestuff = "Pregame";
}


if (gametypelist == 'LT'){
	var themegame = "";
}

// console.log(phasestuff + '('+phaselist + ' ' + phasenumfield + ', ' + pagenumfield + ' page(s))');

document.getElementById('output').style.display="block";

document.getElementById('preview').innerHTML=monthlist + ' ' + dayfield + ' - <strong><em><a href="' + gamelinkfield + '">' + themegame + gamenamefield + '<\/a><\/em><\/strong><br \/><strong>Moderator:<\/strong> <a href="<?php echo(getenv('PHPBB_FORUM_SERVER_PROTOCOL') . getenv('FORUM_FQDN')) ?>/memberlist.php?mode=viewprofile&un=' + modnamefield + '">' + modnamefield + '<\/a><\/span> <span style="padding-left:30px;"><strong>Status: <\/strong>' + phasestuff + '<\/span><span style="padding-left:30px;"><strong>Replacing: <\/strong>' + repnamefield;

document.getElementById('bbcode').innerHTML= monthlist + ' ' + dayfield + ' - [i][url=' + gamelinkfield + ']' + themegame + gamenamefield + '[\/url][\/i]\u000D[b]Moderator:[\/b] [user]' + modnamefield + '[\/user] [tab]3[\/tab][b]Status:[\/b] ' + phasestuff + '[tab]3[\/tab][b]Replacing:[\/b] ' + repnamefield + '\u000A'
}

</script>
</body>
</html>
