<?php 
// Créé par Olivier ROUX, 02-08-2008
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
// definit a quelle section appartient la page
$section="gerer";
$subsection="documentation";

$zone=2; // zone edition (voir table admin_zones)

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
<?php 
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
?>
	<title>ppeao::g&eacute;rer::documentation</title>
	
<script src="/js/edition.js" type="text/javascript"  charset="iso-8859-15"></script>
<script src="/ckfinder/ckfinder.js" type="text/javascript"></script>

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
<h2 style="padding-left:200px">G&eacute;rer la documentation sur les données</h2>
<!-- édition des tables de référence -->

<?php

include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';

?>

<script type="text/javascript" charset="utf-8">
	function BrowseFiles()
				{
						
					// You can use the "CKFinder" class to render CKFinder in a page:
					var finder = new CKFinder() ;
					finder.removePlugins = 'basket';
					finder.basePath = '/ckfinder/' ;
					finder.width=700;
					finder.height=350;
					finder.popup() ;
				}
</script>

<p style="padding-top:20px">Cette page vous permet de g&eacute;rer les documents (textes, figures et cartes) associ&eacute;s &agrave; des unit&eacute;s g&eacute;ographiques.</p>
<p>Ces documents pourront &ecirc;tre t&eacute;l&eacute;charg&eacute;s par les utilisateurs du module de consultation/extraction des donn&eacute;es.</p>
<h5>associer des documents à :</h5>
<ul>
	<li><a href="/edition/edition_table.php?selector=no&editTable=meta_pays">des pays</a></li>
	<li><a href="/edition/edition_table.php?selector=no&editTable=meta_systemes">des syst&egrave;mes</a></li>
	<li><a href="/edition/edition_table.php?selector=no&editTable=meta_secteurs">des secteurs</a></li>
</ul>
<h5><a href="javascript:BrowseFiles();">acc&eacute;der au gestionnaire de fichiers</a></h5>
<p>permet de g&eacute;rer les fichiers pr&eacute;sents sur le serveur, comme avec l&#x27;explorateur de Windows.</p>





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
