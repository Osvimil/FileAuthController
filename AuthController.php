<?php
class AuthController extends Zend_Controller_Action
{
    function init()
    {
       $this->view->baseUrl = $this->_request->getBaseUrl()."/";
       $this->view->user = Zend_Auth::getInstance()->getIdentity();
       Zend_Session::namespaceUnset('noFolio');

       //unset($_SESSION[humanCheck]);
       //print_r($_SESSION);
    }

    /**
	 *
	 * Para el alumno
	 *
	 */

	function indexAction()
	{

		Zend_Auth::getInstance()->clearIdentity();

		$this->view->action='auth/idenAlumno/';

		$arrMjs1[1]	='Por favor proporcione los datos  correctamente.';
		$arrMjs1[2]	='Por favor proporcione el N&uacute;mero de Cuenta.';
		$arrMjs1[3]	='Escriba los caracteres que muestra la imagen.';
		$arrMjs1[4]	='La informaci&oacute;n proprocionada no coincide con la imagen.';
		$arrMjs1[10]='No existe alg&uacute;n dato relacionado al Grado prorcionado.
						<br />Verifique su informaci&oacute;n.';

		$intMsjstp1 = (int)$this->_request->getParam('nomjs',0);
		$intMsjstp2 = (int)$this->_request->getParam('nomjs2',0);

		$this->view->mjstp1=$arrMjs1[$intMsjstp1];

		Zend_Loader::loadClass('Titulo');
        $objTitulo=new Titulo();


        $objFecthTitulo=$objTitulo->fetchAll('1=1','titulo_id');
        $this->view->objTitulo=$objFecthTitulo;


        //$lsFontPath='./_public/font/CarawayBold.ttf';
        $lsFontPath='./_public/font/Abtecia Basic Sans Serif Font.ttf';

		$lsImgPath='./_public/captchaimg';

		$this->view->captchaId = $this->generateCaptcha($lsFontPath, $lsImgPath);
	}
	function idenAlumnoAction ()
	{

		if ($this->_request->isPost()) {

	        // collect the data from the user
	        Zend_Loader::loadClass('Zend_Filter_StripTags');

	        $f = new Zend_Filter_StripTags();

	       $titulo_id = (int)(trim($this->_request->getPost('titulo_id'))=='')?0:
	       				$f->filter($this->_request->getPost('titulo_id'));
	       $num_cuenta = (int)$f->filter($this->_request->getPost('num_cuenta'));

	       $lsInputCaptcha = (trim($this->_request->getPost('input_captcha'))=='')?0:
	       				$f->filter($this->_request->getPost('input_captcha'));

	       	$lsIdCaptcha = trim($this->_request->getPost('id_captcha'));

		    $laCaptcha = array('id' => $lsIdCaptcha, 'input' => $lsInputCaptcha);

	        if (empty($num_cuenta) && empty($lsInputCaptcha)) {

	        	$this->_redirect('/auth/index/nomjs/1');
	        }

	        else if (empty($num_cuenta)){

	        	$this->_redirect('/auth/index/nomjs/2');
	        }
	        elseif (empty($lsInputCaptcha))
	        {
	        	$this->_redirect('/auth/index/nomjs/3');
	        }
	        elseif (!$this->validateCaptcha($laCaptcha))
	        {
	        	$this->_redirect('/auth/index/nomjs/4');
	        }
	        else {
//	        	En el cap de especialidad busca en la tabla cert_alumno_especialidad
//	        	if($titulo_id{0}==3){
//	        		Zend_Loader::loadClass('AlumnoEsp');
//	        		$obj=new AlumnoEsp();
//	        		$strWhere=" AND substr (plan,1,1)=3";
//	        	}//Para alumnos  de maestr�a y doctorado
//	        	else{
	        	   	Zend_Loader::loadClass('Alumno');
	        	   	$obj=new Alumno();


/*

$nombre = $_GET["nombre"];
$apaterno = $_GET["apaterno"];
$amaterno = $_GET["amaterno"];
$fecha_nacimiento = $_GET["fecha_nacimiento"];
$genero = $_GET["genero"];
*/

//$numero_cuenta = $_GET["cuenta"];
//----------------------------------------------------------------------------
//Código sugerido por Memos
//$url_service = 'https://www.saep.unam.mx/api-2/cep/alumno/'.$num_cuenta;
//$webSerice = new Zend_Rest_Client($url_service,USUARIO,PASS);
//----------------------------------------------------------------------------
$webSerice = new Zend_Rest_Client('https://www.saep.unam.mx/api-2/cep/alumno/501048453');


  $usuarioWeb = 'cepsistemas';
  $contrasenita = 'ZOpU7MFr8vXV7OE6c8cMui71IIYDESFaZ9FAjm18';

  if (($usuarioWeb == 'cepsistemas') && ($contrasenita=='ZOpU7MFr8vXV7OE6c8cMui71IIYDESFaZ9FAjm18')) {
    $webSerice->arg1('cuenta');
    $webSerice->arg2('nombre');
    $webSerice->arg3('apaterno');
    $webSerice->arg4('amaterno');
    $webSerice->arg5('fecha_nacimiento');
    $webSerice->arg6('genero');
    $webSerice->get();
    //$webSerice->getHttpClient()->setAuth($usuarioWeb,$contrasenita,Zend_Http_Client::AUTH_BASIC);
    //
    $obj=$webSerice;
  }

	        	   	//$strWhere=" AND substr(plan,1,1)=".$titulo_id{0};
	        	//}

	    		$Alumno=$obj;

	   	 		$objFecthAlumno=$Alumno->fetchAll('num_cuenta='.(int)$num_cuenta.$strWhere);

	    		$objCurrentAlumno=$objFecthAlumno->current();

			    // setup Zend_Auth adapter for a database table

	            Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
	            $dbAdapter = Zend_Registry::get('db');

              /*
              //agregado el dia 19/04/17
              $dNext = $db->fetchRow('SELECT * FROM  alumnos WHERE num_cuenta='.$num_cuenta.'');
              $arrData = array('alumnos' =>$dNext,
        							'alumno_id'=>$this->alumno_id,
        			                'programa'=> $programa,
        			                'fecha_nacimiento' =>$fecha_nacimiento,
        			                'sexo' =>$sexo,
        			                'email' =>$email,
        			                'licenciatura'=> $licenciatura,
        			                );
                              $objCurrentAlumno->insert($arrData);

                //termina lo hecho el dia 19/04/17
                */


	            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);


//	           if($titulo_id{0}==3){
//
//	            	$authAdapter->setTableName('cert_alumno_especialidad');
//	            	$authAdapter->setIdentityColumn('num_cuenta');
//	            	$authAdapter->setCredentialColumn('alumno_id');
//	            }
//	            else {
//
	            	$authAdapter->setTableName('alumnos');
	            	$authAdapter->setIdentityColumn('num_cuenta');



	            // Set the input credential values to authenticate against
	            $authAdapter->setIdentity($num_cuenta);




	            // do the authentication
	            $auth = Zend_Auth::getInstance();

	            $result = $auth->authenticate($authAdapter);

	            if ($result->isValid() && substr($objCurrentAlumno->plan,0,1)==$titulo_id{0}) {
	            //if ($result->isValid()) {
	                // success: store database row to auth's storage
	                // system. (Not the password though!)
	                $data = $authAdapter->getResultRowObject(null,'password');
	                $auth->getStorage()->write($data);

	                $this->_redirect('/solicitud/consultarEMyD');

	            } else {

	            	// failure: clear database row from session

					$this->_redirect('/auth/index/nomjs/10');
	            }//Endif if ($result->isValid())
	        }
	    }
		else
	 	$this->_redirect('/index/');


		$lsIdCaptcha = trim($this->_request->getPost('id_captcha'));
        $lsInputCaptcha = trim(strtolower($this->_request->getPost('input_captcha')));

		$laCaptcha = array('id' => $lsIdCaptcha, 'input' => $lsInputCaptcha);

	}



	/**
	 * Para que el alumno actualice sus datos
	 *
	 */

	function indexupAction()
	{

		Zend_Auth::getInstance()->clearIdentity();
	    $this->view->action='/auth/login/';

		$arrMjs2[1]='Por favor proporcione su n&uacute;mero de cuenta y el folio.';
		$arrMjs2[]='Por favor proporcione el N&uacute;mero de Cuenta correctamente.';
		$arrMjs2[]='Por favor proporcione el folio correctamente.';
		$arrMjs2[]='No existen registros para el n&uacute;mero de cuenta.';
		$arrMjs2[]	='La informaci&oacute;n ha sido capturada. <br/>Para seguimiento de la solicitud ingrese al Paso 3 AVANCE';

		$intMsjstp2 = (int)$this->_request->getParam('nomjs2',0);

		$this->view->mjstp2=$arrMjs2[$intMsjstp2];

		Zend_Auth::getInstance()->clearIdentity();
	}

	function loginupAction()
	{

		if ($this->_request->isPost()) {

		    // collect the data from the user
		    Zend_Loader::loadClass('Zend_Filter_StripTags');

		    $f = new Zend_Filter_StripTags();

		   $num_cuenta = (int)trim($f->filter($this->_request->getPost('nocuenta2')));
		   $folio =trim($f->filter($this->_request->getPost('folio')));


		   Zend_Loader::loadClass('Grado');
			$objGrado=new Grado();

			$objFecthGrado=$objGrado->fetchAll('num_cuenta='.$num_cuenta .' AND folio='.$folio);
			$objCurrentGrado=$objFecthGrado->current();

		    if (empty($num_cuenta) && empty($folio)) {
		    	$this->_redirect('/auth/indexup/nomjs2/1');
		    }
		    elseif (empty($num_cuenta)){
		    	$this->_redirect('/auth/indexup/nomjs2/2');
		    }
		    elseif (empty($folio) || is_numeric($folio)!=true)
		    {
		    	$this->_redirect('/auth/indexup/nomjs2/3');
		    }
		    elseif ($objCurrentGrado->fecha_captura_alumno!='') {
				$this->_redirect('/auth/indexup/nomjs2/5');
		    }
		    else {

			        // setup Zend_Auth adapter for a database table
		        Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
		        $dbAdapter = Zend_Registry::get('db');

		        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

		        $authAdapter->setTableName('cert_grado');

		        $authAdapter->setIdentityColumn('folio');
		        $authAdapter->setCredentialColumn('plan');

		        // Set the input credential values to authenticate against
		        $authAdapter->setIdentity($folio);
		        $authAdapter->setCredential((!empty($objCurrentGrado->plan))?$objCurrentGrado->plan:0);

		        // do the authentication
		        $auth = Zend_Auth::getInstance();

		        $result = $auth->authenticate($authAdapter);

		        if ($result->isValid()){
		            // success: store database row to auth's storage
		            // system. (Not the password though!)
		            $data = $authAdapter->getResultRowObject(null,'oid');
		            $auth->getStorage()->write($data);
		            $this->_redirect('/solicitud/consultarup/');
		        } else {
		            // failure: clear database row from session
						$this->_redirect('/auth/indexup/nomjs2/4');
		        }
		    }
		}

			$this->_redirect('/');
	}

	/**
	 *
	 * Avances del alumno
	 *
	 */

	function indexavAction()
	{
	    $this->view->action='auth/loginav/';

	    $arrMjs1[1]='No existen registros para el n&uacute;mero de cuenta.';
		$arrMjs1[]='Por favor proporcione el N&uacute;mero de Cuenta.';
		$arrMjs1[]='Por favor proporcione los datos de imagen correctamente.';
		$arrMjs1[]='Por favor proporcione el N&uacute;mero de Cuenta.';

		$arrMjs2[1]='Por favor proporcione su n&uacute;mero de cuenta y el folio.';
		$arrMjs2[]='Por favor proporcione el N&uacute;mero de Cuenta correctamente';
		$arrMjs2[]='Por favor proporcione el folio correctamente.';
		$arrMjs2[]='No existen registros para el n&uacute;mero de cuenta.';

		$intMsjstp1 = (int)$this->_request->getParam('nomjs',0);
		$intMsjstp2 = (int)$this->_request->getParam('nomjs2',0);

		$this->view->mjstp1=$arrMjs1[$intMsjstp1];
		$this->view->mjstp2=$arrMjs2[$intMsjstp2];
	    $this->render();

	    Zend_Auth::getInstance()->clearIdentity();
	}

	/**
	 *
	 * Para el administrador
	 *
	 */

	function indexadmonAction()
	{
	    $this->view->action='auth/loginadmon/';

		$arrMjs1[1]='Por favor proporcione el nombre de usuario y contrase&ntilde;a.';
		$arrMjs1[]='Por favor proporcione el Usuario.';
		$arrMjs1[]='Por favor proporcione la contrase&ntilde;a.';
		$arrMjs1[]='No existen registros para el usuario.';
		$arrMjs1[]='Los datos proporcionados no son correctos.';

		$intMsjstp1 = (int)$this->_request->getParam('nomjs',0);
		$intMsjstp2 = (int)$this->_request->getParam('nomjs2',0);

		$this->view->mjstp1=$arrMjs1[$intMsjstp1];
	}

	function loginadmonAction()
	{
	    $this->view->message = '';

	    if ($this->_request->isPost()) {

	        // collect the data from the user
	        Zend_Loader::loadClass('Zend_Filter_StripTags');
			// Use 'someNamespace' instead of 'Zend_Auth'
			//require_once 'Zend/Auth/Storage/Session.php';


	        $f = new Zend_Filter_StripTags();

	        $login = $f->filter($this->_request->getPost('nlogin'));
	        $password = $f->filter($this->_request->getPost('password'));

	        if (empty($login) && empty($password)) {
	        	 $this->view->message = 'Por favor proporcione el nombre de usuario y contrase&ntilde;a.';
	        	 $this->_redirect('/auth/indexadmon/nomjs/1');
	        }

	        elseif (empty($login)){
	             $this->view->message = 'Por favor proporcione el nombre de usuario.';
	             $this->_redirect('/auth/indexadmon/nomjs/2');
	        }
	        elseif (empty($password))
	        {
	          $this->view->message = 'Por favor proporcione la contrase&ntilde;a.';
	          $this->_redirect('/auth/indexadmon/nomjs/3');
	        }
	        else {

	            // setup Zend_Auth adapter for a database table
	            Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
	            $dbAdapter = Zend_Registry::get('db');

	            $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

	            $authAdapter->setTableName('cert_users');
	            $authAdapter->setIdentityColumn('username');
	            $authAdapter->setCredentialColumn('password');

	            // Set the input credential values to authenticate against
	            $authAdapter->setIdentity($login);
	            $authAdapter->setCredential($password);

	            // do the authentication
	            $auth = Zend_Auth::getInstance();
				//$auth->setStorage(new Zend_Auth_Storage_Session('NamespaceAdmon'));

	            $result = $auth->authenticate($authAdapter);

	            if ($result->isValid()) {
	                // success: store database row to auth's storage
	                // system. (Not the password though!)
	                $data = $authAdapter->getResultRowObject(null,'password');
	                $auth->getStorage()->write($data);
	                $this->_redirect('/tramite');
	            } else {
	                // failure: clear database row from session
	                $this->_redirect('/auth/indexadmon/nomjs/5');
	            }
	        }
	    }

	    $this->_redirect('/auth/indexadmon/nomjs/4');
	}

	function loginavAction()
	{

	if ($this->_request->isPost()) {

	    // collect the data from the user
	    Zend_Loader::loadClass('Zend_Filter_StripTags');

	    $f = new Zend_Filter_StripTags();

	   $num_cuenta = (int)trim($f->filter($this->_request->getPost('nocuenta2')));
	   $folio =trim($this->_request->getPost('folio'));

	    if (empty($num_cuenta) && empty($folio)) {
	    	$this->_redirect('/auth/indexav/nomjs2/1');
	    }
	    elseif (empty($num_cuenta)){
	    	$this->_redirect('/auth/indexav/nomjs2/2');
	    }
	    elseif (empty($folio) || is_numeric($folio)!=true)
	    {
	    	$this->_redirect('/auth/indexav/nomjs2/3');
	    }
	    else {

		        // setup Zend_Auth adapter for a database table
	        Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
	        $dbAdapter = Zend_Registry::get('db');

	        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

	        Zend_Loader::loadClass('Grado');
			$objGrado=new Grado();


		 	$objFecthGrado=$objGrado->fetchAll('num_cuenta='.(int)$num_cuenta .' AND folio='.$folio);
			$objCurrentGrado=$objFecthGrado->current();

	        $authAdapter->setTableName('cert_grado');

	        $authAdapter->setIdentityColumn('folio');
	        $authAdapter->setCredentialColumn('plan');

	        // Set the input credential values to authenticate against
	         $authAdapter->setIdentityColumn('folio');
		        $authAdapter->setCredentialColumn('plan');

		        // Set the input credential values to authenticate against
		        $authAdapter->setIdentity($folio);
		        $authAdapter->setCredential((!empty($objCurrentGrado->plan))?$objCurrentGrado->plan:0);

		        // do the authentication
		        $auth = Zend_Auth::getInstance();

		        $result = $auth->authenticate($authAdapter);

		        if ($result->isValid()){
		            // success: store database row to auth's storage
		            // system. (Not the password though!)
		            $data = $authAdapter->getResultRowObject(null,'oid');
		            $auth->getStorage()->write($data);
	            $this->_redirect('/solicitud/avance/');
	        } else {
	            // failure: clear database row from session
					$this->_redirect('/auth/indexav/nomjs2/4');
	        }
	    }
	}
		$this->_redirect('/');
	}

	function logoutAction()
	{
	    Zend_Auth::getInstance()->clearIdentity();
	    $this->_redirect('/auth/index');
	}

	function logoutAdminAction()
	{
	    Zend_Auth::getInstance()->clearIdentity();
	    $this->_redirect('/auth/indexadmon');
	}


	private function generateCaptcha($psFontPath, $psImgPath)
    {


	  require_once('Zend/Captcha/Image.php');
      $captcha = new Zend_Captcha_Image();

      $captcha->setTimeout('30')
    	      ->setWordLen('5')
    	      ->setHeight('80')
    	      ->setFont($psFontPath)
    	      ->setFontSize('30px')
    	      ->setImgDir($psImgPath);

      $captcha->generate();    //genera session y crea imagen

      return $captcha->getId();   //devuelve el ID

    }

    private function validateCaptcha($captcha)
    {
		 $captchaId = $captcha['id'];
		 $captchaInput = $captcha['input'];


		 $strNameScapce='Zend_Form_Captcha_'.$captchaId;


		//$captchaSession = new Zend_Session_Namespace($strNameScapce);
		//$captchaIterator = $captchaSession->getIterator();

		//print_r($_SESSION[$strNameScapce]['word']);

		//echo "$captchaInput != $captchaWord;<br>";


		if( ($captchaInput != $_SESSION[$strNameScapce]['word'])  || empty($captchaInput)){
			return false;
		} else {
			return true;
		}
    }

}
