<?php 
//*****************************************
// extraction.php
//*****************************************
// Created by Yann Laurent
// 2009-06-24 : creation
//*****************************************
// Ce programme lance les processus d'extraction des données
//*****************************************
// Paramètres en entrée
// aucun pour l'instant.
// Paramètres en sortie
// aucun pour l'instant.
//*****************************************

// definit a quelle section appartient la page
$section="consulter";
$subsection="";
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';

$zone=6; // zone extraction (voir table admin_zones)
$debugLog = false;
?>


<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php 
		// les balises head communes  toutes les pages
		include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	?>
	<script src="/js/ajaxExtraction.js" type="text/javascript" charset="iso-8859-15"></script>
	<title>ppeao::extraire des donn&eacute;es</title>

</head>

<body>


<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
?>

<div id="main_container" class="home">
	<h1>Extraction : test apr&egrave;s s&eacute;lection</h1>
	<br/>
	<p>Cette section permet de tester l'export des donn&eacute;es apr&egrave;s la s&eacute;lection.</p>
    <?php
	if (isset($_SESSION['s_ppeao_user_id'])){ 
		$userID = $_SESSION['s_ppeao_user_id'];
	} else {
		$userID=null;
	}

	// on teste à quelle zone l'utilisateur a accès
	if (userHasAccess($userID,$zone)) {


		$_SESSION['s_status_export'] = 'ko';
		$_SESSION['listeQualite'] = '';
		$_SESSION['listeProtocole'] = ''; // Oui / non
		$_SESSION['listePoisson'] = '' ;// Oui/non
		$_SESSION['listeEspeces'] = '';
		$_SESSION['listeCatEco'] = '';
		$_SESSION['listeCatTrop'] = '';
		$_SESSION['listeColonne'] = ''; // tableau nomTable / NomChamp des champs comple à afficher
		unset($_SESSION['listeRegroup']); // Contient les regroupements (tableau)
		// Variables de session qui contiendront la sélection de base issu du fichier XML venant de la partie I, la selection
		$_SESSION['typeSelection'] = '';
		$_SESSION['typePeche'] = '';
		$_SESSION['typeStatistiques'] = '';
		$_SESSION['SQLPays'] = '';
		$_SESSION['SQLSysteme'] = '';
		$_SESSION['SQLSecteur'] = '';
		$_SESSION['SQLAgg'] = '';
		$_SESSION['SQLEngin'] = '';
		$_SESSION['SQLGTEngin'] = '';
		$_SESSION['SQLCampagne'] = '';
		$_SESSION['SQLPeEnquete'] = '';		
		$_SESSION['SQLFamille'] = '';
		$_SESSION['SQLEspeces'] = '';
		$_SESSION['SQLdateDebut'] = '';
		$_SESSION['SQLdateFin'] = '';

		

?>
		<br/>
		<p>Cette page a pour but de permettre de tester la partie extraction des donn&eacute;es sous forme ecran ou fichier, en incluant le calcul des statistiques. Il se base sur un fichier XML contenant les donn&eacute;es s&eacute;lectionn&eacute;es. Ce fichier XML sera ult&eacute;rieurement g&eacute;n&eacute;r&eacute; par la partie sélection de l'extraction. Le fichier XML se trouve dans le r&eacute;pertoire temp &agrave; la racine du site et se nomme testExtractionExp.xml (peche exp) ou testExtractionArt.xml (peche art).</p>
		<br/>
		<div id="runProcess">
			<form id="formExtraction" method="get" action="extraction_filieres_exp.php">
			<input id="startProcess" type="submit" value="Test peche exp"/>
            <input type="hidden" id="xml" name="xml" value="testExtractionExp"/>
			G&eacute;n&eacute;rer un fichier de log compl&eacute;mentaire <input type="checkbox" name="logsupp" id="logsupp" checked="checked"/><br/>
			<br/>
			</form>
			<form id="formExtraction" method="get" action="extraction_filieres_art.php">
			<input id="startProcess" type="submit" value="Test peche art"/>
			<input type="hidden" id="xml" name="xml" value="testExtractionArt"/>
			G&eacute;n&eacute;rer un fichier de log compl&eacute;mentaire <input type="checkbox" name="logsupp" id="logsupp" checked="checked"/><br/>
			<br/>
			</form>
			<form id="formExtraction" method="get" action="extraction_filieres_stat.php">
			<input id="startProcess" type="submit" value="Test statistiques"/>
			<input type="hidden" id="xml" name="xml" value="testExtractionStat"/>
			G&eacute;n&eacute;rer un fichier de log compl&eacute;mentaire <input type="checkbox" name="logsupp" id="logsupp" checked="checked"/><br/>
			<br/>
			</form>			
		</div>
		<?php // for test include $_SERVER["DOCUMENT_ROOT"].'/export/export_access.php'; 
		
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
