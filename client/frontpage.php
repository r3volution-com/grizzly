<?php session_start(); if (!isset($_SESSION["uid"])) {header("location: index.php"); exit;} else { include("class/user.class.php"); $user = new User($_SESSION["uid"]); } ?>
<!DOCTYPE html>
<html>
	<head>
		<title>Grizzly.pw</title>
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link type="text/css" rel="stylesheet" href="resources/css/materialize.min.css"	media="screen,projection"/>
		<link type="text/css" rel="stylesheet" href="resources/css/materialize.clockpicker.css" media="screen,projection"/>
		<link type="text/css" rel="stylesheet" href="resources/css/frontpage.css"	media="screen,projection"/>
		<link type="text/css" rel="stylesheet" href="resources/css/nouislider.min.css"	media="screen,projection"/>
		<link type="text/css" rel="stylesheet" href="resources/css/nouislider.pips.css"	media="screen,projection"/>
		<link type="text/css" rel="stylesheet" href="resources/css/nouislider.tooltips.css"	media="screen,projection"/>		
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		<script type="text/javascript" src="resources/js/jquery.min.js"></script>
		<script src="https://cdn.socket.io/socket.io-1.3.7.js"></script>
		<script>
			var socket = io.connect('https://grizzly.pw:8080');
			socket.on('connect', function (data) {
				socket.emit('c-login', {user_id: <?php echo $_SESSION["uid"]; ?>, session_id: '<?php echo session_id(); ?>' });
			});
			socket.on('s-login', function(data){
				if (data.status == "ERROR") Materialize.toast("Error: "+data.msg);
			});
			var username = '<?php echo $user->getUserData()["username"]; ?>';
		</script>
	</head>
	<body>
		<ul id="dropdown1" class="dropdown-content">
			<li><a href="javaScript:changeContent('perfil-usuario')">Perfil</a></li>
			<li class="divider"></li>
			<li><a href="javaScript:changeContent('configuracion_perfil')">Configuraci&oacute;n</a></li>
			<li class="divider"></li>
			<li><a href="operations.php?op=logout">Cerrar sesi&oacute;n</a></li>
		</ul>
		<div class="navbar-fixed">
			<nav>
				<ul id="slide-out" class="side-nav">
					<li><a href="javaScript:void(0)" id="prefil-usuario-but"><img class="foto-perfil" style="height:100px;width:100px;margin-left:75px;" src="uploads/users/noimage.jpg"></img></a></li>
					<li class="buscador">
						<form style="height:62px;">
							<input style="color:black;" type="text" value="Buscar..." onfocus="if (this.value == 'Buscar...') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Buscar...';}" />
						</form>
					</li>
					<li><a href="javaScript:changeContent('index')">Inicio</a></li>
					<li><a href="javaScript:changeContent('mapa')">Mapa</a></li>
					<li><a href="javaScript:changeContent('planes')">Planes</a></li>
					<!--<li><a href="javaScript:changeContent('retos')">Retos</a></li>-->
					<li><hr></li>
					<li><a href="javaScript:changeContent('perfil-usuario')">Perfil</a></li>
					<li><a href="javaScript:changeContent('configuracion_perfil')">Configuraci&oacute;n</a></li>
					<li><a href="operations.php?op=logout">Cerrar sesi&oacute;n</a></li>
				</ul>
				<a href="#" data-activates="slide-out" class="button-collapse"><i class="mdi-navigation-menu"></i></a>
				<div class="nav-wrapper">
					<a href="#" class="brand-logo center"><img class="logo-central" src="resources/img/logo-2-negativo.png"></a>
					<ul id="nav-mobile" class="left hide-on-med-and-down">
						<li><a href="javaScript:void(0)" id="index-but">Inicio</a></li>
						<li><a href="javaScript:void(0)" id="mapa-but">Mapa</a></li>
						<li><a href="javaScript:void(0)" id="planes-but">Planes</a></li>
						<!--<li><a href="javaScript:void(0)" id="retos-but">Retos</a></li>-->
					</ul>
					<ul id="nav-mobile" class="right hide-on-med-and-down">
						
						<li><a style="height:64px;" class='dropdown-button' href="javascript:void(0)" data-beloworigin="true"  data-activates='dropdown1'><img class="foto-perfil" src="uploads/users/noimage.jpg"></img></a></li>
						<li class="buscador">
							<form style="height:62px;">
								<input type="text" value="Buscar..." onfocus="if (this.value == 'Buscar...') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Buscar...';}" />
							</form>
						</li>
						<li class="boton-crear"><a href="javaScript:void(0)"></a>
						</li>
					</ul>
					<div class="boton-crear"><a href="javaScript:void(0)" id="crear-but" class="waves-effect waves-light btn orange white-text">Crear</a></div>
				</div>
			</nav>
		</div>
		<div id="formcontent"></div>
		<script type="text/javascript" src="resources/js/jquery.form.min.js"></script>
		<script type="text/javascript" src="resources/js/materialize.min.js"></script>
		<script type="text/javascript" src="resources/js/materialize.clockpicker.js"></script>
		<script type="text/javascript" src="resources/js/functions.js"></script>
		<script type="text/javascript" src="resources/js/nouislider.min.js"></script>
		<script type="text/javascript" src="//cdn.tinymce.com/4/tinymce.min.js"></script>
		<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDk9l23FxdX-4zX9NA8Ax1ynK4PXY1EQB0"></script>
		<script>
			var prevPage = "";
			var lat = 0, lon = 0;
			$(document).ready(function(){
				if (navigator.geolocation){
					navigator.geolocation.getCurrentPosition(function(objPosition){
						lat = objPosition.coords.latitude;
						lon = objPosition.coords.longitude;	
					}, 
					function(objPositionError) {
						switch (objPositionError.code) {
							case objPositionError.PERMISSION_DENIED:
								Materialize.toast("No se ha permitido el acceso a la posici&oacute;n del usuario.", 5000);
							break;
							case objPositionError.POSITION_UNAVAILABLE:
								Materialize.toast("No se ha podido acceder a la informaci&oacute;n de su posici&oacute;n.", 5000);
							break;
							case objPositionError.TIMEOUT:
								Materialize.toast("El servicio ha tardado demasiado tiempo en responder.", 5000);
							break;
							default:
								Materialize.toast("Error desconocido.", 5000);
							break;
						}
					}, 
					{
						maximumAge: 75000,
						timeout: 15000
					});
				} else {
					Materialize.toast("Su navegador no soporta la API de geolocalizaci&oacute;n.", 5000);
				}
				$('.button-collapse').sideNav({
					menuWidth: 300, // Default is 240
					edge: 'left', // Choose the horizontal origin
					closeOnClick: true // Closes side-nav on <a> clicks, useful for Angular/Meteor
				});
				$('.dropdown-button').dropdown({
					inDuration: 300,
					outDuration: 225,
					constrain_width: false, 
					hover: true, 
					gutter: 0, 
					belowOrigin: true, 
					alignment: 'left' 
				});
				$( "#slider-range" ).slider({
					range: true,
					min: 0,
					max: 500,
					values: [ 75, 300 ],
					slide: function( event, ui ) {
						$( "#amount" ).val( "$" + ui.values[ 0 ] + " - $" + ui.values[ 1 ] );
					}
				});
				if (window.location.hash){
					changeContent(window.location.hash.substr(1));
				} 
				else changeContent("index");
				socket.on('s-event-details', function(data){
					prevPage = 'details';
					if (data.status == "OK"){
						var container = $(document.createElement("div"));
						if ('data' in data){
							$.ajax({
								url: "pages/interior.html",
								success: function(res) {
										var obj = data.data;
										console.log(obj);
										var res2 = $(res);
										date = obj.date.split("-");
										year = date[0];
										month = date[1];
										day = date[2].split("T")[0];
										res2.find(".nombre-evento").html(obj.title);
										res2.find(".descripcion-interior").html(obj.description);
										res2.find(".boton-interior").attr('onclick', "eventSuscribe("+obj.id+")");
										/*res2.find(".user-dist").html(Math.round(obj.distance)+"km");
										res2.find(".nombre-usuario").html(obj.creator_name);
										res2.find(".suscribir-button").attr('id', obj.creator_id);
										res2.find(".user-price").html(obj.cost);
										res2.find(".user-time").html(day+"/"+month+"/"+year);
										res2.find(".user-assist").html(obj.current+"/"+obj.capacity);*/
										container.append(res2);
									}
							});
						} else container.html("No hay eventos disponibles");
						$("#formcontent").html(container);
					} else if (typeof data.msg == "string") Materialize.toast("Error: "+data.msg, 3000);
					else console.log(data.msg);
				});
				socket.on('s-user-follow', function(data){
					if (data.status == "OK") Materialize.toast("Te has suscrito a este usuario", 3000);
					else if (typeof data.msg == "string") Materialize.toast("Error: "+data.msg, 3000);
					else console.log(data.msg);
				});
				socket.on('s-event-subscribe', function(data){
					if (data.status == "OK") Materialize.toast("Te has suscrito a este evento", 3000);
					else if (typeof data.msg == "string") Materialize.toast("Error: "+data.msg, 3000);
					else console.log(data.msg);
				});
				$('#index-but').click(function(){
					changeContent("index");
				});
				$('#perfil-usuario-but').click(function(){
					changeContent("perfil-usuario");
				});
				$('#mapa-but').click(function(){
					changeContent("mapa");
				});
				$('#planes-but').click(function(){
					changeContent("planes");
				});
				$('#retos-but').click(function(){
					changeContent("retos");
				});
				$('#crear-but').click(function(){
					changeContent("crear");
				});
				$('#interior-but').click(function(){
					changeContent("interior");
				});
			});

		</script>
	</body>
</html>