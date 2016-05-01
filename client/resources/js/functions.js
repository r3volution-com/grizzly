/*GENERIC FUNCTIONS*/
function changeLayer(layer, page, action, callback){
	switch (action){
		case "change":
			$.ajax({
				url: page,
				success: function(res) {
					$(layer).html(res);
					if (typeof callback === "function") callback();
				},
				error: function(res) {
					Materialize.toast('Se ha producido un error', 3000);
					console.log(res);
				}
			});
		break;
		case "append":
			$.ajax({
				url: page,
				success: function(res) {
					$(layer).append(res);
					if (typeof callback === "function") callback();
				},
				error: function(res) {
					Materialize.toast('Se ha producido un error', 3000);
					console.log(res);
				}
			});
		break;
		case "prepend":
			$.ajax({
				url: page,
				success: function(res) {
					$(layer).prepend(res);
					if (typeof callback === "function") callback();
				},
				error: function(res) {
					Materialize.toast('Se ha producido un error', 3000);
					console.log(res);
				}
			});
		break;
		default:
			Materialize.toast('No tienes permitido hacer eso', 3000);
		break;
	}
}
/*OUTTER FUNCTIONS*/
function changeLRLayer(type){
	if (type == "register"){
		changeLayer('#lr-layer', 'pages/register.html', 'change', function() {
			$("#lr-button").html('<a href="javascript:void(0)" onclick="changeLRLayer(\'login\')" class="waves-effect waves-light btn orange white-text"><i class="material-icons left">account_box</i>Entra</a>');
		});
	} else if (type == "login"){
		changeLayer('#lr-layer', 'pages/login.html', 'change', function() {
			$("#lr-button").html('<a href="javascript:void(0)" onclick="changeLRLayer(\'register\')" class="waves-effect waves-light btn orange white-text"><i class="material-icons left">add_box</i>Registrate!</a>');
		});
	} else if (type == "recpass"){
		changeLayer('#lr-layer', 'pages/recpass.html', 'change', function() {
			$("#lr-button").html('<a href="javascript:void(0)" onclick="changeLRLayer(\'login\')" class="waves-effect waves-light btn orange white-text"><i class="material-icons left">account_box</i>Entra</a>');
		});
	} else Materialize.toast('No tienes permitido hacer eso', 3000);
}
/*INNER FUNCTIONS*/	
function changeContent(page, extra){
	if (typeof extra != "string") extra = "";
	if (prevPage != page){
		$.ajax({
			url: "pages/"+page+".html"+extra,
			success: function(res) {
				$("#"+prevPage+"-but").parent().removeClass("active");
				$("#"+page+"-but").parent().addClass("active");
				prevPage = page;
				window.location.hash = page;
				$("#formcontent").html(res);
			}
		});
	}
}
function obtenerLista(lon, lat){
	socket.emit('c-event-get', 
		{loc_long: lon, loc_lat: lat, max_radio: $("#input-number3").val(), type: 0, min_cost:$("#input-number2").val(), max_cost: $("#input-number1").val(), 
		min_date: $("#fecha-ini").val(), max_date: $("#fecha-fin").val(), min_radio: $("#input-number4").val()});
}
function getDetails(id){
	socket.emit('c-event-details', {event_id: id});
}
function followUser(id){
	socket.emit('c-user-follow', {followed: id});
}
function eventSuscribe(id){
	socket.emit('c-event-subscribe', {event_id: id});
}
function loadModal(type){
	if (type == "createroom"){
		changeLayer("#modal", 'pages/createroom.html', 'change', function(){
			$('#modal').openModal();
		});
	} else Materialize.toast('No tienes permitido hacer eso', 3000);
}