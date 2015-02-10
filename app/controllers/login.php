<?php
//require('conf.php');

//funcion para comprobar login antes de renderizar la pagina
$login = function ($app) {
	return function () use ($app) {
		if (!isset($_SESSION['user_data']) || !isset($_SESSION['session']) ) {
			unset($_SESSION['user_data']);
			unset($_SESSION['session']);
			
			$app->flash('errors', array('Tiene que estar autentificado'));
			$app->redirect($app->urlFor('login'));
		}else{
			if($_SESSION['session']!=session_id()){
				$app->flash('errors', array('Su sesión ha expirado'));
				$app->redirect($app->urlFor('login'));
			}
		}
	};
};
//disparador del login
$app->hook('slim.before.dispatch', function() use ($app) {
	//$user = null;
	//if (isset($_SESSION['user'])) {
	//	$user = $_SESSION['user'];
	//}
	//$app->view()->setData('user', $user);
});

//controlador del login
$app->get("/login", function () use ($app) {
	$flash = $app->view()->getData('flash');
	$error = '';
	if (isset($flash['error'])) {
		$error = $flash['error'];
	}
	$urlRedirect = '/';
	if ($app->request()->get('r') && $app->request()->get('r') != '/logout' && $app->request()->get('r') != '/login') {
		$_SESSION['urlRedirect'] = $app->request()->get('r');
	}
	if (isset($_SESSION['urlRedirect'])) {
		$urlRedirect = $_SESSION['urlRedirect'];
	}
	$email_value = $email_error = $password_error = '';
	if (isset($flash['email'])) {
		$email_value = $flash['email'];
	}
	if (isset($flash['errors']['email'])) {
		$email_error = $flash['errors']['email'];
	}
	if (isset($flash['errors']['password'])) {
		$password_error = $flash['errors']['password'];
	}
	$app->render('login/login.html', array('error' => $error, 'email_value' => $email_value, 'email_error' => $email_error, 'password_error' => $password_error, 'urlRedirect' => $urlRedirect));
	
})->name('login');


$app->post("/login", function () use ($app) {
	$usuario = $app->request()->post('username');
	$password = $app->request()->post('password');
	
	$errors = array();
	$row = R::getAll("SELECT * FROM sia_users where username=:usuario and activo=1 limit 1"
		,array(':usuario' => $usuario)
	);
	if (count($row)>0){
		
		if (md5($password)!= $row[0]['password']) {
			//$errors['password'] = crypt($password)." ".$row[0]['password'];
			$app->flash('errors', "Usuario / Password no coincide");
			$app->redirect($app->urlFor('login'));
		}
		
		unset($row[0]['password']);
		
		$permisos=array();
		$perms = R::getAll("select p.nombre as perm,r.nombre as perfil from sia_roles as r join sia_roles_permisos as rp on r.id=rp.sia_roles_id
					join sia_permisos as p on p.id=rp.sia_permisos_id
					where r.id=1 and p.activo=1"
			,array(':perfil'=>$row[0]['sia_roles_id'])
		);	
		if (!isset($perms[0]['perfil'])){
			$app->flash('errors', "Error en cargar el perfil");
			$app->redirect($app->urlFor('login'));
		}
		
		$p=array();
		$perfil=$perms[0]['perfil'];
		foreach($perms as $perm){
			$p[]=$perm['perm'];
		}
		$_SESSION['user_data'] = $row[0];
		$_SESSION['user_data']['permisos']=$p;
		$_SESSION['session']=session_id();
	}else{
		$errors['password'] = "Usuario / Password no coincide";
		$app->flash('errors', $errors);
		$app->redirect($app->urlFor('login'));
	}	
	$app->redirect($app->urlFor('index'));
});
$app->get('/',$login($app), function() use($app) {
	$app->render('dashboard/dashboard.html');
})->name('index');  

$app->get('/logout',$login($app), function() use($app) {
	unset($_SESSION['user_data']);
	unset($_SESSION['session']);
	$app->flash('success', 'Usted ha salido del sistema');
	$app->redirect($app->urlFor('login'));
})->name('logout');

$app->get('/registro', function() use($app) {
	$app->render('login/registro.html');
})->name('registro');

$app->get('/recuperar-clave', function() use($app) {
	$app->render('login/forgot.html');
})->name('forgot');

$app->get('/ajax/recuperar-clave', function() use($app) {
	//$app->render('forgot.html');
	$correo=$app->request()->post('email');
	$usuario = R::getAll("SELECT * FROM sia_users where username=:usuario and activo=1 limit 1"
		,array(':usuario' => $correo)
	);
	
	if (count($row)>0){
		$mail=correo();
		$mail->From = 'from@example.com';
		$mail->FromName = 'Mailer';
		$mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
		$mail->addAddress('ellen@example.com');               // Name is optional
		$mail->addReplyTo('info@example.com', 'Information');
		$mail->addCC('cc@example.com');
		$mail->addBCC('bcc@example.com');

		$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = 'Here is the subject';
		$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		if(!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			echo 'Message has been sent';
		}
	}else{
		// no existe mail
	
	}
	

});

$app->post('/registrar_usuario', function() use($app) {
	$usuario = R::dispense( 'sia_users' );
	$usuario->import($_POST, 'nombres,apellido_paterno,apellido_materno,email');
	
	$usuario->username=$app->request()->post('email');
	$usuario->password=md5($app->request()->post('password'));
	$usuario->sia_roles_id=2;
	
	try{
        $id=R::store($usuario);
    }
    catch(Exception $e){
		$app->flash('errors', 'Hubo un error al crear el usuario en nuestro sistema, ('.$e->getMessage().')');
		$app->redirect($app->urlFor('registro'));
       
	   // echo $e->getMessage();
        //$invalid = $prestamo->getInvalid();
        //print_r($invalid);
        //exit;
    }
	$app->flash('success', 'Su cuenta ha sido creada satisfactoriamente');
	$app->redirect($app->urlFor('login'));
	

	//$app->render('registro.html');
});

