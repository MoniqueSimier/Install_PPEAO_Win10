<?php 
// Créé par Olivier ROUX, 02-08-2008
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
// definit a quelle section appartient la page
$section="gerer";
$subsection="administration";

$zone=1; // zone edition (voir table admin_zones)

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
<?php 
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
?>
	<title>ppeao::g&eacute;rer::tables d&#x27;administration</title>
	
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
<h2 style="padding-left:200px">G&eacute;rer les tables d&#x27;administration</h2>
<!-- édition des tables de référence -->

<?php

include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';

?>



<?php
// on affiche la liste des tables
echo('<div id="tables_liste">');	
echo('<h5 style="padding-top:25px">contr&ocirc;le de l&#x27;extraction des donn&eacute;es</h5>');
echo('<ul>');
// JMEcoutin 03/2016 suppression de 2 entrées dans la fenètre Gérer (date butoir et autoriser un utilisateur)
//echo('<li><a href="/edition/edition_table.php?selector=no&editTable=acces_systemes">d&eacute;finir une date butoir particuli&egrave;re pour un syst&egrave;me</a></li>');
// 28/03/2014 F.WOEHL systeme par user >>> 
echo('<li><a href="/edition/edition_table.php?selector=no&editTable=users_systemes">d&eacute;finir une restriction syst&egrave;me par utilisateur</a></li>');
// 28/03/2014 F.WOEHL systeme par user <<<
//echo('<li><a href="/edition/edition_droits_acces.php">autoriser un utilisateur ou un groupe &agrave; acc&eacute;der &agrave; toutes les donn&eacute;es</a></li>');
echo('</ul>');
buildTableList("admin");

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
