<?php session_start(); if (isset($_SESSION["uid"])) {header("location: in"); exit;} ?>
<!DOCTYPE html>
<html>
	<head>
		<title>Grizzly.pw</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link type="text/css" rel="stylesheet" href="resources/css/materialize.min.css"	media="screen,projection"/>
		<link type="text/css" rel="stylesheet" href="resources/css/outter.css"	media="screen,projection"/>
		<script type="text/javascript" src="resources/js/jquery.min.js"></script>
	</head>
	<body>
		<div class="lr-button" id="lr-button">
			<a href="javascript:void(0)" onclick="changeLRLayer('register')" class="waves-effect waves-light btn orange white-text"><i class="material-icons left">add_box</i>Registrate!</a>
		</div>
		<div class="row center-align" style="margin: 0px;">
			<div class="hide-on-small-only col m4 l4 offset-m1 offset-l2 intro-text">
				<span class="intro-text-title">Experimenta con Grizzly</span>
				<br>
				<br>
				Participa en cientos de eventos que se crean cerca o lejos de ti. Asiste a inolvidables experiencias a la vuelta de la esquina o en cualquier ciudad.
				Descubre nuevas personas y maneras de vivir la vida, con eventos alocados. ¿Qué hacer hoy con tus amigos?
				¿Degustación de insectos, carrera de sacos o participar en una competición de autos locos?
				Todo ello, descúbrelo, con Grizz.ly
			</div>
			<div class="col s12 m6 l3 offset-m6 offset-l7">

				<div id="logo-index">
					<div id="logo-index-fondo">
						<div id="logo-index-fondo2">
							<div id="logo-index-fondo3">
								<div id="logo-index-fondo4">
								</div>	
							</div>	
						</div>
					</div>

					<img src="resources/img/logo.png" id="logo-foto">
				</div>
				<div class="card blue-white lr-layer" id="lr-layer">
					<?php include("pages/login.html"); ?>
					<p class="z-depth-2"></p>
				</div>
			</div>
		</div>
		<div class="slider fullscreen" style="z-index: -3;">
			<ul class="slides">
				<li>
					<img src="resources/img/fondo1.jpg">
				</li>
				<li>
					<img src="resources/img/fondo2.jpg">
				</li>
				<li>
					<img src="resources/img/fondo3.jpg">
				</li>
				<li>
					<img src="resources/img/fondo4.jpg">
				</li>
				<li>
					<img src="resources/img/fondo5.jpg">
				</li>
			</ul>
		</div>
		<script type="text/javascript" src="resources/js/jquery.form.min.js"></script>
		<script type="text/javascript" src="resources/js/materialize.min.js"></script>
		<script type="text/javascript" src="resources/js/functions.js"></script>
		<script>
			$(document).ready(function(){
				$('.slider').slider({full_width: true, indicators:false, transition:2000});
			});
		</script>
	</body>
</html>