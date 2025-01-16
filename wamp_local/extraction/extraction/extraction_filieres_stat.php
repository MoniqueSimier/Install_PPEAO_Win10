<?php 
//*****************************************
// extraction_filiere.php
//*****************************************
// Created by Yann Laurent
// 2009-06-24 : creation
//*****************************************
// Ce programme gere le choix des filieres et lance les traitements adequats
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
$zone=0; // zone libre (voir table admin_zones)
global $debugLog;
global $EcrireLogComp;
$EcrireLogComp = true;
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php 
		// les balises head communes  toutes les pages
		include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	?>
	<script src="/js/ajaxExtraction.js" type="text/javascript" charset="iso-8859-15"></script>
 
	<title>ppeao::statistiques::afficher r&eacute;sultats</title>
</head>
<body>
<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/extraction_xml.php';
if (isset($_SESSION['s_ppeao_user_id'])){ 
	$userID = $_SESSION['s_ppeao_user_id'];
} else {
	$userID=null;
}
// Fichier de sélection à analyser
// Soit un fichier issu d'ubne variable de session envoyé par la selection
// Soit depuis un fichier présent dans le repertoire /temp passé en paramètre &xml=
if (isset($_GET["xml"])) {
	$filename =  $_GET["xml"].".xml";
	$_SESSION['fichier_xml']=$_GET["xml"];
}else {
	$filename = "ER";
	$_SESSION['fichier_xml'] = "";
}
if (isset($_GET["gselec"])) {
	$gardeSelection =  $_GET["gselec"];
}else {
	$gardeSelection = "";
}
if (isset($_GET["modiffil"])) {
	$modifFiliere =  $_GET["modiffil"];
}else {
	$modifFiliere = "";
}
if (isset($_GET["action"])) {
	$typeAction =  $_GET["action"];
}else {
	$typeAction = "";
}
$file=$_SERVER["DOCUMENT_ROOT"]."/work/temp/".$filename;
if (!(file_exists($file)) ) {
	$dirTemp = $_SERVER["DOCUMENT_ROOT"]."/work/temp/".$userID;
	$resultatDir = creeDirTemp($dirTemp);
	if (strpos("erreur",$resultatDir) === false ){
		$file = $dirTemp."/tempStat.xml";
	} else {
		echo "erreur a la creation du repertoire temporaire ".$dirTemp." arret du traitement.<br/>";
		exit;
	}
	//$file = $_SERVER["DOCUMENT_ROOT"]."/temp/tempExtractionArt.xml";
	$fileopen=fopen($file,'w');
	fwrite($fileopen,$_SESSION["selection_xml"]);
	rewind($fileopen);
}
// fin de modification par Olivier
?>
<div id="main_container" class="home">
	<h2 style="padding-left:120px">consulter des données : statistiques de p&ecirc;ches</h2>
    <h5>choix compl&eacute;mentaires</h5>
    <?php
	// on teste à quelle zone l'utilisateur a accès
	if (userHasAccess($userID,$zone)) {
?>	<a id="stat_toggle" href="#" title="afficher l'aide sur l'extraction des statistiques" onClick="javascript:toggleHelp();">aide >></a></span>
	<div id="Aide_stat">
		<p class="hint_text">
        Les fichiers de statistiques globales, par esp&egrave;ces et par grands types d'engins de p&ecirc;che correspondant à la s&eacute;lection r&eacute;alis&eacute;e sont extraits dans un m&ecirc;me fichier zipp&eacute; ; le fichier des statistiques globales est affich&eacute; &agrave; l'&eacute;cran pour visualiser les r&eacute;sultats de la s&eacute;lection. 
Des s&eacute;lections d'esp&egrave;ces ou des regroupements d'esp&egrave;ces peuvent &ecirc;tre effectu&eacute;es avant l'extraction finale de donn&eacute;es.
</p></div>

		<div id="resumeChoix">
			<?php 
				// On recupere les paramètres
				echo "<input type=\"hidden\" name=\"logsupp\" id=\"logsupp\" checked=\"checked\" />";
				// On récupère les valeurs des paramètres pour les fichiers log
				$dirLog = GetParam("repLogExtr",$PathFicConf);
				$nomLogLien = "/".$dirLog; // pour créer le lien au fichier dans le cr ecran
				$dirLog = $_SERVER["DOCUMENT_ROOT"]."/".$dirLog;
				$fileLogComp = GetParam("nomFicLogExtr",$PathFicConf);
				$logComp="";
				$nomLogLien="";
				ouvreFichierLog($dirLog,$fileLogComp);
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "#",$pasdefichier);
					WriteCompLog ($logComp, "#",$pasdefichier);
					WriteCompLog ($logComp, "*-#####################################################",$pasdefichier);
					WriteCompLog ($logComp, "*- DEBUT EXTRACTION STATISTIQUES ".date('y\-m\-d\-His'),$pasdefichier);
					WriteCompLog ($logComp, "*-#####################################################",$pasdefichier);
					WriteCompLog ($logComp, "#",$pasdefichier);
					WriteCompLog ($logComp, "#",$pasdefichier);
				}			
				// Si on change de filière, on remet tous à blanc
				if (!($gardeSelection == "y")) { 
					$_SESSION['listeColonne'] = ""; // tableau nomTable / NomChamp des champs comple à afficher
					$_SESSION['listetablesynth'] = ""; // Gestion de la table de synthèse a extraire
					$_SESSION['pasderesultat'] = false; // indicateur global si pas de resultat
					$_SESSION['listeDocPays'] = ""; //liste contenant les ID des documents pays a mettre en zip
					$_SESSION['listeDocSys'] = ""; //liste contenant les ID des documents systeme a mettre en zip
					$_SESSION['listeDocSect'] = ""; //liste contenant les ID des documents secteur a mettre en zip
					unset($_SESSION['listeRegroup']); // Liste des regroupements
					unset($_SESSION['libelleTable']); // Pour recuperer les noms des tables
					unset($_SESSION['libelleChamp']); // Pour recuperer les noms des champs
				}
				unset($_SESSION['listeEffortTotal']);
				// Variables pour construire les SQL	
				$SQLPays 	= "";
				$SQLSysteme	= "";
				$SQLSecteur	= "";
				$SQLAgg		= "";
				$SQLEngin	= "";
				$SQLGTEngin = "";
				$SQLCampagne = "";
				$SQLEspeces	= "";
				$SQLFamille = "";
				$SQLdateDebut = ""; // format annee/mois
				$SQLdateFin = ""; // format annee/mois
				// Données pour la selection 
				$typeSelection = "";
				$typePeche = "";
				$typeStatistiques = "";
				$listeEnquete = ""; // contiendra soit la 
				$listeGTEngin = "";
				$compteurItem = 0;
				// Pour construire le bandeau avec la sélection
				$listeSelection ="";
				$resultatLecture = "";
				$labelSelection = "";
				$locSelection = AfficherSelection($file,$typeAction); 
				if ($typeStatistiques =="agglomeration") {
					echo "<h2>Statistiques par agglom&eacute;rations</h2>"; 
				} else {
					echo "<h2>Statistiques g&eacute;n&eacute;rales</h2>";
				}
				echo "<span class=\"showHide\">
<a id=\"selection_precedente_toggle\" href=\"#\" title=\"afficher ou masquer la la s&eacute;lection\" onclick=\"javascript:toggleSelection();\">[afficher/modifier ma s&eacute;lection]</a></span>";
				echo "<div id=\"selection_precedente\">";
				if (!($_SESSION["selection_url"] =="")) {
					echo" <span id=\"changeSel\"><a href=\"".$_SESSION["selection_url"]."&amp;open=1\" >modifier la s&eacute;lection en cours...</a></span>";
				}				
				echo $locSelection;

				echo "</div>";
				AfficherDonnees($file,$typeAction);

				
				if (!( $typeSelection == "statistiques")) {
					echo "<br/><br/><b>Erreur dans le fichier XML en entr&eacute;e. Il ne s'agit pas d'une s&eacute;lection de donn&eacute;es de p&ecirc;che artisanale.</b><br/>.";
					exit;
				}				
			?>
		</div>
		<div id="runProcess">
        <?php if ($_SESSION['pasderesultat']) {
			echo "La s&eacute;lection n'a pas retourn&eacute; de r&eacute;sultats.<br/>";
		} else { ?>
		</ul>
        <?php } ?>
		</div>
		<div id="resultfiliere"></div>
		<div id="exportFic"></div>
        <?php // On lance systematiquement l'affichage de la filiere statistiques vu qu'on exporte toutes les tables 
        echo "<div id=\"sel_compteur\"><p><b>votre s&eacute;lection correspond &agrave; : </b></p><ul><li>".$compteurItem." ".$labelSelection."</li></ul></div>"; ?>   
		<script type="text/javascript" charset="utf-8">runFilieresStat('<?php echo $typeStatistiques ?>','stats','1','','n','','','','');</script>
        <script type="text/javascript" charset="utf-8">
			var mySlider = new Fx.Slide('selection_precedente', {duration: 500});
			mySlider.hide();
			// affiche ou masque le DIV contenant la selection precedente
			function toggleSelection() {
				mySlider.toggle() //toggle the slider up and down.
			}
			var Aide = new Fx.Slide('Aide_stat', {duration: 500});
			Aide.hide();
			function toggleHelp() {
				Aide.toggle() //toggle the slider up and down.
			}
		</script>		
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
