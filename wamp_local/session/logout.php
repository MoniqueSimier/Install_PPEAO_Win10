<?php 
//************************************************
//script appelé via Ajax par la fonction JS ajaxLogout() et qui déconnecte un utilisateur
//************************************************
// includes the file containing the db connection script
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
// inclut le fichier contenant le fonctions php communes
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';



// calling session
session_start();

// on stocke les infos sur l'utilisateur avant de détruire la session, pour pouvoir les noter dans le journal
$longName=$_SESSION['s_ppeao_longname'];
$email=$_SESSION['s_ppeao_email'];
$userId=$_SESSION['s_ppeao_user_id'];

// clearing the session array
$_SESSION = array();

// terminating the session
if (@session_destroy()) {
	
	// on inscrit la déconnexion dans le journal
	$logoutMessage='d&eacute;connexion de '.$longName;
	logWriteTo(5,"notice",$logoutMessage,'','',0);
	
	
}


$response='success';
echo $response;

?>