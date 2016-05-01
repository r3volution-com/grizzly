<?php
session_start();
include("include/config.php");
include("class/user.class.php");
require("resources/libs/class.phpmailer.php");
if (!isset($_GET["op"])) die(json_encode(array("status" => "ERROR", "msg" => "No tienes permitido hacer eso")));
if (isset($_SESSION["uid"])) $user = new User($_SESSION["uid"]);
else $user = new User();
switch ($_GET["op"]){
	case "login":
		if ($user->getUID()) die(json_encode(array("status" => "ERROR", "msg" => "No tienes permitido hacer eso")));
		if ((isset($_POST["username"]) && isset($_POST["password"])) && ($_POST["username"] != "" && $_POST["password"] != "")){
			if ($user->doLogin(htmlspecialchars($_POST["username"], ENT_QUOTES, 'UTF-8'), htmlspecialchars($_POST["password"], ENT_QUOTES, 'UTF-8'))){
				die(json_encode(array("status" => "OK", "data" => array("userid" => $user->getUID(), "sessionid" => $user->getSessionID()))));
			}else die(json_encode(array("status" => "ERROR", "msg" => "El usuario/email o la contrase&ntilde;a no coinciden")));
		}else die(json_encode(array("status" => "ERROR", "msg" => "El usuario/email y la contrase&ntilde;a no pueden estar en blanco")));
	break;
	case "register":
		if ($user->getUID()) die(json_encode(array("status" => "ERROR", "msg" => "No tienes permitido hacer eso")));
		if ((isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"]) && isset($_POST["repassword"])) && ($_POST["username"] != "" && $_POST["password"] != "" && $_POST["email"] != "" && $_POST["repassword"] != "")){
			if (ctype_alnum($_POST["username"])){
				if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
					if (strlen($_POST["password"]) >= 6) {
						if ($_POST["password"] == $_POST["repassword"]){
							if ($user->doRegister($_POST["username"], $_POST["email"], $_POST["password"])){
								$mail = new PHPMailer;
								$mail->setFrom($email, $emailname);
								$mail->addAddress($_POST["email"]); 
								$mail->isHTML(true);
								$mail->Subject = $mail_registersubject;
								$body = "";
								sprintf($body, $mail_registerbody, $_POST['username'], $_POST['email']);
								$mail->Body = $body;
								$mail->send();
								die(json_encode(array("status" => "OK")));
							} else die(json_encode(array("status" => "ERROR", "msg" => "Este usuario o email ya esta registrado")));
						} else die(json_encode(array("status" => "ERROR", "msg" => "Las contrase&ntilde;as no coinciden")));
					} else die(json_encode(array("status" => "ERROR", "msg" => "La contrase&ntilde;a debe tener 6 o mas caracteres")));
				} else die(json_encode(array("status" => "ERROR", "msg" => "El e-mail introducido no es valido")));
			} else die(json_encode(array("status" => "ERROR", "msg" => "El nombre de usuario solo puede contener numeros y letras")));
		} else die(json_encode(array("status" => "ERROR", "msg" => "Por favor rellene todos los campos")));
	break;
	case "recpass":
		if ($user->getUID()) die(json_encode(array("status" => "ERROR", "msg" => "No tienes permitido hacer eso")));
		if (isset($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
			$newpass = recoverPassword($email);
			if ($newpass) {
				$mail = new PHPMailer;
				$mail->setFrom($email, $emailname);
				$mail->addAddress($_POST["email"]); 
				$mail->isHTML(true);
				$mail->Subject = $mail_recpasssubject;
				$mail->Body = printf($mail_recpassbody, $_POST["email"], $newpass);
				if (!$mail->send()) die(json_encode(array("status" => "ERROR", "msg" => "El email ya se encuentra registrado")));
				die(json_encode(array("status" => "OK", "msg" => "Tu nueva contraseña es: ".$newpass)));
			} else die(json_encode(array("status" => "ERROR", "msg" => "El email ya se encuentra registrado")));
		} else die(json_encode(array("status" => "ERROR", "msg" => "El email introducido no es valido")));
	break;
	//TODO ESTO ES MI DESTROZO, POR SI CAGADITA
	case "change_email":
		if (!$user->getUID()) die(json_encode(array("status" => "ERROR", "msg" => "No tienes permitido hacer eso")));
		//TODO LO QUE REQUIERA TOCAR LA TABLA DE USUARIOS DE MYSQL HAZLO EN EL ARCHIVO users.class.php de la carpeta class
		if (isset($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
			if($user->updateEmail(htmlspecialchars($_POST["email"], ENT_QUOTES, 'UTF-8'))){
				die(json_encode(array("status" => "OK")));
			}else die(json_encode(array("status" => "ERROR", "msg" => "Error en la base de datos, intentelo mas tarde")));
			//Consulta SQL cambia email
			//si se cambia correctamente $cambios = true;
		}else die(json_encode(array("status" => "ERROR", "msg" => "Introduzca un email correcto")));
	break;
	case "change_pass":
		if (!$user->getUID()) die(json_encode(array("status" => "ERROR", "msg" => "No tienes permitido hacer eso")));
		if (isset($_POST["password"]) && strlen($_POST["password"]) >= 6) {
			if ($_POST["password"] == $_POST["repassword"]){
				if($user->updatePassword(htmlspecialchars($_POST["password"], ENT_QUOTES, 'UTF-8'))){
					die(json_encode(array("status" => "OK")));
				}else die(json_encode(array("status" => "ERROR", "msg" => "Fallo en la base de datos, intentelo mas tarde")));
			}else die(json_encode(array("status" => "ERROR", "msg" => "Repita correctamente la contraseña")));
		}else die(json_encode(array("status" => "ERROR", "msg" => "Introduzca una contraseña mayor de 6 caracteres")));
	break;
	case "change_profile_pic":
		if (!$user->getUID()) die(json_encode(array("status" => "ERROR", "msg" => "No tienes permitido hacer eso")));
		if (isset($_FILES["image"]) && isset($_FILES["image"]["name"])) {
			$dir_subida="uploads/users/";
			$fichero_subido = $dir_subida . basename($_FILES['image']['name']);
			if(move_uploaded_file($_FILES['image']['tmp_name'], $fichero_subido)){
				if($user->updateProfilePic(htmlspecialchars($fichero_subido, ENT_QUOTES, 'UTF-8'))){
					die(json_encode(array("status" => "OK")));
				}else die(json_encode(array("status" => "ERROR", "msg" => "No se ha podido insertar en la base de datos, intentelo mas tarde")));
			}else die(json_encode(array("status" => "ERROR", "msg" => "No se ha podido subir el fichero correctamente")));
			//subir la imagen a la carpeta uploads/users y guardarla en la bD
		}else die(json_encode(array("status" => "ERROR", "msg" => "Coloque un archivo imagen, por favor")));
	break;
	case "logout":
		session_unset();
		header("location: /");
		exit;
	break;
	default:
		die(json_encode(array("status" => "ERROR", "msg" => "No tienes permitido hacer eso")));
	break;
}