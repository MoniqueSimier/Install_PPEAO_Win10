<?php 
//*****************************************
// functions.php
//*****************************************
// Created by Yann Laurent
// 2009-06-30 : creation
//*****************************************
// Ce fichier contient une serie de fonctions php utilisées dans l'extraction des données
//*****************************************


//*********************************************************************
// recupereLibelleFiliere : Fonction pour recuperer le libelle de la filiere
function recupereLibelleFiliere($typeAction){
switch ($typeAction) {
	case "activite":
		$libelleAction = "Activit&eacute;";
		break;
	case "capture":
		$libelleAction = "Captures totales";
		break;
	case "NtPart":
		$libelleAction = "NtPt";
		break;
	case "taillart":
		$libelleAction = "Structure de taille";
		break;
	case "engin":
		$libelleAction = "Engin";
		break;
	default:
	$libelleAction = $typeAction;
		break;
}
return $libelleAction;
}


//*********************************************************************
// AfficheCategories : Fonction pour afficher les catégories troph / ecologiques a selectionner
function AfficheCategories($typeCategorie,$typeAction,$ListeCE,$changtAction,$typePeche,$numTab,$ListEsp,$ListPoiss) {
// Cette fonction permet de construire la liste des checkboxes pour la selection des especes à selectionner
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $typeCategorie : le type de catégorie, soit Ecologiques soit Trophiques
// $typeAction : La filiere en cours
// $ListeEsp : la liste des valeurs sélectionnées pour la categorie en cours
// $changtAction : est-ce qu'on vient juste de changer la selection ?
//*********************************************************************
// En sortie : 
// La fonction renvoie $construitSelection
//*********************************************************************

	// Pour construire les SQL (il faut d'abord avoir rempli ces champs !!!
	// donc avoir appele AfficherSelection
	// Données pour la selection 
	// Note si $changtAction = "y", alors on remet les choix par defaut, i.e. on coche toutes les valeurs.
	global $connectPPEAO;
	global $CRexecution;
	global $erreurProcess;
	$SQLEspeces	= $_SESSION['SQLEspeces'];
	$construitSelection = "";
	$listEspFamille = "";
	// Definition des differents parametres selon qu'on recupere les categories trop ou eco
	switch ($typeCategorie) {
		case "Ecologiques":
			$champID = "ref_categorie_ecologique_id";
			$table = "ref_categorie_ecologique";
			$libelleTable = "categorie ecologique";
			$nomInput = "CEco";
			break;
		case "Trophiques":
			$champID = "ref_categorie_trophique_id";
			$table = "ref_categorie_trophique";
			$libelleTable = "categorie trophique";
			$nomInput = "CTro";
			break;
	}
	// Selon le type de peches, la fonction Js n'est pas la meme.
	switch ($typePeche) {
		case "artisanale" :
		$runfilieres = "runFilieresArt";
		break;
		case "experimentale":
		$runfilieres = "runFilieresExp";
		break;
	 }
	// Definition du SQL pour trouver toutes les catégories trophiques des especes de la selection
	//  $SQLEspeces ne contient que la liste des ID des especes de la selection
	$SQLCEco = "select distinct(".$champID.") from ref_espece where id in (".$SQLEspeces.")";	
	//echo $SQLCEco."<br/>";
	//$SQLCEco = "select * from ref_espece";
	$SQLCEcoResult = pg_query($connectPPEAO,$SQLCEco);
	$erreurSQL = pg_last_error($connectPPEAO);
	if ( !$SQLCEcoResult ) {
		echo "erreur execution SQL pour ".$SQLCEco." erreur complete = ".$erreurSQL."<br/>";
	//erreur
	} else { 
		if (pg_num_rows($SQLCEcoResult) == 0) {
			// Erreur
			echo "pas d'especes trouvees dont le id est ".$SQLEspeces."<br/>" ;
		} else { 
			$cptInput = 1;
			$construitSelection .="<table id=\"".$nomInput."\"><tr><td class=\"catitem\">"; 
			// A faire : formater le resultat avec une table
			if (strpos($ListeCE,"tout") === false) {
				$construitSelection .= "&nbsp;<input id=\"".$nomInput.$cptInput."\" type=\"checkbox\"  name=\"".$nomInput."\" value=\"tout\"  onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','tout-".$nomInput."','','')\"/>&nbsp;<b>tout</b></td><td class=\"catitem\">";
			} else {
				$construitSelection .= "&nbsp;<input id=\"".$nomInput.$cptInput."\" type=\"checkbox\"  name=\"".$nomInput."\" value=\"tout\" checked=\"checked\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','aucun-".$nomInput."','','')\"/>&nbsp;<b>tout</b></td><td class=\"catitem\">";
			}
			// Analyse des categories disponibles pour l'espèce considérée
			while ($CERow = pg_fetch_row($SQLCEcoResult) ) {
				$ContinueTrt = false ;
				$cptInput ++;				
				if (!($CERow[0] =="" || $CERow[0] == null)) {
					// on récupère le libelle de la categorie ecologique
					$SQLlibelle = "select libelle from ".$table." where id = '".$CERow[0]."'";
					$SQLlibelleResult = pg_query($connectPPEAO,$SQLlibelle);
					$erreurSQL = pg_last_error($connectPPEAO);
					$libelleCE ="";
					
					if ( !$SQLCEcoResult ) {
						echo "erreur execution SQL pour ".$SQLlibelle." erreur complete = ".$erreurSQL."<br/>";
						$cptInput --;
					//erreur
					} else { 
						if (pg_num_rows($SQLlibelleResult) == 0) {
							// Erreur
							echo "pas de ".$libelleTable." trouvee pour id = ".$CERow[0]."<br/>";
							$cptInput --;
						} else {
							$libelleRow = pg_fetch_row($SQLlibelleResult)	;
							$libelleCE = $libelleRow[0];
							$ContinueTrt = true;
							$valCont = $CERow[0];
						}
						pg_free_result($SQLlibelleResult);
					}// fin du if ( !$SQLtestResult )	
				} else { 
					$valCont = "";
					$libelleCE = "Vide";
					if ($CERow[0] == null) {
						$libelleCE = "Null";
						$valCont = "null";
					}
					$ContinueTrt = true;
				}	// fin du if (!($CERow[0] =="" || $CERow[0] == null))
				if ($ContinueTrt) {
					// Si on est en train de changer d'action, on remet à zéro
					if ($changtAction =="y" || strpos($ListeCE,"toutX") > 0  ) {
						$checked ="checked=\"checked\"";
					} else {
						// On teste si la valeur a déjà été saisie par l'utilisateur.
						if ($ListeCE == "") {
							$checked =""; 
						} else {
							if (strpos($ListeCE,$valCont) === false || (strpos($ListeCE,"pasttX") > 0 )) {
								$checked =""; 
							} else {
								$checked ="checked=\"checked\"";
							}
						}
					}
					$construitSelection .= "&nbsp;<input id=\"".$nomInput.$cptInput."\" type=\"checkbox\"  name=\"".$nomInput."\" value=\"".$valCont."\" ".$checked." />&nbsp;".$libelleCE;
					// C'est super moche c'est juste pour tester la validiter de la chose, a modifier pour faire quelque chose de mieux
					$str_cptInput = strval($cptInput);
					if (fmod($str_cptInput,'3') == '0') {
						$construitSelection .= "</td></tr><tr><td class=\"catitem\">";
					} else {
						$construitSelection .= "</td><td class=\"catitem\">";
					}
				} // fin du if ($ContinueTrt)
			} // fin du while
			$construitSelection .="</td></tr></table>";
			$construitSelection .= "<input id=\"num".$nomInput."\" type=\"hidden\" name=\"num".$nomInput."\" value=\"".$cptInput ."\"/>";
		}
	}	
	pg_free_result($SQLCEcoResult);
	return $construitSelection;
}

//*********************************************************************
// AfficheEspeces : Fonction pour afficher les especes a selectionner 
function AfficheEspeces($SQLEspeces,$ListeEsp,$changtAction,$typePeche,$typeAction,$numTab,$regroup,$ListCatE,$ListCatT,$ListPoiss) {
// Cette fonction permet de construire la liste des checkboxes pour la selection des especes à selectionner
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $SQLEspeces : la liste des especes issues de la sélection initiale (du module précédent)
// $ListeEsp : la liste des especes sélectionnées
// $changtAction : est-ce qu'on vient juste de changer la selection ? y/n
// $regroup : est qu'on gere le regroupement d'especes ? y/n
//*********************************************************************
// En sortie : 
// La fonction renvoie $construitSelection
//*********************************************************************
	global $connectPPEAO;
	global $EcrireLogComp;
	global $logComp;
	global $pasdefichier;
	$construitSelection = "";
	$listeSelectEsp = "";
	
	if ($SQLEspeces == "") {
		echo "erreur SQLEspeces vide dans la fonction AfficheEspeces<br/>Arret du traitement<br/>.";
		exit;
	}
	// Selon le type de peches, la fonction Js n'est pas la meme.
	switch ($typePeche) {
		case "artisanale" :
			$runfilieres = "runFilieresArt";
			break;
		case "experimentale":
			$runfilieres = "runFilieresExp";
			break;
		case "agglomeration":
			$runfilieres = "runFilieresStat";
			break;
		case "generales":
			$runfilieres = "runFilieresStat";
			break;
	 }
	 // Attention, warning dans le cas ou un ou plusieures regroupements existent.
	 if (isset($_SESSION['listeRegroup']) && (!($_SESSION['listeRegroup']=="")) ) {
		 //if (isset($_SESSION['listeRegroup'])) {
		$construitSelection .= "<span id=\"EspInfo\"><img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>Des regroupements ont d&eacute;j&agrave; &eacute;t&eacute; cr&eacute;&eacute;s. Toute modification de la liste des esp&egrave;ces risque de g&eacute;n&eacute;rer des erreurs.</span><br/><br/>";
	}
// Gere l'affichage des différentes espèces
	$SQLCEco = "select id,libelle,ref_famille_id,ref_categorie_trophique_id,ref_categorie_ecologique_id from ref_espece where id in (".$SQLEspeces.") order by libelle";	
	$SQLCEcoResult = pg_query($connectPPEAO,$SQLCEco);
	$erreurSQL = pg_last_error($connectPPEAO);
	if ( !$SQLCEcoResult ) {
		echo "erreur execution SQL pour ".$SQLCEco." erreur complete = ".$erreurSQL."<br/>";
	//erreur
	} else { 
		if (pg_num_rows($SQLCEcoResult) == 0) {
			// Erreur
			echo "pas d'especes trouvees dont le id est ".$SQLEspeces."<br/>" ;
		} else { 
			$cptInput = 1;
			$cptEsptotal = 0;
			$construitSelection .="<table id=\"espece\"><tr><td>"; 
			if (strpos($ListeEsp,"XtoutX") === false) {
				$construitSelection .= "&nbsp;<input id=\"Esp".$cptInput."\" type=\"checkbox\"  name=\"Esp\" value=\"XtoutX\"  onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','tout','')\"/>&nbsp;<b>tout</b></td>";
			} else {
				$construitSelection .= "&nbsp;<input id=\"Esp".$cptInput."\" type=\"checkbox\"  name=\"Esp\" value=\"XtoutX\" checked=\"checked\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','aucun','')\"/>&nbsp;<b>tout</b></td>";
			}
			//echo $ListPoiss." - ".$ListCatT." - ".$ListCatE." <br/>";
			while ($CERow = pg_fetch_row($SQLCEcoResult) ) {
				if (!($CERow[0] =="" || $CERow[0] == null)) {
					$cptEsptotal ++;
					$non_poisson =-1;
					// Test sur le critere poisson - non poisson
					$SQLFam = "select id,non_poisson from ref_famille where id = ".$CERow[2] ;	
					$SQLFamResult = pg_query($connectPPEAO,$SQLFam);
					$erreurSQL = pg_last_error($connectPPEAO);
					$addClass = " grise";
					$checked ="";
					if ( !$SQLFamResult ) {
						echo "erreur execution SQL pour ".$SQLFam." erreur complete = ".$erreurSQL."<br/>";
					//erreur
					} else { 
						if (pg_num_rows($SQLFamResult) == 0) {
							// Erreur
							echo "pas de famille trouvee pour l'espece ".$CERow[0]." / id famille = ".$CERow[2]."<br/>" ;
						} else { 
							$famRow = pg_fetch_row($SQLFamResult)	;
							$non_poisson = $famRow[1];
						}
					}
					$TestPoisson = ExclureSelectionPoisson($non_poisson,$ListPoiss);
					$TestCatT = ExclureCategorie($CERow[3],$ListCatT);					
					$TestCatE = ExclureCategorie($CERow[4],$ListCatE);
					// Si on est en train de changer d'action, on remet à zéro
					if ($changtAction =="y" || strpos($ListeEsp,"toutX") > 0  ){
						$checked ="checked=\"checked\"";
					} else {
						// On teste si la valeur a déjà été saisie par l'utilisateur.
						if ($ListeEsp == "") {
							$checked ="checked=\"checked\""; 
						} else {
							if (strpos($ListeEsp,$CERow[0]) === false || (strpos($ListeEsp,"pasttX") > 0 )) {
								$checked =""; 
							} else {
								$checked ="checked=\"checked\"";
							}
						}
					}
					//echo syntheseTestExclusionEsp($TestPoisson,$TestCatE,$TestCatT)." catT = ".$CERow[3]."/ catE = ".$CERow[4]."<br/>";
					if (syntheseTestExclusionEsp($TestPoisson,$TestCatE,$TestCatT) == "ok") {
						$cptInput ++;
						$libelleEsp = $CERow[1];
						$construitSelection .= "<td>&nbsp;<input  id=\"Esp".$cptInput."\" type=\"checkbox\"  name=\"Esp\" value=\"".$CERow[0]."\" ".$checked."/>&nbsp;".$libelleEsp;
						// C'est super moche c'est juste pour tester la validiter de la chose, a modifier pour faire quelque chose de mieux
						$str_cptInput = strval($cptInput);
						if (fmod($str_cptInput,'3') == '0') {
							$construitSelection .= "</td></tr><tr>";
						} else {
							$construitSelection .= "</td>";
						}
					}
				}
			} // fin du while
			
			$construitSelection .="</td></tr></table>";
			$construitSelection = "<span class=\"hint small\">".($cptInput-1)." Esp&egrave;ces disponibles (sur ".$cptEsptotal." totales pour la s&eacute;lection en cours)</span>".$construitSelection;
			$construitSelection .= "<input id=\"numEsp\" type=\"hidden\" name=\"numEsp\" value=\"".$cptInput."\"/>";
		}
		pg_free_result($SQLCEcoResult);
	}	
	
	return $construitSelection;
}

//*********************************************************************
// ExclureSelectionPoisson : Fonction de test des valeurs non-poissons par rapport a la valeur selectionnée
function ExclureSelectionPoisson($valeurATester,$listePoisson) {
	$ValeurRetournee = "ok";
	if ($listePoisson=="") {
		$ValeurRetournee = "ok";
	} else {
		// Si listepoisson = "0,1", on prend tout...
		if (!($listePoisson =="0,1")) {
			$valPoisson = explode(",",$listePoisson);
			$nbPoisson = count($valPoisson) - 1;
			$StopTest = false;
			for ($cptPoi=0 ; $cptPoi<=$nbPoisson;$cptPoi++) {
				switch ($valPoisson[$cptPoi]) {
					case "0":
						if  ($valeurATester == "1") {
							$ValeurRetournee = "ko";
							$StopTest = true;
						}
						break;
					case "1":
						if  ($valeurATester == "0") {
							$ValeurRetournee = "ko";
							$StopTest = true;
						}
						break;
					case "np":
						if  ($valeurATester == "1") {
							$ValeurRetournee = "ko";
							$StopTest = true;
						}
						break;
					case "pp":
						if  ($valeurATester == "0") {
							$ValeurRetournee = "ko";
							$StopTest = true;
						}
						break;			
				}
				if ($StopTest) {break;}
			}
		}
	}
	//echo $valeurATester." - ".$listePoisson." - ".$ValeurRetournee."<br/>";
	return $ValeurRetournee;
}

//*********************************************************************
// ExclureCategorie : Fonction de test des valeurs categories eco ou troph par rapport a la valeur selectionnée
function ExclureCategorie($valeurATester,$ListCat) {
	$ValeurRetournee = "ko";
	if ($ListCat=="") {
		$ValeurRetournee = "ok";
	} else {
		if ($valeurATester == "") {
			if (strpos($ListCat,"null") === false) {
			
			} else {
				$ValeurRetournee = "ok";
			}
		} else {
			$valCat = explode(",",$ListCat);
			$nbCat = count($valCat) - 1;
			$StopTest = false;
			for ($cptCat=0 ; $cptCat<=$nbCat;$cptCat++) {
				if ($valCat[$cptCat] == $valeurATester) {
					$ValeurRetournee = "ok";
					$StopTest = true;
				}
				if ($StopTest) {break;}
			}
		}
	}
	return $ValeurRetournee;
}

//*********************************************************************
// syntheseTestExclusionEsp : Fonction de synthes des 3 test poisson / catE / CatT
function syntheseTestExclusionEsp($TestPoisson,$TestCatE,$TestCatT) {
	$ValeurRetournee = "ok";
	if ($TestPoisson == "ko") {$ValeurRetournee = "ko";}
	if ($TestCatE == "ko") {$ValeurRetournee = "ko";}
	if ($TestCatT == "ko") {$ValeurRetournee = "ko";}
	return $ValeurRetournee;	
}

//*********************************************************************
// AfficheColonnes : Fonction pour afficher les tables / colonnes a selectionner par type de peche
function AfficheRegroupEsp($typePeche,$typeAction,$numTab,$SQLEspeces,$RegroupEsp,$RegEncours,$CreeReg) {
// Cette fonction permet de gerer les regroupements d'especes
// On crée un variable de session contenant un tableau multidimensionnel
// pour un regroupement, la colonne O contient le code et le libelle (separé par &#&), et enfin, les colonnes >1 contiennent
// les especes pour ce regroupement
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $SQLespeces : le SQL contenant les especes sélectionnées
//*********************************************************************
// En sortie : 
// La fonction renvoie $tableau
//*********************************************************************
	global $connectPPEAO;
	global $EcrireLogComp;
	global $logComp;
	global $pasdefichier;
	global $RegroupPourFic;
	if (!(isset($_SESSION['listeRegroup']))) {
		$_SESSION['listeRegroup'] = "";	
	}
	$ulrComp="";
	$info = "";
	// Reinitialisation des variables d'affichage
	$OptionRegroup = "";
	$labelRegroup = "";
	$OptionEspDispo = "";
	$nouveauRegroupement = "";
	$OptionRegroupCont = "";
	$labelListeRegroupt = "";
	$construitSelection = "<b>g&eacute;rez les regroupements d'esp&egrave;ces</b><br/>";
	if ($RegEncours == "" && (!($_SESSION['listeRegroup'] ==""))) {
		$RegEncours = 1;					  
	}
	// Selon le type de peches, la fonction Js n'est pas la meme.
	//$tableaudebug = print_r($_SESSION['listeRegroup']);
	switch ($typePeche) {
		case "artisanale" :
		$runfilieres = "runFilieresArt";
		break;
		case "agglomeration":
		$runfilieres = "runFilieresStat";
		break;
		case "generales":
		$runfilieres = "runFilieresStat";
		break;
	 }
	// *******************************
	// Gestion des différentes actions
	// *******************************
	//echo count($_SESSION['listeRegroup'])." ".$RegEncours."<br/>";
	// Reinitialisation des regroupements
	// Ou suppression d'un regroupement
	if (isset($_GET['suppReg'])) {
		switch ($_GET['suppReg']) {
			case "tout":
				unset($_SESSION['listeRegroup']);
				$_SESSION['listeRegroup'] = "";
				$info ="tous les regroupements ont &eacute;t&eacute; supprim&eacute;s";	
				break;
			case "EC":
				$infoReg = explode("&#&",$_SESSION['listeRegroup'][$RegEncours][1]);
				$nomRegSupp = $infoReg[1];
				unset($_SESSION['listeRegroup'][$RegEncours]);
				//$_SESSION['listeRegroup'] = "";
				$info ="regroupement ".$nomRegSupp." supprim&eacute;";
				$RegEncours = $RegEncours - 1;
				break;
		}
	}

	// Reinitialisation des especes pour un regroupement
	// Ou suppression d'une espece pour un regroupement
	if (isset($_GET['suppEsp'])) {
		switch ($_GET['suppEsp']) {
			case "tout":
					$nbListEsp = count($_SESSION['listeRegroup'][$RegEncours]);
					for ($cptEsp=2 ; $cptEsp<=$nbListEsp;$cptEsp++) {
						unset($_SESSION['listeRegroup'][$RegEncours][$cptEsp]);
					}
					// On reindexe le tableau.
					reset($_SESSION['listeRegroup'][$RegEncours]);
					$infoReg = explode("&#&",$_SESSION['listeRegroup'][$RegEncours][1]);
					$info ="toutes les esp&egrave;ces ont &eacute;t&eacute; supprim&eacute;es du regroupement ".$infoReg[1];
				break;
			case "EC":
			// Ca ne fonctionne pas comme ca devrait, il reste des blancs dans le tableau.
			// Pour l'instant, pas accessible
				if (isset($_GET['espasup'])) {
					$espVraimentSup = "";
					$EspAsupp = $_GET['espasup'];
					//echo "liste a supp = ".$EspAsupp."<br/>";
					$nbListEsp = count($_SESSION['listeRegroup'][$RegEncours]);
					for ($cptEsp=2 ; $cptEsp<=$nbListEsp;$cptEsp++) {
						//echo $nbListEsp." ".$cptEsp." ".$EspAsupp." ".$_SESSION['listeRegroup'][$RegEncours][$cptEsp]."<br/>";
						$infoEsp = explode("&#&",$_SESSION['listeRegroup'][$RegEncours][$cptEsp]);
						if (strpos($EspAsupp,$infoEsp[0]) === false) {
						} else {
							$_SESSION['listeRegroup'][$RegEncours][$cptEsp]="";
							//unset($_SESSION['listeRegroup'][$RegEncours][$cptEsp]); doesn't work as wanted..
							$espVraimentSup .= ",".$_SESSION['listeRegroup'][$RegEncours][$cptEsp];
						}
					}
					// On reindexe le tableau.
					reset($_SESSION['listeRegroup'][$RegEncours]);
					$infoReg = explode("&#&",$_SESSION['listeRegroup'][$RegEncours][1]);
					$info ="les esp&egrave;ces ".$espVraimentSup." ont &eacute;t&eacute; supprim&eacute;es du regroupement ".$infoReg[1];			
				} 
				break;
		}
	}
	// Gestion de l'ajout d'espèces dans un groupe
	// Ou création d'un groupe pour cette espece unique (garder=y)
	$garderEsp = "";
	if (isset($_GET['garder'])) {
		$garderEsp =$_GET['garder'];
	}
	if (isset($_GET['affEsp'])) {
		if( $_GET['affEsp']=="y") {
			if (isset($_GET['espAff'])) {
				$EspAAffecter = $_GET['espAff'];
				//echo "liste a ajouter = ".$EspAAffecter."<br/>";
				$ListeEsp = explode(",",$EspAAffecter);
				$nbListEsp = count($ListeEsp);	
				if (!($_SESSION['listeRegroup'] == "")) {
					$derEsp = intval(count($_SESSION['listeRegroup'][$RegEncours]))-1;
				} else {
					$derEsp = 1;
				}
				for ($cptEsp=0 ; $cptEsp<$nbListEsp;$cptEsp++) {
					$SQLReg = "select id,libelle from ref_espece where id = '".$ListeEsp[$cptEsp]."'";	
					$SQLRegResult = pg_query($connectPPEAO,$SQLReg);
					$erreurSQL = pg_last_error($connectPPEAO);
					if ( !$SQLRegResult ) {
						echo "erreur execution SQL pour ".$SQLReg." erreur complete = ".$erreurSQL."<br/>";
					//erreur
					} else { 
						if (pg_num_rows($SQLRegResult) == 0) {

						} else { 
							// On n'a qu'une seule ligne en résultat.
							$RegRow = pg_fetch_row($SQLRegResult);
							if ($garderEsp == "") {
								// On ajoute les especes au regroupement sélectionné						
								$rangEsp = intval($cptEsp+2+$derEsp); // le + 2 indique qu'on commence au rang 1 et que le rang 1 est déjà pris par le nom du regroupement
								$_SESSION['listeRegroup'][$RegEncours][$rangEsp] = $ListeEsp[$cptEsp]."&#&".$RegRow[1];
								$infoReg = explode("&#&",$_SESSION['listeRegroup'][$RegEncours][1]);
								$info ="les esp&egrave;ces ".$EspAAffecter." ont &eacute;t&eacute; ajout&eacute;es au regroupement ".$infoReg[1];
							} else {
								// On crée un regroupement par espece. On récupère le libellé
								if (!($_SESSION['listeRegroup'] == "")) {
									$rangNvReg = count($_SESSION['listeRegroup']) + 1;
								} else {
									$rangNvReg = 1;
								}
								$_SESSION['listeRegroup'][$rangNvReg][1]=$RegRow[0]."&#&".$RegRow[1];
								$_SESSION['listeRegroup'][$rangNvReg][2]=$RegRow[0]."&#&".$RegRow[1];
								$info .="Regroupement espece unique n&deg;".$rangNvReg." ".$RegRow[1]." (".$RegRow[0].") ajout&eacute;<br/>";
								$RegEncours = $rangNvReg;
							}
						}
						pg_free_result($SQLRegResult);
					}
				}
			} 
		}
	}
	// Gestion de la création d'un nouveau regroupement
	switch ($CreeReg) {
		case "y" : 
			$ulrComp="&nvReg=f";
			$gardLib = "";
			$nvNomReg = "";
			$nvCodeReg = "";
			if (isset($_GET['gard'])) {
				// On a déjà travaillé sur ce regroupement, on a voulu garder le libelle, on le precharge
				if (isset($_GET['nomReg'])) {
					$gardLib = $_GET['gard'];
					$nvNomReg = $_GET['nomReg'];
				}
			}
			if (isset($_GET['codeEC'])) {
				// On a déjà travaillé sur ce regroupement, le libelle etait vide, on precharge le code deja saisi
				$nvCodeReg = $_GET['codeEC'];
			}
			$nouveauRegroupement = "<b>cr&eacute;er un nouveau regroupement</b>
			<table id=\"CreeReg\">
				<tr><td>code&nbsp;</td><td><input id=\"codeReg\" title=\"code du regroupement\" type=\"textbox\" maxlength=\"3\" size=\"3\" value=\"".$nvCodeReg."\"/></td><tr>
				<tr><td>nom&nbsp;&nbsp;</td><td><input id=\"nomReg\" type=\"textbox\" title=\"nom du regroupement\" value=\"".$nvNomReg."\"/></td><tr>
				<tr><td colspan=\"2\">";
			$nouveauRegroupement .= "<a href=\"#\" class=\"lienReg\" onClick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','".$ulrComp."')\">cr&eacute;er regroupement</a> - <a href=\"#\" class=\"lienReg\" onClick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','')\">annuler</a></td></tr>
			</table>";
			break;
		case "f" : 
			// Creation effective du nouveau regroupement
			if (isset($_GET['nomReg'])) {
				if (isset($_GET['gard'])) {
					$gardLib = $_GET['gard'];
				}else {
					$gardLib = "";
				}
				$ajoutRegOK = true;
				$nvNomReg = $_GET['nomReg'];
				$nvCodeReg = strtoupper ($_GET['codeReg']);
				if (!($_SESSION['listeRegroup'] =="" )) {
					// on controle que le groupe n'existe pas deja dans les regroupements déjà saisis.
					$NbReg = count($_SESSION['listeRegroup']);
					for ($cptR=1 ; $cptR<=$NbReg;$cptR++) {
						$infoReg = explode("&#&",$_SESSION['listeRegroup'][$cptR][1]);
						if ($infoReg[0] == $nvCodeReg) {
							$info .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/> le groupe en cours d'ajout ".$nvNomReg." (code = <b>".$nvCodeReg."</b>) existe d&eacute;j&agrave; ! Merci d'utiliser un autre code<br/>";
							$ajoutRegOK = false;
							break;
						}
					}
					if ($ajoutRegOK) {
						// On contrôle que le regroupement n'existe pas deja dans les especes. Si oui, proposer le meme label.
						// Si refus d'accepter le meme label, proposer la saisie d'un autre.						
						$SQLReg = "select id,libelle from ref_espece where id = '".$nvCodeReg."'";	
						$SQLRegResult = pg_query($connectPPEAO,$SQLReg);
						$erreurSQL = pg_last_error($connectPPEAO);
						if ( !$SQLRegResult ) {
							echo "erreur execution SQL pour ".$SQLReg." erreur complete = ".$erreurSQL."<br/>";
						//erreur
						} else { 
							if (pg_num_rows($SQLRegResult) == 0) {

							} else { 
							// On n'a qu'une seule ligne en résultat.
								$RegRow = pg_fetch_row($SQLRegResult);
								if (!(trim($RegRow[1]) == trim($nvNomReg))) {
									if (!($gardLib == "y")) {
										// On controle le libelle
										$ajoutRegOK = false;
										$info .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/> un groupe existe dans la base des esp&egrave;ces dont le libell&eacute; est :<b>".$RegRow[1]."</b>.<br/>";
										$info .="Voulez-vous garder ce libell&eacute; ?<input id=\"codeReg\" type=\"hidden\" value=\"".$RegRow[0]."\"/><input id=\"nomReg\" type=\"hidden\" value=\"".$nvNomReg."\"/>(<a href=\"#\" class=\"lienReg\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&nvReg=f&gard=y')\">[Oui]</a>&nbsp;<a href=\"#\" class=\"lienReg\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&nvReg=y&gard=n')\">[Non]</a>)";
										$info .="Si non, merci de resaisir le regroupement avec un autre code.<br/>";
									} else {
										// On garde le libellé
										$nvNomReg = $RegRow[1];
									}
								}
							}
							pg_free_result($SQLRegResult);
						}
						if ($ajoutRegOK) {
							$rangNvReg = count($_SESSION['listeRegroup']) + 1;
							$_SESSION['listeRegroup'][$rangNvReg][1]=$nvCodeReg."&#&".$nvNomReg;
							$info .="Regroupement numero ".$rangNvReg." ".$nvNomReg." (".$nvCodeReg.") ajout&eacute;<br/>";
							$RegEncours = $rangNvReg;
						}
					}
				} else {
					$ajoutRegOK = true;
					// On contrôle que le regroupement n'existe pas deja dans les especes. Si oui, proposer le meme label.
					// Si refus d'accepter le meme label, proposer la saisie d'un autre.						
					$SQLReg = "select id,libelle from ref_espece where id = '".$nvCodeReg."'";	
					$SQLRegResult = pg_query($connectPPEAO,$SQLReg);
					$erreurSQL = pg_last_error($connectPPEAO);
					if ( !$SQLRegResult ) {
						echo "erreur execution SQL pour ".$SQLReg." erreur complete = ".$erreurSQL."<br/>";
					//erreur
					} else { 
						if (pg_num_rows($SQLRegResult) == 0) {

						} else { 
						// On n'a qu'une seule ligne en résultat.
							$RegRow = pg_fetch_row($SQLRegResult);
							if (!(trim($RegRow[1]) == trim($nvNomReg))) {
								if (!($gardLib == "y")) {
									// On controle le libelle
									$ajoutRegOK = false;
									$info .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/> un groupe existe dans la base des esp&egrave;ces dont le libell&eacute; est :<b>".$RegRow[1]."</b>.<br/>";
									$info .="Voulez-vous garder ce libell&eacute; ?<input id=\"codeReg\" type=\"hidden\" value=\"".$RegRow[0]."\"/><input id=\"nomReg\" type=\"hidden\" value=\"".$nvNomReg."\"/>(<a href=\"#\" class=\"lienReg\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&nvReg=f&gard=y')\">[Oui]</a>&nbsp;<a href=\"#\" class=\"lienReg\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&nvReg=y&gard=n')\">[Non]</a>)";
									$info .="Si non, merci de resaisir le regroupement avec un autre code.<br/>";
								} else {
									// On garde le libellé
									$nvNomReg = $RegRow[1];
								}
							}
						}
						pg_free_result($SQLRegResult);
					}
					if ($ajoutRegOK) {
						$_SESSION['listeRegroup'][1][1]=$nvCodeReg."&#&".$nvNomReg;
						$RegEncours = 1;
						$info .="Regroupement num&eacute;ro 1 ".$nvNomReg." (".$nvCodeReg.") ajout&eacute;<br/>";
					}
				}
			} else {
				$info .= "erreur saisie nom <br/>";
			}
			break;
	}
	// Fin des différentes actions
	// On construit les différentes options
	// **** contruction du select pour les espèces disponibles à la sélection.
	$labelEspDispo = "esp&egrave;ces disponible &agrave; la s&eacute;lection";
	$libGarderEsp = ""; // permet de gérer des boutons pour selectionner des especes et en créer directement des groupes
	// Analayse des restrictions possibles sur le choix des especes
	if (!($_SESSION['listeEspeces'] == "")) {
		$TempSQLEspeces = $SQLEspeces;
		$SQLEspeces = "";
		$EspecesSele = explode (",",$_SESSION['listeEspeces']);
		$NumEsp = count($EspecesSele) - 1;
		for ($cptES=0 ; $cptES<=$NumEsp;$cptES++) {
			if (strpos($TempSQLEspeces,$EspecesSele[$cptES]) === false ){
	
			} else {
				// La valeur est disponible, on la met à jour
				if ($SQLEspeces == "" ) {
					$SQLEspeces = "'".$EspecesSele[$cptES]."'";
				} else {
					$SQLEspeces .= ",'".$EspecesSele[$cptES]."'";
				}
			}
		}
	} 
	$SQLReg = "select id,libelle from ref_espece where id in (".$SQLEspeces.") order by libelle";	
	$SQLRegResult = pg_query($connectPPEAO,$SQLReg);
	$erreurSQL = pg_last_error($connectPPEAO);
	if ( !$SQLRegResult ) {
		echo "erreur execution SQL pour ".$SQLReg." erreur complete = ".$erreurSQL."<br/>";
	//erreur
	} else { 
		if (pg_num_rows($SQLRegResult) == 0) {
			// Erreur
			echo "pas d'especes trouvees dont le id est ".$SQLEspeces."<br/>" ;
		} else { 
			$cptEsp = 0;
			while ($RegRow = pg_fetch_row($SQLRegResult) ) {
				if ($_SESSION['listeRegroup'] == "" ) {
					if (!($RegRow[0] =="" || $RegRow[0] == null)) {
						$OptionEspDispo .= "<option value=\"".$RegRow[0]."\">".$RegRow[1]."</option>";
						$cptEsp ++;
					} 
				} else {
					$pasAjoutEsp = false;
					// On regarde si l'espece est déja dans un groupe. Si oui, on ne l'affiche pas.
					$NbReg = count($_SESSION['listeRegroup']);
					for ($cptR=1 ; $cptR<=$NbReg;$cptR++) {
						$NbReg2 = count($_SESSION['listeRegroup'][$cptR]);
						for ($cptR2=2 ; $cptR2<=$NbReg2;$cptR2++) {
							$infoEsp = explode("&#&",$_SESSION['listeRegroup'][$cptR][$cptR2]);
							//echo $infoEsp[0]." - ".$RegRow[0]."<br/>";
							if ($infoEsp[0] == $RegRow[0]) {
								$pasAjoutEsp = true;
							}
						}
					}
					if (!($pasAjoutEsp)) {
						$OptionEspDispo .= "<option value=\"".$RegRow[0]."\">".$RegRow[1]."</option>";
						$cptEsp ++;
					}
				}
			}
			$libGarderEsp = "<a href=\"#\" class=\"lienReg\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&affEsp=y&garder=y')\" title=\"garder les esp&egrave;ces s&eacute;lectionn&eacute;es comme groupe\">garder ces esp&egrave;ces </a>";
		}
	pg_free_result($SQLRegResult);		
	}
	// Fin de la liste des especes disponible à la sélection
	
	// **** contruction du select pour les regroupements disponibles.
	if ($_SESSION['listeRegroup'] == "" ) {
		$NbReg = 0;
		$labelRegroup = "aucun regroupement cr&eacute;&eacute;";
	} else {
		$NbReg = count($_SESSION['listeRegroup']);
		$labelRegroup = $NbReg." regroupements disponibles";
	}
	// Le onlclick sur le regroupement permet d'afficher les especes de ce regroupement
	$OptionRegroup ="liste des regroupements<br/><select id=\"Regroupement\" class=\"level_select\" size=\"10\" style=\"min-width: 10em;\" name=\"Regroupement\"> \">";
	// Remplissage de la liste des regroupements
	if ($NbReg > 0) {
		for ($cptR=1 ; $cptR<=$NbReg;$cptR++) {
			if ($RegEncours == $cptR) {
				$selected = "selected =\"selected\"";
			} else {
				$selected = "";
			}
			$infoReg = explode("&#&",$_SESSION['listeRegroup'][$cptR][1]);
			$OptionRegroup .= "<option value=\"".$cptR."\" ".$selected." onClick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&regRec=change') \">".$infoReg[1]."</option>";
		}
	} else {
		$OptionRegroup .= "<option disabled=\"disabled\">pas de regroupement disponible</option>";
	}
	$OptionRegroup .="</select><br/>";
	// Ajout des options de création / suppression
	$OptionRegroup .=$labelRegroup."<br/><a href=\"#\" class=\"lienReg\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&nvReg=y')\" title=\"ajouter un regroupement\">ajouter</a> - <a href=\"#\" class=\"lienReg\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&suppReg=EC')\" title=\"supprimer le regroupement\">supprimer </a> <br/><a href=\"#\" class=\"lienReg\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&suppReg=tout')\" title=\"supprimer tous les regroupements\">supprimer tous les regroupements</a> <br/>";
	
	// **** contruction du select pour afficher le contenu du regroupement en cours.	
	$selectionComp="";
	$OptionRegroupCont ="liste des esp&egrave;ces/regroupement<br/><select id=\"Regroupcontenu\" class=\"level_select\" multiple=\"multiple\" style=\"min-width: 10em;\" size=\"10\" name=\"Regroupcontenu\">";
	$labelListeRegroupt="";
	// Remplissage des especes pour ce groupement
	if ($NbReg > 0 ) {
		for ($cptR=1 ; $cptR<=$NbReg;$cptR++) {
			if ($RegEncours == $cptR) {
				$NbReg2 = count($_SESSION['listeRegroup'][$cptR]);
		
				if ($NbReg2 >=2) {
					for ($cptR2=2 ; $cptR2<=$NbReg2;$cptR2++) {
						$nbrEspeceReg = $cptR2- 1;
						$infoEsp = explode("&#&",$_SESSION['listeRegroup'][$cptR][$cptR2]);
						$OptionRegroupCont .= "<option value=\"".$infoEsp[0]."\">".$infoEsp[1]."</option>";
					}
					//$selectionComp = "<br/>".$nbrEspeceReg." esp&egrave;ces pour le regroupement s&eacute;lectionn&eacute<br/> supprimer : <a href=\"#\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&suppEsp=EC')\" title=\"supprimer l'esp&egrave;ce s&eacute;lectionn&eacute;e\">s&eacute;lection</a> - <a href=\"#\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&suppEsp=tout')\" title=\"supprimer toutes les esp&egrave;ces\">tout</a> <br/>";
					$selectionComp = "<br/>".$nbrEspeceReg." esp&egrave;ces pour le regroupement s&eacute;lectionn&eacute<br/><a href=\"#\" class=\"lienReg\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&suppEsp=tout')\" title=\"supprimer toutes les esp&egrave;ces\">supprimer toutes les esp&egrave;ces</a> <br/>";
					break;
				} else {
					$OptionRegroupCont .= "<option disabled=\"disabled\">pas d'esp&egrave;ces associ&eacute;es</option>";
					$selectionComp = "<br/>Pas d'esp&egrave;ces pour ce regroupement";
					$info .="S&eacute;lectionnez une esp&egrave;ce dans la troisi&egrave;me colonne et cliquez sur <-- pour l'affecter &agrave; ce regroupement.";
					break;
				}
			}
		}	
	} else {
		$OptionRegroupCont .= "<option disabled=\"disabled\">pas d'esp&egrave;ces associ&eacute;es</option>";
	}
	$OptionRegroupCont .="</select>".$selectionComp;
	
	// **** Fin de la gestion de la liste des regroupements.
	// Gestion des icones (quand il y en aura) pour deplacer une especes dans un regroupement ou l'enlever
	if (!($info == "")) { 
		$info = "<span id=\"infoSuppReg\">".$info."</span>";
	}
	if ($_SESSION['listeRegroup'] =="" ) {
		$AffAffection="";
	} else {
		//$AffAffection="<div id=\"gereAffectation\" class=\"level_div\"><br/><br/><br/><a href=\"#\" class=\"lienReg2\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&affEsp=y')\" title=\"ajouter l'esp&egrave;ce au regroupement\"\><--</a><br/><br/><br/><a href=\"#\" class=\"lienReg2\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&suppEsp=EC')\" title=\"supprimer l'esp&egrave;ce du regroupement\"\>--></a> </div>";
		$AffAffection="<div id=\"gereAffectation\" class=\"level_div\"><br/><br/><br/><a href=\"#\" class=\"lienReg2\" onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','','','','&affEsp=y')\" title=\"ajouter l'esp&egrave;ce au regroupement\"\><--</a><br/><br/><br/></div>";
	}
	// Enfin derniere etape,
	// On construit l'affichage
	// Les trois premiers div sont dans le meme bloc
	// Premier div : contient la liste des especes disponibles
	$AffListeEspecesDispo = "<div id=\"listeEspece\" class=\"reg_div\">".$labelEspDispo."<br/>
							<select id=\"especesDispo\" class=\"reg_select\" multiple=\"multiple\" size=\"10\" name=\"especesDispo\">
							".$OptionEspDispo."</select><br/>".$cptEsp." esp&egrave;ces disponibles <br/>".$libGarderEsp."</div>";
	// Deuxième div : contient la liste des regroupements
	$AffListeRegroup = "<div id=\"Regroupt\" class=\"reg_div\">".$OptionRegroup."</div>" ;
	// Troisieme div : contient la liste des especes pour le regroupement
	$AffListeRegroupCont = "<div id=\"listeRegroupt\" class=\"reg_div\">".$OptionRegroupCont."</div>" ;
	// Construction de la ligne contenant les 3 divs (on peut changer l'ordre sans impacter sur la structure de chacun des div
	if ($_SESSION['listeRegroup'] =="") {
		$infoDummies = "<span class=\"hint clear small\">Si vous n'avez jamais utilis&eacute; cette interface, vous pouvez vous r&eacute;ferer &agrave; l'aide en bas de page</span>";
	} else {
		$infoDummies = "";	
	}
	$construitSelection .= $infoDummies."<br/>".$AffListeRegroup.$AffListeRegroupCont.$AffAffection.$AffListeEspecesDispo;
	// Ligne suivante : affichage de la zone de travail et/ou des messages
	if ( (!($info == "")) || (!( $nouveauRegroupement=="") )) {
		$construitSelection .= "<div id=\"Reginfo\" class=\"clear \"><span id=\"Reginfogen\">".$info."</span><span id=\"zonetrav\">".$nouveauRegroupement."</span></div><br/>";
	} else {
		$construitSelection .="<br/>";
	}
	$construitSelection .="<div class=\"hint clear \">
	<span class=\"hint_label\"><a id=\"help_toggle\" href=\"#\" title=\"afficher l'aide sur les regroupements\" onclick=\"javascript:toggleHelpReg();\">aide >></a></span>
	<div id=\"Aide_regroup\" >
	<span class=\"hint_text\">
	Pour commencer, cliquez soit sur \"Ajouter\" sous la colonne des regroupements, soit sur une esp&egrave;ce puis sur \"garder ces esp&egrave;ces\" pour cr&eacute;er un regroupement d'une seule esp&egrave;ce. <br/>Une fois le regroupement cr&eacute;&eacute;, s&eacute;lectionnez une esp&egrave;ce dans la liste des esp&egrave;ces disponibles puis cliquez sur la fl&ecirc;che <-- pour affecter cette esp&egrave;ce au regroupement s&eacute;lectionn&eacute;.<br/>
	Les esp&egrave;ces non s&eacute;lectionn&eacute;es seront regroup&eacute;es dans une fraction DIV (divers, m&eacute;langes d'esp&egrave;ces).<br/>
	Vous pouvez s&eacute;lectionner ou d&eacute;s&eacute;lectionner plusieurs &eacute;l&eacute;ments en cliquant dessus tout en maintenant la touche \"CTRL\" (Windows, Linux) ou \"CMD\" (Mac) enfonc&eacute;e.
</span>	</div>
	</div>
	</div>";
	// Ajout de la fonction javascript pour permettre l'ouverture/fermeture de l'aide
	$construitSelection .="<script type=\"text/javascript\" charset=\"utf-8\">
			var AideReg = new Fx.Slide('Aide_regroup', {duration: 500});
			AideReg.hide();
			function toggleHelpReg() {
				AideReg.toggle();
			}

	</script>";
	
	return $construitSelection;
}

//*********************************************************************
// AfficheColonnes : Fonction pour afficher les tables / colonnes a selectionner par type de peche
function AfficheColonnes($typePeche,$typeAction,$TableEnCours,$numTab,$ListeColonnes) {
// Cette fonction permet de construire la liste des checkboxes pour la selection des tables/colonnes à selectionner
// Pour cela, elle va lire le fichier de definition (XML)
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $typePeche : le type de peche (artisanale/experimentale)
// $typeAction : la filere en cours
// $TableEnCours : la table en cours d'affichage
// $numTab: le numéro du tab en cours
// $ListeColonnes : la liste des colonnes deja cochées
//*********************************************************************
// En sortie : 
// La fonction renvoie $tableau
//*********************************************************************
	global $ListeTable;
	global $ListeChampTableDef ;
	global $ListeChampTableFac ;
	global $TableATester ;
	global $Filiere ;
	global $FiliereEnCours ;
	global $TypePecheEnCours;
	global $NumChampDef;	
	global $NumChampFac;
	global $TabEnCours;
	global $EcrireLogComp;
	global $logComp;
	global $pasdefichier;
	if ($TableEnCours=="") {$TableEnCours = "Pays";}
	//if ($EcrireLogComp ) {
	//	WriteCompLog ($logComp, "DEBUG : liste colonnes dans  affichescolonnees = ".$ListeColonnes,$pasdefichier);
	//}	
	$inputNumFac = "";
	$inputNumDef = "";
	$inputListeTable = "";
	// Fichier à analyser
	$TableATester = $TableEnCours;
	$FiliereEnCours = $typeAction;
	$TypePecheEnCours = $typePeche;
	$ListeChampTableDef = "";
	$ListeChampTableFac = "";
	// Selon le type de peches, la fonction Js n'est pas la meme.
	switch ($typePeche) {
		case "artisanale" :
		$runfilieres = "runFilieresArt";
		break;
		case "experimentale":
		$runfilieres = "runFilieresExp";
		break;
		case "agglomeration":
		$runfilieres = "runFilieresStat";
		break;
		case "generales":
		$runfilieres = "runFilieresStat";
		break;
	}
	$TabEnCours = $numTab;
	$fichiercolonne = $_SERVER["DOCUMENT_ROOT"]."/conf/ExtractionDefColonnes.xml";
	// Appel à la fonction de création et d'initialisation du parseur
	if (!(list($xml_parser_col, $fp) = new_xml_parser_Colonnes($fichiercolonne,"un"))){ 
		die("Impossible d'ouvrir le document XML"); 
	}
	// Traitement de la ressource XML
	while ($data = fread($fp, 4096)){
		if (!xml_parse($xml_parser_col, $data, feof($fp))){
			die(sprintf("Erreur XML : %s à la ligne %d<br/>",
			xml_error_string(xml_get_error_code($xml_parser_col)),
			xml_get_current_line_number($xml_parser_col)));
		}
	}
	// Libération de la ressource associée au parser
	xml_parser_free($xml_parser_col);
	if ($ListeChampTableFac == "") {
		$ContenuChampTableFac = ""; // ca ne devrait plus etre le cas !!! 
	} else {
		$ContenuChampTableFac = "liste des colonnes export&eacute;es pour <b>".$TableEnCours."</b><br/><span class=\"hints_small\">Vous pouvez les s&eacute;lectionner en les cochant quand elles ne sont pas gris&eacute;es </span><br/><br/><table id=\"colonneSel\"><tr><td class=\"colitem\">".$ListeChampTableFac."</td></tr></table><br/>";
	}
	$inputTableEC = "<input type=\"hidden\" id=\"tableEC\" value=\"".$TableEnCours."\"/>";
	$inputNumDef = "<input type=\"hidden\" id=\"numDef\" value=\"".$NumChampDef."\"/>";
	$inputNumFac = "<input type=\"hidden\" id=\"numFac\" value=\"".$NumChampFac."\"/>";
	$InputTout = "";
	if (strpos($ListeColonnes,"XtoutX") === false) {
		$InputTout = "<input id=\"facTout\" type=\"checkbox\"  name=\"fac0\" value=\"tout\"  onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','tout','','','')\" />&nbsp;tout<br/>";
	} else {
		$InputTout = "<input id=\"facTout\" type=\"checkbox\"  name=\"fac0\" value=\"tout\"  onclick=\"".$runfilieres."('".$typePeche."','".$typeAction."','".$numTab."','','n','aucun','','','')\" checked=\"checked\" />&nbsp;tout<br/>";
	}
	
	$tableau = $InputTout."<table class=\"ChoixChampComp\"><tr><td class=\"CCCTable\">".$ListeTable."</td><td class=\"CCCChamp\">";
	//if($ListeChampTableDef =="") {
		$tableau .=	$ContenuChampTableFac."</td></tr></table>".$inputTableEC.$inputNumFac.$inputNumDef;
	//} else {
	//	$tableau .=	"colonnes par d&eacute;faut : <br/>".$ListeChampTableDef."<br/>".$ContenuChampTableFac."</td></tr></table>".$inputTableEC.$inputNumFac.$inputNumDef;	
	//}
	return $tableau; 
}


?>