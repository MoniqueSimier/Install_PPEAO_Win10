<?php 
// definit a quelle section appartient la page
$section="gerer";
$subsection="export";
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';

$zone=2	; // zone gerer (voir table admin_zones) changement JME 03 2016
?>


<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php 
		// les balises head communes  toutes les pages
		include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	?>
	<script src="/js/ajaxExport.js" type="text/javascript" charset="iso-8859-15"></script>
	<title>ppeao::exporter param&eacute;trage et données</title>

</head>

<body>


<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
?>

<div id="main_container" class="home">

<?php
	if (isset($_SESSION['s_ppeao_user_id'])){ 
		$userID = $_SESSION['s_ppeao_user_id'];
	} else {
		$userID=null;
	}
	$OSType="";
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		$OSType="WIN";
	} else {
		$OSType="OTHER";
	}
	// on teste à quelle zone l'utilisateur a accès
	if (userHasAccess($userID,$zone)) {


		$_SESSION['s_status_export'] = 'ko';

?>
		<h2 style="padding-left:200px">Export</h2>
		<p style="padding-top:15px">Cette section permet de pr&eacute;parer et de r&eacute;aliser l'exportation des donn&eacute;es vers un PC.</p>

		<p>Trois types d'export sont prévues : export d'une structure pour la saisie de nouvelles données; export de toute l'information concernant un pays; export de toute la base bdppeao (à éviter).</p>


		  <h5 style="padding-top:35px">Op&eacute;rations pour la mise &agrave; jour de la base de r&eacute;f&eacute;rence bdppeao sur le PC</h5>
            <ul>
			<?php if ($OSType=="WIN") { ?>
	             <li><a href="javascript:doExport('videbdppeaoPC','exportOutputVide','Base cible en cours de nettoyage ...');">Vider la base de r&eacute;f&eacute;rence bdppeao du PC pour pr&eacute;parer sa mise &agrave; jour</a>
              <div id="exportOutputVide"></div>
              </li>
              <li><a href="javascript:doExport('majbdppeaoPC','exportOutputInt','Base cible en cours de mise a jour...');">Lancer l'int&eacute;gration des donn&eacute;es de r&eacute;f&eacute;rence dans la base bdppeao sur le PC depuis le fichier /work/export/SQL-bdppeao/bdppeao_a_importer.sql</a>
              <div id="exportOutputInt"></div>
              </li>
            </ul>
            <h5>Export des donn&eacute;es en format ACCESS</h5>
  Pour préparer une structure de saisie de nouvelles données :<a href="export_process.php" target="_blank"> exporter les donn&eacute;es en format ACCESS</a>.
			<?php } else { 
			// Dans le cas de linux, on ne peut faire que l'export de la base de référence ?>
              <li><a href="javascript:doExportSelect('exportBaseRef');">Exporter la base de r&eacute;f&eacute;rence apr&egrave;s s&eacute;lection des donn&eacute;es &agrave; extraire pour pr&eacute;parer la mise &agrave; jour de la base de r&eacute;f&eacute;rence sur le PC</a>
              <div id="exportSelComp"></div>
              </li>			
			
            <?php } ?>
		<br/>
        <br/>
        <br/>
        
<?php



// note : on termine la boucle testant si l'utilisateur a accès à la page demandée

} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
else {userAccessDenied($zone);}
?>
</div> <!-- end div id="main_container"-->

<?php 
include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';

?>
</body>
</html>
