<?php
//getTime();
set_time_limit(0);
//***********************************************
// Création Yann LAURENT, 01-07-2008
// A partir du fichier php initial du lot 2 PPEAO
//***********************************************
// YL 15/07/2008 on remplace les messages direct par une variable qu'on affiche ou non en fin de traitement
$messageProcess = "Debut programme <br/>" ;
//print "GEt ==== ".print_r($_GET);
//print "<br/>";
//echo "debut programme";
// Variables pour affichage ou non des messages
if (isset($_GET['aff'])) {
	$afficherMessage = $_GET['aff'] ;
} else {
	$afficherMessage = "0" ;
}
$nb_enr = $_GET['nb_enr'];
$bdd = $_GET['base'];
$to = $_GET['adresse'];
if($bdd==""){
	$bdd=$db_default;
}
$messageProcess .= "<br/>travail sur la base : ".$bdd ;

//CONNECTION A LA BD
$connection =pg_connect ("host=".$host." dbname=".$bdd." user=".$user." password=".$passwd);
//print "host=".$host." dbname=".$bdd." user=".$user." password=".$passwd;
if (!$connection) {  echo "Non connecté"; exit;}

include_once $_SERVER["DOCUMENT_ROOT"].'/recomposition/functions_recomposition_generique.inc.php';
include_once $_SERVER["DOCUMENT_ROOT"].'/recomposition/functions_query.inc.php';

$date = date("H:i:s");
//print_debug("bb".$date);

//CREATION DES TABLEAUX DE DONNEES
//include_once $_SERVER["DOCUMENT_ROOT"].'/recomposition/create_array_coef_esp.php';
include_once $_SERVER["DOCUMENT_ROOT"].'/recomposition/create_array_info_deb.php';
include_once $_SERVER["DOCUMENT_ROOT"].'/recomposition/create_array_tailles.php';
include_once $_SERVER["DOCUMENT_ROOT"].'/recomposition/create_array_info_non_deb.php';
include_once $_SERVER["DOCUMENT_ROOT"].'/recomposition/create_array_coef_esp.php';

//CALCUL ET AJOUT DES Wdft et Ndft POUR CHAQUE FRACTION DANS LE TABLEAU  $info_deb       
$info_deb=calcul_Wdft_Ndft_par_fraction($info_deb,$FT,$coef_esp);

$date = date("H:i:s");
//print_debug("cc".$date);
//print ($date);	
// TRAITEMENT DES FRACTIONS DEBARQUEES Et compraison des poids               //
include_once $_SERVER["DOCUMENT_ROOT"].'/recomposition/recomposition_info_deb.php';

$date = date("H:i:s");
//print_debug("dd".$date);

//  TRAITEMENT DES FRACTIONS NON DEBARQUEES                            Fndbq                               Et nex fractions                 //
include_once $_SERVER["DOCUMENT_ROOT"].'/recomposition/recomposition_info_non_deb.php';

//INSERT DATAS RECOMPOSEES
echo insert_values_recompose($info_deb,$afficherMessage,$nb_enr);

pg_close();

/*
//ENVOI MAIL
//$to = 'f@.ird.fr';
// Subject
$subject = 'Base de données'.$_GET['base'];
// Message
$msg = 'Fin du traitement de recomposition des données';
// Headers
$headers = 'From: base_PPEAO'."\r\n";
$headers .= "\r\n";
// Function mail()
mail($to, $subject, $msg, $headers);
*/

?>