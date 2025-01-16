<?php 
// Mis à jour par Olivier ROUX, 29-07-2008
// definit a quelle section appartient la page
$section="gerer";
$subsection="portage";
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
$zone=2; // zone portage (voir table admin_zones) chamgement JME 03 2016
?>

<?php 
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/config.php';
//Include for documentation
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php 
			// les balises head communes  toutes les pages
			include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
		?>
		<title>ppeao::portage automatique</title>
		<script src="/js/ajaxProcessAuto.js" type="text/javascript" charset="iso-8859-15"></script>
		<script src="/js/document.js" type="text/javascript" charset="iso-8859-15"></script>
		<?php //<script src="/js/portageAutoCM.js" type="text/javascript" charset="iso-8859-15"> ?>
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
			
			// on teste à quelle zone l'utilisateur a accès
			if (userHasAccess($userID,$zone)) {
			$_SESSION['s_cpt_champ_total'] = 0;	// Lecture d'une table, nombre d'enregistrements lus total
			$_SESSION['s_cpt_champ_diff'] = 0;	// Lecture d'une table, nombre d'enregistrements différents
			$_SESSION['s_cpt_champ_vide'] = 0;	// Lecture d'une table, nombre d'enregistrements vide
			$_SESSION['s_cpt_table_total'] = 0;	// Nombre global de tables lues
			$_SESSION['s_cpt_table_diff'] = 0;	// Nombre global de tables différentes entre reference et cible
			$_SESSION['s_cpt_table_egal'] = 0;	// Nombre global de tables identiques entre reference et cible
			$_SESSION['s_cpt_table_vide'] = 0;	// Nombre global de tables vides dans cible 
			$_SESSION['s_cpt_table_source_vide'] = 0;	// Nombre global de tables vides dans source 
			$_SESSION['s_cpt_table_manquant'] = 0;	// Nombre global de tables avec des enreg manquants dans cible 
			$_SESSION['s_num_encours_fichier_SQL'] = 1; // Numero du fichier SQL en cours
			$_SESSION['s_cpt_lignes_fic_sql'] = 0;		// Nombre de lignes dans le fichier SQL en cours
			$_SESSION['s_cpt_table_diff_manquant'] = 0;
			$_SESSION['s_erreur_process'] = false;
			$_SESSION['s_cpt_erreurs_sql'] = 0;
			$_SESSION['s_CR_processAuto'] = "";
			$_SESSION['s_AllScriptSQL'] = "";
			$_SESSION['s_max_envir_Id_Source'] = 0;
			$_SESSION['s_cpt_maj'] 	= 0; 
			$_SESSION['s_max_Id_Source'] = 0;
			$_SESSION['s_status_restauration'] = "no";
			
			// Pour test: les 3 premières etapes et la purge sont obligatoires.
			// On ajoute une variable qui permet de les rendre globalement obligatoire
			$boutDisabled = "disabled=\"disabled\""; // desactivés
			//$boutDisabled = ""; // activés
		
		?>

			<!--<div id="BDDetail">
				<?php  //$subsection="auto"; include $_SERVER["DOCUMENT_ROOT"].'/left_navbar.inc'; ?>
			</div>-->
			<div id="subContent">
				<h2 style="padding-left:120px">Import / recalcul automatique de données</h2>
				<h2 style="padding-left:200px">Portage Automatique</h2>
				<br/>
				<p>Ce processus permet un portage automatique des bases issues des bases Access dans la base principale de l'application PPEAO.</p>
				<p>Les diff&eacute;rents traitements sont d&eacute;taill&eacute;s ci-dessous. Vous pouvez choisir de lancer tout ou partie des traitements.<br/>
				Une erreur sur un traitement entra&icirc;ne l'arr&ecirc;t de l'ensemble du processus.<br/>
				Une base de sauvegarde de bdpeche comme de bdppeao est cr&eacute;&eacute;e en d&eacute;but de traitement. Elle peut &ecirc;tre utilis&eacute;e pour restaurer la base de r&eacute;f&eacute;rence en cas de probl&egrave;me. <br/>
				Un fichier de log compl&eacute;mentaire permet d'avoir l'ensemble des avertissements ou informations sur le traitement. </p>
				<br/>
				<?php // get the help/documentation for this page 
				//getDocumentation("portage_auto","icone","n","");
				?>
				<br/>
				<?php 
					logWriteTo(4,"notice","*** Ouverture page portage automatique","","","0");
					// Récupération du nombre d'enquête à traiter pour la recomposition auto
					$query = "select count(art_debarquement.id) FROM art_debarquement";
					$result = pg_query($connectPPEAO, $query);
					if (!$result) {  echo "Une erreur s'est produite";  exit;}
					// Recuperation du resultat
					$row= pg_fetch_row($result);
					$nb_enr = $row[0];
				?>
				<div id="runProcess">
				<form id="formProcessAuto">
					<!--Vous pouvez saisir une adresse mail pour recevoir le compte-rendu de traitement de recomposition automatique.
					<input type="text" name="adresse" id="adresse"/>-->
					<input type="hidden" name="adresse" id="adresse" value=""/>
					<br/>

					<input id="startProcess" type="button" value="Lancer le traitement" onClick="runProcess()"/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;G&eacute;n&eacute;rer un fichier de log compl&eacute;mentaire <input type="checkbox" name="logsupp" id="logsupp" checked="checked"/><br/>
					<?php  // Input pour recomposition automatique ?>
					<input type="hidden" id="BDName" value="<?php  echo "$bdd"; ?>">
					<input type="hidden" id="NBEnr" value="<?php   echo "$nb_enr";?>" >
				</form><br/>
				</div>
				<div id="titleProcess">D&eacute;tail des process.</div>
				<br/>
				<div id="sauvegarde">
					<div id="sauvegarde_img"><img src="/assets/incomplete.png" alt=""/></div>
					<div id="sauvegarde_txt">Sauvegarde.</div>
					<div id="sauvegarde_chk">Lancer sauvegarde.&nbsp;<input type="checkbox" id="svgcheck" checked="checked" <?php echo $boutDisabled;?>/></div>
					<?php 	$navbarLevel = 1;
							$texteDiv = "Compte-rendu de sauvegarde.";	
							include $_SERVER["DOCUMENT_ROOT"].'/process_auto/navbarCR.inc'; ?>
				</div>

				<div id="comparaison">
					<div id="comparaison_img"><img src="/assets/incomplete.png" alt=""/></div>
					<div id="comparaison_txt">Comparaison r&eacute;f&eacute;rentiel et param&eacute;trage.</div>
					<div id="comparaison_chk">Lancer comp. param / ref.&nbsp;<input type="checkbox" id="compcheck" checked="checked" <?php echo $boutDisabled;?>/></div>
					<?php 	$navbarLevel = 2;
							$texteDiv = "Compte-rendu de comparaison r&eacute;f&eacute;rentiel et param&eacute;trage.";	
							include $_SERVER["DOCUMENT_ROOT"].'/process_auto/navbarCR.inc'; ?>
				</div>

				<div id="comparaisonInv">
					<div id="comparaisonInv_img"><img src="/assets/incomplete.png" alt=""/></div>
					<div id="comparaisonInv_txt">Comparaison du param&eacute;trage p&ecirc;ches artisanales avec la base de r&eacute;f&eacute;rence.</div>
					<div id="comparaisonInv_chk">Lancer comp. param. art.&nbsp;<input type="checkbox" id="compinvcheck" checked="checked" <?php echo $boutDisabled;?>/></div>
					<?php 	$navbarLevel = 3;
							$texteDiv = "Compte-rendu de comparaison referentiel et parametrage.";	
							include $_SERVER["DOCUMENT_ROOT"].'/process_auto/navbarCR.inc'; ?>
	
				</div>

				<div id="copieScientifique">
					<div id="copieScientifique_img"><img src="/assets/incomplete.png" alt=""/></div>
					<div id="copieScientifique_txt">Copie des p&ecirc;ches scientifiques.</div>
					<div id="copieScientifique_chk">Lancer copie p&ecirc;ches scient.&nbsp;<input type="checkbox" id="majsccheck" checked="checked" /></div>
					<?php 	$navbarLevel = 4;
							$texteDiv = "Compte-rendu de copie des peches scientifiques.";	
							include $_SERVER["DOCUMENT_ROOT"].'/process_auto/navbarCR.inc'; ?>

				</div>

				<div id="processAutoRec">
					<div id="processAutoRec_img"><img src="/assets/incomplete.png" alt=""/></div>
					<div id="processAutoRec_txt">Process recomposition donn&eacute;es.</div>
					<div id="processAutoRec_chk">Lancer recomp. donn&eacute;es.&nbsp;<input type="checkbox" id="reccheck" checked="checked"/></div>
					<?php 	$navbarLevel = 5;
						$texteDiv = "Compte-rendu de recomposition donnees.";	
						include $_SERVER["DOCUMENT_ROOT"].'/process_auto/navbarCR.inc'; ?>				
				</div>

				<div id="processAutoStat">
					<div id="processAutoStat_img"><img src="/assets/incomplete.png" alt=""/></div>
					<div id="processAutoStat_txt">Process calcul statistiques.</div>
					<div id="processAutoStat_chk">Lancer calcul stat.&nbsp;<input type="checkbox" id="statcheck" checked="checked"/></div>
					<?php 	$navbarLevel = 6;
						$texteDiv = "Compte-rendu du calcul statistiques.";	
						include $_SERVER["DOCUMENT_ROOT"].'/process_auto/navbarCR.inc'; ?>
				</div>

				<div id="copieRecomp">
					<div id="copieRecomp_img"><img src="/assets/incomplete.png" alt=""/></div>
					<div id="copieRecomp_txt">Copie des donn&eacute;es p&ecirc;ches artisanales.</div>
					<div id="copieRecomp_chk">Lancer copie p&ecirc;ches art.<input type="checkbox" id="majreccheck" checked="checked"/></div>
					<?php 	$navbarLevel = 7;
						$texteDiv = "Compte-rendu de la copie des p&ecirc;ches artisanales.";	
						include $_SERVER["DOCUMENT_ROOT"].'/process_auto/navbarCR.inc'; ?>
				</div>
				<div id="portageOK"><div id="portageOK_img"><img src="/assets/incomplete.png" alt=""/></div><div id="portageOK_txt">Statut du portage automatique.</div></div>
				<div id="purge"><div id="purge_img"><img src="/assets/incomplete.png" alt=""/></div>
				<div id="purge_txt">Purge des donn&eacute;es.</div>
				<div id="purge_chk">Lancer purge donn&eacute;es.&nbsp;<input type="checkbox" id="purgecheck" checked="checked" <?php echo $boutDisabled;?>/></div>
									
				<?php 	$navbarLevel = 8;
						$texteDiv = "Compte-rendu de la purge des donnees.";	
						include $_SERVER["DOCUMENT_ROOT"].'/process_auto/navbarCR.inc'; ?>
				</div>
				<?php // Un formulaire bidon pour renvoyer l'etat du traitement au javascript*/ ?>
				<form id="statusProcess">
					<input id="valStatusProc" value="ok" type="hidden"/>
				</form>
			</div>



<?php 
// note : on termine la boucle testant si l'utilisateur a accès à la page demandée

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
else {userAccessDenied($zone);}

?>
<?php 
include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';

?>	
	</div>	<!-- end div id="main_container"-->
	</body>
</html>
