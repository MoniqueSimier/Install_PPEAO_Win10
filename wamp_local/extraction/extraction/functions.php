<?php 
//*****************************************
// functions.php
//*****************************************
// Created by Yann Laurent
// 2009-06-30 : creation
//*****************************************
// Ce fichier contient une serie de fonctions php utilisées dans l'extraction des données
//*****************************************
// FW 20191125 => modification table exp_coup_peche pour ne pas avoir de exp_environement_id à null
// => créer exp_environnement id=2337 avec toutes les colonnes à NULL (dernier id + 1)
// UPDATE exp_coup_peche SET exp_environnement_id = 2337 WHERE exp_environnement_id is null
//*****************************************
//
// Include pour les statistiques générales
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/functions_statgene.php';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/extraction/functions_affichage.php';
// Definition d'un paramètre global
$PathFicConf = $_SERVER["DOCUMENT_ROOT"]."/conf/extraction.txt" ;//*	
$pasdefichier = false; // residu du portage que j'aurai du virer depuis longtemps
// Variables qui seront globales dans les fonctions
$ListeTable = "";
$TableATester = "";
$Filiere = "";
$TypePecheEnCours="";
$StatEnCours="";
$NomTableEnCours="";
$NumChampDef = 0;
$NumChampFac = 0;
$ListeTableInput = "";


//*********************************************************************
// convertit num : test et ajoute
function  convertitNum($valeurAConvertir) {
	if (is_numeric($valeurAConvertir)){
		$valsub = -1 * (strlen($valeurAConvertir) - strpos($valeurAConvertir,".") - 3);
		//echo "val sub = ".$valsub."<br/>";
		//echo $valeurAConvertir." - ".strpos($valeurAConvertir,".")."  ".strlen($valeurAConvertir)." ".substr($valeurAConvertir,0,$valsub)."<br/>";
		if ( strpos($valeurAConvertir,".") > 0 && $valsub < 0 ) {
			$AjChps = str_replace(".",",",$valeurAConvertir);
			//$AjChps = str_replace(".",",",substr($valeurAConvertir,0,$valsub));
		} else {
			if ( strpos($valeurAConvertir,".") > 0) {
				$AjChps = str_replace(".",",",$valeurAConvertir);
			} else {
				$AjChps = $valeurAConvertir;
			}
		}

	}else {
		$AjChps = $valeurAConvertir;
	}
	return $AjChps;
}

//*********************************************************************
// ajouterAuWhere : test et ajoute
function  ajouterAuWhere($WhereEncours,$CodeAajouter) {
// Cette fonction permet  d'ajouter une table a un SQL si elle n'existe pas deja  
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $WhereEncours,$CodeAajouter
//*********************************************************************
// En sortie : le nouvel SQL
// 
//*********************************************************************
	if (strpos($WhereEncours,$CodeAajouter) === false ) {
		if ($WhereEncours == "" ) {
			$WhereEncours = $CodeAajouter;
		} else {
			$WhereEncours .= " and ".$CodeAajouter;
		}
	}
	return $WhereEncours;
}
//*********************************************************************
// ajoutAuTableSel : test et ajoute
function  ajoutAuTableSel($ListeTableSel,$TNomLongTable,$CondAAjouter) {
// Cette fonction permet d'ajouter une condition a un SQL si elle n'existe pas deja 
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $ListeTableSel,$TNomLongTable,$CondAAjouter
//*********************************************************************
// En sortie : le nouvel SQL
// 
//*********************************************************************
	if (strpos($ListeTableSel,$TNomLongTable) === false ) {
		$ListeTableSel .= $CondAAjouter;
	} 
	return $ListeTableSel;
}
//*********************************************************************
// TestSQLAucun : test si le SQL contient aucun/aucune, si oui, renvoie blanc, sinon renvoie le SQL sans la derniere virgule
function TestSQLAucun($SQLATester) {
// Cette fonction permet de tester si le SQL contient aucun/aucune, si oui, renvoie blanc, sinon renvoie le SQL sans la derniere virgule
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $SQLATester = le SQL a tester
//*********************************************************************
// En sortie : 
// si le SQL contient aucun/aucune, si oui, renvoie blanc, sinon renvoie le SQL sans la derniere virgule
//*********************************************************************
	$SQLARenvoyer = "";
	if (!($SQLATester == "")) {
		if (strpos("aucun",$SQLATester) === false ) {
			$SQLARenvoyer 	= substr($SQLATester,0,- 1); // pour enlever la virgule surnumeraire;
		} 
	}
	return $SQLARenvoyer;
}

//*********************************************************************
// AfficherSelection : Fonction d'affichage de la selection
function AfficherSelection($file) {
// Cette fonction est la fonction qui analyse le ficher de sélection et qui affiche la dite selection
// Elle permet aussi de remplir les variables SQL* qui contient la traduction en liste de variables de la sélection 
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $file : le fichier de paramétrage qui contient la sélection issue de l'etape précédente
//*********************************************************************
// En sortie : 
// La fonction renvoie $listeSelection
//*********************************************************************
	// Données pour la selection 
	global $typeSelection ;
	global $typePeche;
	global $typeStatistiques;

	global $listeGTEngin;
	// Pour construire le bandeau avec la sélection
	global $listeSelection;
	// Pour construire les SQL
	global $SQLPays 	;
	global $SQLSysteme	;
	global $SQLSecteur	;
	global $SQLAgg		;
	global $SQLEngin	;
	global $SQLGTEngin ;
	global $SQLCampagne ;
	global $SQLPeEnquete; // liste des enquetes
	global $SQLEspeces	;
	global $SQLFamille ;
	global $SQLdateDebut ; // format annee/mois
	global $SQLdateFin ; // format annee/mois
	global $listeDocPays; // liste des docs pour le pays
	global $listeDocSyst; // liste des docs pour le systeme
	global $listeDocSect; // liste des docs pour le secteur
	global $connectPPEAO;	

	
	// Appel à la fonction de création et d'initialisation du parseur
	if (!(list($xml_parser, $fp) = new_xml_parser($file))){ 
		die("Impossible d'ouvrir le document XML"); 
	}
	// Traitement de la ressource XML
	
	while ($data = fread($fp, 4096)){
	
		if (!xml_parse($xml_parser, $data, feof($fp))){
			die(sprintf("Erreur XML : %s à la ligne %d<br/>",
			xml_error_string(xml_get_error_code($xml_parser)),
			xml_get_current_line_number($xml_parser)));
		   }
	}
	
	// Libération de la ressource associée au parser
	xml_parser_free($xml_parser);
	// On colle tous en variable de session, comme ca, pas de pb...
	$_SESSION['typeSelection'] = $typeSelection;
	$_SESSION['typePeche'] = $typePeche;
	$_SESSION['typeStatistiques'] = $typeStatistiques;
	$_SESSION['SQLdateDebut'] = $SQLdateDebut;
	$_SESSION['SQLdateFin'] = $SQLdateFin;
	$SQLPays 		= TestSQLAucun($SQLPays);
	$SQLSysteme 	= TestSQLAucun($SQLSysteme);
	$SQLSecteur 	= TestSQLAucun($SQLSecteur);
	$SQLAgg 		= TestSQLAucun($SQLAgg);
	$SQLEngin 		= TestSQLAucun($SQLEngin);
	$SQLGTEngin 	= TestSQLAucun($SQLGTEngin);
	$SQLCampagne 	= TestSQLAucun($SQLCampagne);
	$SQLEspeces 	= TestSQLAucun($SQLEspeces);
	$SQLFamille 	= TestSQLAucun($SQLFamille);
	$SQLPeEnquete 	= TestSQLAucun($SQLPeEnquete);
	$listeDocPays 	= TestSQLAucun($listeDocPays); 
	$listeDocSyst	= TestSQLAucun($listeDocSyst); 
	$listeDocSect	= TestSQLAucun($listeDocSect); 	
	$_SESSION['SQLPays'] = $SQLPays;
	$_SESSION['SQLSysteme'] = $SQLSysteme;
	$_SESSION['SQLSecteur'] = $SQLSecteur;
	$_SESSION['SQLAgg'] = $SQLAgg;
	$_SESSION['SQLEngin'] = $SQLEngin;
	$_SESSION['SQLGTEngin'] = $SQLGTEngin;
	$_SESSION['SQLCampagne'] = $SQLCampagne;
	$_SESSION['SQLFamille'] = $SQLFamille;
	$_SESSION['SQLPeEnquete'] = $SQLPeEnquete;
	$_SESSION['listeDocPays'] = $listeDocPays; //liste contenant les ID des documents pays a mettre en zip
	$_SESSION['listeDocSys'] = $listeDocSyst; //liste contenant les ID des documents systeme a mettre en zip
	$_SESSION['listeDocSect'] = $listeDocSect; //liste contenant les ID des documents secteur a mettre en zip

	// On ajoute dans la liste des especes les ID venant des especes selectionnees.
	// Au moins c'est fait ici, on n'a plus a se poser de questions et le faire 100 fois apres
	$listEspFamille = "";
	if (!($SQLFamille =="")) {
		$SQLfam = "select id from ref_espece where ref_famille_id in (".$SQLFamille.")";	
		$SQLfamResult = pg_query($connectPPEAO,$SQLfam);
		$erreurSQL = pg_last_error($connectPPEAO);
		if ( !$SQLfamResult ) {
			echo "erreur execution SQL pour ".$SQLfam." erreur complete = ".$erreurSQL."<br/>";
		//erreur
		} else { 
			if (pg_num_rows($SQLfamResult) == 0) {
				// Erreur
				echo "pas d'especes trouv&eacute;es dont le famille_id est ".$SQLFamille."<br/>" ;
			} else { 
				
				while ($famRow = pg_fetch_row($SQLfamResult) ) {
					if ($listEspFamille == "") {
						$listEspFamille = "'".$famRow[0]."'";
					} else {
						$listEspFamille .= ",'".$famRow[0]."'";
					}
				}
			}
		}
		if ($SQLEspeces == "") {
			$SQLEspeces = $listEspFamille;
		} else {
			$SQLEspeces .= ",".$listEspFamille;
		}
		pg_free_result($SQLfamResult);		
	} else  {
		$listeSelection = str_replace("<b>familles</b> :","<b>familles</b> : toutes",$listeSelection);
	}	
	$_SESSION['SQLEspeces'] = $SQLEspeces;
	if ($SQLEspeces=="") {
		$listeSelection = str_replace("<b>especes</b> :","<b>especes</b> : toutes",$listeSelection);
		// On va reconstruire cette liste plus tard dans la fonction afficherdonnees
	}
	return $listeSelection;
}

//*********************************************************************
// genereOrdre : Fonction d'extraction trie les donnees
function genereOrdre($contexte,$typeAction,$typeStatistiques) {
	// ces variables globales definissent l'ordre de tri des diverses extractions (definies dans /ordre_tri.inc)
	global $peuplement,$environnement,$ntpt,$biologie,$trophique,$st_tot,$st_sp,$st_dft,$st_gt,$st_gt_sp,$st_gt_sp_dft,$st_tot_gen,$st_sp_gen,$st_dft_gen,$st_gt_gen,$st_gt_sp_gen,$st_gt_sp_dft_gen,$activite,$captures,$fractions,$dft,$engin,$default;
	$default='';
	switch($contexte) {
		case 'activite' : $varNom='activite';
		break;
		case 'environnement' : $varNom='environnement';
		break;
		case 'peuplement': $varNom='peuplement';
		break;
		case 'NtPt': $varNom='ntpt';
		break;
		case 'biologie': $varNom='biologie';
		break;
		case 'trophique': $varNom='trophique';
		break;
		case 'activite': $varNom='activite';
		break;
		case 'capture': $varNom='captures';
		break;
		case 'NtPart': $varNom='fractions';
		break;
		case 'taillart': $varNom='dft';
		break;
		case 'engin': $varNom='engin';
		break;
		case 'ast': if ($typeStatistiques == "agglomeration" ) {$varNom='st_tot';} else {$varNom='st_tot_gen';}
		break;
		case 'asp': if ($typeStatistiques == "agglomeration" ) {$varNom='st_sp';} else {$varNom='st_sp_gen';}
		break;
		case 'ats': if ($typeStatistiques == "agglomeration" ) {$varNom='st_dft';} else {$varNom='st_dft_gen';}
		break;
		case 'asgt': if ($typeStatistiques == "agglomeration" ) {$varNom='st_gt';} else {$varNom='st_gt_gen';}
		break;
		case 'attgt': if ($typeStatistiques == "agglomeration" ) {$varNom='st_gt_sp';} else {$varNom='st_gt_sp_gen';}
		break;
		case 'atgts': if ($typeStatistiques == "agglomeration" ) {$varNom='st_gt_sp_dft';} else {$varNom='st_gt_sp_dft_gen';}
		break;
		case 'stats': if ($typeStatistiques == "agglomeration" ) {$varNom='st_tot';} else {$varNom='st_tot_gen';}
		break;
		default: $varNom='default';
		break;
	}	
	//echo "contexte = ".$contexte." nom var tri = ".$varNom." | ".$typeAction." - ".$typeStatistique."<br/>";
	if (!empty($varNom)) {
			$ordreListe=$$varNom;
			//echo "liste=".$ordreListe."<br/>";
			$ordreTriTemp=explode(",",$ordreListe);
			$ordreListeRecons = "";
			$NbResultat = count($ordreTriTemp);
			// On controle qu'il n'y a pas des champs a supprimer
			$Asupprimer = false;
			for ($cptResult = 0;$cptResult < $NbResultat;$cptResult++) {
				$Asupprimer = TestsuppressionChampTri($typeAction,$ordreTriTemp[$cptResult],$typeStatistiques);
				if (!$Asupprimer) {
					if ($ordreListeRecons == "") {
						$ordreListeRecons = $ordreTriTemp[$cptResult];
					} else {
						$ordreListeRecons .= ",".$ordreTriTemp[$cptResult];
					}
				} 
			}
			//echo "GenerOrdre = ".$ordreListeRecons." FIN <br/>";
			$ordreTri=explode(",",$ordreListeRecons);
			//echo count($ordreTri)."  <br/>";
		}else {$ordreListe=array();}
	return $ordreTri;
}

//*********************************************************************
// AfficherDonnees : Fonction d'extraction qui affiche les données
function AfficherDonnees($file,$typeAction){
// Cette fonction est la fonction principale de l'extraction qui permet de compter les resultats mais aussi de les afficher
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $file : le fichier de paramétrage qui contient la sélection issue de l'etape précédente
// $typeAction : la filere en cours
//*********************************************************************
// En sortie : 
// La fonction ne renvoie rien. Mais la variable $resultatLecture est mise à jour pour un affichage dans le script qui appelle
// cette fonction. 
//*********************************************************************
	$debugLog = false;

	// Il faut s'assurer qu'au moins une fois la fonction qui remplit ces variables de session a été lancée 
	$typeSelection 	= $_SESSION['typeSelection'];
	$typePeche		= $_SESSION['typePeche'];
	$typeStatistiques = $_SESSION['typeStatistiques'];
	$SQLPays 		= $_SESSION['SQLPays'];
	$SQLSysteme		= $_SESSION['SQLSysteme'];
	$SQLSecteur		= $_SESSION['SQLSecteur'];
	$SQLAgg			= $_SESSION['SQLAgg'];
	$SQLEngin		= $_SESSION['SQLEngin'];
	$SQLGTEngin 	= $_SESSION['SQLGTEngin'];
	$SQLCampagne 	= $_SESSION['SQLCampagne'];
	$SQLPeEnquete 	= $_SESSION['SQLPeEnquete']; // liste des enquetes	
	$SQLEspeces		= $_SESSION['SQLEspeces'];
	$SQLFamille 	= $_SESSION['SQLFamille'];
	$SQLdateDebut 	= $_SESSION['SQLdateDebut']; // format annee/mois
	$SQLdateFin 	= $_SESSION['SQLdateFin']; // format annee/mois
	$listeDocPays 	= $_SESSION['listeDocPays']; // liste des docs pour le pays
	$listeDocSyst 	= $_SESSION['listeDocSys'] ; // liste des docs pour le systeme
	$listeDocSect 	= $_SESSION['listeDocSect']; // liste des docs pour le secteur
	set_time_limit(0);
	// Attention, le cas des especes est un peu particulier.
	// On utilise 2 variables de session : 
	// SQLEspeces contient les données venant de la sélection (ie si lors de l'étape précédente, on a sélectionné des especes ou familles
	// listeEspeces contient la sélection des especes venant des filières. Elle est au maximum égale a SQLEspeces.
	// La référence pour le SQL final doit etre ListeEspeces.

	$listeChamps = "";
	global $connectPPEAO;
	global $resultatLecture;
	global $divExportFic;
	global $compteurItem;
	global $restSupp;
	global $labelSelection;
	global $CRexecution;
	global $erreurProcess;
	global $exportFichier;
	global $EcrireLogComp;
	global $pasdefichier;
	global $logComp;
	global $codeTableEnCours;
	global $SelectionPourFic;
	// pour recuperer les listes des selections par colonne
	global $listeChampsSel;
	global $ListeTableSel;
	global $AjoutWhere;
	global $LeftOuterJoin;
	global $listeDocURL;
	global $debugAff;
	global $start_while;
	global $erreurStatGene;
	global $creationRegBidon;
	$PasDeResultat = false;
	$debugAff = false;
	$start_while=timer(); 		// début du chronométrage du for
	unset($_SESSION['listeDIV']);
	if ($debugAff) {
		$debugTimer = number_format(timer()-$start_while,4);
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "TEST PERF : debut AfficherDonnees :".$debugTimer,$pasdefichier);
		} else {
			echo "debut AfficherDonnees :".$debugTimer."<br/>";
		}
	}
	if (!($typeAction == "")) {
		$divExportFic = "<div id=\"exportFic\"><input type=\"button\" id=\"validation\" onClick=\"runFilieresArt('".$typePeche."?>','".$typeAction."','1','".$codeTableEnCours."','y',,'','')\" value=\"Voir les r&eacute;sultats\"/>
	<input type=\"checkbox\" id=\"ExpFic\" />Exporter sous forme de fichier</div>";
	}
	// Cas de la navigation a l'interieur d'une pagination :
	// On ne regenere pas le fichier...	
	if (isset($_GET['dejf'])) {
		if ($_GET['dejf'] =="y") {
			$fichierDejaCree = true;
		} else {
			$fichierDejaCree = false;
		}
	} else {
		$fichierDejaCree = false;
	} 			
	if ($exportFichier ) {
		// On recupère les info pour creer le fichier d'export 
		$nomLogLien = "/work/extraction";
		$dirLog = $_SERVER["DOCUMENT_ROOT"].$nomLogLien;
		// On fait tous les tests associés
		if (! file_exists($dirLog)) {
			if (! mkdir($dirLog) ) {
				$resultatLecture .= " erreur de cr&eacute;ation du r&eacute;pertoire d'export des fichiers";
				exit;
			}
		}
		//	Controle fichiers
		// Pour les statistiques, il n'y a pas un seul fichier de données mais 6
		// On cree directement le zip avec tous les fichiers.
		if ($typeSelection == "statistiques") {
			$ajoutNomStat = "_".$typeStatistiques;
		} else {
			$ajoutNomStat = "";
		}
		$nomFicExport = $dirLog."/".date('y\-m\-d-H-i')."_".$typeSelection."_".$typeAction.".txt";
		$nomFicExportSel = $dirLog."/".date('y\-m\-d-H-i')."_".$typeSelection."_".$typeAction."-Selection.txt";
		if (!($_SESSION['listeRegroup'] == "")) {
			$nomFicExportReg = $dirLog."/".date('y\-m\-d-H-i')."_".$typeSelection."_".$typeAction.$ajoutNomStat."-Regroupement.txt";
		}
		$nomFicExpLien = $nomLogLien."/".date('y\-m\-d-H-i')."_".$typeSelection."_".$typeAction.$ajoutNomStat.".txt";
		// On ne cree le fichier que si il n'a pas deja ete rempli !
		if (!($fichierDejaCree)) {
			if (!($typeSelection == "statistiques")) {
				$ExpComp = fopen($nomFicExport , "w+");
				if (! $ExpComp ) {
					$resultatLecture .= " erreur de cr&eacute;ation du fichier export ".$nomFicExpLien;
					exit;		
				}
			}
			$ExpCompSel = fopen($nomFicExportSel , "w+");
			if (! $ExpCompSel ) {
				$resultatLecture .= " erreur de cr&eacute;ation du fichier export contenant la selection";
				exit;		
			}
			if (!($_SESSION['listeRegroup'] == "")) {
				$ExpCompReg = fopen($nomFicExportReg , "w+");
				if (! $ExpCompReg ) {
					$ExpReg .= " erreur de cr&eacute;ation du fichier export contenant la definition des regroupements ";
					exit;		
				}
			}
		}
		// Gestion du fichier d'archive
		$zipFilename = $_SERVER["DOCUMENT_ROOT"]."/work/extraction/extraction_".$typeAction.$ajoutNomStat."_".date('y\-m\-d-H-i').".zip";
		$zipFilelien = "/work/extraction/extraction_".$typeAction.$ajoutNomStat."_".date('y\-m\-d-H-i').".zip";
		if (!($fichierDejaCree)) {
			if (file_exists($zipFilename)) {
				// pas forcement necessaire, verifier que le x+ vide le fichier
				unlink($zipFilename);
			}
			$theZipFile=new zip_file($zipFilename);	
			//setting the zip options: write to disk, do not recurse directories, do not store path and do not compress
			$theZipFile->set_options(array('inmemory' => 0, 'recurse' => 0, 'storepaths' => 0, 'method'=>0));			
		}	
	} 	
	// Analyse des paramètres communs
	if ($SQLSecteur == "") {
		$WhereSect = "";
	} else {
		$WhereSect = "se.id in (".$SQLSecteur.") and";
	}		
	if ($SQLSysteme == "") {
		$WhereSyst = "";
		// Ici on doit traiter du cas d'une sélection restrictive des pays
	} else {
		$WhereSyst = "sy.id in (".$SQLSysteme.") and";
	}	
	$LabCatEco = "";
	$LabCatTrop = "";
	$LabCatPois = "";
	$LeftOuterJoin = "";
	$ConstIDunique = ""; // Va contenir la définition pour la construction de l'ID unique de ligne. contient une valeur differente selon type peche / filiere
	// Analyse des categories trophiques / ecologiques / poisson-non poisson
	// Analyse des categories ecologiques sélectionnées par l'utilisateur (selection restreinte depuis la filiere)
	if (!($_SESSION['listeCatEco'] == "")) {
		$compCatEcoSQL = "";
		$CatEcoNull = false;
		$LabCatEco = " restreint aux cat&eacute;gories &eacute;cologiques : ";
		$champSel = explode(",",$_SESSION['listeCatEco']);
		$nbrSel = count($champSel)-1;
		$valCatE = "";
		for ($cptSel = 0;$cptSel <= $nbrSel;$cptSel++) {
			// Traitement special pour la valeur null
			if ($champSel[$cptSel] == "null") {
				$CatEcoNull = true;
			} else {
				if ($valCatE == "") {
					$valCatE = "'".$champSel[$cptSel]."'";
				} else {
					$valCatE .= ",'".$champSel[$cptSel]."'";
				}
			}
			$LabCatEco .= $champSel[$cptSel]." ";
		}
		if (!($valCatE=="")){
			$compCatEcoSQL =" esp.ref_categorie_ecologique_id in (".$valCatE.") "; // 
		}
		// Si a choisi de selectionner les categories null, il faut l'expliciter
		if ($CatEcoNull) {
			if ($compCatEcoSQL==""){
				$compCatEcoSQL = "esp.ref_categorie_ecologique_id is null";
			} else  {
				$compCatEcoSQL = "(".$compCatEcoSQL." or esp.ref_categorie_ecologique_id is null)";
			}
		}
	} else {
		$compCatEcoSQL = "";
		$LabCatEco = " - toutes les cat&eacute;gories &eacute;cologiques ";
	}
	// Analyse des categories trophiques sélectionnées par l'utilisateur (selection restreinte  depuis la filiere)
	if (!($_SESSION['listeCatTrop'] == "")) {
		$compCatTropSQL = "";
		$CatTropNull = false;
		$LabCatTrop = " restreint aux cat&eacute;gories trophiques : ";
		$champSel = explode(",",$_SESSION['listeCatTrop']);
		$nbrSel = count($champSel)-1;
		$valCatT = "";
		for ($cptSel = 0;$cptSel <= $nbrSel;$cptSel++) {
			// Traitement special pour la valeur null
			if ($champSel[$cptSel] == "null") {
				$CatTropNull = true;
			} else {
				if ($valCatT == "") {
					$valCatT = "'".$champSel[$cptSel]."'";
				} else {
					$valCatT .= ",'".$champSel[$cptSel]."'";
				}
			}
			$LabCatTrop .= $champSel[$cptSel]." ";
		}
		if (!($valCatT=="")){
			$compCatTropSQL =" esp.ref_categorie_trophique_id in (".$valCatT.")"; // Pas and a la fin, c'est le dernier SQL
		}
		// Si a choisi de selectionner les categories null, il faut l'expliciter
		if ($CatTropNull) {
			if ($compCatTropSQL == ""){
				$compCatTropSQL = "esp.ref_categorie_trophique_id is null";
			} else  {
				$compCatTropSQL = "(".$compCatTropSQL." or esp.ref_categorie_trophique_id is null)";
			}
		}
	} else {
		$compCatTropSQL = "";
			$LabCatTrop = " toutes les cat&eacute;gories trophiques ";
	}
	// Analyse du type poisson non poisson sélectionné par l'utilisateur (selection restreinte depuis la filiere)
	if (!($_SESSION['listePoisson'] == "")) {
		$champSel = explode(",",$_SESSION['listePoisson']);
		$nbrSel = count($champSel)-1;
		$valPoisson = "";
		$LabCatPois= "";
		for ($cptSel = 0;$cptSel <= $nbrSel;$cptSel++) {
			switch ($champSel[$cptSel]) {
				case "0" : 
					if ($valPoisson == "") {
						$valPoisson = $champSel[$cptSel];
					} else {
						$valPoisson .= ",".$champSel[$cptSel];
					}
					$LabCatPois .= "  poissons inclus";
					break;
				case "1" : 
					if ($valPoisson == "") {
						$valPoisson = $champSel[$cptSel];
					} else {
						$valPoisson .= ",".$champSel[$cptSel];
					}
					$LabCatPois .= " 'non poissons' inclus";
					break;
				case "pp" :
					$LabCatPois .=" poissons exclus";
					break;
				case "np":
					$LabCatPois .= " 'non poissons' exclus";
					break;	
			}
		}
		$compPoisSQL =" and fam.non_poisson in (".$valPoisson.") ";
	} else {
		if (!($typeAction =="environnement") && !($typeAction =="activite") && !($typeAction =="capture")){
			$LabCatPois = " tous les poissons ";
		}
	} // fin du if (!($_SESSION['listePoisson'] == ""))
	// *******************************
	// Debut du traitement principal *	
	// *******************************
	$builQuery = false; // il a l'air de rien celui-la, mais ce flag est super important pour créer le SQL final qui sera executé.
	switch ($typeSelection) {
		// #####################################################################################
		// EXTRACTION
		// #####################################################################################
		case "extraction" :
			switch ($typePeche) {
				// *********************************************************************************
				// PECHE EXPERIMENTALE
				// *********************************************************************************
				case "experimentale" :
					// ********** ANALYSE DES SELECTIONS DE L'UTILISATEUR
					// ********** si aucune selection de campagne, alors on aura pas de resultat
					if ($SQLCampagne == "") {
						$_SESSION['pasderesultat'] = true;
						return "pas de resultat <br/>";
					}
					// ==> construction des SQL correspondant - traitement des cas particuliers
					// On controle que des sélections ont été faites pour les espèces / familles
					if ($SQLEngin == "") {
						$WhereEngin = "";
						// Ici on doit traiter du cas d'une sélection restrictive des pays
					} else {
						$WhereEngin = "cph.exp_engin_id in (".$SQLEngin.") and";
					}							
					// Prise en compte des sélections complémentaires
					$compSQL = "";
					if 	(!($_SESSION['listeQualite'] =="")) {
						$compSQL =" cph.exp_qualite_id in (".$_SESSION['listeQualite'].") ";
						$restSupp = " qualit&eacute; limit&eacute;e à =".$_SESSION['listeQualite'];
					}
					if (!($_SESSION['listeProtocole'] == "")) {
						switch ($_SESSION['listeProtocole']) {
						case "0" : $restSupp .= " - non restreint aux coups du protocole ";
									break;
						case "1" : $restSupp .= " - restreint aux coups du protocole ";
									if ($compSQL == "") {
										$compSQL =" cph.protocole = 1";
									} else {
										$compSQL .=" and cph.protocole = 1";
									}
									break;
						}
					}
					// Les selections ci-dessous ne sont valables que pour les filieres autres que l'environnement
					if ($typeAction =="environnement" ||  $typeAction =="peuplement"){
						$compCatEcoSQL = "";
						$compCatTropSQL ="";
						if ($typeAction =="environnement") {
							$compPoisSQL ="";	
							$LabCatPois= "";							
						}
						$restSupp .= " - ".$LabCatPois." ";
					} 	else {
						// Maj du libelle de la selection en tete avec les restriction CatEco CatTroph et poisson
						$restSupp .= " - ".$LabCatEco." - ".$LabCatTrop." - ".$LabCatPois." ";
		
					}
					// ********** Gestion de l'affichage des colonnes sélectionnées 
					$listeChampsSel = "";
					$ListeTableSel = "";
					$WhereSel = "";
					$AjoutWhere = "";
					// Analyse de la liste des colonnes venant des sélections précédentes, ajout de ces colonnes au fichier
					analyseColonne($typePeche,$typeAction,"",$typeStatistiques);	
					// Analyse des différents composants du where et ajout des and quand nécessaire
					// C'est un peu le bronx pour construire ces SQL, mais pas le choix. On doit pouvoir optimiser...
					if ($compSQL == "" ) {
						$WhereSel = $compCatEcoSQL;
					} else {
						if ($compCatEcoSQL == "") {
							$WhereSel = $compSQL;
						} else {
							$WhereSel = $compSQL." and ".$compCatEcoSQL;
						}
					}
					// Gestion des categories trophiques...
					if (!($compCatTropSQL == "" )) {
						if ($WhereSel == "" ) {
							$WhereSel = $compCatTropSQL;
						} else {
							$WhereSel = $WhereSel." and ".$compCatTropSQL;
						}
					}
					// Enfin on ajoute les noms des nouveaux champs à lire depuis les colonnes
					if ($WhereSel == "" ) {
						$WhereSel = $AjoutWhere;
					} else {
						if (!($AjoutWhere == "")) {
							$WhereSel = $WhereSel." and ".$AjoutWhere;
						}
					}
					// Cas particulier d'aucun sélection des espèces : 
					// On reconstruit cette liste pour l'ensemble de la sélection car on va en avoir besoin
					// pour les catégories trophiques/ecologiques
					if ($SQLEspeces == "") {
						// On reconstruit la liste des especes de la sélection.
						$SQLEsp = "select esp.id from ref_pays as py,ref_systeme as sy,ref_secteur as se,exp_station as stat,exp_campagne as cpg,exp_coup_peche as cph,exp_fraction as fra,ref_espece as esp
								where cpg.id = cph.exp_campagne_id and
								stat.id = cph.exp_station_id and
								sy.id = cpg.ref_systeme_id and
								".$WhereSyst."
								py.id = sy.ref_pays_id and
								se.id = stat.ref_secteur_id and
								fra.exp_coup_peche_id = cph.id and
								esp.id = fra.ref_espece_id and
								".$WhereSect."
								cpg.id in (".$SQLCampagne.")";
						$SQLEspResult = pg_query($connectPPEAO,$SQLEsp);
						$erreurSQL = pg_last_error($connectPPEAO);
						if ( !$SQLEspResult ) { 
							$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;erreur query ".$SQLEsp." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
							if ($EcrireLogComp) {
								WriteCompLog ($logComp, "ERREUR : echec construction liste espece query sql = ".$SQLEsp." (erreur complete = ".$erreurSQL,$pasdefichier);
							}
							$erreurProcess = true;
							return ("erreur SQL especes");
						} else {
							
							if (pg_num_rows($SQLEspResult) == 0) {
							// Erreur
								$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>pas de coup de peche dispo vide...<br/>";
								if ($EcrireLogComp) {
									WriteCompLog ($logComp, "Warning :  pas de coupe de peches trouves pour remplir la liste des especes.",$pasdefichier);
								}
							} else {
								while ($EspRow = pg_fetch_row($SQLEspResult) ) {
									if (strpos($SQLEspeces,$EspRow[0]) === false ) {
										$SQLEspeces .= "'".$EspRow[0]."',";	
									}
								}		
							}
							pg_free_result($SQLEspResult);
						}
						$SQLEspeces	= substr($SQLEspeces,0,- 1); // pour enlever la virgule surnumeraire;
						$_SESSION['SQLEspeces'] = $SQLEspeces; // ca va servir pour la suite....
					}

					// Si malgré tout, toujours pas d'especes dispo, ben tant pis....
					if ($SQLEspeces == "") {
						$WhereEsp = "";
					} else {
						// Enfin on verifie qu'il n'y a pas eu de restriction supplémentaires
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
						if (!($SQLEspeces == "" )) {
							$WhereEsp = "fra.ref_espece_id in (".$SQLEspeces.") and";
						} else {
							if ($EcrireLogComp ) {
								WriteCompLog ($logComp, "ERREUR SQLEspeces est encore vide.",$pasdefichier);
							}
						}
					}
					// ********** PREPARATION DU SQL
					// Definition de tout ce qui est commun aux peches expérimentales
					$listeChampsCom = "py.id, py.nom, sy.id, sy.libelle, se.id_dans_systeme, se.nom, stat.id, stat.nom, cpg.date_debut, cpg.numero_campagne, cph.date_cp, cph.heure_debut, cph.numero_coup, cph.protocole, cph.exp_qualite_id,xqua.libelle, cph.exp_engin_id, xeng.libelle";
					$ListeTableCom = "ref_pays as py,ref_systeme as sy,ref_secteur as se,exp_campagne as cpg,exp_coup_peche as cph,exp_qualite as xqua,exp_engin as xeng";
					$WhereCom = "cpg.id = cph.exp_campagne_id and
									stat.id = cph.exp_station_id and
									sy.id = cpg.ref_systeme_id and
									".$WhereSyst."
									py.id = sy.ref_pays_id and
									se.id = stat.ref_secteur_id and
									".$WhereSect."
									cpg.id in (".$SQLCampagne.") and
									xqua.id = cph.exp_qualite_id and
									".$WhereEngin."
									xeng.id = cph.exp_engin_id ";
					$OrderCom = "order by py.nom asc,sy.libelle asc,cpg.numero_campagne asc, cph.numero_coup asc";
					// ********** CONSTRUCTION DES SQL DEFINITIFS PAR FILIERE
					switch ($typeAction) {
						case "peuplement" :
							$labelSelection = "donn&eacute;e(s) de peuplement ";	
							// On n'extrait que des donnéees de fraction
							// Il n'y aucune selection de colonnes supplémentaires
							// On prend tous les poissons (pas de différence poisson/non poisson
							$listeChampsSpec = ",esp.id, esp.libelle, esp.ref_categorie_ecologique_id, esp.ref_categorie_trophique_id,fra.nombre_total ,fra.poids_total";
							$ListeTableSpec = ",exp_fraction as fra,ref_famille as fam"; 
							$WhereSpec = " and fra.exp_coup_peche_id = cph.id and ".$WhereEsp."
								esp.id = fra.ref_espece_id and
								fam.id = esp.ref_famille_id ".$compPoisSQL;
							$OrderCom .= ",esp.libelle asc";
							$valueCount = "fra.id" ; // pour gerer la pagination	
							$builQuery = true;
							// Gestion des outer join, si pas present, on doit rajouter les tables et leur alias
							// Si on a un outer join, on l'analyse pour etre sur qu'il ne manque pas des declarations de tables dans le SQL
							if (strpos($LeftOuterJoin,"left outer join") === false ) {
								$LeftOuterJoin = ",exp_station as stat,ref_espece as esp";
							} else {	
								if (strpos($LeftOuterJoin,"exp_station as stat") === false ) {
									$LeftOuterJoin = ",exp_station as stat ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"ref_espece as esp") === false ) {
									$LeftOuterJoin = ",ref_espece as esp ".$LeftOuterJoin;
								}
							}
							break;
						case "environnement" :
							$labelSelection = "donn&eacute;e(s) d'environnement ";
							// On n'extrait que des donnéees environnements
							// Pas de données poisson
							$listeChampsSpec = "";
							$ListeTableSpec = " "; 
							$WhereSpec = " 	and env.id = cph.exp_environnement_id ";
							$valueCount = "cph.id" ; // pour gerer la pagination						
							$builQuery = true;
							// Gestion des outer join, si pas present, on doit rajouter les tables et leur alias
							// Si on a un outer join, on l'analyse pour etre sur qu'il ne manque pas des declarations de tables dans le SQL
							if (strpos($LeftOuterJoin,"left outer join") === false ) {
								$LeftOuterJoin = ",exp_station as stat,exp_environnement as env";
							} else {
								if (strpos($LeftOuterJoin,"exp_station as stat") === false ) {
									$LeftOuterJoin = ",exp_station as stat ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"exp_environnement as env") === false ) {
									$LeftOuterJoin = ",exp_environnement as env ".$LeftOuterJoin;
								}
							}
							break;
						case "NtPt" :
							$labelSelection = "donn&eacute;e(s) NtPt ";
							// C'est un mixte entre les données peuplements et environnement + des selections de colonnes
							$listeChampsSpec = ",fra.nombre_total, fra.poids_total,esp.id, esp.libelle, esp.ref_categorie_ecologique_id, esp.ref_categorie_trophique_id";
							$ListeTableSpec = ",exp_fraction as fra,ref_famille as fam";
							$WhereSpec = " 	and fra.exp_coup_peche_id = cph.id and ".$WhereEsp."
								esp.id = fra.ref_espece_id and
								fam.id = esp.ref_famille_id  ".$compPoisSQL;
							$OrderCom .= ",fra.id asc,esp.libelle asc";
							$valueCount = "cph.id" ; // pour gerer la pagination						
							$builQuery = true;
							// Gestion des outer join, si pas present, on doit rajouter les tables et leur alias
							// Si on a un outer join, on l'analyse pour etre sur qu'il ne manque pas des declarations de tables dans le SQL
							if (strpos($LeftOuterJoin,"left outer join") === false ) {
								$LeftOuterJoin = ",exp_station as stat,ref_espece as esp";
							} else {
								if (strpos($LeftOuterJoin,"exp_station as stat") === false ) {
									$LeftOuterJoin = ",exp_station as stat ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"ref_espece as esp") === false ) {
									$LeftOuterJoin = ",ref_espece as esp ".$LeftOuterJoin;
								}
								// Attention, il faut rajouter une condition dans le cas ou on ajoute des variables environnement
								if (!(strpos($LeftOuterJoin,"exp_environnement as env") === false )) {
									$WhereSpec .= "and env.id = cph.exp_environnement_id";
								}
							}
							break;
						case "biologie" :
							$labelSelection = "donn&eacute;e(s) biologique(s) ";
							// Construction de la liste d'individus
							// ATTENTION !!!!!! Si la liste ci-dessous est modifiée, il faut imperativement modifier la requete pour calculer le 
							// le coefficient d'extrapolation apres l'execution de la requete 
							$listeChampsSpec = ",fra.id, fra.nombre_total, fra.poids_total,esp.id, esp.libelle, esp.ref_categorie_ecologique_id, esp.ref_categorie_trophique_id,bio.longueur,bio.id";
							$ListeTableSpec = ",exp_fraction as fra,ref_famille as fam";
							$WhereSpec = " 	and fra.exp_coup_peche_id = cph.id and ".$WhereEsp." 
								esp.id = fra.ref_espece_id and
								fam.id = esp.ref_famille_id and
								bio.exp_fraction_id = fra.id ".$compPoisSQL;
							$OrderCom .= ",esp.libelle asc,bio.id asc ";
							$valueCount = "fra.id" ; // pour gerer la pagination						
							$builQuery = true;
							// Gestion des outer join, si pas present, on doit rajouter les tables et leur alias
							// Si on a un outer join, on l'analyse pour etre sur qu'il ne manque pas des declarations de tables dans le SQL
							if (strpos($LeftOuterJoin,"left outer join") === false ) {
								$LeftOuterJoin = ",exp_station as stat,exp_biologie as bio,ref_espece as esp";
							} else {
								if (strpos($LeftOuterJoin,"exp_station as stat") === false ) {
									$LeftOuterJoin = ",exp_station as stat ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"exp_biologie as bio") === false ) {
									$LeftOuterJoin = ",exp_biologie as bio ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"ref_espece as esp") === false ) {
									$LeftOuterJoin = ",ref_espece as esp ".$LeftOuterJoin;
								}
								// Attention, il faut rajouter une condition dans le cas ou on ajoute des variables environnement
								if (!(strpos($LeftOuterJoin,"exp_environnement as env") === false )) {
									$WhereSpec .= "and env.id = cph.exp_environnement_id";
								}
							}
							break;	
						case "trophique" :
							// Construction de la liste d'individus
							$labelSelection = "donn&eacute;e(s) trophique(s) ";
							$listeChampsSpec = ",fra.nombre_total, fra.poids_total,esp.id, esp.libelle, esp.ref_categorie_ecologique_id, esp.ref_categorie_trophique_id,bio.longueur,bio.id,trop.exp_contenu_id,cont.libelle,bio.exp_remplissage_id";
							$ListeTableSpec = ",exp_fraction as fra,ref_famille as fam,exp_trophique as trop, exp_contenu as cont";
							$WhereSpec = " 	and fra.exp_coup_peche_id = cph.id and ".$WhereEsp."  
								esp.id = fra.ref_espece_id and
								fam.id = esp.ref_famille_id and
								bio.exp_fraction_id = fra.id and 
								trop.exp_biologie_id = bio.id 	and
								cont.id = trop.exp_contenu_id ".$compPoisSQL;	
							$OrderCom .= ",esp.libelle asc,bio.id asc,trop.exp_contenu_id asc";
							$valueCount = "bio.id" ; // pour gerer la pagination
							$builQuery = true;	
							// Gestion des outer join, si pas present, on doit rajouter les tables et leur alias
							// Si on a un outer join, on l'analyse pour etre sur qu'il ne manque pas des declarations de tables dans le SQL
							if (strpos($LeftOuterJoin,"left outer join") === false ) {
								$LeftOuterJoin = ",exp_station as stat,exp_biologie as bio,ref_espece as esp";
							} else {
								if (strpos($LeftOuterJoin,"exp_station as stat") === false ) {
									$LeftOuterJoin = ",exp_station as stat ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"exp_biologie as bio") === false ) {
									$LeftOuterJoin = ",exp_biologie as bio ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"ref_espece as esp") === false ) {
									$LeftOuterJoin = ",ref_espece as esp ".$LeftOuterJoin;
								}
								// Attention, il faut rajouter une condition dans le cas ou on ajoute des variables environnement
								if (!(strpos($LeftOuterJoin,"exp_environnement as env") === false )) {
									$WhereSpec .= "and env.id = cph.exp_environnement_id";
								}
							}
							break;
						default	:	
							$labelSelection = "coup(s) de p&ecirc;che ";
							$SQLfinal = "select py.id, py.nom, sy.id, sy.libelle, se.id_dans_systeme, se.nom, cpg.date_debut, cpg.id,cpg.numero_campagne, cph.date_cp, cph.heure_debut, cph.id,cph.numero_coup, cph.protocole from ref_pays as py,ref_systeme as sy,ref_secteur as se,exp_campagne as cpg,exp_coup_peche as cph,exp_station as stat
									where cpg.id = cph.exp_campagne_id and
									stat.id = cph.exp_station_id and
									sy.id = cpg.ref_systeme_id and
									".$WhereSyst."
									py.id = sy.ref_pays_id and
									se.id = stat.ref_secteur_id and
									".$WhereSect." ".$WhereEngin."
									cpg.id in (".$SQLCampagne.")";
							$SQLcountfinal = "select count(cpg.id) from ref_pays as py,ref_systeme as sy,ref_secteur as se,exp_campagne as cpg,exp_coup_peche as cph,exp_station as stat
									where cpg.id = cph.exp_campagne_id and
									stat.id = cph.exp_station_id and
									sy.id = cpg.ref_systeme_id and
									".$WhereSyst."
									py.id = sy.ref_pays_id and
									se.id = stat.ref_secteur_id and
									".$WhereSect." ".$WhereEngin."
									cpg.id in (".$SQLCampagne.") "; // Pour gerer la pagination
							break;
					}
					break;
				// ********** FIN TRAITEMENT PECHE EXPERIMENTALE
				// *
				// *********************************************************************************
				// PECHE ARTISANALE
				// *********************************************************************************		
				case "artisanale" :
					// ********** DEBUT TRAITEMENT PECHE ARTISANALE
					// ********** Gestion de l'affichage des colonnes sélectionnées 
					if ($SQLPeEnquete == "") {
						$_SESSION['pasderesultat'] = true;
						return "pas de resultat <br/>";
					}
					if ($debugAff) {
						$debugTimer = number_format(timer()-$start_while,4);
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp, "TEST PERF : debut traitement donnees artisanales :".$debugTimer,$pasdefichier);
						} else {
							echo "debut traitement donnees artisanales :".$debugTimer."<br/>";
						}
					}		
					$posDEBID = 0 ; 	//Pour gestion regroupement
					$posESPID = 0 ; 	//Pour gestion regroupement
					$posPoids = 0 ; 	//Pour gestion regroupement
					$posNbre = 0 ; 		//Pour gestion regroupement
					$posStat4 = -1;
					$posStat5 = -1;
					$listeChampsSel = "";
					$ListeTableSel = "";
					$WhereSel = "";
					$compSQL = "";
					if ($SQLAgg == "") {
						$WhereAgg = "";
					} else {
						$WhereAgg = "agg.id in (".$SQLAgg.") and";
					}
					$WherePeEnq = "penq.id in (".$SQLPeEnquete.") and ";
					// Grand type engin
					if (!($_SESSION['SQLGTEngin'] == "")) {
						$LabGTE = " - restreint aux grands types engin : ";
						$champSel = explode(",",$_SESSION['SQLGTEngin']);
						$nbrSel = count($champSel)-1;
						$valGTE= "";
						for ($cptSel = 0;$cptSel <= $nbrSel;$cptSel++) {
							if ($valGTE == "") {
								$valGTE = "'".$champSel[$cptSel]."'";
							} else {
								$valGTE .= ",'".$champSel[$cptSel]."'";
							}
							$LabGTE .= $champSel[$cptSel]." ";
						}
						$compGTESQL ="gte.id in (".$valGTE.") and ";
					} else {
						$compGTESQL = "";
						$LabGTE = " - toutes les grands types engin ";
					}
					// Les selections ci-dessous ne sont valables que pour les filieres autres que l'environnement
					switch ($typeAction) {
						case "activite" :
							$compCatEcoSQL = "";
							$compCatTropSQL ="";
							$compPoisSQL ="";
							$compGTESQL = "";
						break;
						case "capture":
							$compCatEcoSQL = "";
							$compCatTropSQL ="";
							$compPoisSQL ="";
							break;
						default :
							$restSupp .= " - ".$LabCatEco." - ".$LabCatTrop." - ".$LabCatPois." - ".$LabGTE ;
						break;
					}
					$AjoutWhere = "";
					// Analyse de la liste des colonnes venant des sélections précédentes, ajout de ces colonnes au fichier
					analyseColonne($typePeche,$typeAction,"",$typeStatistiques);			
					// Analyse des différents composants du where et ajout des and quand nécessaire
					// C'est un peu le bronx pour construire ces SQL, mais pas le choix. On doit pouvoir optimiser...
					if ($compSQL == "" ) {
						$WhereSel = $compCatEcoSQL;
					} else {
						if ($compCatEcoSQL == "") {
							$WhereSel = $compSQL;
						} else {
							$WhereSel = $compSQL." and ".$compCatEcoSQL;
						}
					}
					// Gestion des categories trophiques...
					if (!($compCatTropSQL == "" )) {
						if ($WhereSel == "" ) {
							$WhereSel = $compCatTropSQL;
						} else {
							$WhereSel = $WhereSel." and ".$compCatTropSQL;
						}
					}
					// Enfin on ajoute les noms des nouveaux champs à lire depuis les colonnes
					if ($WhereSel == "" ) {
						$WhereSel = $AjoutWhere;
					} else {
						if (!($AjoutWhere == "")) {
							$WhereSel = $WhereSel." and ".$AjoutWhere;
						}
					}
					// Cas particulier d'aucune sélection des espèces : 
					// On reconstruit cette liste pour l'ensemble de la sélection car on va en avoir besoin
					// pour les catégories trophiques/ecologiques
					$ajouteTable ="";
					if ($SQLEspeces == "") {
						if (!($compGTESQL == "")) {
							$ajouteTable =",art_grand_type_engin as gte"; // On ajoute la selection du GT
					}
					$SQLEsp = "select distinct(afra.ref_espece_id) from art_debarquement as deb,art_fraction as afra,art_agglomeration as agg,			
						art_periode_enquete as penq".$ajouteTable."
						where ".$WhereAgg." ".$WherePeEnq." ".$compGTESQL."
						deb.art_agglomeration_id = agg.id and
						deb.mois = penq.mois and 
						deb.annee = penq.annee and
						deb.art_agglomeration_id = penq.art_agglomeration_id and 
						afra.art_debarquement_id = deb.id ";
						$SQLEspeces = RecupereEspeces($SQLEsp);
						$_SESSION['SQLEspeces'] = $SQLEspeces; // ca va servir pour la suite..
			
					}
					// Si malgré tout, toujours pas d'especes dispo, ben tant pis....
					if ($SQLEspeces == "") {
						$WhereEsp = "";
					} else {
						// Enfin on verifie qu'il n'y a pas eu de restriction supplémentaires
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
						if (!($SQLEspeces == "")) {
							$WhereEsp = "afra.ref_espece_id in (".$SQLEspeces.") and ";
						} else {
							if ($EcrireLogComp ) {
								WriteCompLog ($logComp, "ERREUR SQL especes encor vide ",$pasdefichier);
							}
						}
					}
					// ********** PREPARATION DU SQL
					// Definition de tout ce qui est commun aux peches expérimentales
					// Il va y avoir moins de données communes que pour les peches exp car certaines dependent de la filiere acti ou deb 
					// Donc on cree des variables generales selon qu'on va traiter activite ou debarquement
					// Définition des SQL de base pour les activites (art_activite)
					$listeChampsArt = "py.id, py.nom, sy.id, sy.libelle, se.id_dans_systeme, se.id,se.nom, act.art_agglomeration_id, agg.nom, act.annee, act.mois, act.date_activite, act.id,upec.id";
					$ListeTableArt = "ref_pays as py,ref_systeme as sy,ref_secteur as se,art_periode_enquete as penq";
		
					$WhereArt = "	py.id = sy.ref_pays_id and
									sy.id = se.ref_systeme_id and
									se.id = agg.ref_secteur_id and
									".$WhereSyst." ".$WhereAgg." ".$WhereSect." ".$WherePeEnq." 
									act.art_agglomeration_id = agg.id and
									act.mois = penq.mois and 
									act.annee = penq.annee and
									act.art_agglomeration_id = penq.art_agglomeration_id and
									upec.id = act.art_unite_peche_id";			
					$OrderArt = "order by py.id asc, sy.id asc, agg.nom, act.annee asc,act.mois asc,act.id asc";
					// Définition des SQL de base pour les débarquements (art_debarquement)
					$listeChampsDeb = "py.id, py.nom, sy.id, sy.libelle, se.id_dans_systeme, se.nom,se.id, deb.art_agglomeration_id, agg.nom, deb.annee, deb.mois, deb.id, deb.date_debarquement";
					$ListeTableDeb = "ref_pays as py,ref_systeme as sy,ref_secteur as se,art_periode_enquete as penq";
				
					$WhereDeb = "	py.id = sy.ref_pays_id and
									sy.id = se.ref_systeme_id and
									se.id = agg.ref_secteur_id and
									".$WhereSyst." ".$WhereAgg." ".$WhereSect." ".$WherePeEnq."
									gte.id = deb.art_grand_type_engin_id and
									".$compGTESQL."
									deb.art_agglomeration_id = agg.id and
									deb.mois = penq.mois and 
									deb.annee = penq.annee and
									deb.art_agglomeration_id = penq.art_agglomeration_id and
									upec.id = deb.art_unite_peche_id";
					$OrderDeb = "order by py.id asc, sy.id asc, agg.nom, deb.annee asc,deb.mois asc,deb.id asc";
					// ********** CONSTRUCTION DES SQL DEFINITIFS PAR FILIERE
					switch ($typeAction) {
						case "activite" :
							// On considere les données d'activité. On commence par mettre à jour les variables communes *com
							$listeChampsCom = $listeChampsArt;
							$ListeTableCom = $ListeTableArt ;
							$WhereCom = $WhereArt ;
							$OrderCom = $OrderArt ;
							$labelSelection = "donn&eacute;e(s) d'activit&eacute;";	
							//echo $listeChampsSel."<br/>";
							if (strpos($listeChampsSel,"act.art_grand_type_engin_id") === false) {
								$listeChampsSpec = ",act.art_type_activite_id,act.nbre_unite_recencee,act.art_grand_type_engin_id";
							} else {
								$listeChampsSpec = ",act.art_type_activite_id,act.nbre_unite_recencee";
							}
							//echo $listeChampsSpec."<br/>";
							$ListeTableSpec = ""; 
							$WhereSpec = "";	
							$ConstIDunique = "ART-##-12"; // Ce qui apres le -##-n sera remplacé par la valeur d'index n de la lecture de la requete par exemple, ici, on va recuperer art.id  
							$valueCount = "act.id" ; // pour gerer la pagination				
							$builQuery = true;
							if (strpos($LeftOuterJoin,"left outer join") === false ) {
								$LeftOuterJoin = ",art_activite as act,art_agglomeration as agg,art_unite_peche as upec";
							}  else {
								if (strpos($LeftOuterJoin,"art_activite as act") === false ) {
									$LeftOuterJoin = ",art_activite as act ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_agglomeration as agg") === false ) {
									$LeftOuterJoin = ",art_agglomeration as agg ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_unite_peche as upec") === false ) {
									$LeftOuterJoin = ",art_unite_peche as upec ".$LeftOuterJoin;
								}
							}
							break;			
						case "capture" :
							// Liste des debarquements.
							$labelSelection = "donn&eacute;e(s) de capture";	
							$listeChampsCom = $listeChampsDeb;
							$ListeTableCom = $ListeTableDeb ;
							$WhereCom = $WhereDeb ;
							$OrderCom = $OrderDeb ;
							if (strpos($listeChampsSel,"deb.art_grand_type_engin_id") === false) {
								$listeChampsSpec = ", deb.poids_total,debrec.poids_total,deb.art_unite_peche_id,deb.art_grand_type_engin_id";
							} else {
								$listeChampsSpec = ", deb.poids_total,debrec.poids_total,deb.art_unite_peche_id";
							}
							$ListeTableSpec = " ,art_debarquement_rec as debrec "; 
							$WhereSpec = " and debrec.art_debarquement_id = deb.id ";
							$ConstIDunique = "DEB-##-11";
							$valueCount = "deb.id" ; // pour gerer la pagination	
							$builQuery = true;
							if (strpos($LeftOuterJoin,"left outer join") === false ) {
								$LeftOuterJoin = ",art_debarquement as deb,art_grand_type_engin as gte,art_agglomeration as agg,art_unite_peche as upec";
							} else {
								if (strpos($LeftOuterJoin,"art_debarquement as deb") === false ) {
									$LeftOuterJoin = ",art_debarquement as deb ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_grand_type_engin as gte") === false ) {
									$LeftOuterJoin = ",art_grand_type_engin as gte ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_agglomeration as agg") === false ) {
									$LeftOuterJoin = ",art_agglomeration as agg ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_unite_peche as upec") === false ) {
									$LeftOuterJoin = ",art_unite_peche as upec ".$LeftOuterJoin;
								}
							}
							break;
						case "NtPart" :
							$labelSelection = "donn&eacute;e(s) NtPt";				
							$listeChampsCom = $listeChampsDeb;
							$ListeTableCom = $ListeTableDeb ;
							$WhereCom = $WhereDeb ;
							if (!($_SESSION['listeRegroup'] == "")) {
								$OrderCom = $OrderDeb."  , afra.ref_espece_id asc ";
								} else {
								$OrderCom = $OrderDeb ;
							}
							$posDEBID = 11 ; //position deb.id - 1 / Pour gestion regroupement
							$posESPID = 21 ; //position afra.ref_espece_id - 1 / Pour gestion regroupement
							$posESPNom = 22 ; //position esp.libelle - 1 / Pour gestion regroupement
							$posStat1 = 17 ; //position afra.poids - 1 / Pour gestion regroupement
							$posStat2 = 18 ; //position afrarec.poids - 1 / Pour gestion regroupement
							$posStat3 = 19 ; //position afra.nbre_poissons
							$posStat4 = 20 ; //position afrarec.nbre_poissons
							$posStat5 = -1;
							if (strpos($listeChampsSel,"deb.art_grand_type_engin_id") === false) {
								$listeChampsSpec = ", deb.poids_total,debrec.poids_total,deb.art_unite_peche_id,afra.id,afra.poids,afrarec.poids, afra.nbre_poissons, afrarec.nbre_poissons, afra.ref_espece_id,esp.libelle, deb.art_grand_type_engin_id";
							} else {
								$listeChampsSpec = ", deb.poids_total,debrec.poids_total,deb.art_unite_peche_id,afra.id,afra.poids,afrarec.poids,afra.nbre_poissons, afrarec.nbre_poissons, afra.ref_espece_id, esp.libelle";
							}
							if (strpos($listeChampsSel,"catt.id") === false) {
								$listeChampsSpec .= ",esp.ref_categorie_trophique_id";
							}
							if (strpos($listeChampsSel,"cate.id") === false) {
								$listeChampsSpec .= ",esp.ref_categorie_ecologique_id";
							}					
							$ListeTableSpec = ", ref_famille as fam,art_debarquement_rec as debrec,art_fraction as afra left outer join art_fraction_rec as afrarec on afrarec.id = afra.id"; 
							$WhereSpec = " 	and ".$WhereEsp." afra.art_debarquement_id = deb.id 
											and debrec.art_debarquement_id = deb.id
											and esp.id = afra.ref_espece_id	
											and fam.id = esp.ref_famille_id ".$compPoisSQL;					
							$ConstIDunique = "DEB-##-11";
							$valueCount = "deb.id" ; // pour gerer la pagination	
							$builQuery = true;
							if (strpos($LeftOuterJoin,"left outer join") === false ) {
								$LeftOuterJoin = ",art_debarquement as deb,ref_espece as esp,art_grand_type_engin as gte,art_agglomeration as agg,art_unite_peche as upec";
							} else {
								if (strpos($LeftOuterJoin,"art_debarquement as deb") === false ) {
									$LeftOuterJoin = ",art_debarquement as deb ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"ref_espece as esp") === false ) {
									$LeftOuterJoin = ",ref_espece as esp ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_grand_type_engin as gte") === false ) {
									$LeftOuterJoin = ",art_grand_type_engin as gte ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_agglomeration as agg") === false ) {
									$LeftOuterJoin = ",art_agglomeration as agg ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_unite_peche as upec") === false ) {
									$LeftOuterJoin = ",art_unite_peche as upec ".$LeftOuterJoin;
								}
							}
							break;
						case "taillart" :
							$labelSelection = "donn&eacute;e(s) de tailles";	
							$listeChampsCom = $listeChampsDeb;
							$ListeTableCom = $ListeTableDeb ;
							$WhereCom = $WhereDeb ;
							if (!($_SESSION['listeRegroup'] == "")) {
								$OrderCom = $OrderDeb."  , afra.ref_espece_id asc,ames.taille";
								} else {
								$OrderCom = $OrderDeb."   ,ames.taille";
							}
							$listeChampsDeb = "py.id, py.nom, sy.id, sy.libelle, se.id_dans_systeme, se.nom,se.id, deb.art_agglomeration_id, agg.nom, deb.annee, deb.mois, deb.id, deb.date_debarquement";
							$posDEBID = 11 ; //position deb.id - 1 / Pour gestion regroupement
							$posESPID = 22 ; //position afra.ref_espece_id - 1 / Pour gestion regroupement
							$posESPNom = 23 ; //position esp.libelle - 1 / Pour gestion regroupement
							$posStat1 = -1; //position afra.poids - 1 / Pour gestion regroupement
							$posStat2 = -1; //Pas de cumul pour les structures de taille
							$posStat3 = -1; //Pas de cumul pour les structures de taille
							$posStat4 = -1; //Pas de cumul pour les structures de taille
							$posStat5 = -1; //Pas de cumul pour les structures de taille
							if (strpos($listeChampsSel,"deb.art_grand_type_engin_id") === false) {
								$listeChampsSpec = ", deb.poids_total, debrec.poids_total,deb.art_unite_peche_id,afra.id, afra.poids,afrarec.poids,  afra.nbre_poissons,  afrarec.nbre_poissons,ames.taille, afra.ref_espece_id, esp.libelle, deb.art_grand_type_engin_id ";
							} else {
								$listeChampsSpec = ", deb.poids_total, debrec.poids_total,deb.art_unite_peche_id,afra.id, afra.poids,afrarec.poids,  afra.nbre_poissons,  afrarec.nbre_poissons,ames.taille, afra.ref_espece_id, esp.libelle ";
							}
							if (strpos($listeChampsSel,"catt.id") === false) {
								$listeChampsSpec .= ",esp.ref_categorie_trophique_id";
							}
							if (strpos($listeChampsSel,"cate.id") === false) {
								$listeChampsSpec .= ",esp.ref_categorie_ecologique_id";
							}
							$ListeTableSpec = ",ref_famille as fam,art_debarquement_rec as debrec, (art_fraction as afra left outer join art_fraction_rec as afrarec on afrarec.id = afra.id) left outer join art_poisson_mesure as ames on ames.art_fraction_id = afra.id"; 
							$WhereSpec = " 	and ".$WhereEsp." afra.art_debarquement_id = deb.id 
											and debrec.art_debarquement_id = deb.id
											and esp.id = afra.ref_espece_id	
											and fam.id = esp.ref_famille_id ".$compPoisSQL;						
							$ConstIDunique = "DEB-##-11";
							$valueCount = "deb.id" ; // pour gerer la pagination
							$builQuery = true;
							if (strpos($LeftOuterJoin,"left outer join") === false ) {
								$LeftOuterJoin = ",art_debarquement as deb,ref_espece as esp,art_grand_type_engin as gte,art_agglomeration as agg,art_unite_peche as upec";
							} else {
								if (strpos($LeftOuterJoin,"art_debarquement as deb") === false ) {
									$LeftOuterJoin = ",art_debarquement as deb ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"ref_espece as esp") === false ) {
									$LeftOuterJoin = ",ref_espece as esp ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_grand_type_engin as gte") === false ) {
									$LeftOuterJoin = ",art_grand_type_engin as gte ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_agglomeration as agg") === false ) {
									$LeftOuterJoin = ",art_agglomeration as agg ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_unite_peche as upec") === false ) {
									$LeftOuterJoin = ",art_unite_peche as upec ".$LeftOuterJoin;
								}
							}
							break;
						case "engin" :
							$labelSelection = "donn&eacute;e(s) d'engin";	
							$listeChampsCom = $listeChampsDeb;
							$ListeTableCom = $ListeTableDeb ;
							$WhereCom = $WhereDeb ;
							$OrderCom = $OrderDeb ;	
							$listeChampsSpec = ",deb.art_unite_peche_id,deb.art_grand_type_engin_id, aeng.art_type_engin_id";
							$ListeTableSpec = ", art_engin_peche as aeng, art_type_engin as teng"; 
							$WhereSpec = " and aeng.art_debarquement_id = deb.id and teng.id = aeng.art_type_engin_id";						
							$ConstIDunique = "DEB-##-11";
							$valueCount = "deb.id" ; // pour gerer la pagination
							$builQuery = true;
							if (strpos($LeftOuterJoin,"left outer join") === false ) {
								$LeftOuterJoin = ",art_debarquement as deb,art_grand_type_engin as gte,art_agglomeration as agg,art_unite_peche as upec";
							} else {
								if (strpos($LeftOuterJoin,"art_debarquement as deb") === false ) {
									$LeftOuterJoin = ",art_debarquement as deb ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_grand_type_engin as gte") === false ) {
									$LeftOuterJoin = ",art_grand_type_engin as gte ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_agglomeration as agg") === false ) {
									$LeftOuterJoin = ",art_agglomeration as agg ".$LeftOuterJoin;
								}
								if (strpos($LeftOuterJoin,"art_unite_peche as upec") === false ) {
									$LeftOuterJoin = ",art_unite_peche as upec ".$LeftOuterJoin;
								}
							}
							break;															
						default	:	
							$labelSelection = "p&eacute;riode(s) d'enqu&ecirc;te";
							$SQLfinal = "select * from art_periode_enquete as penq
											where penq.id in (".$SQLPeEnquete.")";
							$SQLcountfinal = "select count(*) from art_periode_enquete as penq
											where penq.id in (".$SQLPeEnquete.")";; // pour gerer la pagination	
							break;
					}
					break;
				// ********** FIN TRAITEMENT PECHE ARTISANALE
				default:
					echo "Pas de selection de type de peche. Recommencez une selection...<br/>";
					exit;
			} 
			break;
		// ********** FIN TRAITEMENT EXTRACTION
		// #
		// #####################################################################################
		// STATISTIQUES
		// #####################################################################################		
		case "statistiques" :
			// ********** DEBUT TRAITEMENTDES STATISTIQUES
			// ********** Gestion de l'affichage des colonnes sélectionnées 
			// Le traitement est different des deux premiers cas : on va construire plusieurs requetes a executer a la suite
			//
			$listeChampsSel = "";
			$ListeTableSel = "";
			$WhereSel = "";
			$compSQL = "";
			if ($SQLAgg == "") {
				$WhereAgg = "";
			} else {
				$WhereAgg = "agg.id in (".$SQLAgg.") and";
			}
			if ($SQLPeEnquete == "") {
				$WherePeEnq = "";
			} else {
				$WherePeEnq = "penq.id in (".$SQLPeEnquete.") and ";
			}
			// Grand type engin
			if (!($_SESSION['SQLGTEngin'] == "")) {
				$LabGTE = " - restreint aux grands types engin : ";
				$champSel = explode(",",$_SESSION['SQLGTEngin']);
				$nbrSel = count($champSel)-1;
				$valGTE= "";
				for ($cptSel = 0;$cptSel <= $nbrSel;$cptSel++) {
					if ($valGTE == "") {
						$valGTE = "'".$champSel[$cptSel]."'";
					} else {
						$valGTE .= ",'".$champSel[$cptSel]."'";
					}
					$LabGTE .= $champSel[$cptSel]." ";
				}
				$compGTESQL ="gte.id in (".$valGTE.") and ";
			} else {
				$compGTESQL = "";
				$LabGTE = " - toutes les grands types engin ";
			}

			// Cas particulier d'aucun sélection des espèces : 
			// On reconstruit cette liste pour l'ensemble de la sélection car on va en avoir besoin
			// pour les catégories trophiques/ecologiques
			$ajouteTable ="";
			if ($SQLEspeces == "") {
				if (!($compGTESQL == "")) {
					$ajouteTable =",art_grand_type_engin as gte";
			}
			$SQLEsp = "select distinct(asp.ref_espece_id) from ref_pays as py,ref_systeme as sy,ref_secteur as se,art_agglomeration as agg,art_periode_enquete as penq, art_stat_totale as ast,art_stat_sp as asp,ref_espece as esp 
			where ".$WhereSyst." ".$WhereAgg." ".$WhereSect." ".$WherePeEnq."	
				agg.id = penq.art_agglomeration_id and
				ast.art_agglomeration_id = penq.art_agglomeration_id and
				ast.annee = penq.annee and
				ast.mois = penq.mois and
				py.id = sy.ref_pays_id and
				sy.id = se.ref_systeme_id and
				se.id = agg.ref_secteur_id  and asp.art_stat_totale_id = ast.id and esp.id = asp.ref_espece_id";
				$SQLEspeces = RecupereEspeces($SQLEsp);
				$_SESSION['SQLEspeces'] = $SQLEspeces; // ca va servir pour la suite..
			}
			// Si malgré tout, toujours pas d'especes dispo, ben tant pis....
			if ($SQLEspeces == "") {
				$WhereEsp = "";
			} else {
				// Enfin on verifie qu'il n'y a pas eu de restriction supplémentaires
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
			}
			$EspAcompter=explode(",",$SQLEspeces);
			$nbreEsp = count($EspAcompter);
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "INFO : ".$nbreEsp." especes trouvees = ".$SQLEspeces,$pasdefichier);
			} else {
			echo "pas ecriture log<br/>";	
			}
			// On charge les colonnes supplémentaires juste pour l'affichage.
			analyseColonne("statistiques",$typeAction,"ast",$typeStatistiques);
			$toutesColonnes = recupereTouteColonnes("statistiques",$typeStatistiques); // C'est juste pour charger le nom des alias dans la variable de session 
			// Dans le cas des stats par systeme, on n'a pas de liste de période d'enquetes, on la reconstruit.
			// $SQLdateDebut annee/mois
			// $SQLdateFin annee/mois
			if ($WherePeEnq == "") {
				// si pas de secteur selectionné, on recupere toutes les agglo du systeme
				if ($SQLSecteur=="") {
					if ($SQLSysteme == "") {
						// Ca ne devrait pas arriver, les stats sont par systeme.
						$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;Erreur, pas de systeme selectionne...<br/>";
					} else {
						$SQLSecteurPenq = "select id from ref_secteur where ref_systeme_id in (".$SQLSysteme.")";
					} 
				} else {
					$SQLSecteurPenq = $SQLSecteur;
				}
				//$SQLperEnq = "select id from art_periode_enquete as penq where penq.annee||'/'||penq.mois between  '".$SQLdateDebut."' and  '".$SQLdateFin."'
				//and penq.art_agglomeration_id in (select id from art_agglomeration where ref_secteur_id in (".$SQLSecteurPenq."))";
				// Dommage le script ci-dessus ne fonctionne pas toujours... C'etait elegant. On fait dans le bourrin.
				$SQLperEnq = "select id,annee,mois from art_periode_enquete as penq where penq.art_agglomeration_id in (select id from art_agglomeration where ref_secteur_id in (".$SQLSecteurPenq."))";
				//echo $SQLperEnq."<br/>";
				$SQLperEnqResult = pg_query($connectPPEAO,$SQLperEnq);
				$erreurSQL = pg_last_error($connectPPEAO);
		
				if ( !$SQLperEnqResult ) { 
					if ($EcrireLogComp ) {
						WriteCompLog ($logComp, "ERREUR : Erreur query final ".$SQLfinal." (erreur complete = ".$erreurSQL.")",$pasdefichier);
					}
					$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;erreur query ".$SQLfinal." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
					$erreurProcess = true;
				} else {
					if (pg_num_rows($SQLperEnqResult) == 0) {
						// Avertissement
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp, "Pas de resultat disponible pour la selection ".$SQLfinal,$pasdefichier);
						}
					} else {
						$dateDeb = explode("/",$SQLdateDebut);
						$dateFin = explode("/",$SQLdateFin);
						$dateDebaComp = intval($dateDeb[0].str_pad($dateDeb[1], 2, "0", STR_PAD_LEFT)."01");
						$jfmdateFin  = days_in_month($dateFin[0],$dateFin[1]);
						$dateFinaComp = intval($dateFin[0].str_pad($dateFin[1], 2, "0", STR_PAD_LEFT).$jfmdateFin);		
						while ($PenqRow = pg_fetch_row($SQLperEnqResult) ) {
							// On explose les dates pour contruire une valeur comparable.
							$dateEnq = intval($PenqRow[1].str_pad($PenqRow[2], 2, "0", STR_PAD_LEFT)."01");
							//echo "date enquete = ".$dateEnq." date deb = ".$dateDebaComp." date fin = ".$dateFinaComp."<br/>";
							if ( $dateEnq >= $dateDebaComp && $dateEnq <= $dateFinaComp ) {	 
								if ($SQLPeEnquete == "") {
									$SQLPeEnquete = $PenqRow[0];
								} else {
									$SQLPeEnquete .= ",".$PenqRow[0];
								}
							}
																													   
						}
					}
				}
				$WherePeEnq = "penq.id in (".$SQLPeEnquete.") and ";
				pg_free_result($SQLperEnqResult);
			}
			//echo $WherePeEnq."<br/>";
			// ********** DEBUT STATISTIQUES. Agglomeration et generale se basent sur les memes tables, pour générales, on va effectuer des calculs
			if ($typeStatistiques == "generales") {
				// On doit tester si on a un effort par secteur ou uniquement sur le systeme.
				// On prend le premier secteur qui vient. Si il existe un effort sur le systeme et pas sur le secteur 
				// alors on considere que pour cette selection on est sur un effort par systeme.
				// Le tri du SQL en sera changé.
					$explodedatedebut = explode("/",$SQLdateDebut);
					$anneeEnCours=$explodedatedebut[0];
					$moisEnCours=$explodedatedebut[1];
					$explodeSysteme = explode(",",$SQLSysteme);
					$systemeEncours = $explodeSysteme[0];
					$explodeSecteur = explode(",",$SQLSecteur);
					$sectEnCours = $explodeSecteur[0];
					//echo $systemeEncours."-".$sectEnCours."-".$anneeEnCours."-".$moisEnCours."<br/>";
					$RecEffortSysSect = recupereEffort($systemeEncours,$sectEnCours,$anneeEnCours,$moisEnCours,"TOUS");
					$tabEffortSysSect = explode ("&#&",$RecEffortSysSect); // tableau contenant le resultat de la requete : [type]-[valeur sect/syst]&#&[valeur effort] 
					$tabsectSystEncours = explode ("-",$tabEffortSysSect[0]);
					$typesectSystEncours = $tabsectSystEncours[0]; // Va contenir soit sect soit syst si l'effort est trouvé au niveau du systeme ou du secteur

			}
			// ********** CONSTRUCTION DES SQL DEFINITIFS PAR TYPE DE STATISTIQUES CHOISIS
			$listeChampsCom = "py.id, py.nom, sy.id, sy.libelle, se.id_dans_systeme, se.nom ,agg.id ,agg.nom ,penq.annee ,penq.mois";
			$ListeTableCom = "ref_pays as py,ref_systeme as sy,ref_secteur as se,art_agglomeration as agg";
			$WhereCom = 	$WhereSyst." ".$WhereAgg." ".$WhereSect." ".$WherePeEnq."	
							agg.id = penq.art_agglomeration_id and
							ast.mois = penq.mois and
							ast.annee = penq.annee and 
							ast.art_agglomeration_id = penq.art_agglomeration_id and
							py.id = sy.ref_pays_id and
							sy.id = se.ref_systeme_id and
							se.id = agg.ref_secteur_id " ;		
			$posSystemeID = 2 ; //position systeme ID pour calcul stats generales
			if ($typeStatistiques == "generales" && $typesectSystEncours == "syst") {
				// Le tri ne prend pas en compte les secteurs, on fait un traitement sur le systeme / annee / mois
				$OrderCom = "order by py.id asc,sy.id asc,penq.annee asc,penq.mois asc";
			} else {
				$OrderCom = "order by py.id asc,sy.id asc,se.id asc,penq.annee asc,penq.mois asc";
			}
			switch ($typeAction) {
				case "stats" :
					// On construit les differentes requetes a executer a la suite:
					// Les variables pour l'affichage a l'ecran :
					$labelSelection = "Statistiques totales";	
					$listeChampsSpec = ",ast.pue,ast.fm,ast.cap,ast.id,se.id";
					
					$ListeTableSpec = ",art_periode_enquete as penq, art_stat_totale as ast"; 
					$WhereSpec = " and ast.art_agglomeration_id = penq.art_agglomeration_id";						
					$ConstIDunique = "AST-##-13";
					$valueCount = "ast.id" ; // pour gerer la pagination
					$builQuery = true;
					$posSecteurID = 14 ; //position systeme ID pour calcul stats generales
					// ******************
					// **** art_stat_totale
					$listeChampsSpecast = ",ast.pue,ast.fm,ast.cap,ast.id,se.id";
					$ListeTableSpecast = ",art_periode_enquete as penq, art_stat_totale as ast"; 
					$WhereSpecast = " and ast.art_agglomeration_id = penq.art_agglomeration_id";
					$OrderComast = "";
					$ConstIDuniqueast = "AST-##-13";
					$posSecteurIDast = 14 ; //position systeme ID pour calcul stats generales
					$posGTEIDast = -1 ; //pas de GTE
					$posStat1ast = 11 ; //position stat 1 a cumuler  - 1 / Pour stats generales
					$posStat2ast = 12 ; //position stat 2 a cumuler  - 1 / Pour stats generales
					$posStat3ast = -1 ; //position stat 3 a cumuler  - 1 / Pour stats generales
					$posStat4ast = -1 ;
					$posStat5ast = -1 ;
					$posRupSupast = -1;
					// ******************
					// **** art_stat_sp
					$listeChampsSpecasp = ",asp.ref_espece_id,esp.libelle ,asp.pue_sp,asp.cap_sp ,asp.id ,ast.id,se.id,ast.cap,ast.pue,ast.fm";
					if ($_SESSION['listeColonne'] == "XtoutX") { 
						$ListeTableSpecasp = ",art_periode_enquete as penq, art_stat_totale as ast,art_stat_sp as asp"; 
					} else {
						$ListeTableSpecasp = ",art_periode_enquete as penq, art_stat_totale as ast,art_stat_sp as asp,ref_espece as esp"; 
					}
					$WhereSpecasp = "	and asp.art_stat_totale_id = ast.id and esp.id = asp.ref_espece_id";
					if (!($SQLEspeces == "")) {
						$WhereSpecasp .= " and asp.ref_espece_id in (".$SQLEspeces.") ";
					}
					if ($typeStatistiques == "agglomeration") {
						$OrderComasp = ",agg.id asc,asp.ref_espece_id asc";
					} else {
						$OrderComasp = ",asp.ref_espece_id asc";
					}
					$ConstIDuniqueasp = "AST-##-15";
					$posSecteurIDasp = 16 ; //position systeme ID pour calcul stats generales
					$posGTEIDasp = -1 ; //pas de GTE
					// Gestion des positionnements pour les regroupements
					$posDEBIDasp = 15 ; //position ast.id - 1 / Pour gestion regroupement
					$posESPIDasp = 10 ; //position afra.ref_espece_id - 1 / Pour gestion regroupement
					$posESPNomasp = 11 ; //position esp.libelle - 1 / Pour gestion regroupement
					$posRupSupasp = -1;
					$posStat1asp = 12 ; //position stat 1 a cumuler  - 1 / Pour gestion regroupement
					$posStat2asp = 13 ; //position stat 2 a cumuler  - 1 / Pour gestion regroupement
					$posStat3asp = -1 ; //position stat 3 a cumuler  - 1 / Pour gestion regroupement
					$posStat4asp = -1 ;
					$posStat5asp = -1 ;
					$posProrataTotasp = 17; // Pour calcul du prorata pour les stats générales
					$posProrataEspGTasp = 13; // Pour calcul du prorata pour les stats générales
					// ******************
					// **** art_taille_sp
					$listeChampsSpecats = ",asp.ref_espece_id,esp.libelle, asp.pue_sp,asp.cap_sp,ats.li,ats.xi,asp.id,ast.id,ats.id,se.id,ast.cap,ast.pue,ast.fm";
					if ($_SESSION['listeColonne'] == "XtoutX") { 
						$ListeTableSpecats = ",art_periode_enquete as penq, art_stat_totale as ast,art_stat_sp as asp,art_taille_sp as ats"; 
					} else {
						$ListeTableSpecats = ",art_periode_enquete as penq, art_stat_totale as ast,art_stat_sp as asp,art_taille_sp as ats,ref_espece as esp";  
					}
					$WhereSpecats = " 	and ats.art_stat_sp_id = asp.id and
											asp.art_stat_totale_id = ast.id and esp.id = asp.ref_espece_id";
					if (!($SQLEspeces == "")) {
						$WhereSpecats .= " and  asp.ref_espece_id in (".$SQLEspeces.") ";
					}
					if ($typeStatistiques == "agglomeration") {
						$OrderComats =",agg.id asc,asp.ref_espece_id asc,ats.li asc";
					} else {
						$OrderComats =",asp.ref_espece_id asc,ats.li asc";
					}
					$ConstIDuniqueats = "AST-##-17";
					$posSecteurIDats = 19 ; //position systeme ID pour calcul stats generales
					$posGTEIDats = -1 ; //pas de GTE
					// Gestion des positionnements pour les regroupements
					$posDEBIDats = 17 ; //position ast.id - 1 / Pour gestion regroupement
					$posESPIDats = 10 ; //position afra.ref_espece_id - 1 / Pour gestion regroupement
					$posESPNomats = 11 ; //position esp.libelle - 1 / Pour gestion regroupement
					$posRupSupats = 14;	//position longueur - 1 / Pour gestion regroupement - rupture supplemenataire pour les repartitions par taille
					$posStat1ats = 15 ; //position stat 1 a cumuler  - 1 / Pour gestion regroupement
					$posStat2ats = -1 ; //position stat 2 a cumuler  - 1 / Pour gestion regroupement
					$posStat3ats = -1 ; //position stat 3 a cumuler  - 1 / Pour gestion regroupement
					$posStat4ats = -1 ;
					$posStat5ats = -1 ;
					$posProrataTotats = 20; // Pour calcul du prorata pour les stats générales
					$posProrataEspGTats = 13; // Pour calcul du prorata pour les stats générales
					// ******************
					// **** art_stat_gt	attgt
					$listeChampsSpecasgt = ", asgt.art_grand_type_engin_id,gte.libelle,asgt.pue_gt,asgt.fm_gt,asgt.cap_gt, asgt.id,ast.id,se.id";
					$ListeTableSpecasgt = ",art_periode_enquete as penq, art_stat_gt as asgt, art_stat_totale as ast,art_grand_type_engin as gte"; 
					$WhereSpecasgt = "	and asgt.art_stat_totale_id = ast.id 
											and gte.id = 	asgt.art_grand_type_engin_id";
					$OrderComasgt =",gte.id asc";
					$ConstIDuniqueasgt = "AST-##-16";
					$posSecteurIDasgt = 17 ; //position systeme ID pour calcul stats generales
					$posGTEIDasgt = 10 ; //position systeme ID pour calcul stats generales
					$posStat1asgt = 13 ; //position stat 1 a cumuler  - 1 / Pour stats generales
					$posStat2asgt = 14 ; //position stat 2 a cumuler  - 1 / Pour stats generales
					$posStat3asgt = -1 ; //position stat 3 a cumuler  - 1 / Pour stats generales
					$posStat4asgt = -1 ;
					$posStat5asgt = -1 ;
					$posRupSupasgt = -1;
					// ******************
					// **** art_stat_gt_sp
					$listeChampsSpecattgt = ",asgt.art_grand_type_engin_id,gte.libelle,attgt.ref_espece_id, esp.libelle,attgt.pue_gt_sp,attgt.cap_gt_sp,attgt.id, asgt.id, ast.id,se.id,asgt.cap_gt,asgt.pue_gt,asgt.fm_gt";
					if ($_SESSION['listeColonne'] == "XtoutX") { 
						$ListeTableSpecattgt = ",art_periode_enquete as penq, art_stat_gt_sp as attgt,art_stat_gt as asgt, art_stat_totale as ast,art_grand_type_engin as gte"; 
					} else {
						$ListeTableSpecattgt = ",art_periode_enquete as penq, art_stat_gt_sp as attgt,art_stat_gt as asgt, art_stat_totale as ast,art_grand_type_engin as gte,ref_espece as esp"; 
					}
					$WhereSpecattgt = "	and attgt.art_stat_gt_id = asgt.id  
											and asgt.art_stat_totale_id = ast.id 
											and gte.id = asgt.art_grand_type_engin_id
											and esp.id = attgt.ref_espece_id";
					if (!($SQLEspeces == "")) {
						$WhereSpecattgt .= " and  attgt.ref_espece_id in (".$SQLEspeces.")";
					}
					if ($typeStatistiques == "agglomeration") {
						$OrderComattgt =",agg.id,gte.id asc,attgt.ref_espece_id asc";
					} else {
						$OrderComattgt =",gte.id asc,attgt.ref_espece_id asc";
					}
					$ConstIDuniqueattgt = "AST-##-18";
					$posSecteurIDattgt = 19 ; //position systeme ID pour calcul stats generales
					$posGTEIDattgt = 10 ; //position systeme ID pour calcul stats generales
					// Gestion des positionnements pour les regroupements
					$posDEBIDattgt = 18 ; //position ast.id - 1 / Pour gestion regroupement
					$posESPIDattgt = 12 ; //position afra.ref_espece_id - 1 / Pour gestion regroupement
					$posESPNomattgt = 13 ; //position esp.libelle - 1 / Pour gestion regroupement
					$posRupSupattgt = -1;
					$posStat1attgt = 14 ; //position stat 1 a cumuler  - 1 / Pour gestion regroupement
					$posStat2attgt = 15 ; //position stat 2 a cumuler  - 1 / Pour gestion regroupement
					$posStat3attgt = -1;
					$posStat4attgt = -1 ;
					$posStat5attgt = -1 ;
					$posProrataTotattgt = 20; // Pour calcul du prorata pour les stats générales
					$posProrataEspGTattgt = 15; // Pour calcul du prorata pour les stats générales
					// ******************
					// art_taille_gt_sp
					$listeChampsSpecatgts = ", asgt.art_grand_type_engin_id,gte.libelle,attgt.ref_espece_id, esp.libelle, attgt.pue_gt_sp, attgt.cap_gt_sp, atgts.li, atgts.xi,atgts.id, attgt.id, asgt.id, ast.id,se.id,asgt.cap_gt,asgt.pue_gt,asgt.fm_gt";
					if ($_SESSION['listeColonne'] == "XtoutX") { 
						$ListeTableSpecatgts = ",art_periode_enquete as penq, art_taille_gt_sp as atgts, art_stat_gt_sp as attgt,art_stat_gt as asgt, art_stat_totale as ast,art_grand_type_engin as gte"; 
					} else {
						$ListeTableSpecatgts = ",art_periode_enquete as penq, art_taille_gt_sp as atgts, art_stat_gt_sp as attgt,art_stat_gt as asgt, art_stat_totale as ast,art_grand_type_engin as gte,ref_espece as esp";
					}
					$WhereSpecatgts = "	and atgts.art_stat_gt_sp_id = attgt.id  
											and attgt.art_stat_gt_id = asgt.id  
											and asgt.art_stat_totale_id = ast.id 
											and gte.id = asgt.art_grand_type_engin_id
											and esp.id = attgt.ref_espece_id";
					if (!($SQLEspeces == "")) {
						$WhereSpecatgts .= " and  attgt.ref_espece_id in (".$SQLEspeces.")";
					}
					if ($typeStatistiques == "agglomeration") {
						$OrderComatgts =",agg.id asc,gte.id asc,attgt.ref_espece_id asc,atgts.li asc";
					} else {
						$OrderComatgts =",gte.id asc,attgt.ref_espece_id asc,atgts.li asc";
					}
					$ConstIDuniqueatgts = "AST-##-21";
					$posSecteurIDatgts = 22 ; //position systeme ID pour calcul stats generales
					$posGTEIDatgts = 10 ; //position systeme ID pour calcul stats generales
					// Gestion des positionnements pour les regroupements
					$posDEBIDatgts = 21 ; //position ast.id - 1 / Pour gestion regroupement
					$posESPIDatgts = 12 ; //position attgt.ref_espece_id - 1 / Pour gestion regroupement
					$posESPNomatgts = 13 ; //position esp.libelle - 1 / Pour gestion regroupement
					$posRupSupatgts = 16 ; //position longueur - 1 / Pour gestion regroupement - rupture supplemenataire pour les repartitions par taille
					$posStat1atgts = 17 ; //position stat 1 a cumuler  - 1 / Pour gestion regroupement
					$posStat2atgts = -1 ; //position stat 2 a cumuler  - 1 / Pour gestion regroupement
					$posStat3atgts = -1 ; //position stat 3 a cumuler  - 1 / Pour gestion regroupement
					$posStat4atgts = -1 ;
					$posStat5atgts = -1 ;
					$posProrataTotatgts = 23; // Pour calcul du prorata pour les stats générales
					$posProrataEspGTatgts = 15; // Pour calcul du prorata pour les stats générales
					break;
				default	:	
					$labelSelection = "p&eacute;riode(s) d'enqu&ecirc;te";
					$SQLfinal = "select * from art_periode_enquete as penq
									where penq.id in (".$SQLPeEnquete.")";
					$SQLcountfinal = "select count(*) from art_periode_enquete as penq
							where penq.id in (".$SQLPeEnquete.")";; // pour gerer la pagination	
				break;
			}
			break;
		// #
		// ********** FIN TRAITEMENT STATISTIQUES
		// #
		default:
			echo "Pas de selection de type de statistiques. Recommencez une selection...<br/>";
			exit;
		
	} // fin du switch ($typeSelection) 

	// *
	// *********************************************************************************
	// EXECUTION DE LA REQUETE APRES SA CONSTRUCTION
	// *********************************************************************************

	if ($debugAff) {
		$debugTimer = number_format(timer()-$start_while,4);
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "TEST PERF : fin traitement donnees artisanales - avant execution requete :".$debugTimer,$pasdefichier);
		} else {
			echo "fin traitement donnees artisanales - avant execution requete :".$debugTimer."<br/>";
		}
	}	
	// On construit (ou non) la requete finale.
	// Elle peut avoir déjà été construite précédement, notament dans les cas par defaut
	if ($EcrireLogComp && $debugLog) {
		WriteCompLog ($logComp, "DEBUG :  synthese champs = ".$listeChampsSel." table = ".$ListeTableSel." where = ".$WhereSel,$pasdefichier);
	}
	if ($builQuery) {
	//echo "listeChampsCom : $listeChampsCom<br/><br/>" ;
	//echo "listeChampsSpec : $listeChampsSpec<br/><br/>" ;
	//echo "listeChampsSel : $listeChampsSel<br/><br/>" ;
	//echo "ListeTableCom : $ListeTableCom<br/><br/>" ;
	//echo "ListeTableSpec : $ListeTableSpec<br/><br/>" ;
	//echo "ListeTableSel : $ListeTableSel<br/><br/>" ;
		$listeChamps = $listeChampsCom.$listeChampsSpec.$listeChampsSel;
		$listeTable = $ListeTableCom.$ListeTableSpec.$ListeTableSel; // L'ordre est important pour les join
		if ($WhereSel == "") {
			$WhereTotal = $WhereCom.$WhereSpec;
		} else {
			$WhereTotal = $WhereCom.$WhereSpec." and ".$WhereSel;
		}
		$SQLfinal = "select ".$listeChamps." from ".$listeTable." ".$LeftOuterJoin." where ".$WhereTotal ." ".$OrderCom;
		//$SQLcountfinal = "select count(".$valueCount.") from ".$ListeTableCom.$ListeTableSpec." ".$LeftOuterJoin." where ".$WhereCom.$WhereSpec;
		$SQLcountfinal = "select count(".$valueCount.") from ".$listeTable." ".$LeftOuterJoin." where ".$WhereTotal ;
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "INFO SQL en cours :".$SQLfinal,$pasdefichier);
		}
		if ($EcrireLogComp && $debugLog) {
			WriteCompLog ($logComp, "DEBUG :  select countfinal = ".$SQLcountfinal,$pasdefichier);
		}
	}
	//echo "SQL Final $SQLfinal<br/><br/>";
	//echo "SQL Count $SQLcountfinal<br/><br/>";
	// Gestion des regroupements / stats generales
	// A ce niveau, pour gérer les regroupements, il faut passer par une étape intermédiaire d'agrégation
	// On exécute la requete, on effectue les groupements et enfin on créé des entrées dans la table temporaire temp_extraction
	// on gère aussi a ce niveau la creation des stats générales, puisqu'il s'agit du meme type d'agregation de données, mais pas sur les mêmes ruptures.
	// Le regroupement se gere sur la rupture Id stat/Id espece, alors que la rupture pour les stats générales sera secteur (ou systeme) / id Stat (ou especes)
	// Dans le cas ou il n'y a pas de regroupement pour les statistiques generales, on en cree un bidon.
	$creationRegBidon = false;
	//echo "nbre reg=".count($_SESSION['listeRegroup'])."<br.>";
	//echo "<pre>";print_r($_SESSION['listeRegroup']);echo"</pre>";
	if (!($_SESSION['listeRegroup'] == "") || ($typeStatistiques == "generales" && $typeAction =="stats")) {
		if ($typeSelection == "statistiques") {
			if ($typeStatistiques == "generales") {
				$listeTableStat = "ast,asp,ats,asgt,attgt,atgts"; // Toutes les tables
				if ($_SESSION['listeRegroup'] == "") {
					// Pas de regroupement en cours, il faut creer un regroupement par espece
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
							$creationRegBidon = true;
							while ($RegRow = pg_fetch_row($SQLRegResult) ) {
								// On crée un regroupement par espece. On récupère le libellé
								if (!($_SESSION['listeRegroup'] == "")) {
									$rangNvReg = count($_SESSION['listeRegroup']) + 1;
								} else {
									$rangNvReg = 1;
								}
								$_SESSION['listeRegroup'][$rangNvReg][1]=$RegRow[0]."&#&".$RegRow[1];
								$_SESSION['listeRegroup'][$rangNvReg][2]=$RegRow[0]."&#&".$RegRow[1];
							}
						}
						pg_free_result($SQLRegResult);
					}

				}
				$SQLfinal = "select * from temp_extraction where key4 = 'ast' order by key1 asc,key2 asc,key3 asc";
				$SQLcountfinal = "select count(*) from temp_extraction where key4 = 'ast'";
				$ConstIDunique = "AST-##-1";

			} else {
				// On est forcement dans le cas des stats par agglo avec un regroupement
				$listeTableStat = "asp,ats,attgt,atgts"; // que celles qui ont un regroupement
			}
			$tableStat = explode(",",$listeTableStat);
			$nbrTS = count($tableStat)-1;
			// On boucle sur toutes les tables pour extraire les donnees
			unset($_SESSION['listeEffortTotal']);
			unset($_SESSION['listeEffortGTETotal']);
			unset($_SESSION['listeEffortGTEESPTotal']);
			unset($_SESSION['listeEffortEspeces']);
			if ($EcrireLogComp && $debugLog) {
					WriteCompLog ($logComp, "DEBUG : remise a zero des tables temps pour calcul stat listeEffortTotal listeEffortGTETotal et listeEffortEspeces",$pasdefichier);
			}
			$_SESSION['calculStatSysteme '] = false;
			for ($cptTS = 0;$cptTS <= $nbrTS;$cptTS++) {
				$nomValLChampsSpec = "listeChampsSpec".$tableStat[$cptTS];
				$nomValLTableSpec = "ListeTableSpec".$tableStat[$cptTS];
				$nomValWhereSpec = "WhereSpec".$tableStat[$cptTS];
				// Construction du SQL pour chacun des tables
				// Analyse de la liste des colonnes venant des sélections précédentes, ajout de ces colonnes au fichier
				$AjoutWhere = "";
				$listeChampsSel="";
				$listeChampsSel="";
				$LeftOuterJoin = "";
				analyseColonne("statistiques",$typeAction,$tableStat[$cptTS],$typeStatistiques);
				$WhereSel = $AjoutWhere;
				$listeChampsReg = $listeChampsCom.${$nomValLChampsSpec}.$listeChampsSel;
				$listeTableReg = $ListeTableCom.${$nomValLTableSpec}.$ListeTableSel; 
				$nomOrderCom = "OrderCom".$tableStat[$cptTS];
				if ($WhereSel == "") {
					$WhereTotalReg = $WhereCom.${$nomValWhereSpec};
				} else {
					$WhereTotalReg = $WhereCom.${$nomValWhereSpec}." and ".$WhereSel;
				}
				$SQLfinalreg = "select ".$listeChampsReg." from ".$listeTableReg." ".$LeftOuterJoin." where ".$WhereTotalReg ." ".$OrderCom.${$nomOrderCom};
				//echo "<br/><b>requete [".$tableStat[$cptTS]."] </b> = ".$SQLfinalreg."<br/>";
				$posDEBIDm = posDEBID.$tableStat[$cptTS];
				$posESPIDm = posESPID.$tableStat[$cptTS];
				$posESPNomm = posESPNom.$tableStat[$cptTS];
				$posStat1m = posStat1.$tableStat[$cptTS];
				$posStat2m = posStat2.$tableStat[$cptTS];
				$posStat3m = posStat3.$tableStat[$cptTS];
				$posProrataTotm = posProrataTot.$tableStat[$cptTS];
				$posposProrataEspGTm = posProrataEspGT.$tableStat[$cptTS];
				$posRupSupm = posRupSup.$tableStat[$cptTS];
				// Variables supplémentaires pour les stats générales
				$posSecteurIDm = posSecteurID.$tableStat[$cptTS];
				$posGTEIDm = posGTEID.$tableStat[$cptTS];
				//echo $tableStat[$cptTS]."-".$$posDEBIDm." - ".$$posESPIDm." - ".$$posESPNomm." - ".$$posStat1m." - ".$$posStat2m." - ".$$posStat3m."<br/>";
				creeRegroupement($SQLfinalreg,${$posDEBIDm} ,${$posESPIDm},${$posESPNomm},${$posStat1m},${$posStat2m},${$posStat3m},-1,-1,$typeSelection,$tableStat[$cptTS],$cptTS,$posSystemeID,${$posSecteurIDm},${$posGTEIDm},$creationRegBidon,$typeStatistiques,${$posProrataTotm},${$posposProrataEspGTm},${$posRupSupm},"");				
			}
		} else {
			if (!($typeAction == "taillart")) {
				creeRegroupement($SQLfinal,$posDEBID ,$posESPID,$posESPNom,$posStat1,$posStat2,$posStat3,$posStat4,$posStat5,$typeSelection,"",0,-1,-1,-1,false,"",-1,-1,-1,$typeAction);
				$SQLfinal = "select * from temp_extraction order by key1 asc,key2 asc,key3 asc";
				$SQLcountfinal = "select count(*) from temp_extraction ";
				if ($typeSelection == "extraction") {
					$ConstIDunique = "DEB-##-1";			
				}else {
					$ConstIDunique = "AST-##-1";
				}
				$valueCount = "temp_extraction.id" ; // pour gerer la pagination	
			}

		}
	} // fin du if (!($_SESSION['listeRegroup'] == ""))
	// **** fin gestion des regroupements
	//**++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// Debut des traitements d'affichage à l'écran et extraction fichiers
	//**++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	// ********************************
	// Gestion d'affichage a l'ecran
	// ********************************	
	// Gestion de la pagination
	$countTotal=0; // Contient le resultat total de la requete
	//echo $SQLfinal."<br/>";
	//echo $SQLcountfinal."<br/>";
	// On recupere le nombre total de resultat.
	// On doit executer la requete
	$SQLcountfinalResult = pg_query($connectPPEAO,$SQLcountfinal);
	$erreurSQL = pg_last_error($connectPPEAO);
	$cpt1 = 0;
	if ( !$SQLcountfinalResult ) { 
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "WARNING : Erreur pagination pour requete ".$SQLcountfinal." (erreur complete = ".$erreurSQL.")",$pasdefichier);
		}
		$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>Erreur SQL pagination<br/>";
	} else {
		if (pg_num_rows($SQLcountfinalResult) == 0) {
			// Avertissement
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "WARNING : pagination Pas de resultat disponible pour la selection ".$SQLcountfinal,$pasdefichier);
			}
			$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>Erreur pagination : aucun resultat requete comptage<br/>";
			$PasDeResultat = true;
		} else {
			$countRow=pg_fetch_row($SQLcountfinalResult);
			$countTotal=$countRow[0];
		}	
	}
	if ($countTotal == 0) {
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "WARNING : pagination Pas de resultat disponible pour la selection ".$SQLcountfinal,$pasdefichier);
		}
		$PasDeResultat = true;
	}
	pg_free_result($SQLcountfinalResult); 
	if ($PasDeResultat) {
		$resultatLecture .= "<span class=\"infozip\"><img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>Attention, la requ&ecirc;te ne renvoie aucun r&eacute;sultat. Aucun fichier de donn&eacute;e n'est disponible. <br/>Le probl&egrave;me vient peut &ecirc;tre d'une restriction trop s&eacute;v&egrave;re sur les cat&eacute;gories trophiques/&eacute;cologiques</span><br/>";
	} else {
		$resultatLecture .= "<span class=\"infozip\">Le fichier de donn&eacute;es (Zip) est disponible au t&eacute;l&eacute;chargement <a href=\"".$zipFilelien."\" class=\"lienReg\" target=\"export\"/>ici</a>.</span><br/>";
	}
	// On gère la pagination
	// on prend en compte la pagination
	// Déclaration des variables  
	$rowsPerPage = 5; // nombre d'entrées à afficher par page (entries per page) 
	$countPages = ceil($countTotal/$rowsPerPage); // calcul du nombre de pages $countPages (on arrondit à l'entier supérieur avec la fonction ceil() ) 
 
	// Récupération du numéro de la page courante depuis l'URL avec la méthode GET  
	if(!isset($_GET['page']) || !is_numeric($_GET['page']) ) // si $_GET['page'] n'existe pas OU $_GET['page'] n'est pas un nombre (petite sécurité supplémentaire) 
		$currentPage = 1; // la page courante devient 1 
	else { 
		$currentPage = intval($_GET['page']); // stockage de la valeur entière uniquement 
		if ($currentPage < 1) $currentPage=1; // cas où le numéro de page est inférieure 1 : on affecte 1 à la page courante 
		elseif ($currentPage > $countPages) $currentPage=$countPages; //cas où le numéro de page est supérieur au nombre total de pages : on affecte le numéro de la dernière page à la page courante 
		else $currentPage=$currentPage; // sinon la page courante est bien celle indiquée dans l'URL 
	} 
 	// $start est la valeur de départ du LIMIT dans notre requête SQL (est fonction de la page courante)  
	$startRow = ($currentPage * $rowsPerPage - $rowsPerPage);
	// on construit la requête SQL pour obtenir les valeurs de la table à afficher si il y en a
	if ($countTotal!=0) {
		// Pour pouvoir gérer la pagination, on doit séparer la requete d'affichage de la requete de creation du fichier.
		// On ne creera le fichier qu'une seule fois!
		$SQLfinalFichier = $SQLfinal; // On stocke la requete pour le fichier
		// ********************************
		// Gestion de l'affichage ecran
		// ********************************
		$SQLfinal .= " LIMIT ".$rowsPerPage." OFFSET ".$startRow;
		// Execution de la requete
		$SQLfinalResult = pg_query($connectPPEAO,$SQLfinal);
		$erreurSQL = pg_last_error($connectPPEAO);
		$cpt1 = 0;
		if ( !$SQLfinalResult ) { 
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "ERREUR : Erreur query final ".$SQLfinal." (erreur complete = ".$erreurSQL.")",$pasdefichier);
			}
			$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;erreur query ".$SQLfinal." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
			$erreurProcess = true;
		} else {
			if (pg_num_rows($SQLfinalResult) == 0) {
				// Avertissement
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "Pas de resultat disponible pour la selection ".$SQLfinal,$pasdefichier);
				}
				$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>Pas de resultat disponible pour la sélection<br/>";
			} else {
				// On recupere le libelle de l'agglo unite de peche
				$chercheAggUpec = false;
				$posAggUpec = 0;
				if (strpos($listeChamps,"upec.art_agglomeration_id") > 0) {
					$tabChamp = explode(",",$listeChamps);
					$nbrChamp  = count($tabChamp)-1;
					// Transcription du resultat de la requete globale pour un affichage écran et un export sous forme de fichier
					for ($cptChamp  = 0;$cptChamp  <= $nbrChamp ;$cptChamp ++) {
						if ($tabChamp[$cptChamp] == "upec.art_agglomeration_id") {
							$posAggUpec = $cptChamp;
							break;
						}
					}
					$listeChamps = str_replace("upec.art_agglomeration_id","upec.art_agglomeration_id,Agglomeration_origine_unite",$listeChamps);
					$chercheAggUpec = true;
				}				
				// Si on ajoute un identifiant unique en debut de ligne, on l'indique dans la liste des champs.
				if (!($ConstIDunique =="")) {
					$listeChamps ="id.unique,".$listeChamps;
				}
				if ($typeAction == "biologie") {
					// On ajoute le libelle pour le coefficient
					$listeChamps .=",Nombre_individus_mesures,Coeff_extrapolation";
				}
				if ($typeStatistiques == "generales") {
					$listeChamps .=",Pue_totale,Effort_total,Captures_totales";
				}
				//echo $listeChamps."<br/>";
				// On remplace les noms des alias par le nom des tables...
				if (!($ConstIDunique =="")) {
					$listeChamps = remplaceAlias($listeChamps,"y",$typeAction,$typeStatistiques);
				} else {
					$listeChamps = remplaceAlias($listeChamps,"n",$typeAction,$typeStatistiques);
				}
				//echo $listeChamps."<br/>";
				// On commence le formatage sous forme de table/
				$libelleAction = recupereLibelleFiliere($typeAction);
				if ($typeSelection == "statistiques") {
					$resultatLecture .="<br/><span class=\"titreAff\">Liste des résultats (stats par ".$typeStatistiques.") </span>";
				} else {
					$resultatLecture .="<br/><span class=\"titreAff\">Liste des résultats (".$libelleAction.") </span>";	
				}
				$resultatLecture .="<div id=\"tableauresultats\"><table id=\"affresultat\" ><tr class=\"affresultattitre\"><td>";
				//ajout de <div id=\"tableauresultats\">
				// Gestion des regroupements a l'affichage. Attention, non valable pour les statistiques vu qu'on affiche toujours 
				// art_stat_totale qui ne peut pas avoir de regroupement
				// *********************** TRI ************
				// on genere le tableau de tri
				if (!empty($typeAction)) {$contexte=$typeAction;}
				$ordreTri=genereOrdre($contexte,$typeAction,$typeStatistiques);

				//****************************
				// TRI DES EN TETES DE TABLEAU
				//on trie les en-tetes selon l'ordre defini 
				$enTetes=explode(',',$listeChamps);
				foreach ($enTetes as $key=>$value) {
					$headers[$value]=$value;
				}				
				if (!empty($ordreTri)) {$enTetesTries=sortArrayByArray($headers,$ordreTri);} else {$enTetesTries=$headers;}				
				$enTetesTries=implode(",",$enTetesTries);
				if (!($_SESSION['listeRegroup'] == "") && (!($typeSelection == "statistiques"))) {
					// On modifie le label pour les especes (nom et cid)
					$enTetesTries = str_replace("Espece_id","Code_regroupement",$enTetesTries);
					$enTetesTries = str_replace("Espece","Nom_regroupement",$enTetesTries);
				}
				$resultatLecture .= str_replace(","," </td><td> ",$enTetesTries);
				
				// FIN DE TRI DES EN TETES DE TABLEAU
				//***********************************
				$resultatLecture .="</td></tr>";
				$cptNbRow = 0;
				while ($finalRow = pg_fetch_row($SQLfinalResult) ) {
					if ( $cptNbRow&1 ) {$rowStyle='edit_row_odd';} else {$rowStyle='edit_row_even';}
					$resultatLecture .="<tr class=".$rowStyle.">";
					// Construction de la liste des résultat
					// *******************
					// TRI DE LA LIGNE : utilisation de $resultatLectureX au lieu de $resultatLecture
					// pour bricoler tranquille
					$resultatLectureX='';
					// Tout d'abord, construction de l'ID unique
					// Ex $ConstIDunique = "DEB-##-11";
					// On garde le prefixe DEB et on extrait l'index du champ a recuperer de la ligne du resultat de la requete. ici 11
					$IDunique = "";
					$PondIDUnique = 0;
					if (!($ConstIDunique =="")) {
						$Locprefixe = substr($ConstIDunique,0,3); // Attention, pour des raisons de simplicité, le sufffixe n'est que sur 3 caractères.
						$locIndex = substr(strrchr($ConstIDunique, "-##-"),1);
						$IDunique = $Locprefixe.$finalRow[$locIndex];
						$resultatLectureX .= "<td>".$IDunique."</td>";
						$PondIDUnique = 1;
					}
					if ( (!($_SESSION['listeRegroup'] == "") && (!($typeSelection == "statistiques") && !($typeAction =="taillart") )) || 
						($typeSelection == "statistiques" && $typeStatistiques == "generales") ) {
						// Gestion des regroupements
						// On doit récupérer la liste dans le champ valeur_ligne de la table temp_extraction
						// et construire la ligne de resultat avec
						//echo"gestion regroupement - statistiques generales<br/>";
						$ligne_resultat = $finalRow[8];
						$tabResultat = explode("&#&",$ligne_resultat);
						$NbResultat = count($tabResultat);
						for ($cptResult = 1;$cptResult < $NbResultat;$cptResult++) {
							$AjChps = convertitNum($tabResultat[$cptResult]);
							$resultatLectureX .= "<td>".$AjChps."</td>";
						}
					} else {
						// Le traitement normal
						switch ($typeAction) {
							case "taillart" :
								// c'est un traitement normal sauf que si on a fait un regroupement, on remplace les especes par le regroupement
								$nbrRow = count($finalRow)-1;
								// Transcription du resultat de la requete globale pour un affichage écran et un export sous forme de fichier
								if ( !($_SESSION['listeRegroup'] == "")) {
									$CodeNomReg = recupereRegroupement($finalRow[$posESPID]);
									$infoReg = explode("&#&",$CodeNomReg);
								}
								for ($cptRow = 0;$cptRow <= $nbrRow;$cptRow++) {
									if ($chercheAggUpec && $cptRow == $posAggUpec) {
										// Recherche de l'agg d'origine de l'UPEC
										$SQLRec = "select nom from art_agglomeration where id = ".intval($finalRow[$cptRow]);
										$SQLRecResult = pg_query($connectPPEAO,$SQLRec);
										$erreurSQL = pg_last_error($connectPPEAO);
										$cpt1 = 0;
										if ( !$SQLRecResult ) { 
											if ($EcrireLogComp ) {
												WriteCompLog ($logComp, "ERREUR : Erreur recherche agglomeration ".$SQLRec." (erreur complete = ".$erreurSQL.")",$pasdefichier);
											}
											//$resultatLecture .= "<td>".$finalRow[$cptRow]."</td><td>erreur lecture</td>";
											$resultatLectureX .= "<td>".$finalRow[$cptRow]."</td><td>erreur lecture</td>";
										} else {
											$RowRec = pg_fetch_row($SQLRecResult);
											$NomAgg = $RowRec[0];
											if ($NomAgg == "") {$NomAgg = "inconnu";}
											//$resultatLecture .= "<td>".$finalRow[$cptRow]."</td><td>".$NomAgg."</td>";
											$resultatLectureX .= "<td>".$finalRow[$cptRow]."</td><td>".$NomAgg."</td>";
										}
									} else {
										// On contruit la ligne de resutat
										if ( !($_SESSION['listeRegroup'] == "")) {
											switch ($cptRow) {
												case $posESPID:
													$ValAAjouter = $infoReg[0];
													break;
												case $posESPNom:
													$ValAAjouter = $infoReg[1];
													break;	
												default: 
													$ValAAjouter = $finalRow[$cptRow];
													break;
											}
											$Asupprimer = TestsuppressionChamp($tableStat,$cptRow,$typeStatistiques);
											if (!$Asupprimer) {
												$AjChps = convertitNum($ValAAjouter);
												$resultatLectureX .= "<td>".$AjChps."</td>";
											}
										} else {
											$Asupprimer = TestsuppressionChamp($tableStat,$cptRow,$typeStatistiques);
											if (!$Asupprimer) {	
												$AjChps = convertitNum($finalRow[$cptRow]);
												$resultatLectureX .= "<td>".$AjChps."</td>";
											}
										}
									}
								}	
								break;
							case "biologie" :
								// On doit calculer un coefficient d'extrapolation 
								// On execute une requete supplémentaire pour recuperer le nombre d'individu dans exp_biologie pour la fraction et l'espece considerée
								// On recupere le nombre de poissons reellement mesures pour une fraction donnée (qui elle meme correspond à 
								// une seule espece.
								// On recupere le nombre total d'individu de la fraction
								$totalIndividus = $finalRow[19];
								// On compte les enregs dans biologie (individus effectivement analyse) pour cette fraction
								$SQLcomplement = "Select count(id) from exp_biologie where exp_fraction_id =  ".$finalRow[18] ;
								$SQLcomplementResult = pg_query($connectPPEAO,$SQLcomplement);
								$erreurSQL = pg_last_error($connectPPEAO);
								if ( !$SQLcomplementResult ) { 
									if ($EcrireLogComp ) {
										WriteCompLog ($logComp, "ERREUR : Erreur query complementaire biologie ".$SQLcomplement." (erreur complete = ".$erreurSQL.")",$pasdefichier);
									}							
								} else {
									$RowComplement = pg_fetch_row($SQLcomplementResult); 
									$totalBio = $RowComplement[0];
									pg_free_result($SQLcomplementResult);
								}
								// Calcul du coefficient = nombre de poisson peches / nombre de poissons mesures
								$coefficient =floatval( intval($totalIndividus) / intval($totalBio));	
								$coefficient = number_format($coefficient,2,",","");
								$nbrRow = count($finalRow)-1;
								// Transcription du resultat de la requete globale pour un affichage écran et un export sous forme de fichier
								for ($cptRow = 0;$cptRow <= $nbrRow;$cptRow++) {
									$Asupprimer = TestsuppressionChamp($tableStat,$cptRow,$typeStatistiques);
									if (!$Asupprimer) {	
										$AjChps = convertitNum($finalRow[$cptRow]);
										$resultatLectureX .= "<td>".$AjChps."</td>";
									}
								}
								// Ajout du coefficient tout a la fin du fichier
								$resultatLectureX .= "<td>".$totalBio."</td><td>".$coefficient."</td>";
								break;	
							default	:
								$nbrRow = count($finalRow)-1;
								// Transcription du resultat de la requete globale pour un affichage écran et un export sous forme de fichier
								for ($cptRow = 0;$cptRow <= $nbrRow;$cptRow++) {
									if ($chercheAggUpec && $cptRow == $posAggUpec) {
										// Recherche de l'agg d'origine de l'UPEC
										$SQLRec = "select nom from art_agglomeration where id = ".intval($finalRow[$cptRow]);
										$SQLRecResult = pg_query($connectPPEAO,$SQLRec);
										$erreurSQL = pg_last_error($connectPPEAO);
										$cpt1 = 0;
										if ( !$SQLRecResult ) { 
											if ($EcrireLogComp ) {
												WriteCompLog ($logComp, "ERREUR : Erreur recherche agglomeration ".$SQLRec." (erreur complete = ".$erreurSQL.")",$pasdefichier);
											}
											$resultatLectureX .= "<td>".$finalRow[$cptRow]."</td><td>erreur lecture</td>";
										} else {
											$RowRec = pg_fetch_row($SQLRecResult);
											$NomAgg = $RowRec[0];
											if ($NomAgg == "") {$NomAgg = "inconnu";}
											$resultatLectureX .= "<td>".$finalRow[$cptRow]."</td><td>".$NomAgg."</td>";
										}
									} else {
										// On contruit la ligne de resutat
										if ($tableStat == "") {
											$Asupprimer = TestsuppressionChamp($typeAction,$cptRow,$typeStatistiques);
										} else {
											$Asupprimer = TestsuppressionChamp($tableStat,$cptRow,$typeStatistiques);
										}
										if (!$Asupprimer) {	
											$AjChps = convertitNum($finalRow[$cptRow]);
											$resultatLectureX .= "<td>".$AjChps."</td>";
										}
									}
								}	
								break;
						}
					}

					// note : $resultatLectureX contient les <td>
					// on trie la ligne
					if (!empty($ordreTri)) {
						//echo"gestion du tri<br/>";
						// on va transformer ça en un tableau correct pour le trier
						$valeursATrier=explode("</td><td>",$resultatLectureX);
						// au cas òu un <td> se ballade dans les valeurs...
						$valeursATrier= str_replace("<td>","",$valeursATrier);
						//debug 	
						//echo"ordre du tri<br/>";
						//echo('<pre>');print_r($ordreTri);echo('</pre>');
						//echo"valeurs a trier <br/>";
						//echo('<pre>');print_r($valeursATrier);echo('</pre>');
						// on construit un tableau avec comme clés les valeurs de $headers (voir tri des en tetes plus haut)
						$i=0;
						$tableauATrier=array();
						foreach($headers as $key=>$value) {
							$tableauATrier[$value]=$valeursATrier[$i];
							$i++;
						}
						//echo"tableau a trier <br/>";
						//echo('<pre>');print_r($tableauATrier);echo('</pre>');
						$tableauTrie=sortArrayByArray($tableauATrier,$ordreTri);
						//debug		
						//echo"tableau trie<br/>";
						//echo('<pre>');print_r($tableauTrie);echo('</pre>');
						// a partir du tableau trie, on recree les <td>
						$resultatTds='<td>';
						//debug 
						//echo('<pre><table border="1"><tr><td>');print_r(implode("</td><td>",$tableauTrie));echo('</td></tr></table></pre>');
						$resultatTds.=implode("</td><td>",$tableauTrie);
						$resultatTds.='</td>';
						//debug 					
						//echo('<pre>');print_r($resultatTds);echo('</pre>');
					}
					else {
						$resultatTds=$resultatLectureX;
					}
					
					$resultatLecture.=$resultatTds;
					//$resultatLecture.=$resultatLectureX; // Cas d'origine si pas de tri
					
					// FIN TRI DE LA LIGNE DE VALEURS
					//************************************
					$resultatLecture .="</tr>";
					$cptNbRow ++;
				}//fin du while			
				$resultatLecture .="</table></div>";		
			}			//ajout du dernier </div>
			pg_free_result($SQLfinalResult);
		} // fin du !$SQLfinalResult
		// ********************************
		// Fin gestion affichage ecran
		// ********************************
		// ********************************
		// Gestion de creation du fichier
		// ********************************
		if ($exportFichier && (!($fichierDejaCree))) {
			// Creation du fichier d'apres le SQL
			if ($typeSelection == "statistiques") {
				$listeTableStat = "ast,asp,ats,asgt,attgt,atgts";
				$tableStat = explode(",",$listeTableStat);
				$nbrTS = count($tableStat)-1;
				// On boucle sur toutes les tables pour extraire les donnees
				for ($cptTS = 0;$cptTS <= $nbrTS;$cptTS++) {
					$nomValLChampsSpec = "listeChampsSpec".$tableStat[$cptTS];
					$nomValLTableSpec = "ListeTableSpec".$tableStat[$cptTS];
					$nomValWhereSpec = "WhereSpec".$tableStat[$cptTS];
					$nomOrderCom = "OrderCom".$tableStat[$cptTS];
					//echo $nomValWhereSpec. " - ".${$nomValWhereSpec}."<br/>";
					// Construction du SQL pour chacune des tables
					// Analyse de la liste des colonnes venant des sélections précédentes, ajout de ces colonnes au fichier
					$AjoutWhere = "";
					$listeChampsSel="";
					$listeChampsSel="";
					$LeftOuterJoin= "";
					analyseColonne("statistiques",$typeAction,$tableStat[$cptTS],$typeStatistiques);
					$WhereSel = $AjoutWhere;
					$listeChamps = $listeChampsCom.${$nomValLChampsSpec}.$listeChampsSel;
					$listeTableTot = $ListeTableCom.${$nomValLTableSpec}.$ListeTableSel; // L'ordre est important pour les join
					if ($WhereSel == "") {
						$WhereTotalTot = $WhereCom.${$nomValWhereSpec};
					} else {
						$WhereTotalTot = $WhereCom.${$nomValWhereSpec}." and ".$WhereSel;
					}
					$SQLfinal = "select ".$listeChamps." from ".$listeTableTot." ".$LeftOuterJoin." where ".$WhereTotalTot ." ".$OrderCom.${$nomOrderCom};					
					//echo "<b>".$tableStat[$cptTS]."</b> ". $SQLfinal."<br/>";
					// Creation du fichier par stat.
					$ficSuffixe = getSuffixeFicStat($tableStat[$cptTS]);
					$nomFicExport = $dirLog."/".date('y\-m\-d')."_".$ficSuffixe.".txt";
					//echo $nomFicExport."<br/>";
					// On ne cree le fichier que si il n'a pas deja ete rempli !
					if (!($fichierDejaCree)) {
						$ExpCompStat = fopen($nomFicExport , "w+");
						if (! $ExpCompStat ) {
							$resultatLecture .= " erreur de cr&eacute;ation du fichier export ".$nomFicExpLien;
							exit;		
						}
					}
					// Deux cas: si stat par agglo, on fait la difference regroupement / pas regroupement, si stat generales on lit systemeatiquement la table temp
					if ($typeStatistiques == "generales") {
						$SQLfinal = "select * from temp_extraction where key4 = '".$tableStat[$cptTS]."' order by key1 asc,key2 asc,key3 asc";
						$SQLcountfinal = "select count(*) from temp_extraction ";
						$ConstIDunique = "AST-##-1";
						$listeChamps ="id.unique,".$listeChamps;
						// Selon la table, on ajoute des coefficients a la fin de la ligne
						switch ($tableStat[$cptTS]) {
							case "ast":
								$listeChamps =$listeChamps.",Pue_totale,Effort_total,Captures_totales";
								break;
							case "asp":
								$listeChamps =$listeChamps.",Pue_espece,Captures_espece,Pue_totale,Effort_total,Captures_totales";
								break;
							case "ats":
								$listeChamps =$listeChamps.",Effectif,Pue_espece,Captures_espece,Pue_totale,Effort_total,Captures_totales";
								break;
							case "asgt":
								$listeChamps =$listeChamps.",Pue_GT,Effort_GT,Captures_GT";
								break;
							case "attgt":
								$listeChamps =$listeChamps.",Pue_GT_espece,Captures_GT_espece,Pue_GT,Effort_GT,Captures_GT";
								break;
							case "atgts":
								$listeChamps =$listeChamps.",Effectif,Pue_GT,Effort_GT,Captures_GT,Pue_GT_espece,Captures_GT_espece";
								break;	
						}
						$listeChamps = remplaceAlias($listeChamps,"y",$tableStat[$cptTS],$typeStatistiques);
						creeFichier($SQLfinal,$listeChamps,$typeAction,$ConstIDunique,$ExpCompStat,false,0,0,false,false,$tableStat[$cptTS],$typeStatistiques);
					} else {
						$listeTableStatSp = "asp,ats,attgt,atgts";
						if ( strpos($listeTableStatSp,$tableStat[$cptTS]) === false) {
							$ConstIDuniqueStat = "ConstIDunique".$tableStat[$cptTS];
							$listeChamps ="id.unique,".$listeChamps;
							$listeChamps = remplaceAlias($listeChamps,"y",$tableStat[$cptTS],$typeStatistiques);
							creeFichier($SQLfinal,$listeChamps,$typeAction,${$ConstIDuniqueStat},$ExpCompStat,true,0,0,false,false,$tableStat[$cptTS],$typeStatistiques);
						} else {
							if (!($_SESSION['listeRegroup'] == "")) {
								$SQLfinal = "select * from temp_extraction where key4 = '".$tableStat[$cptTS]."' order by key1 asc,key2 asc,key3 asc";
								$SQLcountfinal = "select count(*) from temp_extraction ";
								$ConstIDunique = "AST-##-1";
								$listeChamps ="id.unique,".$listeChamps;
								$listeChamps = remplaceAlias($listeChamps,"y",$tableStat[$cptTS],$typeStatistiques);
								creeFichier($SQLfinal,$listeChamps,$typeAction,$ConstIDunique,$ExpCompStat,false,0,0,false,false,$tableStat[$cptTS],$typeStatistiques);
							} else {
								$ConstIDuniqueStat = "ConstIDunique".$tableStat[$cptTS];
								$listeChamps ="id.unique,".$listeChamps;
								$listeChamps = remplaceAlias($listeChamps,"y",$tableStat[$cptTS],$typeStatistiques);
								creeFichier($SQLfinal,$listeChamps,$typeAction,${$ConstIDuniqueStat},$ExpCompStat,false,0,0,false,false,$tableStat[$cptTS],$typeStatistiques);
							}
						}
					}
				}
			} else {
				creeFichier($SQLfinalFichier,$listeChamps,$typeAction,$ConstIDunique,$ExpComp,false,$posESPID,$posESPNom,$chercheAggUpec,$posAggUpec,"",$typeStatistiques);
			}
			// Export des selections et regroupements dans des fichiers separes
			$SelectionPourFic = str_replace("<br/>","\n",$SelectionPourFic);
			$SelectionPourFic = str_replace("<b>","",$SelectionPourFic);
			$SelectionPourFic = str_replace("</b>","",$SelectionPourFic);
			$SelectionPourFic = str_replace("&eacute;","é",$SelectionPourFic);
			$SelectionPourFic = str_replace("&ecirc;","ê",$SelectionPourFic);
			$SelectionPourFic = str_replace("&egrave;","è",$SelectionPourFic);
			$SelectionPourFic = str_replace("&#x27;","'",$SelectionPourFic);
			if ( !($erreurStatGene == "")) {
				$erreurStatGene = str_replace("<br/>","\n",$erreurStatGene);
				$erreurStatGene = str_replace("<b>","",$erreurStatGene);
				$erreurStatGene = str_replace("</b>","",$erreurStatGene);
				$erreurStatGene = str_replace("&eacute;","é",$erreurStatGene);
				$erreurStatGene = str_replace("&ecirc;","ê",$erreurStatGene);
				$erreurStatGene = str_replace("&egrave;","è",$erreurStatGene);
				$erreurStatGene = str_replace("&#x27;","'",$erreurStatGene);
				$SelectionPourFic .= "\n------------------------------------------------------------\n";
				$SelectionPourFic .= "Liste des erreurs lors du calcul des statistiques generales\n";
				$SelectionPourFic .= "------------------------------------------------------------\n";
				$SelectionPourFic .= $erreurStatGene;
			}
			//$SelectionPourFic = "test";
			if (! fwrite($ExpCompSel,$SelectionPourFic) ) {
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "ERREUR : erreur ecriture dans fichier export selection : ".$SelectionPourFic,$pasdefichier);
				} else {
					$resultatLecture .= "erreur ecriture dans fichier export selection" ;
				}
				return "erreur ecriture fichier selection <br/>";
			} else {
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "INFO : selection ecrite dans le fichier [date]_".$typeSelection."_".$typeAction."-Selection.txt",$pasdefichier);
				}
			}
			fclose($ExpCompSel);
			$RegroupPourFic= "-------------------------------------------------------------------  \n";
			// debug
			if (!($_SESSION['listeRegroup'] == "") && !$creationRegBidon) {
				// On cree la liste
				$NbReg = count($_SESSION['listeRegroup']);
				for ($cptR=1 ; $cptR<=$NbReg;$cptR++) {
					$NbReg2 = count($_SESSION['listeRegroup'][$cptR]);
					$infoReg = explode("&#&",$_SESSION['listeRegroup'][$cptR][1]);
					$RegroupPourFic .="Le regroupement ".$infoReg[1]." (".$infoReg[0].") contient les especes suivantes :\n";
					for ($cptR2=2 ; $cptR2<=$NbReg2;$cptR2++) {
						$infoEsp = explode("&#&",$_SESSION['listeRegroup'][$cptR][$cptR2]);
						$RegroupPourFic .="\t - ".$infoEsp[0]." - ".$infoEsp[1]."\n";
					}
				}
				$RegroupPourFic .="Toutes les autres especes se retrouvent dans le regroupement DIV :  \n";
				$NbReg = count($_SESSION['listeDIV']);
				for ($cptR=1 ; $cptR<=$NbReg;$cptR++) {
					$RegroupPourFic .= "\t - ".$_SESSION['listeDIV'][$cptR][0]." - ".$_SESSION['listeDIV'][$cptR][1]."\n";
				}			
				$RegroupPourFic .="-------------------------------------------------------------------  \n";
				if (! fwrite($ExpCompReg,$RegroupPourFic) ) {
					if ($EcrireLogComp ) {
						WriteCompLog ($logComp, "ERREUR : erreur ecriture dans fichier export regroupement.".$RegroupPourFic,$pasdefichier);
					} else {
						$resultatLecture .= "erreur ecriture dans fichier export regroupement" ;
					}
					return "erreur ecriture fichier regroupement <br/>";
				} else {
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "INFO : description regroupement ecrite dans le fichier [date]_".$typeSelection."_".$typeAction."-Regroupement.txt",$pasdefichier);
				}
				fclose($ExpCompReg);
			}
			}
			// ********* Fin creation fichier ==> Zip
			// On contrôle si il existe des documents sélectionnés, si oui, on les extrait
			// Doc pays
			$listeDocURL = array();	
// FW TEST
//echo print_r( $_SESSION )."<br/>" ;		
// CHERCHER $listeDocSyst (avec le t)... c'est la clé !
			if (!($_SESSION['listeDocPays'] == "")) {
				// Appel de la fonction litTableDocument qui renvoie une variable partagée avec les chemins des fichiers à ajouter
				// Elle renvoie un message d'erreur en cas de probleme, sinon vide.
				 $resultatLecture .=litTableDocument("meta_pays",$_SESSION['listeDocPays']);
			}
			if (!($_SESSION['listeDocSys'] == "")) {
				// Appel de la fonction litTableDocument qui renvoie une variable partagée avec les chemins des fichiers à ajouter
				// Elle renvoie un message d'erreur en cas de probleme, sinon vide.
				$resultatLecture .= litTableDocument("meta_systemes",$_SESSION['listeDocSys']);
			}
			if (!($_SESSION['listeDocSect'] == "")) {
				// Appel de la fonction litTableDocument qui renvoie une variable partagée avec les chemins des fichiers à ajouter
				// Elle renvoie un message d'erreur en cas de probleme, sinon vide.
				$resultatLecture .= litTableDocument("meta_secteurs",$_SESSION['listeDocSect']);
			}		
// FW TEST
//echo print_r( $listeDocURL ) ;
			$NbrDoc = count($listeDocURL);
			if ($NbrDoc>0) {
				for ($cpt = 1; $cpt <= $NbrDoc; $cpt++) {
					$FileFullPath = $_SERVER["DOCUMENT_ROOT"]."/".$listeDocURL[$cpt];
					if ($EcrireLogComp ) {
						WriteCompLog ($logComp, "Ajout de fichiers sup ".$FileFullPath." dans l'archive ".$zipFilename ,$pasdefichier);
					}
					$theZipFile->add_files($FileFullPath);
				}
			}
			// Ajout des fichiers de stats dans l'archive
			if ($typeSelection == "statistiques") {
								// On boucle sur toutes les tables pour extraire les donnees
				for ($cptTS = 0;$cptTS <= $nbrTS;$cptTS++) {
					$ficSuffixe = getSuffixeFicStat($tableStat[$cptTS]);
					$nomFicExport = $dirLog."/".date('y\-m\-d')."_".$ficSuffixe.".txt";
					$theZipFile->add_files($nomFicExport);
				}
			} else {
				// Ajout des autres fichiers dans l'archive
				// Fichier de données
				$theZipFile->add_files($nomFicExport);
			}
			// Fichier de selection
			$theZipFile->add_files($nomFicExportSel);
			// Si regroupement, le fichier de definition des regroupements
			if (!($_SESSION['listeRegroup'] == "")) {
				$theZipFile->add_files($nomFicExportReg);
			}		
			// Creation effective de l'archive
			$theZipFile->create_archive();
			$fichierDejaCree = true;
		} // fin if ($exportFichier && (!($fichierDejaCree))
	}// fin du if ($countTotal!=0) 
	if ($fichierDejaCree)  {
		$addURLPag = "&dejf=y";
	} else {
		$addURLPag = "";
	}
	$resultatLecture .= paginate($_SERVER['PHP_SELF'].'?'.removeQueryStringParam($_SERVER['QUERY_STRING'],'page'), $addURLPag."&amp;page=", $countPages, $currentPage);
	
	$compteurItem = $countTotal;
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "INFO : resultat pour la requete en cours : ".$compteurItem." / ".$countTotal." lignes.",$pasdefichier);
	}
	if ($exportFichier && $EcrireLogComp && !($PasDeResultat) ) {
		WriteCompLog ($logComp, "Les donnees ont ete ecrites dans le fichier ".$nomFicExpLien." pour la filiere ".$typeAction,$pasdefichier);
	}
	// Suppression du regroupement fictif cree pour les stats globales
	if ($creationRegBidon) {
		unset($_SESSION['listeRegroup']);
	}
	if ($debugAff) {
		$debugTimer = number_format(timer()-$start_while,4);	
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "TEST PERF : fin du traitement en ".$debugTimer,$pasdefichier);
		} else {
			echo "fin du traitement en ".$debugTimer."<br/>";
		}
	}

}

//*********************************************************************
// creeFichier : Fonction de creation d'un fichier a partir d'un SQL
function creeFichier($SQLaExecuter,$listeChamps,$typeAction,$ConstIDunique,$ExpComp,$pasTestReg,$posESPID,$posESPNom,$chercheAggUpec,$posAggUpec,$tableStat,$typeStatistiques) {
// Cette fonction permet de creer un fichier a exporter a partir d'un SQL
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $SQLaExecuter : SQL a executer pour creer le fichier
// $listeChamps
// $typeAction
// $ConstIDunique
// $ExpComp
// $pasTestReg : si vrai, on ne teste pas les regroupements
// $posESPID
// $posESPNom
// $chercheAggUpec: est-ce qu'on cherche l'agglo de l'unite de peche
// $posAggUpec : la post de l'agglo Id de l'unite de peche
// $tableStat : la table stat en cours si en cours de gestion des stats
//*********************************************************************
// En sortie : créé le fichier $ExpCom
// La fonction met a jour $resultatLecture
//*********************************************************************
// 
	global $connectPPEAO;
	global $EcrireLogComp;
	global $logComp;
	global $pasdefichier;
	global $erreurProcess;
	global $resultatLecture;
	global $debugAff;
	global $start_while;
	global $creationRegBidon;
	// Execution de la requete
	//echo "cree fichier ".$tableStat." : ".$SQLaExecuter."<br/>";
	$SQLfinalResult = pg_query($connectPPEAO,$SQLaExecuter);
	$erreurSQL = pg_last_error($connectPPEAO);
	$cpt1 = 0;
	if ( !$SQLfinalResult ) { 
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "ERREUR : Erreur creation fichier query final ".$SQLaExecuter." (erreur complete = ".$erreurSQL.")",$pasdefichier);
		}
		$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;erreur creation fichier query ".$SQLaExecuter." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
		$erreurProcess = true;
	} else {
		if (pg_num_rows($SQLfinalResult) == 0) {
			// Avertissement
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "Pas de resultat disponible pour la selection ".$SQLaExecuter,$pasdefichier);
			}
			$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>Pas de resultat disponible pour la sélection (creation fichier)<br/>";
		} else {
			// *********************** TRI ************
			// on genere le tableau de tri
			
			if (!empty($typeAction)) {$contexte=$typeAction;}
			if (!empty($tableStat)) {$contexte=$tableStat;}
			$ordreTri=genereOrdre($contexte,$typeAction,$typeStatistiques);
			//****************************
			// TRI DES EN TETES DE TABLEAU
			//on trie les en-tetes selon l'ordre defini 
			$enTetes=explode(',',$listeChamps);
			foreach ($enTetes as $key=>$value) {
				$headers[$value]=$value;
			}				
			if (!empty($ordreTri)) {$enTetesTries=sortArrayByArray($headers,$ordreTri);} else {$enTetesTries=$headers;}				
			$enTetesTries=implode(",",$enTetesTries);
			if (!($_SESSION['listeRegroup'] == "") && !$creationRegBidon) {
				// On modifie le label pour les especes (nom et cid)
				$enTetesTries = str_replace("Espece_id","Code_regroupement",$enTetesTries);
				$enTetesTries = str_replace("Espece","Nom_regroupement",$enTetesTries);
			}
			$resultatFichier = str_replace(",","\t",$enTetesTries);
			// FIN DE TRI DES EN TETES DE TABLEAU
			//***********************************	
			if (! fwrite($ExpComp,$resultatFichier."\r\n") ) {
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "ERREUR : erreur ecriture dans fichier export.",$pasdefichier);
				} else {
					$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>Erreur ecriture dans fichier export" ;
				}
				exit;
			}	
			$cptDebug = 0;
			while ($finalRow = pg_fetch_row($SQLfinalResult) ) {			
				// Construction de la liste des résultat
				// *******************
				// TRI DE LA LIGNE : utilisation de $resultatFichierX au lieu de $resultatFichier
				// pour bricoler tranquille
				$resultatFichierX="";
				$resultatFichier = "";
				// Construction de la liste des résultat
				// Tout d'abord, construction de l'ID unique
				// Ex $ConstIDunique = "DEB-##-11";
				// On garde le prefixe DEB et on extrait l'index du champ a recuperer de la ligne du resultat de la requete. ici 11
				$IDunique = "";
				if (!($ConstIDunique == "")) {		
					$Locprefixe = substr($ConstIDunique,0,3); // Attention, pour des raisons de simplicité, le suffixe n'est que sur 3 caractères.
					$locIndex = substr(strrchr($ConstIDunique, "-##-"),1);
					$IDunique = $Locprefixe.$finalRow[$locIndex];
					$resultatFichierX .= $IDunique."\t";
				}
				if (!($_SESSION['listeRegroup'] == "") && (!($pasTestReg))  && !($typeAction =="taillart") ) {
					// Gestion des regroupements
					// On doit récupérer la liste dans le champ valeur_ligne de la table temp_extraction
					// et construire la ligne de resultat avec
					//echo"gestion fichier <br/>";
					$ligne_resultat = $finalRow[8];
					$tabResultat = explode("&#&",$ligne_resultat);
					$NbResultat = count($tabResultat);
					for ($cptResult = 1;$cptResult <= $NbResultat;$cptResult++) {
						//$resultatFichierX .=$tabResultat[$cptResult]."\t";
						//$resultatFichier .= $tabResultat[$cptResult]."\t";
						// Pas besoin de gerer la suppression ici, elle est faite lors de la construction de la ligne avant
						$AjChps = convertitNum($tabResultat[$cptResult]);
						//echo $tabResultat[$cptResult]." - ".$AjChps." <br/>";
						$resultatFichierX  .= $AjChps."\t";
					}
				} else {
					switch ($typeAction) {
						case "taillart" :
							// c'est un traitement normal sauf que si on a fait un regroupement, on remplace les especes par le regroupement
							$nbrRow = count($finalRow)-1;
							// Transcription du resultat de la requete globale pour un affichage écran et un export sous forme de fichier
							if ( !($_SESSION['listeRegroup'] == "")) {
								$CodeNomReg = recupereRegroupement($finalRow[$posESPID]);
								$infoReg = explode("&#&",$CodeNomReg);
							}
							for ($cptRow = 0;$cptRow <= $nbrRow;$cptRow++) {
								if ($chercheAggUpec && $cptRow == $posAggUpec) {

									// Recherche de l'agg d'origine de l'UPEC
									$SQLRec = "select nom from art_agglomeration where id = ".intval($finalRow[$cptRow]);
									$SQLRecResult = pg_query($connectPPEAO,$SQLRec);
									$erreurSQL = pg_last_error($connectPPEAO);
									$cpt1 = 0;
									if ( !$SQLRecResult ) { 
										if ($EcrireLogComp ) {
											WriteCompLog ($logComp, "ERREUR : Erreur recherche agglomeration ".$SQLRec." (erreur complete = ".$erreurSQL.")",$pasdefichier);
										}
										//$resultatLecture .= "<td>".$finalRow[$cptRow]."</td><td>erreur lecture</td>";
										$resultatFichierX .= $finalRow[$cptRow]."\t erreur lecture \t";
									} else {
										$RowRec = pg_fetch_row($SQLRecResult);
										$NomAgg = $RowRec[0];
										if ($NomAgg == "") {$NomAgg = "inconnu";}
										$resultatFichierX .= $finalRow[$cptRow]."\t".$NomAgg."\t";
									}
								} else {
									// On contruit la ligne de resultat
									if ( !($_SESSION['listeRegroup'] == "")) {
										// Si on a un regroupement on remplace les especes par le code / nom regroupement.
										switch ($cptRow) {
											case $posESPID:
												$ValAAjouter = $infoReg[0];
												break;
											case $posESPNom:
												$ValAAjouter = $infoReg[1];
												break;	
											default: 
												$ValAAjouter = $finalRow[$cptRow];
												break;
										}
										$Asupprimer = TestsuppressionChamp($typeAction,$cptRow,$typeStatistiques);
										if (!$Asupprimer) {
											$AjChps = convertitNum($ValAAjouter);
											$resultatFichierX .=$AjChps."\t";
										}

									} else {
										$Asupprimer = TestsuppressionChamp($typeAction,$cptRow,$typeStatistiques);
										if (!$Asupprimer) {	
											$AjChps = convertitNum($finalRow[$cptRow]);
											$resultatFichierX .= $AjChps."\t";
										}
									}
								}
							}	
							break;
						case "biologie" :
							// On doit calculer un coefficient d'extrapolation 
							// On execute une requete supplémentaire pour recuperer le nombre d'individu dans exp_biologie pour la fraction et l'espece considerée
							// On recupere le nombre de poissons reellement mesures pour une fraction donnée (qui elle meme correspond à 
							// une seule espece.
							// On recupere le nombre total d'individu de la fraction
							$totalIndividus = $finalRow[19];
							// On compte les enregs dans biologie (individus effectivement analyse) pour cette fraction
							$SQLcomplement = "Select count(id) from exp_biologie where exp_fraction_id =  ".$finalRow[18] ;
							$SQLcomplementResult = pg_query($connectPPEAO,$SQLcomplement);
							$erreurSQL = pg_last_error($connectPPEAO);
							if ( !$SQLcomplementResult ) { 
								if ($EcrireLogComp ) {
									WriteCompLog ($logComp, "ERREUR : Erreur query complementaire biologie ".$SQLcomplement." (erreur complete = ".$erreurSQL.")",$pasdefichier);
								}							
							} else {
								$RowComplement = pg_fetch_row($SQLcomplementResult); 
								$totalBio = $RowComplement[0];
								pg_free_result($SQLcomplementResult);
							}
							// Calcul du coefficient = nombre de poisson peches / nombre de poissons mesures
							$coefficient =floatval( intval($totalIndividus) / intval($totalBio));	
							$coefficient = number_format($coefficient,2,",","");
							$nbrRow = count($finalRow)-1;

							// Transcription du resultat de la requete globale pour un affichage écran et un export sous forme de fichier
							for ($cptRow = 0;$cptRow <= $nbrRow;$cptRow++) {
									$Asupprimer = TestsuppressionChamp($typeAction,$cptRow,$typeStatistiques);
									if (!$Asupprimer) {	
										$AjChps = convertitNum($finalRow[$cptRow]);
										$resultatFichierX .=$AjChps."\t";
									}
							}
							// Ajout du coefficient tout a la fin du fichier
							$resultatFichierX .= $totalBio."\t".$coefficient;
							break;	
						default	:
							$nbrRow = count($finalRow)-1;
							for ($cptRow = 0;$cptRow <= $nbrRow;$cptRow++) {
								if ($chercheAggUpec && $cptRow == $posAggUpec) {
									// Recherche de l'agg d'origine de l'UPEC
									$SQLRec = "select nom from art_agglomeration where id = ".intval($finalRow[$cptRow]);
									$SQLRecResult = pg_query($connectPPEAO,$SQLRec);
									$erreurSQL = pg_last_error($connectPPEAO);
									$cpt1 = 0;
									if ( !$SQLRecResult ) { 
										if ($EcrireLogComp ) {
											WriteCompLog ($logComp, "ERREUR : Erreur recherche agglomeration ".$SQLRec." (erreur complete = ".$erreurSQL.")",$pasdefichier);
										}
										$resultatFichierX  .= $finalRow[$cptRow]." \t erreur lecture \t";
									} else {
										$RowRec = pg_fetch_row($SQLRecResult);
										$NomAgg = $RowRec[0];
										if ($NomAgg == "") {$NomAgg = "inconnu";}
										$resultatFichierX .= $finalRow[$cptRow]."\t".$NomAgg."\t";
									}
								} else {
									if ($tableStat == "") {
										$Asupprimer = TestsuppressionChamp($typeAction,$cptRow,$typeStatistiques);
									} else {
										$Asupprimer = TestsuppressionChamp($tableStat,$cptRow,$typeStatistiques);
									}
									if (!$Asupprimer) {	
										$AjChps = convertitNum($finalRow[$cptRow]);
										$resultatFichierX .=$AjChps."\t";
									}
								}
							}		
					}
				}
				//echo $resultatFichierX."<br/>";
				// note : $resultatFichierX contient les \t
				// on trie la ligne
				if (!empty($ordreTri)) {
					// on va transformer ça en un tableau correct pour le trier
					$valeursATrier=explode("\t",$resultatFichierX);
					// au cas òu un \t se ballade dans les valeurs...
					$valeursATrier= str_replace("\t","",$valeursATrier);
					//debug 
					//echo "Ordre de tri <br/>";
					//echo('<pre>');print_r($ordreTri);echo('</pre>');
					//echo "valeurs a trier <br/>";
					//echo('<pre>');print_r($valeursATrier);echo('</pre>');
					// on construit un tableau avec comme clés les valeurs de $headers (voir tri des en tetes plus haut)
					$i=0;
					$tableauATrier=array();
					foreach($headers as $key=>$value) {
						$tableauATrier[$value]=$valeursATrier[$i];
						$i++;
					}
					//debug 
					//echo "tableau a trier <br/>";
					//echo('<pre>');print_r($tableauATrier);echo('</pre>');
					$tableauTrie=sortArrayByArray($tableauATrier,$ordreTri);
					//debug
					//echo "tableau trie <br/>";
					//echo('<pre>');print_r($tableauTrie);echo('</pre>');
					// a partir du tableau trie, on recree les \t
					//debug 
					$resultatTds=implode("\t",$tableauTrie);
					//debug 					
					//echo('<pre>');print_r($resultatTds);echo('</pre>');
					}
					else {
						$resultatTds=$resultatFichierX;
					}
					$resultatFichier.=$resultatTds;
					//$resultatFichier.=$resultatFichierX; // Cas d'origine si pas de tri
					// FIN TRI DE LA LIGNE DE VALEURS
					//************************************
				$resultatFichier .="\n";
				if (! fwrite($ExpComp,$resultatFichier) ) {
					if ($EcrireLogComp ) {
						WriteCompLog ($logComp, "ERREUR : erreur ecriture dans fichier export.",$pasdefichier);
					} else {
						$resultatLecture .= "erreur ecriture dans fichier export" ;
					}
					exit;
				}
			// Compteur
			$cpt1++;							
			}

		}
		pg_free_result($SQLfinalResult);	
	} // fin du !$SQLfinalResult
	fclose($ExpComp);
}

//*********************************************************************
// AfficheCategories : Fonction pour afficher les catégories troph / ecologiques a selectionner
function litTableDocument($nomTable,$ListeID) {
// Cette fonction permet de construire la liste des checkboxes pour la selection des especes à selectionner
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $nomTable : nom de la table a lire
// $ListeID : liste des ID sur lesquels faire la requete
//*********************************************************************
// En sortie : 
// La fonction renvoie $messageErreur
// La fonction met aussi à jour la variable partagée listeDocURL
//*********************************************************************
	global $connectPPEAO;
	global $EcrireLogComp;
	global $logComp;
	global $pasdefichier;
	global $listeDocURL;
	$messageErreur = "";

	$SQLDoc = "select * from ".$nomTable." where meta_id in (".$ListeID.") order by meta_id asc";
// FW TEST
//echo "$SQLDoc<br/>" ;
	$SQLDocResult = pg_query($connectPPEAO,$SQLDoc);
	$erreurSQL = pg_last_error($connectPPEAO);
	if ( !$SQLDocResult ) { 
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "ERREUR : Erreur lecture table doc pays ".$SQLDoc." (erreur complete = ".$erreurSQL.")",$pasdefichier);
		}
		$messageErreur .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;Erreur lecture table doc pays ".$SQLDoc." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";

	} else {
		if (pg_num_rows($SQLDocResult) == 0) {
			$messageErreur .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>la requ&ecirc;te ".$SQLDoc." ne renvoie rien alors qu'un document a &eacute;t&eacute; s&eacute;lectionn&eacute;.<br/>";

		} else {
			// pour debug 	
			//$messageErreur .="<br/>Document trouv&eacute; !!<br/>";
			$cptURL = count($listeDocURL);
			while ($docRow = pg_fetch_row($SQLDocResult) ) {
				$cptURL ++;
				$listeDocURL[$cptURL] = "work/documentation/metadata/".$docRow[3];
			}
		}
		pg_free_result($SQLDocResult);
	}
	return $messageErreur;
}

//*********************************************************************
// recupereTouteColonnes : Fonction qui recupère l'ensemble de colonnes
function recupereTouteColonnes ($typePeche,$typeAction) {
// Cette fonction permet de recupérer l'ensemble des colonnes pour toutes les tables quand l'option tout a été cochée
// Pour cela, elle va lire le fichier de definition (XML)
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $typePeche : le type de peche ou de stats(artisanale/experimentale)
// $typeAction : le type de filiere
//*********************************************************************
// En sortie : 
// La fonction renvoie $tableau
//*********************************************************************	
	global $FiliereEnCours ;
	global $TypePecheEnCours;
	global $ListeToutesValeurs;
	$ListeToutesValeurs = "";
	$FiliereEnCours = $typeAction;
	$TypePecheEnCours = $typePeche;
	$fichiercolonne = $_SERVER["DOCUMENT_ROOT"]."/conf/ExtractionDefColonnes.xml";
	// Appel à la fonction de création et d'initialisation du parseur
	if (!(list($xml_parser_col, $fp) = new_xml_parser_Colonnes($fichiercolonne,"tout"))){ 
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
	return $ListeToutesValeurs;
}

//*********************************************************************
// recupereTouteColonnes : Fonction qui analyse la colonne en cours et complete le SQL si besoin
function analyseColonne($typePeche,$typeAction,$tableStat,$typeStatistiques){
// Cette fonction permet de recupérer controler si la colonne en cours necessite une requete SQL supplementaire et l'ajoute 
// cas echeant
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $typePeche : le type de peche ou de stats(artisanale/experimentale)
// $typeAction : le type de filiere
// $tableStat : uniquement pour les tables de statistiques.
//*********************************************************************
// En sortie : 
// La fonction renvoie un message d'erreur ou non
// La fonction met a jour les variables globales :	$listeChampsSel, $ListeTableSel, $AjoutWhere;
//*********************************************************************	
	global $EcrireLogComp;
	global $logComp;
	global $pasdefichier;
	global $listeChampsSel;
	global $ListeTableSel;
	global $AjoutWhere;
	global $LeftOuterJoin;
	
	$CR="ok";
	if (!($_SESSION['listeColonne'] =="")){
		// On gere deux cas distincts
		// Les stats pour lesquelles c'est tout ou rien.
		// L'extration PecheArt PecheExp pour lesquelles on gere table par table.
		if ($typePeche == "statistiques"){
			if ($_SESSION['listeColonne'] == "XtoutX") {
				switch ($tableStat) {
					case "ast":
						if ($typeStatistiques == "generales") {
							$listeChampsSel = ",ast.nbre_obs, ast.obs_min,ast.obs_max";
						}else {
							$listeChampsSel = ",agg.art_type_agglomeration_id,ast.nbre_obs, ast.obs_min,ast.obs_max, ast.pue_ecart_type,ast.fpe,ast.nbre_unite_recensee_periode,ast.nbre_jour_activite,ast.nbre_jour_enq_deb";
						}
						$ListeTableSel = "";
						$AjoutWhere = "";						
						break;	
					case "asp":
						if ($typeStatistiques == "generales") {
							$listeChampsSel = ",asp.nbre_enquete_sp, asp.obs_sp_min,asp.obs_sp_max,esp.ref_categorie_ecologique_id,cate.libelle,esp.ref_categorie_trophique_id,catt.libelle,fam.libelle";
						}else {
							$listeChampsSel = ",asp.nbre_enquete_sp, asp.obs_sp_min,asp.obs_sp_max, asp.pue_sp_ecart_type,esp.ref_categorie_ecologique_id,cate.libelle,esp.ref_categorie_trophique_id,catt.libelle,fam.libelle";
						}
						$ListeTableSel = ",ref_famille as fam";
						$AjoutWhere = " fam.id = esp.ref_famille_id  ";
						$LeftOuterJoin = ",(ref_espece as esp left outer join ref_categorie_ecologique as cate on cate.id = esp.ref_categorie_ecologique_id) left outer join ref_categorie_trophique as catt on catt.id = esp.ref_categorie_trophique_id ";
						
					break;
					case "ats":
						$listeChampsSel = ",esp.ref_categorie_ecologique_id,cate.libelle,esp.ref_categorie_trophique_id,catt.libelle,fam.libelle";
						$ListeTableSel = ",ref_famille as fam";
						$AjoutWhere = " fam.id = esp.ref_famille_id  ";
						$LeftOuterJoin = ",(ref_espece as esp left outer join ref_categorie_ecologique as cate on cate.id = esp.ref_categorie_ecologique_id) left outer join ref_categorie_trophique as catt on catt.id = esp.ref_categorie_trophique_id ";
					break;
					case "asgt":
						if ($typeStatistiques == "generales") {
							$listeChampsSel = ",asgt.nbre_enquete_gt, asgt.obs_gt_min, asgt.obs_gt_max";
						}else {
							$listeChampsSel = ",asgt.nbre_enquete_gt, asgt.obs_gt_min, asgt.obs_gt_max, asgt.pue_gt_ecart_type,asgt.fpe_gt";
						}
						$ListeTableSel = "";
						$AjoutWhere = "";
					break;
					case "attgt":
						if ($typeStatistiques == "generales") {
							$listeChampsSel = ",attgt.nbre_enquete_gt_sp,attgt.obs_gt_sp_min, attgt.obs_gt_sp_max,esp.ref_categorie_ecologique_id,cate.libelle,esp.ref_categorie_trophique_id,catt.libelle,fam.libelle";
						}else {
							$listeChampsSel = ",attgt.nbre_enquete_gt_sp,attgt.obs_gt_sp_min, attgt.obs_gt_sp_max,attgt.pue_gt_sp_ecart_type,esp.ref_categorie_ecologique_id,cate.libelle,esp.ref_categorie_trophique_id,catt.libelle,fam.libelle";
						}
						$ListeTableSel = ",ref_famille as fam";
						$AjoutWhere = " fam.id = esp.ref_famille_id  ";
						$LeftOuterJoin = ",(ref_espece as esp left outer join ref_categorie_ecologique as cate on cate.id = esp.ref_categorie_ecologique_id) left outer join ref_categorie_trophique as catt on catt.id = esp.ref_categorie_trophique_id ";
					break;
					case "atgts":
						$listeChampsSel = ",esp.ref_categorie_ecologique_id,cate.libelle,esp.ref_categorie_trophique_id,catt.libelle,fam.libelle";
						$ListeTableSel = ",ref_famille as fam";
						$AjoutWhere = " fam.id = esp.ref_famille_id  ";
						$LeftOuterJoin = ",(ref_espece as esp left outer join ref_categorie_ecologique as cate on cate.id = esp.ref_categorie_ecologique_id) left outer join ref_categorie_trophique as catt on catt.id = esp.ref_categorie_trophique_id ";
					break;
				}	
			} 
		} else {
			$champSel = explode(",",$_SESSION['listeColonne']);
			// On va completer les champs si on a tout selectionné.
			if (strpos($_SESSION['listeColonne'],"toutX") > 0) {
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "INFO : liste complete a construire",$pasdefichier);
				} 
				$toutesColonnes = recupereTouteColonnes($typePeche,$typeAction);
				//echo "toute colonnes = ".$toutesColonnes."<br/>";
				$champSel = explode(",",$toutesColonnes);
			}  
			$nbrSel = count($champSel)-1;
			for ($cptSel = 0;$cptSel <= $nbrSel;$cptSel++) {
				$TNomLongTable ="";
				if (($champSel[$cptSel] == "XtoutX") || ($champSel[$cptSel] == "XpasttX")) {
					continue ;
				}
				if (strpos($champSel[$cptSel],"-N") === false  ) { // On ne traite pas les colonnes décochées, ni le choix tout / pas tout
					if ( strpos($champSel[$cptSel],"-X") === false ) {
						$valTest = $champSel[$cptSel];
					} else {
						$valTest = substr($champSel[$cptSel],0,-2);
					}
					// Extraction de l'alias de la variable correspondant au champ coché
					$PosDas = strpos($valTest,"-");
					$TNomTable = substr($valTest,0,$PosDas);
					// Traitement de cas particulier ou on ajoute des champs supplémentaires
					switch ($typePeche) {
						case "experimentale" :
								case "cate" : 
								if ($typeAction=="NtPart") {
									if (strpos($listeChampsSel,"cate.id") === false) {$listeChampsSel .= ",cate.id";}
								}
								break;
								case "catt" : 
								if ($typeAction=="NtPart") {
									if (strpos($listeChampsSel,"catt.id") === false) {$listeChampsSel .= ",catt.id";}
								}
						break;
						case "artisanale" :	
							switch ($TNomTable) {
								case "cate" : 
								if ($typeAction=="NtPart") {
									if (strpos($listeChampsSel,"cate.id") === false) {$listeChampsSel .= ",cate.id";}
								}
								break;
								case "catt" : 
								if ($typeAction=="NtPart") {
									if (strpos($listeChampsSel,"catt.id") === false) {$listeChampsSel .= ",catt.id";}
								}
								break;
								case "gte" :
								if ($typeAction=="activite") {
									if (strpos($listeChampsSel,"act.art_grand_type_engin_id") === false) {
										$listeChampsSel .= ",act.art_grand_type_engin_id";
									}
								}
								if ($typeAction=="capture" || $typeAction=="NtPart" || $typeAction=="taillart") {
									if (strpos($listeChampsSel,"deb.art_grand_type_engin_id") === false) {
										$listeChampsSel .= ",deb.art_grand_type_engin_id";
									}
								}
								break;
							}
							break;
					}					
					// Ajout du champ en cours dans la liste des champs supplémentaires
					$listeChampsSel .= ",".str_replace("-",".",$valTest);
//echo "valTest $valTest listeChampsSel $listeChampsSel TNomTable $TNomTable TNomLongTable $TNomLongTable<br/><br/>" ; // FW où est passé 'env' ????

					switch ($typePeche) {
						case "experimentale" :						
							switch ($TNomTable) {
								// On peut avoir des valeurs null, on y met un left outer join	
								// On le fait que si on n'a pas mis de remplissage deja dedans car la selection de remplissage arrive avant.
								// si l'ordre est changé, ce test doit etre mis a jour (ou non !)
								case "env" : // FWOEHL 20180213 rajout du case env comprenant tout de meme le decodage des force et sens_courant pour simplifier les 3 cas
									// on a 3 cas :
									// - env (sans decodage force sens courant)
									// - efs (envir + decodage)
									// - xssc (force et sens id uniquement)
									// Mais on a éventuellement les tables force_courant liées et pour simplifier on va les liéer dans tous les cas env...
//echo "on passe par ce case 'env' (functions.php #3100)" ;
									if (strpos($LeftOuterJoin,"efc.id = env.exp_force_courant_id") == false ) { // FW as xssc because of env case
										$LeftOuterJoin .= ",(exp_environnement as env left outer join exp_force_courant as efc on efc.id = env.exp_force_courant_id) left outer join exp_sens_courant as xssc on xssc.id = env.exp_sens_courant_id";
									}
//									$ListeTableSel = ajoutAuTableSel($ListeTableSel,"exp_environnement", ",exp_environnement as env");
//									$AjoutWhere = ajouterAuWhere($AjoutWhere," env.id = cph.exp_environnement_id  " ) ; 		
									break;	
								case "cate" : 	
									if (strpos($LeftOuterJoin,"cate.id = esp.ref_categorie_ecologique_id") === false ) {
										$LeftOuterJoin .= ",(ref_espece as esp left outer join ref_categorie_ecologique as cate on cate.id = esp.ref_categorie_ecologique_id) left outer join ref_categorie_trophique as catt on catt.id = esp.ref_categorie_trophique_id";
									}
									break;
								case "catt" :
									if (strpos($LeftOuterJoin,"catt.id = esp.ref_categorie_trophique_id") === false ) {
										$LeftOuterJoin .= ",(ref_espece as esp left outer join ref_categorie_ecologique as cate on cate.id = esp.ref_categorie_ecologique_id) left outer join ref_categorie_trophique as catt on catt.id = esp.ref_categorie_trophique_id";
									}			
									break;
								case "ord" :
									$TNomLongTable = "ref_ordre";	
									$ListeTableSel = ajoutAuTableSel($ListeTableSel,$TNomLongTable, ", ".$TNomLongTable." as ".$TNomTable);
									$AjoutWhere = ajouterAuWhere($AjoutWhere," ".$TNomTable.".id = fam.".$TNomLongTable."_id "); 		
									break;	
								case "xsed" :
									if (strpos($LeftOuterJoin,"xdeb.id = stat.exp_debris_id") == false ) {
										$LeftOuterJoin .= ",(((exp_station as stat left outer join exp_vegetation as xveg on xveg.id = stat.exp_vegetation_id) left outer join exp_debris as xdeb on  xdeb.id = stat.exp_debris_id) left outer join exp_sediment as xsed  on xsed.id = stat.exp_sediment_id) left outer join exp_position as xpos  on xpos.id = stat.exp_position_id";
									} 
									//$TNomLongTable = "exp_sediment";	
									//$ListeTableSel = ajoutAuTableSel($ListeTableSel,$TNomLongTable, ", ".$TNomLongTable." as ".$TNomTable);
									//$AjoutWhere = ajouterAuWhere($AjoutWhere," ".$TNomTable.".id = stat.".$TNomLongTable."_id "); 		
									break;	
								case "efc" :
//echo "on passe par ce case 'efc' (functions.php #3137)" ;
									if (strpos($LeftOuterJoin,"efc.id = env.exp_force_courant_id") == false ) { // FW as xssc because of env case
										$LeftOuterJoin .= ",(exp_environnement as env left outer join exp_force_courant as efc on efc.id = env.exp_force_courant_id) left outer join exp_sens_courant as xssc on xssc.id = env.exp_sens_courant_id";
									}
									//$TNomLongTable = "exp_force_courant";	
									//$ListeTableSel = ajoutAuTableSel($ListeTableSel,$TNomLongTable, ", ".$TNomLongTable." as ".$TNomTable);
									//$AjoutWhere = ajouterAuWhere($AjoutWhere," ".$TNomTable.".id = env.".$TNomLongTable."_id "); 		
									break;
								case "xremp" :
									$LeftOuterJoin .= ",((exp_biologie as bio left outer join exp_sexe as xsex  on xsex.id = bio.exp_sexe_id) left outer join exp_remplissage as xremp  on xremp.id = bio.exp_remplissage_id) left outer join exp_stade as xsta  on xsta.id = bio.exp_stade_id";		
									break;
								case "xpos" :
									if (strpos($LeftOuterJoin,"xdeb.id = stat.exp_debris_id") == false ) {
										$LeftOuterJoin .= ",(((exp_station as stat left outer join exp_vegetation as xveg on xveg.id = stat.exp_vegetation_id) left outer join exp_debris as xdeb on  xdeb.id = stat.exp_debris_id) left outer join exp_sediment as xsed  on xsed.id = stat.exp_sediment_id) left outer join exp_position as xpos  on xpos.id = stat.exp_position_id";
									} 
									//$TNomLongTable = "exp_position";	
									//$ListeTableSel = ajoutAuTableSel($ListeTableSel,$TNomLongTable, ", ".$TNomLongTable." as ".$TNomTable);
									//$AjoutWhere = ajouterAuWhere($AjoutWhere," ".$TNomTable.".id = stat.".$TNomLongTable."_id "); 		
									break;
								case "xsta" :
									if (strpos($LeftOuterJoin,"xremp.id = bio.exp_remplissage_id") == false ) {
										$LeftOuterJoin .= ",((exp_biologie as bio left outer join exp_sexe as xsex  on xsex.id = bio.exp_sexe_id) left outer join exp_remplissage as xremp  on xremp.id = bio.exp_remplissage_id) left outer join exp_stade as xsta  on xsta.id = bio.exp_stade_id";
									}		
									break;
								case "xssc" :
									if (strpos($LeftOuterJoin,"efc.id = env.exp_force_courant_id") == false ) {
										$LeftOuterJoin .= ",(exp_environnement as env left outer join exp_force_courant as efc  on efc.id = env.exp_force_courant_id) left outer join exp_sens_courant as xssc  on xssc.id = env.exp_sens_courant_id";
									}
									//$TNomLongTable = "exp_sens_courant";	
									//$ListeTableSel = ajoutAuTableSel($ListeTableSel,$TNomLongTable, ", ".$TNomLongTable." as ".$TNomTable);
									//$AjoutWhere = ajouterAuWhere($AjoutWhere," ".$TNomTable.".id = env.".$TNomLongTable."_id "); 		
									break;
								case "xsex" :
									if (strpos($LeftOuterJoin,"xsta.id = bio.exp_stade_id") == false ) {
										$LeftOuterJoin .= ",((exp_biologie as bio left outer join exp_sexe as xsex  on xsex.id = bio.exp_sexe_id) left outer join exp_remplissage as xremp  on xremp.id = bio.exp_remplissage_id) left outer join exp_stade as xsta  on xsta.id = bio.exp_stade_id";
									} 		
									break;								
								case "xveg" :
									if (strpos($LeftOuterJoin,"xdeb.id = stat.exp_debris_id") == false ) {
										$LeftOuterJoin .= ",(((exp_station as stat left outer join exp_vegetation as xveg on xveg.id = stat.exp_vegetation_id) left outer join exp_debris as xdeb on  xdeb.id = stat.exp_debris_id) left outer join exp_sediment as xsed  on xsed.id = stat.exp_sediment_id) left outer join exp_position as xpos  on xpos.id = stat.exp_position_id";
									}
									break;
								case "xdeb" :
									if (strpos($LeftOuterJoin,"xdeb.id = stat.exp_debris_id") == false ) {
										$LeftOuterJoin .= ",(((exp_station as stat left outer join exp_vegetation as xveg on xveg.id = stat.exp_vegetation_id) left outer join exp_debris as xdeb on  xdeb.id = stat.exp_debris_id) left outer join exp_sediment as xsed  on xsed.id = stat.exp_sediment_id) left outer join exp_position as xpos  on xpos.id = stat.exp_position_id";
									} 	
									break;								
						} // fin du switch ($TNomTable) 
							break;
						case "artisanale" :
							$LeftOuterJoinDeb = "(((((art_debarquement as deb left outer join art_grand_type_engin as gte on gte.id = deb.art_grand_type_engin_id) left outer join art_type_sortie as atsor on atsor.id = deb.art_type_sortie_id) left outer join art_lieu_de_peche as alieup on alieup.id = deb.art_lieu_de_peche_id) left outer join art_vent as avent on avent.id = deb.art_vent_id) left outer join art_millieu as amil on amil.id = deb.art_millieu_id) left outer join art_etat_ciel as aetatc on aetatc.id = deb.art_etat_ciel_id";
							$LeftOuterJoinAct = "(((((art_activite as act left outer join art_grand_type_engin as gte on gte.id = act.art_grand_type_engin_id) left outer join art_type_sortie as atsor on atsor.id = act.art_type_sortie_id) left outer join art_engin_activite as aenga on aenga.art_activite_id = act.id ) left outer join art_type_engin as teng on teng.id = aenga.art_type_engin_id) left outer join art_type_activite as tact on tact.id = act.art_type_activite_id) left outer join art_millieu as amil on amil.id = act.art_millieu_id";
							switch ($TNomTable) {
								case "cate" : 
									if (strpos($LeftOuterJoin,"cate.id = esp.ref_categorie_ecologique_id") === false ) {
										$LeftOuterJoin .= ",(ref_espece as esp left outer join ref_categorie_ecologique as cate on cate.id = esp.ref_categorie_ecologique_id) left outer join ref_categorie_trophique as catt on catt.id = esp.ref_categorie_trophique_id";
									}
									break;
								case "catt" :
									if (strpos($LeftOuterJoin,"catt.id = esp.ref_categorie_trophique_id") === false ) {
										$LeftOuterJoin .= ",(ref_espece as esp left outer join ref_categorie_ecologique as cate on cate.id = esp.ref_categorie_ecologique_id) left outer join ref_categorie_trophique as catt on catt.id = esp.ref_categorie_trophique_id";
									}		
									break;
								case "ord" :
									$TNomLongTable = "ref_ordre";		
									$ListeTableSel = ajoutAuTableSel($ListeTableSel,$TNomLongTable, ", ".$TNomLongTable." as ".$TNomTable);
									$AjoutWhere = ajouterAuWhere($AjoutWhere," ".$TNomTable.".id = fam.".$TNomLongTable."_id ");
									break;	
								case "fam" :
									$TNomLongTable = "ref_famille";
									$AjoutWhere = ajouterAuWhere($AjoutWhere," ".$TNomTable.".id = esp.".$TNomLongTable."_id "); 		
									break;
								case "acsp" :
									$LeftOuterJoin .= ",art_unite_peche as upec left outer join art_categorie_socio_professionnelle as acsp on acsp.id = upec.art_categorie_socio_professionnelle_id";
									break;
								case "aengp" :
									if (strpos($LeftOuterJoin,"gte.id = deb.art_grand_type_engin_id") === false ) {
										if (strpos($LeftOuterJoin,"aengp on aengp.art_debarquement_id = deb.id") === false ) {
											$LeftOuterJoin .= ",(".$LeftOuterJoinDeb.") left outer join art_engin_peche as aengp on aengp.art_debarquement_id = deb.id";
										} else {
											$LeftOuterJoin = str_replace (",art_debarquement as deb left outer join art_engin_peche as aengp on aengp.art_debarquement_id = deb.id","",$LeftOuterJoin);
											$LeftOuterJoin .= ",(".$LeftOuterJoinDeb.") left outer join art_engin_peche as aengp on aengp.art_debarquement_id = deb.id";
										}
									} else {
										if (strpos($LeftOuterJoin,"aengp on aengp.art_debarquement_id = deb.id") === false ) {
											$LeftOuterJoin .= ",art_debarquement as deb left outer join art_engin_peche as aengp on aengp.art_debarquement_id = deb.id";
										}
									}
									break;
								case "aetatc" :
									if (strpos($LeftOuterJoin,"gte.id = deb.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinDeb;}

									break;								
								case "amil" :
									if ($typeAction=="activite") {
										if (strpos($LeftOuterJoin,"gte.id = act.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinAct;}
									} else {
										if (strpos($LeftOuterJoin,"gte.id = deb.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinDeb;}
									}
									break;
								case "alieup" :
									if (strpos($LeftOuterJoin,"gte.id = deb.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinDeb;}
									break;
								case "atsor" :
									if ($typeAction=="activite") {
										if (strpos($LeftOuterJoin,"gte.id = act.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinAct;}
									} else {
										if (strpos($LeftOuterJoin,"gte.id = deb.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinDeb;}
									}
									break;
								case "avent" :
									if (strpos($LeftOuterJoin,"gte.id = deb.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinDeb;}
									break;									
								case "gte" :
									if ($typeAction=="activite") {
										if (strpos($LeftOuterJoin,"gte.id = act.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinAct;}
									}
									if ($typeAction=="capture" || $typeAction=="NtPart") {
										if (strpos($LeftOuterJoin,"gte.id = deb.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinDeb;}
									}
									break;
								case "teng" :
									if ($typeAction=="activite") {
										if (strpos($LeftOuterJoin,"gte.id = act.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinAct;}
									}
									break;
								case "tagg" :
									$LeftOuterJoin .= ",art_agglomeration as agg left outer join art_type_agglomeration as tagg on tagg.id = agg.art_type_agglomeration_id";		
									break;
								case "tact" :
									if (strpos($LeftOuterJoin,"gte.id = act.art_grand_type_engin_id") === false ) {$LeftOuterJoin .= ",".$LeftOuterJoinAct;}		
									break;
							} // fin du switch ($TNomTable)
							break;
					}// fin du switch ($typePeche)
				} // fin du if (strpos($champSel[$cptSel],"-N") === false  )
			} // fin du for ($cptSel = 0;$cptSel <= $nbrSel;$cptSel++) {
		}// fin du if ($typePeche == "statistiques")
	} // fin du (!($_SESSION['listeColonne'] ==""))	
	return $CR;
}

//*********************************************************************
// AnaylseVarSession : Fonction qui reconstruit une variable de session
function AnaylseVarSession($ValeurATester){
// Cette fonction permet de tester si la variable de session contient la valeur à tester
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $VarSession : la variable de session
// $ValeurATester : la valeur à tester
//*********************************************************************
// En sortie : 
// La fonction renvoie $VarSession
//*********************************************************************
// Euh, est-ce encore important de garder cette fonction ????? C'est un peu cretin comme truc. N'a d'interet que si on teste quelque chose..... A REVOIR ET/OU A VIRER
// On reconstruit les valeurs pour la variable de session
	$VarSession = "";
	$colRecues = explode (",",$ValeurATester);
	$NumColR = count($colRecues) - 1;
	for ($cptCR=0 ; $cptCR<=$NumColR;$cptCR++) {
		if ($VarSession == "") {
			$VarSession = $colRecues[$cptCR] ;
		} else {
			$VarSession .= ",".$colRecues[$cptCR];
		}
	}
	return $VarSession;
}

//*********************************************************************
// ouvreFichierLog : Fonction pour ouvrir le fichier log
function ouvreFichierLog($dirLog,$fileLogComp) {
// Cette fonction permet d'ouvrir le fichier log
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $dirLog : le répertoire du fichier log
// $fileLogComp : le nom du fichier log
//*********************************************************************
// En sortie : 
// La fonction renvoie $listeSelection
//*********************************************************************
	Global $logComp;
	Global $nomLogLien;
	Global $EcrireLogComp;
	if (! file_exists($dirLog)) {
		if (! mkdir($dirLog) ) {
			$messageGen = " erreur de cr&eacute;ation du r&eacute;pertoire de log";
			echo "<b>Erreur de cr&eacute;ation du r&eacute;pertoire de log ".$dirLog."<b/><br/>" ;
			exit;
		}
	}
//	Controle fichiers
//	Resultat de la comparaison
	if ($EcrireLogComp ) {
		$nomFicLogComp = $dirLog."/".date('y\-m\-d')."-".$fileLogComp;
		$nomLogLien = $nomLogLien."/".date('y\-m\-d')."-".$fileLogComp;
		$logComp = fopen($nomFicLogComp , "a+");
		if (! $logComp ) {
			$messageGen = " erreur de cr&eacute;ation du fichier de log";

			echo "<b>Erreur de cr&eacute;ation du fichier de log ".$nomFicLogComp." dans function ouvreFichierLog <b/><br/>" ;
			exit;		
		}
	}
}

//*********************************************************************
// RecupereEspeces : Fonction pour ouvrir le fichier log
function RecupereEspeces($SQLAexec){
// Cette fonction permet de nettoyer le SQL des especes des especes surnumeraires
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $SQLAexec : Le SQL contenant la liste des especes en cours
//*********************************************************************
// En sortie : 
// La fonction renvoie $SQLEspeces, la liste nettoyée des doublons
//*********************************************************************
// On reconstruit la liste des especes de la sélection.
	global $connectPPEAO;
	global $EcrireLogComp;
	global $logComp;
	global $pasdefichier;
	global $erreurProcess;
	global $resultatLecture;
	$SQLEspeces = "";
	//if ($EcrireLogComp && $debugLog) {
	//	WriteCompLog ($logComp, "Var SQLEsp = ".$SQLEsp,$pasdefichier);
	//}	

	$SQLEspResult = pg_query($connectPPEAO,$SQLAexec);
	$erreurSQL = pg_last_error($connectPPEAO);
	if ( !$SQLEspResult ) { 
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "ERREUR : construction liste especes. Requete en erreur : ".$SQLEsp." (erreur compl&egrave;te = ".$erreurSQL.")",$pasdefichier);
		} else {
			$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;erreur query ".$SQLEsp." (erreur compl&egrave;te = ".$erreurSQL.")<br/>";
		}
		$erreurProcess = true;
	} else {
		if (pg_num_rows($SQLEspResult) == 0) {
		// Erreur
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "Activite/debarquement vide pour recuperer les especes...",$pasdefichier);
			} else {
				$resultatLecture .= "<img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>Activite/debarquement vide pour recuperer les especes...<br/>";}
		} else {
		//echo "<b>Nbre especes pr&eacute;lectionnes = &eacute;".pg_num_rows($SQLEspResult)."</b><br/>";
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "INFO : nombre d'especes preselectionnees = ".pg_num_rows($SQLEspResult),$pasdefichier);
		}
			while ($EspRow = pg_fetch_row($SQLEspResult) ) {
				if (strpos($SQLEspeces,$EspRow[0]) === false ) {
					$SQLEspeces .= "'".$EspRow[0]."',";	
				}
			}		
		}
		pg_free_result($SQLEspResult);
	}
	$SQLEspeces	= substr($SQLEspeces,0,- 1); // pour enlever la virgule surnumeraire;
	return $SQLEspeces;
}

//*********************************************************************
// remplaceAlias : Fonction pour remplacer les alias par le nom de la table
function remplaceAlias($listeDesChamps,$Idunique,$table,$typeStatistiques) {
// Cette fonction permet de remplacer pour l'affichage les alias par les nom complets des tables
//*********************************************************************
// En entrée, les paramètres suivants sont :
// $listeDesChamps : la liste des champs avec les alias
// $Idunique = y si ajout id unique, n si pas ajout idunique
// $table : la table a tester
//*********************************************************************
// En sortie : 
// La fonction renvoie $listeDesChamps, la liste mise à jour avec les noms des tables
//*********************************************************************
// On reconstruit le titre en recuperant les noms de tables depuis le fichier XML
	$listeDesTitres = "";
	$listeTitre = explode(",",$listeDesChamps);
	$nbrTitre = count($listeTitre)-1;
	for ($cptT=0 ; $cptT<=$nbrTitre;$cptT++) {
		// Test suppression
		if ($Idunique == "y") {
			$cptRow = $cptT - 1; // On doit prendre en compte la position de l'ID unique en debut de ligne
		} else {
			$cptRow = $cptT; // Pas d'ID unique en debut de ligne
		}
		$Asupprimer = TestsuppressionChamp($table,$cptRow,$typeStatistiques);
		if (!$Asupprimer) {
			//echo "pas a supprimer : ".$table." ".$listeTitre[$cptT]." <br/>";
			if ( $listeTitre[$cptT]=="id.unique" || $listeTitre[$cptT]=="Coeff_extrapolation" || $listeTitre[$cptT]=="Nombre_individus_mesures" || 
			$listeTitre[$cptT]=="Effort_total" || $listeTitre[$cptT]=="Agglomeration_origine_unite" || $listeTitre[$cptT]=="Effort_total_strate" ||
			$listeTitre[$cptT]=="Pue_totale" || $listeTitre[$cptT]=="Captures_totales" ||
			$listeTitre[$cptT]=="Pue_espece" ||  $listeTitre[$cptT]=="Captures_espece" ||
			$listeTitre[$cptT]=="Effectif_strate" || 
			$listeTitre[$cptT]=="Pue_GT" || $listeTitre[$cptT]=="Effort_GT" || $listeTitre[$cptT]=="Captures_GT" ||
			$listeTitre[$cptT]=="Pue_GT_espece" || $listeTitre[$cptT]=="Captures_GT_espece" ||
			$listeTitre[$cptT]=="Effectif" 
			) {
				if ($listeDesTitres == "") {
					$listeDesTitres = $listeTitre[$cptT];
				} else {
					$listeDesTitres .= ",".$listeTitre[$cptT];
				}			
			} else {
				$champ = explode(".",$listeTitre[$cptT]);
				$nomTableEC = $champ[0];
				$nomChampEC = $champ[1];
				//$nomTable = recupeNomTableAlias($nomTableEC);
				$nomChampTemp = recupeNomChamps($nomTableEC."-".$nomChampEC);
				if ($nomChampTemp == "inconnu") {
					$nomChampTemp = $nomChampEC;
				}
				$nomChamp = $nomChampTemp;
				if ($listeDesTitres == "") {
					$listeDesTitres = $nomChamp;
				} else {
					$listeDesTitres .= ",".$nomChamp;
				}
			}		
		} else {
			//echo "a supprimer : ".$table." ".$listeTitre[$cptT]." <br/>";	
		}
	}
	//echo $listeDesTitres."<br/>";
	return $listeDesTitres;
}

//*********************************************************************
// recupeNomTableAlias : Fonction pour recuperer le nom de la table
function recupeNomTableAlias($tableAlias){
// Cette fonction permet de 
//*********************************************************************
// En entrée, les paramètres suivants sont :
// 
//*********************************************************************
// En sortie : 
// 
//*********************************************************************
	// Attention, comme cette fonction n'est pour l'instant plus utilisé, le remplissage de la variable $_SESSION['libelleTable'] a été désactivé dans extraction_xml.php
	$NbReg = count($_SESSION['libelleTable']);
	$nomTable = "inconnu";
	for ($cptR=1 ; $cptR<=$NbReg;$cptR++) {
		$tablib = explode(",",$_SESSION['libelleTable'][$cptR]);
		if(trim($tablib[1]) == trim ($tableAlias)) {
			$nomTable = $tablib[0];
			break;
		}
	}
	return $nomTable;
}

//*********************************************************************
// recupeNomChamps : Fonction pour recuperer le libelle du champs en cours defini dans le fichier de conf XML
function recupeNomChamps($ValeurATester){
// Cette fonction permet de 
//*********************************************************************
// En entrée, les paramètres suivants sont :
// 
//*********************************************************************
// En sortie : 
// 
//*********************************************************************
	$NbReg = count($_SESSION['libelleChamp']);
	//echo $NbReg." dans libelleChamp <br/>";
	$libelleChamps = "inconnu";
	for ($cptR=1 ; $cptR<=$NbReg;$cptR++) {
		//echo $cptR." ".$_SESSION['libelleChamp'][$cptR]."<br/>";
		$tablib = explode(",",$_SESSION['libelleChamp'][$cptR]);
		if(trim($tablib[0]) == trim ($ValeurATester)) {
			$libelleChamps = $tablib[1];
			break;
		}
	}
	return $libelleChamps;
}

//*********************************************************************
// creeDirTemp : Fonction pour creer le repertoire temporaire
function creeDirTemp($dir){
// Cette fonction permet de 
//*********************************************************************
// En entrée, les paramètres suivants sont :
// 
//*********************************************************************
// En sortie : 
// 
//*********************************************************************
	if (! file_exists($dir)) {
		if (! mkdir($dir) ) {
			$resultat = " erreur,repertoire ne peut etre cree";
		} else {
			$resultat = "ok,repertoire cree";
		}
	} else {
	$resultat = "ok,repertoire deja existant";
	}
	return $resultat;
}

//*********************************************************************
// creeDirTemp : Fonction pour creer le repertoire temporaire
function getSuffixeFicStat($ValATester) {
// Cette fonction permet de 
//*********************************************************************
// En entrée, les paramètres suivants sont :
// 
//*********************************************************************
// En sortie : 
// 
//*********************************************************************
	$ValARetourner = "";
	Switch ($ValATester) {
		case "ast": $ValARetourner = "stat_totale";  break;
		case "asp": $ValARetourner = "stat_sp";  break;
		case "ats": $ValARetourner = "stat_sp_dft";  break;
		case "asgt": $ValARetourner = "stat_GT";  break;
		case "attgt": $ValARetourner = "stat_GT_sp";  break;
		case "atgts": $ValARetourner = "stat_Gt_sp_dft";  break;
	
	}
	return $ValARetourner;
}

?>