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
Global $debugLog;
// declaration variables
$peupActive = "";
$envActive = "";
$NtActive = "";
$bioActive = "";
$trophActive = "";
session_start();
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/extraction_xml.php';
// On recupere les paramètres
if (isset($_GET['log'])) {
	if ($_GET['log'] == "false") {
		$EcrireLogComp = false;// Ecrire dans le fichier de log complémentaire. 
		echo "<input type=\"hidden\" name=\"logsupp\" id=\"logsupp\" />";
	} else {
		echo "<input type=\"hidden\" name=\"logsupp\" id=\"logsupp\" checked=\"checked\" />";
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

if (isset($_GET['action'])) {
	$typeAction = $_GET['action'];
} else {
	echo "erreur, il manque le parametre action <br/>";
	exit;
}
if (isset($_GET['ts'])) {
	$typeStatistiques = $_GET['ts'];
} else {
	echo "erreur, il manque le parametre type de statistiques<br/>";
	exit;
}
if (isset($_GET['tab'])) {
	$numTab = $_GET['tab'];
} else {
	$numTab = "1";
}
if (isset($_GET['chgA'])) {
	$changtAction = $_GET['chgA'];
} else {
	$changtAction = "n";
}
if (isset($_GET['Col'])) {
	$ListeColRecues = $_GET['Col'];
} else {
	$ListeColRecues = "";
}
if (isset($_GET['TEC'])) {
	$codeTableEnCours = $_GET['TEC'];
} else {
	$codeTableEnCours = "Pays";
}
if (isset($_GET['Esp'])) {
	$listeEsp = $_GET['Esp'];
} else {
	$listeEsp = "";
}
if (isset($_GET['synth'])) {
	$listeSynth = $_GET['synth'];
} else {
	$listeSynth = "";
}
if (isset($_GET["gselec"])) {
	$gardeSelection =  $_GET["gselec"];
}else {
	$gardeSelection = "";
}
// Gestion des regroupements
if (isset($_GET['RegEC'])) {
	$RegEncours = intval($_GET['RegEC']) + 1;
} else {
	$RegEncours = "";
}
if (isset($_GET['nvReg'])) {
	$CreerReg = $_GET['nvReg'];
} else {
	$CreerReg = "";
}
// Liste de colonnes, plus simple que les extractions : soit rien soit tous
$_SESSION['listeColonne'] = $ListeColRecues;
// Préchargement des valeurs par défaut
// car on a changé de filiere ou on commence
// Attention aux valeurs poisson / non_poisson (non_poisson = 1 correspond a la selection des especes qui ne sont pas des poissons
if ($changtAction == "y" && $gardeSelection =="") {
	$_SESSION['listetablesynth'] ="";
	$_SESSION['listeEspeces'] = "";
	unset($_SESSION['listeRegroup']);
	

} else {
	$_SESSION['listetablesynth'] = $listeSynth;
	$_SESSION['listeEspeces'] = $listeEsp;
}
// On n'affiche pas de sélection de données liées aux especes pour l'environnement
if ($EcrireLogComp ) {
	WriteCompLog ($logComp, "Selection de la filere ".$typeAction,$pasdefichier);
}	

$tab1 = "";
$tab2 = "";
$tab3 = "";
$colActive="";
$espActive="";
$regActive="";
$ClassEnv = " visi";
switch ($numTab) {
	case "1":
		$colActive=" visible";
		$tab1 = " active";
		break;
	case "2":
		$espActive=" visible";
		$tab2 = " active";
		break;
	case "3":
		$regActive=" visible";
		$tab3 = " active";
		break;
}


?>

<form id="filiere" >
<?php // construit les differentes onglets du tableau ?>
<div id="menuTab">
<a href="#" class="<?php echo $tab1;?>" onClick="runFilieresStat('<?php echo $typeStatistiques;?>','<?php echo $typeAction;?>','1','<?php echo $codeTableEnCours;?>','n','','','','')">s&eacute;lection de variables optionnelles</a>|
<a href="#" class="<?php echo $tab2;?>" onClick="runFilieresStat('<?php echo $typeStatistiques;?>','<?php echo $typeAction;?>','2','<?php echo $codeTableEnCours;?>','n','','','','')">esp&egrave;ces</a>|
<a href="#" class="<?php echo $tab3;?>" onClick="runFilieresStat('<?php echo $typeStatistiques;?>','<?php echo $typeAction;?>','3','<?php echo $codeTableEnCours;?>','n','','','','')">regroupement esp&egrave;ces</a>
</div>
<?php // Les differents div correspondant aux choix disponibles par onglet ?>
<?php // l'onglet qui gere la selection des colonnes complémentaires ?>
<div id="colonnes" class="colonnes<?php echo $colActive;?>">
<span class="hint small">Les variables optionnelles qui peuvent &ecirc;tre extraites concernent le nombre d'observations effectu&eacute;es, les valeurs extr&ecirc;mes enregistr&eacute;es, le nombre de jours d'observation et des informations compl&eacute;mentaires sur les esp&egrave;ces. Si la case d'ajout des variables est coch&eacute;e, elles sont toutes s&eacute;lectionn&eacute;es ensemble.</span><br/>
<?php	if (strpos($_SESSION['listeColonne'],"XtoutX") === false) {
		echo "<input id=\"facTout\" type=\"checkbox\"  name=\"fac0\" value=\"tout\"  />&nbsp;ajouter toutes les variables optionnelles<br/>";
	} else {
		echo "<input id=\"facTout\" type=\"checkbox\"  name=\"fac0\" value=\"tout\"  checked=\"checked\" />&nbsp;&nbsp;ajouter toutes les variables optionnelles<br/>";
	}
	?>
</div>
<?php // l'onglet qui gere les espèces ?>
<div id="especes" class="especes<?php echo $espActive;?>">
<?php echo AfficheEspeces($_SESSION['SQLEspeces'],$listeEsp,$changtAction,$typeStatistiques,$typeAction,$numTab,"","","",""); ?>
</div>

<?php // l'onglet qui gere les regroupements ?>
<div id="regroupesp" class="regroupesp<?php echo $regActive;?>">
<?php 
echo AfficheRegroupEsp($typeStatistiques,$typeAction,$numTab,$_SESSION['SQLEspeces'],$_SESSION['listeRegroup'],$RegEncours,$CreerReg); ?>
</div>

</form>




