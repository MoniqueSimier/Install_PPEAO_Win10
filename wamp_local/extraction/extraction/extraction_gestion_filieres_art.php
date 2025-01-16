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
if (isset($_GET['tp'])) {
	$typePeche = $_GET['tp'];
} else {
	echo "erreur, il manque le parametre type de peche<br/>";
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
if (isset($_GET['pois'])) {
	$listePois = $_GET['pois'];
} else {
	$listePois = "";
}
if (isset($_GET['CE'])) {
	$listeCE = $_GET['CE'];
} else {
	$listeCE = "";
}
if (isset($_GET['CT'])) {
	$listeCT = $_GET['CT'];
} else {
	$listeCT = "";
}
if (isset($_GET['TEC'])) {
	$codeTableEnCours = $_GET['TEC'];
} else {
	$codeTableEnCours = "Pays";
}
if (isset($_GET['Col'])) {
	$ListeColRecues = $_GET['Col'];
} else {
	$ListeColRecues = "";
}
if (isset($_GET['Esp'])) {
	$listeEsp = $_GET['Esp'];
} else {
	$listeEsp = "";
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

if (!($ListeColRecues =="")) {
	$colRecues = explode (",",$ListeColRecues);
	$NumColR = count($colRecues) - 1;
	for ($cptCR=0 ; $cptCR<=$NumColR;$cptCR++) {
		// On extrait la valeur brute table.champ sauf dans le cas ou on la valeur XtoutX ou XpasttX
		if (!($colRecues[$cptCR] == "XtoutX") && !($colRecues[$cptCR] == "XpasttX")) { 
			$valTest = substr($colRecues[$cptCR],0,-2);
			if ($EcrireLogComp && $debugLog) {
				WriteCompLog ($logComp, "DEBUG : ".$colRecues[$cptCR]." - ".$valTest,$pasdefichier);
			}

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
// Préchargement des valeurs par défaut
// car on a changé de filiere ou on commence
// Attention aux valeurs poisson / non_poisson (non_poisson = 1 correspond a la selection des especes qui ne sont pas des poissons
if ($changtAction == "y" && $gardeSelection =="") {
	$_SESSION['listeCatTrop'] ="";
	$_SESSION['listeCatEco'] = "";
	$_SESSION['listeColonne'] = "";
	$_SESSION['listeEspeces'] = "";
	unset($_SESSION['listeRegroup']);
	switch ($typeAction) {
		// On ne gère pas peuplement car on va directement à la page de résultat quand on clique dessus.
		case "activite":
				// On précharge les valeurs par defaut
				$_SESSION['listePoisson'] = "";;
			break;
		case "capture":
				// On précharge les valeurs par defaut
				$_SESSION['listePoisson'] = "";;
			break;
		case "NtPart":
				// On précharge les valeurs par defaut
				$_SESSION['listePoisson'] = "0,np";
			break;
		case "taillart":
				// On précharge les valeurs par defaut
				$_SESSION['listePoisson'] = "0,np";
			break;
		case "engin":
				// On précharge les valeurs par defaut
				$_SESSION['listePoisson'] = "";
			break;
	}
} else {
	$_SESSION['listePoisson'] = $listePois;
	if (!($_SESSION['listeCatEco'] == $listeCE) && !($listeCE =="")) {
		$_SESSION['listeCatEco'] = $listeCE;
	}
	$_SESSION['listeCatTrop'] = $listeCT;
	$_SESSION['listeEspeces'] = $listeEsp;
}
// On n'affiche pas de sélection de données liées aux especes pour l'environnement
if ($EcrireLogComp ) {
	WriteCompLog ($logComp, "Selection de la filere ".$typeAction,$pasdefichier);
}	
$tab1 = "";
$tab2 = "";
$tab3 = "";
$tab4 = "";
$tab5 = "";
$cgActive="";
$ceActive="";
$ctActive="";
$colActive="";
$ClassEnv = "";
$espActive="";
$regActive = "";
switch ($numTab) {
	case "1":
		if ($typeAction == "activite" || $typeAction == "capture" || $typeAction == "engin" ) {
			$cgActive="";
			$ClassEnv = "";
		} else {
			$cgActive=" visible";
			$ClassEnv = " visi";
		}
		$tab1 = " active";
		break;
	case "2":
		if (!($typeAction == "activite") && !($typeAction == "capture") && !($typeAction == "engin") ) {
			$ceActive=" visible";
		}
		$tab2 = " active";
		break;
	case "3":
		if (!($typeAction == "activite") && !($typeAction == "capture") && !($typeAction == "engin") ) {
			$ctActive=" visible";
		}
		$tab3 = " active";
		break;
	case "4":
		$colActive=" visible";
		$tab4 = " active";
		break;
	case "5":
		if (!($typeAction == "activite") && !($typeAction == "capture") && !($typeAction == "engin") ) {
			$espActive=" visible";
		}
		$tab5 = " active";
		break;
	case "6":
		if (!($typeAction == "activite") && !($typeAction == "capture") && !($typeAction == "engin") ) {
			$regActive=" visible";
		}
		$tab6 = " active";
		break;
}
// Gestion des valeurs déjà saisies ou valeurs par défaut
if ($EcrireLogComp && $debugLog) {
	WriteCompLog ($logComp, "Liste variable session: \n CatTrop = ".$_SESSION['listeCatTrop']." \n CatEco = ".$_SESSION['listeCatTrop']." \n Poissons = ".$_SESSION['listePoisson']." \n Especes ".$_SESSION['listeEspeces']." \n",$pasdefichier);
}	

if (strpos($_SESSION['listePoisson'],"0")  === false ) {$valPois1 =""; } else {$valPois1 = "checked=\"checked\"";}
if (strpos($_SESSION['listePoisson'],"pp")  === false ) {$valPois2 =""; } else {$valPois2 = "checked=\"checked\"";}
if (strpos($_SESSION['listePoisson'],"1")  === false ) {$valPois3 =""; } else {$valPois3 = "checked=\"checked\"";}
if (strpos($_SESSION['listePoisson'],"np")  === false ) {$valPois4 =""; } else {$valPois4 = "checked=\"checked\"";}

?>

<form id="filiere" >
<?php // construit les differentes onglets du tableau ?>
<div id="menuTab">
<?php if (!($typeAction == "activite") && !($typeAction == "capture") && !($typeAction == "engin")) { ?>
<a href="#" class="<?php echo $tab1;?>" onClick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction;?>','1','<?php echo $codeTableEnCours;?>','n','','','','')">crit&egrave;res g&eacute;n&eacute;raux</a>|
<a href="#" class="<?php echo $tab2;?>" onClick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction; ?>','2','<?php echo $codeTableEnCours;?>','n','','','','')">cat. &eacute;cologiques</a>|
<a href="#" class="<?php echo $tab3;?>" onClick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction;?>','3','<?php echo $codeTableEnCours;?>','n','','','','')">cat. trophiques</a>|

<?php } ?>
<a href="#" class="<?php echo $tab4;?>" onClick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction;?>','4','<?php echo $codeTableEnCours;?>','n','','','','')">s&eacute;lection variables optionnelles</a>
<?php if (!($typeAction == "activite") && !($typeAction == "capture") && !($typeAction == "engin")) { ?>
|<a href="#" class="<?php echo $tab5;?>" onClick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction;?>','5','<?php echo $codeTableEnCours;?>','n','','','','')">esp&egrave;ces</a> | <a href="#" class="<?php echo $tab6;?>" onClick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction;?>','6','<?php echo $codeTableEnCours;?>','n','','','','')">regroupement esp&egrave;ces</a>
<?php } ?>
</div>
<?php // Les differents div correspondant aux choix disponibles par onglet ?>
<div id="criteresgen" class="criteresgen<?php echo $cgActive;?>">
	<span class="sscriteresgen<?php echo $ClassEnv;?>">choisir les poissons / non poissons :<br/></span>
	<input class="sscriteresgen<?php echo $ClassEnv;?>" id="poisson1" type="radio" name="poissons" value="0"  <?php echo $valPois1;?> onclick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction;?>','<?php echo $numTab;?>','','n','','','','')"/> <span class="sscriteresgen<?php echo $ClassEnv;?>">inclure les poissons</span>
	<input class="sscriteresgen<?php echo $ClassEnv;?>" id="poisson2" type="radio" name="poissons" value="pp"  <?php echo $valPois2;?> onclick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction;?>','<?php echo $numTab;?>','','n','','','','')"/> <span class="sscriteresgen<?php echo $ClassEnv;?>" >ne pas inclure les poissons<br/></span>
	<input class="sscriteresgen<?php echo $ClassEnv;?>" id="poisson3" type="radio" name="poissons1" value="1"  <?php echo $valPois3;?> onclick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction;?>','<?php echo $numTab;?>','','n','','','','')"/> <span class="sscriteresgen<?php echo $ClassEnv;?>">inclure les non poissons</span>
	<input class="sscriteresgen<?php echo $ClassEnv;?>" id="poisson4" type="radio" name="poissons1" value="np"  <?php echo $valPois4;?> onclick="runFilieresArt('<?php echo $typePeche;?>','<?php echo $typeAction;?>','<?php echo $numTab;?>','','n','','','','')"/> <span class="sscriteresgen<?php echo $ClassEnv;?>">ne pas inclure les non poissons</span>
</div>
<?php // l'onglet qui gere la selection des categories ecologiques ?>
<div id="cateco" class="cateco<?php echo $ceActive;?>">
<?php echo AfficheCategories("Ecologiques",$typeAction,$_SESSION['listeCatEco'],$changtAction,$typePeche,$numTab,$_SESSION['SQLEspeces'],$listePois); ?>
</div>
<?php // l'onglet qui gere la selection des categories trophiques ?>
<div id="cattroph" class="cattroph<?php echo $ctActive;?>">
<?php echo AfficheCategories("Trophiques",$typeAction,$_SESSION['listeCatTrop'],$changtAction,$typePeche,$numTab,$_SESSION['SQLEspeces'],$listePois); ?>
</div>
<?php // l'onglet qui gere la selection des colonnes complémentaires ?>
<div id="colonnes" class="colonnes<?php echo $colActive;?>">
<?php echo AfficheColonnes($typePeche,$typeAction,$codeTableEnCours,$numTab,$_SESSION['listeColonne']); ?>
</div>
<?php // l'onglet qui gere les espèces ?>
<div id="especes" class="especes<?php echo $espActive;?>">
<?php 
//echo "session = ".$_SESSION['SQLEspeces']." - liste espe = ".$listeEsp."<br/>";
echo AfficheEspeces($_SESSION['SQLEspeces'],$listeEsp,$changtAction,$typePeche,$typeAction,$numTab,"y",$_SESSION['listeCatEco'],$_SESSION['listeCatTrop'],$listePois); ?>
</div>
<?php // l'onglet qui gere les regroupements ?>
<div id="regroupesp" class="regroupesp<?php echo $regActive;?>">
<?php 
echo AfficheRegroupEsp($typePeche,$typeAction,$numTab,$_SESSION['SQLEspeces'],$_SESSION['listeRegroup'],$RegEncours,$CreerReg); ?>
</div>

</form>




