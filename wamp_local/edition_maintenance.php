<?php 
// Créé par Olivier ROUX, 02-08-2008
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
// definit a quelle section appartient la page
$section="gerer";
$subsection="maintenance";

$zone=2; // zone gerer (voir table admin_zones) changement JME 03 2016

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
<?php 
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
?>
	<title>ppeao::g&eacute;rer::maintenance de la base</title>
	
<script src="/js/edition.js" type="text/javascript"  charset="iso-8859-15"></script>

</head>

<body>

<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
?>

<div id="main_container" class="home">
<!-- édition des tables de référence -->
<?php

// on teste à quelle zone l'utilisateur a accès
if (userHasAccess($_SESSION['s_ppeao_user_id'],$zone)) {
// affiche un avertissement concernant l'utilisation de IE pour les outils d'administration
IEwarning();
?>


<?php

include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';

?>

<h2 style="text-align:center">Opérations de maintenance de la base : "<?php echo($base_principale) ?>"</h2>

<ul>
	<li><h5 style="margin : 12px 0 6px 0"><a href="javascript:doMaintenance('sequences_ref_param');">Mise &agrave; jour des s&eacute;quences des tables de r&eacute;f&eacute;rence et de param&eacute;trage</a></h5>
	<p>Utilisez cet outil apr&egrave;s avoir import&eacute; en batch des donn&eacute;es de r&eacute;f&eacute;rence et de param&eacute;trage, pour mettre &agrave; jour la prochaine valeur de la s&eacute;quence des tables ayant des identifiants uniques g&eacute;n&eacute;r&eacute;s automatiquement.</p>
	</li>
	<li><h5 style="margin : 6px 0 6px 0"><a href="javascript:doMaintenance('sequences_donnees');">Mise &agrave; jour des s&eacute;quences des tables de donn&eacute;es</a></h5>
	<p>Utilisez cet outil apr&egrave;s avoir import&eacute; en batch des donn&eacute;es de p&ecirc;che artisanale ou exp&eacute;rimentale, pour mettre &agrave; jour la prochaine valeur de la s&eacute;quence des tables ayant des identifiants uniques g&eacute;n&eacute;r&eacute;s automatiquement.</p>
	</li>
	<li><h5 style="margin : 6px 0 6px 0"><a href="javascript:doMaintenance('vacuum');">VACUUM</a></h5>
	<p>Utilisez cet outil apr&egrave;s avoir effectu&eacute; un grand nombre de suppressions dans la base de donn&eacute;es.</p>
	</li>
	<li><h5 style="margin : 6px 0 6px 0"><a href="javascript:doMaintenance('reindex');">REINDEX</a></h5>
	<p>Utilisez cet outil pour recr&eacute;er les index apr&egrave;s avoir effectu&eacute; un grand nombre d&#x27;insertions ou de suppressions dans la base de donn&eacute;es.</p>
	</li></ul>
	<h2 style="text-align:center">Opérations de maintenance de la base : "bdpeche"</h2>
	<ul>
	<li><h5 style="margin : 12px 0 6px 0"><a href="javascript:doMaintenance('disable_trigger');">D&eacute;sactiver des contraintes sur bdpeche</a></h5>
	<p>Utilisez cet outil pour supprimer temporairement les contraintes sur la base temporaire bdpeche.</p>
	</li>
	<li><h5 style="margin : 6px 0 6px 0"><a href="javascript:doMaintenance('enable_trigger');">Activer les contraintes sur bdpeche</a></h5>
	<p>Utilisez cet outil pour r&eacute;activer les contraintes sur la base temporaire bdpeche.</p>
	</li>	
    <li><h5 style="margin : 6px 0 6px 0"><a href="javascript:doMaintenance('empty_bdpeche');">Vider la base bdpeche</a></h5>
    <p>Utilisez cet outil pour vider la base temporaire bdpeche en supprimant les contraintes. </p>
    </li>
    <?php	if (!(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')) {?>
        <li>
        <h5 style="margin : 6px 0 6px 0"><a href="javascript:doMaintenance('empty_ACCESS');">Vider les bases PostgreSQL au format ACCESS</a></h5>
        <p>Utilisez cet outil pour vider les bases exp2003_bdd, pechart et les bases pays situ&eacute;es sur le serveur.</p>
        </li>	
     <?php } ?>
</ul>	

<div id="maintenance_output"></div>

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
