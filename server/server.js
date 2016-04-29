var express  = require('express')
    , app    = express()
	, fs 	 = require('fs');
var options = {
    key:    fs.readFileSync('/home/grizzly/privkey.pem'),
    cert:   fs.readFileSync('/home/grizzly/cert.pem')
};
var server = require('https').createServer(options, app)
	, io = require('socket.io').listen(server)
	, mysql = require('mysql')
	, xss = require('xss')
	, connection = mysql.createConnection({
		host     : 'localhost',
		user     : 'grizzly',
		password : 'weplan',
		database : 'weplan'
	});
connection.connect();
server.listen(8080);

var rooms = [];

io.on('connection', function (socket) {
	socket.valid = false;
	/*Evento comprobacion de login*/
	socket.on('c-login', function(data){
		/*Comprobamos que se nos pasen los datos correctos*/
		if (('user_id' in data && !isNaN(data.user_id)) && ('session_id' in data && typeof data.session_id == "string")){
			/*consulta a la bd*/
			connection.query("SELECT count(*) AS cuantos FROM users WHERE id="+data.user_id+" AND session_id="+mysql.escape(data.session_id), function(err, rows, fields) {
				//ERROR en la consulta
				if (err) socket.emit('s-login', {status: 'ERROR', query: "SELECT count(*) AS cuantos FROM users WHERE id="+data.user_id+" AND session_id='"+mysql.escape(data.session_id)+"'", msg: err});
				else { //Exito
					if (rows[0].cuantos > 0) {
						socket.uid = data.user_id;
						socket.valid = true;
						socket.emit('s-login', {status: 'OK'});
					} else socket.emit('s-login', {status: 'ERROR', msg: 'Usuario no valido'});
				}
			});
		} else socket.emit('s-login', {status: 'ERROR', msg: 'Error en el objeto JSON'});
	});
	/*Evento para obtener eventos*/
	socket.on('c-event-get', function (data) {
		if (socket.valid){
			if (('loc_long' in data && !isNaN(data.loc_long)) && ('loc_lat' in data && !isNaN(data.loc_lat)) && ('max_radio' in data && !isNaN(data.max_radio)) && ('type' in data && !isNaN(data.type))){
				var extraparams = "";
				var extradata = ",(SELECT count(*) FROM attendance WHERE event_id = id) as current, capacity, image, "+
					"creator_id, (SELECT username FROM users WHERE id = creator_id) as creator_name, (SELECT count(*) FROM follows WHERE follower="+socket.uid+" AND followed=creator_id) "+
					"as creator_followed, (SELECT count(*) FROM attendance WHERE event_id=id and user_id="+socket.uid+") as attendance, "+
					"ST_Distance_Sphere(ST_GeomFromText('POINT("+data.loc_lat+" "+data.loc_long+")'), geom)/1000 as distance";
				/*Get params by type*/
				switch (data.type) {
					case 0: break;
					case 1:
						extraparams = " AND id IN (SELECT event_id FROM attendance WHERE user_id="+socket.uid+") ";
					break;
					case 2:
						extraparams = " AND id IN (SELECT event_id FROM attendance WHERE user_id IN (SELECT follower FROM follows WHERE followed ="+socket.uid+")) ";
					break;
					case 3:
						extradata = "";
					break;
					default:
						socket.emit('s-event-get', {status: 'ERROR', msg: 'Error en el objeto JSON'});
					break;
				}
				/*Extra params*/
				if (('min_cost' in data && !isNaN(data.min_cost)) && ('max_cost' in data && !isNaN(data.max_cost))){
					extraparams += " AND cost >= "+data.min_cost+" AND cost <= "+data.max_cost;
				}
				if (('min_date' in data && typeof data.min_date == "string") && ('max_date' in data && typeof data.max_date == "string")){
					extraparams += " AND date >= "+mysql.escape(data.min_date)+" AND date <= "+mysql.escape(data.max_date);
				}
				if ('min_radio' in data && !isNaN(data.min_radio)){
					extraparams += " AND ST_Distance_Sphere(ST_GeomFromText('POINT("+data.loc_lat+" "+data.loc_long+")'), geom) >= "+data.min_radio*1000;
				}
				/*query*/
				var query = "SELECT id, title, description, geom, date, cost "+extradata+" FROM events "+
					"WHERE ST_Distance_Sphere(ST_GeomFromText('POINT("+data.loc_lat+" "+data.loc_long+")'), geom) <= "+data.max_radio*1000+" "+extraparams+" ORDER BY distance ASC LIMIT 30";
				connection.query(query, function(err, rows, fields) {
					if (err) socket.emit('s-event-get', {status: 'ERROR', msg: err, query: query});
					socket.emit('s-event-get', {status: 'OK', data: rows, type: data.type});
				});
			} else socket.emit('s-event-get', {status: 'ERROR', msg: 'Error en el objeto JSON'});
		} else socket.emit('s-event-get', {status: 'ERROR', msg: 'Usuario no valido'});
	});
	/*Evento para crear eventos*/
	socket.on('c-event-create', function (data) {
		if (socket.valid){
			if (('title' in data && typeof data.title == "string") && ('description' in data && typeof data.description == "string") && ('loc_long' in data && !isNaN(data.loc_long)) 
				&& ('loc_lat' in data && !isNaN(data.loc_lat)) && ('date' in data && typeof data.date == "string") && ('cost' in data && !isNaN(data.cost)) 
				&& ('capacity' in data && !isNaN(data.capacity))){
				connection.query("INSERT INTO events (title, description, geom, date, cost, capacity, creator_id) VALUES"+
					" ('"+mysql.escape(xss(data.title))+"', '"+mysql.escape(xss(data.description))+"', ST_GeomFromText('POINT("+data.loc_lat+" "+data.loc_long+")'), "+
					"'"+mysql.escape(data.date)+"', "+data.cost+", "+data.capacity+", "+socket.uid+")", function(err, result) {
					if (err) socket.emit('s-event-create', {status: 'ERROR', msg: err});
					socket.emit('s-event-create', {status: 'OK', data: {id:result.insertId}});
				});
			} else socket.emit('s-event-create', {status: 'ERROR', msg: 'Error en el objeto JSON'});
		} else socket.emit('s-event-create', {status: 'ERROR', msg: 'Usuario no valido'});
	});
	/*Evento para editar eventos*/
	socket.on('c-event-edit', function (data) {
		if (socket.valid){
			if (('event_id' in data && !isNaN(data.event_id)) && ('title' in data.data && typeof data.data.title == "string") && ('description' in data.data && typeof data.data.description == "string") 
				&& ('loc_long' in data.data && !isNaN(data.data.loc_long)) && ('loc_lat' in data.data && !isNaN(data.data.loc_lat)) 
				&& ('date' in data.data && typeof data.data.date == "string") && ('cost' in data.data && !isNaN(data.data.cost)) && ('capacity' in data.data && !isNaN(data.data.capacity))){
				connection.query("UPDATE events SET title='"+mysql.escape(xss(data.data.title))+"', description='"+mysql.escape(xss(data.data.description))+"', "+
					"ST_GeomFromText('POINT("+data.data.loc_lat+" "+data.data.loc_long+")')"+
					", date='"+mysql.escape(data.data.date)+"', cost="+data.data.cost+" WHERE id="+data.event_id+" AND creator_id="+socket.uid, function(err, rows, fields) {
					if (err) socket.emit('s-event-edit', {status: 'ERROR', msg: err});
					socket.emit('s-event-edit', {status: 'OK'});
				});
			} else socket.emit('s-event-edit', {status: 'ERROR', msg: 'Error en el objeto JSON'});
		} else socket.emit('s-event-edit', {status: 'ERROR', msg: 'Usuario no valido'});
	});
	/*Evento para borrar eventos*/
	socket.on('c-event-delete', function (data) {
		if (socket.valid){
			if ('event_id' in data && !isNaN(data.event_id)){
				connection.query('DELETE FROM events WHERE id='+data.event_id+" AND creator_id="+socket.uid, function(err, rows, fields) {
					if (err) socket.emit('s-event-delete', {status: 'ERROR', msg: err});
					socket.emit('s-event-delete', {status: 'OK'});
				});
			} else socket.emit('s-event-delete', {status: 'ERROR', msg: 'Error en el objeto JSON'});
		} else socket.emit('s-event-delete', {status: 'ERROR', msg: 'Usuario no valido'});
	});
	/*Evento para obtener detalles de eventos*/
	socket.on('c-event-details', function (data) {
		if (socket.valid){
			if ('event_id' in data && !isNaN(data.event_id)){
				connection.query('SELECT id, title, description, geom, cost, date, (SELECT count(*) FROM attendance WHERE event_id = id) as current, capacity, image, '+
					'creator_id, (SELECT username FROM users WHERE id = creator_id) as creator_name FROM events WHERE id='+data.event_id, function(err, rows, fields) {
					if (err) socket.emit('s-event-details', {status: 'ERROR', msg: err});
					socket.emit('s-event-details', {status: 'OK', data: rows[0]});
				});
			} else socket.emit('s-event-details', {status: 'ERROR', msg: 'Error en el objeto JSON'});
		} else socket.emit('s-event-details', {status: 'ERROR', msg: 'Usuario no valido'});
	});
	/*Estadisticas de eventos*/
	socket.on('c-event-statistics', function(data){ 
		if (socket.valid){
			connection.query('SELECT count(*) as myevents, (SELECT count(*) FROM attendance WHERE user_id='+socket.uid+') as goevents, (SELECT count(*) FROM follows '+
				'WHERE follower = '+socket.uid+') as followed, (SELECT count(*) FROM follows WHERE followed = '+socket.uid+') as followers FROM events WHERE creator_id='+socket.uid, 
			function(err, rows, fields) {
				if (err) socket.emit('s-event-statistics', {status: 'ERROR', msg: err});
				socket.emit('s-event-statistics', {status: 'OK', data: rows[0]});
			});
		}else socket.emit('s-event-statistics', {status: 'ERROR', msg: 'Usuario no valido'});
	});
	/*Evento para suscribirse a eventos*/
	socket.on('c-event-subscribe', function (data) {
		if (socket.valid){
			if ('event_id' in data && !isNaN(data.event_id)){
				connection.query('INSERT INTO attendance VALUES ('+socket.uid+', '+data.event_id+')', function(err, rows, fields) {
					if (err) socket.emit('s-event-subscribe', {status: 'ERROR', msg: err});
					socket.join("room_"+data.event_id);
					io.to("room_"+data.event_id).emit('s-event-useradd');
					socket.emit('s-event-subscribe', {status: 'OK'});
				});
			} else socket.emit('s-event-subscribe', {status: 'ERROR', msg: 'Error en el objeto JSON'});
		} else socket.emit('s-event-subscribe', {status: 'ERROR', msg: 'Usuario no valido'});
	});
	/*Evento para desuscribirse de eventos*/
	socket.on('c-event-subscribe', function (data) {
		if (socket.valid){
			if ('event_id' in data && !isNaN(data.event_id)){
				connection.query('DELETE FROM attendance WHERE user_id='+socket.uid+' AND event_id='+data.event_id, function(err, rows, fields) {
					if (err) socket.emit('s-event-desubscribe', {status: 'ERROR', msg: err});
					socket.leave("room_"+data.event_id);
					io.to("room_"+data.event_id).emit('s-event-userdel');
					socket.emit('s-event-desubscribe', {status: 'OK'});
				});
			} else socket.emit('s-event-desubscribe', {status: 'ERROR', msg: 'Error en el objeto JSON'});
		} else socket.emit('s-event-desubscribe', {status: 'ERROR', msg: 'Usuario no valido'});
	});
	/*Evento para suscribirse a usuarios*/
	socket.on('c-user-follow', function (data) {
		if (socket.valid){
			if ('followed' in data && !isNaN(data.followed)){
				connection.query('INSERT INTO follows VALUES ('+socket.uid+', '+data.followed+')', function(err, rows, fields) {
					if (err) socket.emit('s-user-follow', {status: 'ERROR', msg: err});
					socket.emit('s-user-follow', {status: 'OK'});
				});
			} else socket.emit('s-user-follow', {status: 'ERROR', msg: 'Error en el objeto JSON'});
		} else socket.emit('s-user-follow', {status: 'ERROR', msg: 'Usuario no valido'});
	});
	/*Evento para suscribirse a usuarios*/
	socket.on('c-user-unfollow', function (data) {
		if (socket.valid){
			if ('followed' in data && !isNaN(data.followed)){
				connection.query('DELETE FROM follows where follower ='+socket.uid+" AND followed="+data.followed, function(err, rows, fields) {
					if (err) socket.emit('s-user-unfollow', {status: 'ERROR', msg: err});
					socket.emit('s-user-unfollow', {status: 'OK'});
				});
			} else socket.emit('s-user-unfollow', {status: 'ERROR', msg: 'Error en el objeto JSON'});
		} else socket.emit('s-user-unfollow', {status: 'ERROR', msg: 'Usuario no valido'});
	});
});