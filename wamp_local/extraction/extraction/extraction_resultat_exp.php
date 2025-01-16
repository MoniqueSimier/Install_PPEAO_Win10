<?php 
//*****************************************
// extraction_resultat_exp.php
//*****************************************
// Created by Yann Laurent
// 2009-07-01 : creation
//*****************************************
// Ce programme gere l'affichage des resultats et l'export vers un fichier csv pour l'extraction peches expérimentales
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
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/functions.php';
// on appelle le ficheir de ocnfiguration du tri
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/ordre_tri.inc';
$zone=0; // zone libre (voir table admin_zones)
Global $debugLog;
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php 
		// les balises head communes  toutes les pages
		include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	if (isset($_GET['action'])) {
		$typeAction = $_GET['action'];
	} else {
		echo "erreur, il manque le parametre action <br/>";
		exit;
	}	
	$libelleAction = recupereLibelleFiliere($typeAction);
	?>
	<script src="/js/ajaxExtraction.js" type="text/javascript" charset="iso-8859-15"></script>
	<title>ppeao::extraire des donn&eacute;es::afficher r&eacute;sultats (<?php echo $libelleAction;?>)</title>
</head>
<body>
<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/extraction_xml.php';
include $_SERVER["DOCUMENT_ROOT"].'/zip/archive.php';
if (isset($_SESSION['s_ppeao_user_id'])){ 
	$userID = $_SESSION['s_ppeao_user_id'];
} else {
	$userID=null;
}
// Fichier de sélection à analyser
// Soit un fichier issu d'ubne variable de session envoyé par la selection
// Soit depuis un fichier présent dans le repertoire /temp présent dans une autre variable de session issu d'un paramètre &xml=
if ($_SESSION['fichier_xml'] == "" ) {
	$inputXML = "";
	$filename = "ER";	
}else {
	$inputXML = "?xml=".$_SESSION['fichier_xml'];
	$filename =  $_SESSION['fichier_xml'].".xml";
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
// On recupere les paramètres
if (isset($_GET['log'])) {
	if ($_GET['log'] == "false") {
		$EcrireLogComp = false;// Ecrire dans le fichier de log complémentaire. 
	} else {
		if ($inputXML == "") {
			$InputLog = "?logsupp=true";
		} else {
			$InputLog = "&logsupp=true";
		}
		$EcrireLogComp = true;
	}
} else {
	echo "erreur, il manque le parametre log <br/>";
	exit;
}
// On récupère les valeurs des paramètres pour les fichiers log
$dirLog = GetParam("repLogExtr",$PathFicConf);
$nomLogLien = "/".$dirLog; // pour créer le lien au fichier dans le cr ecran
$dirLog = $_SERVER["DOCUMENT_ROOT"]."/".$dirLog;
$fileLogComp = GetParam("nomFicLogExtr",$PathFicConf);
$logComp="";
$nomLogLien="";
ouvreFichierLog($dirLog,$fileLogComp);
$debugLog= false;
if (isset($_GET['action'])) {
	$typeAction = $_GET['action'];
} else {
	echo "erreur, il manque le parametre action <br/>";
	exit;
}	
?>

<div id="main_container" class="home">
	<h2 style="padding-left:120px">consulter des données : extraction des p&ecirc;ches exp&eacute;rimentales</h2>
	<h5>affichage du r&eacute;sultat</h5>
	<p class="hint_text">Cette section affiche les r&eacute;sultats pour la s&eacute;lection sous forme de tableaux pagin&eacute;s ou de fichiers exportables.</p>
    <?php
// on teste à quelle zone l'utilisateur a accès
	if (userHasAccess($userID,$zone)) {
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "Debut du traitement Resultat de l'extraction des peches experimentales pour filiere ".$typeAction,$pasdefichier);
		}

	// Dans tous les autres cas, toutes les autres valeurs auront été validés avant.
	
?>
<?php
	// Phase préliminaire : on verifie que par hasard, des nouvelles selections n'ont pas
	// ete faires par l'utilisateur juste avant de cliquer sur resultat.
	// Si c'est le cas, les variables de sessions ne seront pas a jour.
	if (isset($_GET['qual'])) {
		$valeurAMJ = AnaylseVarSession($_GET['qual']);
		$_SESSION['listeQualite'] = $valeurAMJ;
	} 
	if (isset($_GET['rest'])) {
		$valeurAMJ = AnaylseVarSession($_GET['rest']);
		$_SESSION['listeProtocole'] = $valeurAMJ;
	} 
	if (isset($_GET['pois'])) {
			$valeurAMJ = AnaylseVarSession($_GET['pois']);
		$_SESSION['listePoisson'] = $valeurAMJ;
	} 
	if (isset($_GET['CE'])) {
		$valeurAMJ = AnaylseVarSession($_GET['CE']);
		$_SESSION['listeCatEco'] = $valeurAMJ;
	} 
	if (isset($_GET['CT'])) {
		$valeurAMJ = AnaylseVarSession($_GET['CT']);
		$_SESSION['listeCatTrop'] = $valeurAMJ;
	} 
	if (isset($_GET['Esp'])) {
		$valeurAMJ = AnaylseVarSession($_GET['Esp']);
		$_SESSION['listeEspeces'] = $valeurAMJ;
	} 
	if (isset($_GET['Col'])) {
		$ListeColRecues = $_GET['Col'];
	} else {
		$ListeColRecues = "";
	}			
	if (!($ListeColRecues =="")) {
		$colRecues = explode (",",$ListeColRecues);
		$NumColR = count($colRecues) - 1;
		for ($cptCR=0 ; $cptCR<=$NumColR;$cptCR++) {
			// On extrait la valeur brute table.champ sauf dans le cas ou on la valeur XtoutX ou XpasttX
			if (!($colRecues[$cptCR] == "XtoutX") && !($colRecues[$cptCR] == "XpasttX")) { 
				$valTest = substr($colRecues[$cptCR],0,-2);
				// Deux cas de figures : soit le champ a déjà été séléectionné : on le met à jour
				// Sinon on l'ajoute avec sa valeur complete (table.nom-X ou -N)
				// On a besoin de cette info pour cocher ou décocher le champ
				if (strpos($_SESSION['listeColonne'],$valTest) === false ){
					// Cette valeur n'est pas disponible dans la liste : on l'ajoute
					if ($_SESSION['listeColonne'] == "") {
						$_SESSION['listeColonne'] = $colRecues[$cptCR] ;
					} else {
						$_SESSION['listeColonne'] .= ",".$colRecues[$cptCR];
					}		
				} else {
					// La valeur est disponible, on la met à jour
					if (strpos($_SESSION['listeColonne'],$colRecues[$cptCR]) === false) {
						// on doit mettre à jour la valeur
						if (strpos($colRecues[$cptCR],"-X") === false) {
							$oldVal = $valTest."-X";
						} else {
							$oldVal = $valTest."-N";
						}
						$newVal = $colRecues[$cptCR];
						$_SESSION['listeColonne'] = str_replace($oldVal,$newVal,$_SESSION['listeColonne']);
					}
				}					
			} else {
				//$valTest = $colRecues[$cptCR];
				$_SESSION['listeColonne'] = $colRecues[$cptCR];
			}
		}
	}
		if ($EcrireLogComp && $debugLog) {
			WriteCompLog ($logComp, "DEBUG = liste colonne = ".$_SESSION['listeColonne'],$pasdefichier);
		}
	$exportFichier = false;
	if (isset($_GET['exf'])) {
		if ($_GET['exf'] =="y") {
			$exportFichier = true;
		} 	
	}

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
	$restSupp = "";
	// Pour construire le bandeau avec la sélection
	$listeSelection ="";
	$resultatLecture = "";
	$labelSelection = "";
	$locSelection = AfficherSelection($file); 
	$SelectionPourFic = $locSelection;
	echo "<span class=\"showHide\">
<a id=\"selection_precedente_toggle\" href=\"#\" title=\"afficher ou masquer la la s&eacute;lection\" onclick=\"javascript:toggleSelection();\">[afficher/modifier ma s&eacute;lection]</a></span>";
	echo "<div id=\"selection_precedente\">";
	if (!($_SESSION["selection_url"] =="")) {
		echo" <span id=\"changeSel\"><a href=\"".$_SESSION["selection_url"]."&amp;open=1\" >modifier la s&eacute;lection en cours...	</a></span>";
	}	
	echo $locSelection."<br/>";

	echo "<div id=\"filEncours\"><span id=\"filEncoursTit\">fili&egrave;re en cours : </span><span id=\"filEncoursText\">".$libelleAction."</span>"; 
	echo "<span id=\"changeSel\"><a href=\"/extraction/extraction/extraction_filieres_exp.php".$inputXML.$InputLog."\" >&nbsp;&nbsp;[modifier la fili&egrave;re]</a></span></div>";
	echo "</div>";
	AfficherDonnees($file,$typeAction);

	
?>
	<div id="resultfiliere"> 
<?php 
		echo $resultatLecture; 
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "-----------------------------------------------------------------",$pasdefichier);
			WriteCompLog ($logComp, "Fin Extraction des peches experimentales pour filiere ".$libelleAction,$pasdefichier);
			WriteCompLog ($logComp, "-----------------------------------------------------------------",$pasdefichier);
		}
?>
	</div>
<?php 
	echo "<div id=\"sel_compteur\"><p><b>votre s&eacute;lection correspond &agrave; : </b></p><ul><li><b>".$compteurItem."</b> ".$labelSelection."</li><li><b>fili&egrave;re en cours</b> : <span id=\"filEncoursText\">".$libelleAction."</span>"; 
	echo "<span  class=\"changeSel2\"><a href=\"/extraction/extraction/extraction_filieres_exp.php".$inputXML.$InputLog."\" >&nbsp;&nbsp;[choisir une autre fili&egrave;re]</a></span></li><li><b>restriction(s) suppl&eacute;mentaire(s)</b> : ".$restSupp; 
	//echo "<span class=\"changeSel2\"><a href=\"/extraction/extraction/extraction_filieres_exp.php".$inputXML.$InputLog."&gselec=y&tab=".$numTab."&modiffil=y&action=".$typeAction."\" >[modifier la s&eacute;lection de la fili&egrave;re en cours]</a></span></li>";
	echo"</ul></div>";
	if (!($exportFichier)) {	?>
	<div id="exportFic2">
		<input type="button" id="validation" onClick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction;?>','1','','y','','','')" value="Exporter en fichier"/>
		<input type="hidden" id="ExpFic" checked="checked"/></div>	
        <?php if ($EcrireLogComp) { echo"<input type=\"hidden\" id=\"logsupp\" checked=\"checked\"/>";} else {echo"<input type=\"hidden\" id=\"logsupp\" />";}	?>	
	</div>
<?php } else {

	echo "<br/>";
}	?>

<script type="text/javascript" charset="utf-8">
var mySlider = new Fx.Slide('selection_precedente', {duration: 500});
mySlider.hide();
// affiche ou masque le DIV contenant la selection precedente
function toggleSelection() {
	mySlider.toggle() //toggle the slider up and down.
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