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
	<title>ppeao::extraire des donn&eacute;es::fili&egrave;res</title>
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
		$file = $dirTemp."/tempExp.xml";
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
	<h2 style="padding-left:150px">consulter des données : extraction des p&ecirc;ches exp&eacute;rimentales</h2>
    <h5>choix des fili&egrave;res</h5>
    <?php

	// on teste à quelle zone l'utilisateur a accès
	if (userHasAccess($userID,$zone)) {

?>
	<span class="showHide">
	<a id="selection_precedente_toggle" href="#" title="afficher l'aide sur l'extraction des peches experimentales" onClick="javascript:toggleHelp();">aide >></a></span>
	<div id="Aide_pechexp">
		<p class="hint_text">Vous pouvez choisir les fili&egrave;res pour finaliser l'exportation des donn&eacute;es sous forme de fichier ou d'affichage &agrave; l'&eacute;cran : <br/>Peuplement : liste de fractions pêch&eacute;es avec nombre et poids total qui permettra de construire des tableaux crois&eacute;s esp&egrave;ces x coups de p&ecirc;che pour des &eacute;tudes de peuplement<br/>
Environnement : liste de relev&eacute;s environnementaux.<br/>
NT-PT : liste de fractions caract&eacute;ris&eacute;es par leur nombre et poids total et par les descripteurs de l'environnement associ&eacute;s.<br/>
Biologie : liste d'individus pêch&eacute;s, caract&eacute;ris&eacute;s par leur longueur (et d'autres param&egrave;tres optionnels), associ&eacute;s aux descripteurs de l'environnement.<br/>
Trophique : listes de couples individus-contenu stomacal, associ&eacute;s aux descripteurs de l'environnement.<br/>
Dans toutes les fili&egrave;res vous avez la possibilit&eacute; de s&eacute;lectionner des variables optionnelles.
</p>
</div>
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
					WriteCompLog ($logComp, "*- DEBUT EXTRACTION PECHES EXPERIMENTALES ".date('y\-m\-d\-His'),$pasdefichier);
					WriteCompLog ($logComp, "*-#####################################################",$pasdefichier);
					WriteCompLog ($logComp, "#",$pasdefichier);
					WriteCompLog ($logComp, "#",$pasdefichier);
				}
			
				// Si on change de filière, on remet tous à blanc
				unset($_SESSION['listeRegroup']); // Liste des regroupements au cas ou...
				if (!($gardeSelection == "y")) { 
					$_SESSION['listeQualite'] 	= "";
					$_SESSION['listeProtocole'] = ""; // Oui / non
					$_SESSION['listeEspeces'] 	= "";	// Liste des espèces selectionnées
					$_SESSION['listeCatEco'] 	= ""; 	// Liste des categories ecologiques selectionnées
					$_SESSION['listeCatTrop'] 	= ""; // Liste des categories trophiques selectionnées
					$_SESSION['listePoisson'] 	= ""; // liste des selections poissons / non poissons
					$_SESSION['listeColonne'] 	= ""; // tableau nomTable / NomChamp des champs comple à afficher
					$_SESSION['listeDocPays'] 	= ""; //liste contenant les ID des documents pays a mettre en zip
					$_SESSION['listeDocSys'] 	= ""; //liste contenant les ID des documents systeme a mettre en zip
					$_SESSION['listeDocSect'] 	= ""; //liste contenant les ID des documents secteur a mettre en zip
					$_SESSION['pasderesultat'] 	= false; // indicateur global si pas de resultat
					unset($_SESSION['libelleTable']); // Pour recuperer les noms des tables
					unset($_SESSION['libelleChamp']); // Pour recuperer les noms des champs
				}
				// Variables pour construire les SQL	
				$SQLPays 	= "";
				$SQLSysteme	= "";
				$SQLSecteur	= "";
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
				echo "<span class=\"showHide\">
<a id=\"selection_precedente_toggle\" href=\"#\" title=\"afficher ou masquer la la s&eacute;lection\" onclick=\"javascript:toggleSelection();\">[afficher/modifier ma s&eacute;lection]</a></span>";
				echo "<div id=\"selection_precedente\">";
				if (!($_SESSION["selection_url"] =="")) {
					echo" <span id=\"changeSel\"><a href=\"".$_SESSION["selection_url"]."&amp;open=1\" >modifier la s&eacute;lection en cours...</a></span>";
				}				
				echo $locSelection;

				echo "</div>";
				AfficherDonnees($file,$typeAction);

				
				if (!( $typePeche == "experimentale")) {
					echo "<br/><br/><b>Erreur dans le fichier XML en entr&eacute;e. Il ne s'agit pas d'une s&eacute;lection de donn&eacute;es de p&ecirc;che exp&eacute;rimentale.</b><br/>.";
					exit;
				}				
			?>
		</div>
		<br/>
		<div id="runProcess">        
        <?php if ($_SESSION['pasderesultat']) {
			echo "La s&eacute;lection n'a pas retourn&eacute; de r&eacute;sultats.<br/>";
		} else { ?>
			<b>choix de la fili&egrave;re :</b>&nbsp;
			<a href="#" onClick="runFilieresExp('<?php echo $typePeche ?>','peuplement','1','','n','','','','')">Peuplement</a>&nbsp;-&nbsp;
			<a href="#" onClick="runFilieresExp('<?php echo $typePeche ?>','environnement','1','','n','','','','')">Environnement</a>&nbsp;-&nbsp;
			<a href="#" onClick="runFilieresExp('<?php echo $typePeche ?>','NtPt','1','','n','','','','')">Nt/Pt</a>&nbsp;-&nbsp;
			<a href="#" onClick="runFilieresExp('<?php echo $typePeche ?>','biologie','1','','n','','','','')">Biologie</a>&nbsp;-&nbsp;
			<a href="#" onClick="runFilieresExp('<?php echo $typePeche ?>','trophique','1','','n','','','','')">Trophique</a>
		</ul>
        <?php } ?>
		</div>
        <div id="resultfiliere"></div>
		<div id="exportFic"></div>
        <input type="hidden" id="gselec" value="<?php echo $gardeSelection;?>"/>
        <?php 
			echo "<div id=\"sel_compteur\"><p><b>votre s&eacute;lection correspond &agrave; : </b></p><ul><li>".$compteurItem." ".$labelSelection."</li></ul></div>";?>
			<?php if ($modifFiliere=="y") { ?>
                   <script type="text/javascript" charset="utf-8">runFilieresExp('<?php echo $typePeche ?>','<?php echo $typeAction ?>','1','','n','','','','')</script>
        <?php }           ?>
        <script type="text/javascript" charset="utf-8">
			var mySlider = new Fx.Slide('selection_precedente', {duration: 500});
			mySlider.hide();
			var Aide = new Fx.Slide('Aide_pechexp', {duration: 500});
			Aide.hide();
			// affiche ou masque le DIV contenant la selection precedente
			function toggleSelection() {
				mySlider.toggle() //toggle the slider up and down.
			}
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
