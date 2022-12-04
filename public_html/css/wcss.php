<?php

header('content-type:text/css; charset:UTF-8;');
$background_color = "#171717ff";
$basic_color = "#e6e6e4ff";
$accent_color = "#449981ff";
$nice_gray = "#1d272fff";
?>
body {
	background-color: <?php echo $background_color ?>;
}

h1 {
	color: <?php echo $accent_color ?>;
    font-family: 'Pepsi Cyr-Lat', sans-serif;
    text-align: center;
    letter-spacing: 4px;
    word-spacing: 3px;
    font-size: 45px;
}

h2 {
	color: <?php echo $accent_color ?>;
}

h3 {
	color: <?php echo $accent_color ?>;
}

h4 {
	color: <?php echo $accent_color ?>;
}

h5 {
	color: <?php echo $accent_color ?>;
}

h6 {
	color: <?php echo $accent_color ?>;
}

p {
	color: <?php echo $basic_color ?>;
}

label {
	color: <?php echo $basic_color ?>;
}

table .profile_info {
	font-family: arial, sans-serif;
	border-collapse: collapse;
	width: 100%;
}

.profile_info td, th {
	border: 1px solid <?php echo $accent_color ?>;
	text-align: left;
	padding: 8px;
	color: <?php echo $basic_color ?>;
}

.profile_info tr:nth-child(even) {
	background-color: <?php echo $accent_color ?>;
}

#theDiv{
	width: 900px;
	border: solid <?php echo $accent_color?> 2px;
	margin: 0 0.5em;
}

sb {
	font-family: sans-serif;
}