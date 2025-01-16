<?php 
//*****************************************
// extraction_lecture.php FICHIER DE TEST, pas en prod
//*****************************************
// Created by Yann Laurent
// 2009-06-29 : creation
//*****************************************
// Ce programme gere le processus de lecture des données a partir 
// de la selection contenue dans le fichier XML
//*****************************************
// Paramètres en entrée
// aucun pour l'instant.
// Paramètres en sortie
// aucun pour l'instant.
//*****************************************

// Include standard
include $_SERVER["DOCUMENT_ROOT"].'/variables.inc';
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';

// Variables standard
$CRexecution = "<br/>";

if (isset($_GET['log'])) {

	if ($_GET['log'] == "false") {
		$EcrireLogComp = false;// Ecrire dans le fichier de log complémentaire. 
	} else {
		$EcrireLogComp = true;
	}
}
if (isset($_GET['trt'])) {
	$trt = $_GET['trt'];
} else {
	$trt = "";
}
// Recuperation des parametres (nom repertoire, nom fichiers etc..) depuis le fichier de parametres
$dirLog = $_SERVER["DOCUMENT_ROOT"]."/log/";
$nomLogLien = "/log/"; // pour créer le lien au fichier dans le cr ecran
$fileLogComp = "extraction";
if ($EcrireLogComp ) {
	$nomFicLogComp = $dirLog."/".date('y\-m\-d')."-".$fileLogComp;
	$nomLogLien = $nomLogLien."/".date('y\-m\-d')."-".$fileLogComp;
	$logComp = fopen($nomFicLogComp , "a+");
	if (! $logComp ) {
		$CRexecution = " erreur de cr&eacute;ation du fichier de log";
		echo "<div id=\"CR_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"CR_txt\">ERREUR .".$CRexecution."</div>" ;
		exit;		
	}
}

if (!$connectPPEAO) { 
	echo "<div id=\"CR_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"CR_txt\">Erreur de connexion à la base de donn&eacute;es pour maj des logs</div>" ;; exit;
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
// Pour construire le bandeau avec la sélection
$listeSelection ="";

// extraction des données du fichiers XML
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/extraction_xml.php';
// construction du résultat.
$resultatLecture = "<br/> <b>Resultat </b> <br/>";

$SQLPays 	= substr($SQLPays,0,- 1); // pour enlever la virgule surnumeraire;
$SQLSysteme	= substr($SQLSysteme,0,- 1); // pour enlever la virgule surnumeraire;
$SQLSecteur	= substr($SQLSecteur,0,- 1); // pour enlever la virgule surnumeraire;
$SQLEngin	= substr($SQLEngin,0,- 1); // pour enlever la virgule surnumeraire;
$SQLGTEngin = substr($SQLGTEngin,0,- 1); // pour enlever la virgule surnumeraire;
$SQLCampagne = substr($SQLCampagne,0,- 1); // pour enlever la virgule surnumeraire;
$SQLEspeces	= substr($SQLEspeces,0,- 1); // pour enlever la virgule surnumeraire;
$SQLFamille = substr($SQLFamille,0,- 1); // pour enlever la virgule surnumeraire;
switch ($typeSelection) {
	case "extraction" :
	switch ($typePeche) {
		case "experimentale" :
		$SQLfinal = "select * from exp_coup_peche where exp_campagne_id in (
				select id from exp_campagne where ref_systeme_id in (".$SQLSysteme.") 
											and date_debut >='".$SQLdateDebut."/01'
											and date_fin <='".$SQLdateFin."/28') 
				and exp_engin_id in (".$SQLEngin.")";
		break;
		case "artisanale" :
			$SQLfinal = "select * form atr_debarquement";
		break;
		default:
	}
	case "statistiques" :
	break;
	default:
}
// Execution de la requete
$SQLfinalResult = pg_query($connectPPEAO,$SQLfinal);
$erreurSQL = pg_last_error($connectPPEAO);
if ( !$SQLfinalResult ) { 
	$CRexecution .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;erreur query ".$SQLfinal." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
	$erreurProcess = true;

} else {
	
	if (pg_num_rows($SQLfinalResult) == 0) {
	// Erreur
		$CRexecution .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>table vide...<br/>";
	} else {
		$cpt1 = 0;
		while ($finalRow = pg_fetch_row($SQLfinalResult) ) {
			if ($trt == "") {
				$resultatLecture .="coup de peche : id = ".$finalRow[0]."<br/>";
				$cpt1++;
			}
		}
	}
}

echo "<div id=\"selection\"><b>".$typeSelection." - ".$typePeche.$typeStatistiques." </b> en cours pour la selection suivante : <br/>".$listeSelection."</div>";
echo "<div id=\"resultat\">Nombre de coup de peches = ".$cpt1."<br/>".$resultatLecture."<br/>";

if ($trt == "1") {
	echo "suite <br/>";
} else {
	echo "<form id=\"formExtractionSuite\">
				<input id=\"continueProcess\" type=\"button\" value=\"continuer le traitement\" onClick=\"continueProcess()\"/>
				<input type=\"hidden\" name=\"logsupp\" id=\"logsupp\" value=\"".$EcrireLogComp."\"/><br/>
			</form><br/>";
}
echo "</div>";

?>