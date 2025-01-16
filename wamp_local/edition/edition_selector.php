<?php 
// script affichant le selecteur qui permet de choisir la table ou les valeurs de la table à éditer
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
// definit a quelle section appartient la page
$section="gerer";
$zone=2; // zone edition (voir table admin_zones)
$targetTable=$_GET["targetTable"];
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';


$tableType=$tablesDefinitions[$targetTable]["type_table_description"];
if (!my_empty($tablesDefinitions[$targetTable]["zone"])) {$zone=$tablesDefinitions[$targetTable]["zone"];}

// on détermine à quelle subsection et à quelle zone appartient la table choisie

switch ($tablesDefinitions[$targetTable]["type_table_nom"]) {
	case 'admin':
	$subsection='administration';
	break;
	case 'param':
	$subsection='parametrage';
	break;
	case 'admin':
	$subsection='administration';
	break;
	case 'ref':
	$subsection='reference';
	break;
	case 'data':
	$subsection='donnees';
	break;
}
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
<?php 
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
?>
	<title>ppeao::g&eacute;rer::<?php echo($tablesDefinitions[$targetTable]["type_table_description"]); ?>::<?php echo($tablesDefinitions[$targetTable]["domaine_description"]); ?></title>

<script src="/js/edition.js" type="text/javascript" charset="iso-8859-15"></script>

</head>

<body>

<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';

// on teste à quelle zone l'utilisateur a accès
if (userHasAccess($_SESSION['s_ppeao_user_id'],$zone)) {
?>

<div id="main_container" class="home">

<div id="selector_container">

<?php
	// insertion du sélecteur, en mode "page de selection"
	createSelector("selection");
?>
</div> <!-- end div selector_container -->


<?php

logWriteTo(1,'notice','acc&egrave;s &agrave; la gestion des '.$tablesDefinitions[$targetTable]["type_table_description"].'&nbsp;: '.$tablesDefinitions[$targetTable]["domaine_description"],'','',0);

?>
	
</div> <!-- end div id="main_container"-->


<?php 
// note : on termine la boucle testant si l'utilisateur a accès à la page demandée

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
else {userAccessDenied($zone);}

?>

<?php 
include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';

?>
</body>
</html>
