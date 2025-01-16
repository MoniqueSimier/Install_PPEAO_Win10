<?php 
//************************************************
//script qui gère la connexion et la déconnexion des utilisateurs
//************************************************


global $zone;

session_start();
// includes the file containing the db connection script
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
// inclut le fichier contenant le fonctions php communes
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';
// inclut le fichier contenant le formulaire de login
include $_SERVER["DOCUMENT_ROOT"].'/session/login_forms.php';



if(isset($_GET) && !empty($_GET['login']) && !empty($_GET['pass'])) {
  extract($_GET);
  // on recupère le password de la table qui correspond au login du visiteur, 
  $loginSql = "	SELECT user_id, user_password, user_longname,  user_active, user_email
			FROM admin_users where user_name LIKE '".$login."'";
  $loginReq = pg_query($loginSql) or die('Erreur  dans la requête<br>'.$loginSql.'<br>'.mysql_error());

  $loginData = pg_fetch_assoc($loginReq);

// il faut encrypter la valeur $_POST['pass'] pour la comparer avec le mot de passe encrypté stocké dans la bdd

	// on utilise les deux premiers caractères du login comme "salt"
	$salt=substr($login,0,2);
	//on encrypte le mot de passe soumis
	$challenge_password=crypt($pass,$salt);
	
  // si le log/pass n'est pas le bon
  if($loginData['user_password'] != $challenge_password) {
    $_SESSION['s_ppeao_login_status']='bad';
  }
	// si le log/pass est le bon, on connecte l'utilisateur 
  else {
	// si son compte est toujours actif
	if ($loginData['user_active']==true) {
    session_start();
	
	// on inscrit un certain nombre d'informations sur l'utilisateur dans la session
	$_SESSION['s_ppeao_user_id'] = $loginData['user_id'];    
	$_SESSION['s_ppeao_name'] = $login;
	$_SESSION['s_ppeao_email'] = $loginData["user_email"];
	$_SESSION['s_ppeao_longname'] = $loginData["user_longname"];
    $_SESSION['s_ppeao_login_status']='good';
	$_SESSION['s_ppeao_user_active']=$loginData['user_active'];
	// initialisation des variables pour le process auto // YL 02-09-08
	$_SESSION['s_cpt_champ_total'] = 0;			// Lecture d'une table, nombre d'enregistrements lus total
	$_SESSION['s_cpt_champ_diff'] = 0;			// Lecture d'une table, nombre d'enregistrements différents
	$_SESSION['s_cpt_champ_vide'] = 0;			// Lecture d'une table, nombre d'enregistrements vide
	$_SESSION['s_cpt_table_total'] = 0;			// Nombre global de tables lues
	$_SESSION['s_cpt_table_diff'] = 0;			// Nombre global de tables différentes entre reference et cible
	$_SESSION['s_cpt_table_diff_manquant']= 0; // Nombre global de tables avec des enreg differents et manquants dans cible
	$_SESSION['s_cpt_table_egal'] = 0;			// Nombre global de tables identiques entre reference et cible
	$_SESSION['s_cpt_table_vide'] = 0;			// Nombre global de tables vides dans cible 
	$_SESSION['s_cpt_table_source_vide'] = 0;	// Nombre global de tables vides dans source 
	$_SESSION['s_cpt_table_manquant'] = 0;		// Nombre global de tables avec des enreg manquants dans cible 
	$_SESSION['s_num_encours_fichier_SQL'] = 1; // Numero du fichier SQL en cours
	$_SESSION['s_cpt_lignes_fic_sql'] = 0;		// Nombre de lignes dans le fichier SQL en cours
	$_SESSION['s_cpt_erreurs_sql'] = 0;			// Nombre d'erreur lors de la mise a jour de la table
	$_SESSION['s_erreur_process'] = false ; 	// Gestion des erreurs
	$_SESSION['s_CR_processAuto'] = ""; 		// Gestion du compte rendu
	$_SESSION['s_AllScriptSQL'] = "";
	$_SESSION['s_max_envir_Id_Source'] = 0;
	$_SESSION['s_cpt_maj'] 	= 0; 
	$_SESSION['s_max_Id_Source'] = 0;
	$_SESSION['s_max_Id_Source'] = 0;
	$_SESSION['s_status_restauration'] = "no";
	// Fin variables process auto
	// variable export
	$_SESSION['s_status_export'] = 'ok';
	
	// on inscrit la connexion dans le journal
	$loginMessage='connexion de '.$_SESSION['s_ppeao_longname'];
	
	$zoneName=$zoneArray['zone_name'];
	logWriteTo(5,"notice",$loginMessage,'','',0);
	
	} // end if ($data['user_active']=='true')
	
	// sinon on ne le connecte pas
	else {$_SESSION['s_ppeao_login_status']='deactivated';}
} // end else
	
} // end if(isset...)



switch ($_SESSION['s_ppeao_login_status']) {
	case 'good': $loginResponse='success';
		break;
	case 'bad': $loginResponse=showLoginForm().'<div id="login_message">Erreur de connexion, veuillez essayer &agrave; nouveau.</div>';
		break;
	case 'deactivated': $loginResponse=showLoginForm().'<div id="login_message">Votre compte a &eacute;t&eacute; d&eacute;sactiv&eacute;, veuillez contacter l\'administrateur.</div>';
		break;
	} // end switch $_SESSION['s_ppeao_login_status']
	

echo $loginResponse;

?>