<?php
$header = new StringBuilder();

$header->append('<!DOCTYPE html>
	<html>
	<head lang="en">
	<meta charset="UTF-8">
	<title>donations</title>');

//Stylesheets / Javascripts
$header->append('<link href="'.TO_ROOT.'css/global.css" rel="stylesheet" type="text/css" />');

$header->append('</head>
	<body>
	<div class="container main-container">
	<header class="header-top">
	<div class="logo">
	<div class="logo-inner"></div>
	</div>
	<nav class="navigation">');
	
for($i = 0; $i < count($navigation); $i++)
{ 
	if($i != 2){
		$header->append('<a href="'.$navigation[$i][1].'" class="navigation-item">'.$navigation[$i][0].'</a>');
	}else{
		$header->append('<a href="'.$navigation[$i][1].'" class="navigation-item play">'.$navigation[$i][0].'</a>');
	}
}
$header->append('</nav>
	</header>');
?>