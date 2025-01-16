<?php 
// Mis à jour par Olivier ROUX, 29-07-2008
// definit a quelle section appartient la page
$section="gerer";
$subsection="";
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';

$zone=2; // zone gerer (voir table admin_zones) changement JME 03 2016
?>


<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
	<?php 
		// les balises head communes  toutes les pages
		include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	?>
	<title>ppeao::g&eacute;rer</title>

	
</head>

<body>


<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
?>

<div id="main_container" class="home">
	<h2 style="text-align:center">Gestion des bases de données de PPEAO</h2>

<?php

// on teste à quelle zone l'utilisateur a accès

if (userHasAccess($_SESSION['s_ppeao_user_id'],$zone)) {
// affiche un avertissement concernant l'utilisation de IE pour les outils d'administration
IEwarning();
?>
<h5 style="padding-top:20px"><a href="/portage.php" title="portage">portage d'une base</a></h5>
<p style="padding-left:35px">permet d&#x27;exporter des donn&eacute;es vers bdpeche et de transf&eacute;rer les donn&eacute;es depuis la base temporaire bdpeche vers la base principale bdppeao.</p>
<h5><a href="/edition_maintenance.php" title="maintenance">maintenance de la base</a></h5>
<p style="padding-left:35px">permet d&#x27;effectuer des op&eacute;rations de maintenance sur les bases bdpeche et bdppeao (mise &agrave; jour des s&eacute;quences, VACUUM...).</p>
<h5><a href="/edition_donnees.php" title="gestion des donn&eacute;es">gestion des donn&eacute;es</a></h5>
<p style="padding-left:35px">permet de modifier, supprimer ou ajouter des valeurs dans les tables de donn&eacute;es.</p>
<h5><a href="/edition_reference.php" title="gestion des tables de r&eacute;f&eacute;rence">gestion des tables de référence</a></h5>
<p style="padding-left:35px">permet de modifier, supprimer ou ajouter des valeurs dans les tables de r&eacute;f&eacute;rence.</p>
<h5><a href="/edition_param.php" title="gestion des tables de param&eacute;trage">gestion des tables de param&eacute;trage</a></h5>
<p style="padding-left:35px">permet de modifier, supprimer ou ajouter des valeurs dans les tables de param&eacute;trage.</p>
<h5><a href="/edition_admin.php" title="gestion des tables d&#x27;administration">gestion des tables d&#x27;administration</a></h5>
<p style="padding-left:35px">permet de modifier, supprimer ou ajouter des valeurs dans les tables d&#x27;administration.</p>
<h5><a href="/gestion_doc.php" title="gestion de la documentation">gestion de la documentation</a></h5>
<p style="padding-left:35px">permet de g&eacute;rer la documentation sur l'application PPEAO et les donn&eacute;es archivées.</p>
<h5><a href="/journal.php" title="journal des op&eacute;rations">journal des op&eacute;rations</a></h5>
<p style="padding-left:35px">permet de consulter le journal enregistrant l&#x27;ensemble des op&eacute;rations r&eacute;alis&eacute;es sur le site&nbsp;: connexions, interventions sur les donn&eacute;es, messages d&#x27;erreur...</p>
<?php 
// note : on termine la boucle testant si l'utilisateur a accès à la page demandée

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès

// suppresion du Message d'erreur à ce niveau; les ME sont gérer par sous section JME 03 2016

//else {userAccessDenied($zone);}
?>
</div> <!-- end div id="main_container"-->

<?php 
include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';

?>
</body>
</html>
