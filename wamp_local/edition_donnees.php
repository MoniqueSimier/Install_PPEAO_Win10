<?php 
// Créé par Olivier ROUX, 02-08-2008
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
// definit a quelle section appartient la page
$section="gerer";
$subsection="donnees";

$zone=2; // zone edition (voir table admin_zones)

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
<?php 
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
?>
	<title>ppeao::g&eacute;rer::tables de donn&eacute;es</title>
	
<script src="/js/edition.js" type="text/javascript"  charset="iso-8859-15"></script>

</head>

<body>

<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
?>

<div id="main_container" class="edition">

<?php

// on teste à quelle zone l'utilisateur a accès
if (userHasAccess($_SESSION['s_ppeao_user_id'],$zone)) {
// affiche un avertissement concernant l'utilisation de IE pour les outils d'administration
IEwarning();
?>
<h2 style="padding-left:200px">Gérer les tables de données</h2>
<!-- édition des tables de référence -->

<?php

include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';

?>


<!-- on affiche les liens pour supprimer des campagnes ou des périodes d'enquête -->
<h5>supprimer...</h5>
<ul>
	<li><a href="/edition_supprimer.php?domaine=exp">des campagnes</a></li>
	<li><a href="/edition_supprimer.php?domaine=art">des p&eacute;riodes d&#x27;enqu&ecirc;te</a></li>
</ul>



<?php
// on affiche la liste des tables
echo('<div id="tables_liste">');
echo('<h5>&eacute;diter les donn&eacute;es des tables de...</h5>');
	buildTableList("data");


echo('</div>');

?>
	

<?php 
// note : on termine la boucle testant si l'utilisateur a accès à la page demandée

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
else {userAccessDenied($zone);}

?>
</div> <!-- end div id="main_container"-->
<?php 
include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';

?>
</body>
</html>
