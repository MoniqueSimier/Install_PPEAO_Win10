<?php 
//*****************************************
// export_compress.php
//*****************************************
// Created by Yann Laurent
// 2009-02-17 : creation
//*****************************************
// Ce programme lance la compression  
// Le résultat du traitement est envoyé à portage_auto.php dans deux div qui seront insérés dans le div général 
// avec une icone de bonne ou mauvaise exécution (dans div id="xxxxxxx_img") et l'explication
// de l'erreur dans div id = "xxxxx_txt"
//*****************************************
// Paramètres en entrée


// Paramètres en sortie
// La liste des différences par table est affichée à l'écran et est stockée dans un fichier


// Attention l'activation de l'ecriture dans la table des logs peut amener a des performances catastrophiques (la table peut rapidement etre enorme
// Privilegier plutot l'ecriture dans le fichier log complémentaire

// Faudrait remplacer tous les access a la table du log par des ecritures dans le fichier de log....

session_start();
// Variable qui permet d'identifier si le traitement est lancé
$pasdetraitement = true;
$pasdefichier = false;
$cptAjoutMaj = 0; // pour compatibilite
set_time_limit(180);
$debugAff = false; // variable globale pour lancer le programme en mode debug
// Variables de traitement
$ErreurProcess = false; // Flag si erreur process
$affichageDetail = false; // Pour afficher ou non le detail des traitements à l'écran
// Includes standard
include $_SERVER["DOCUMENT_ROOT"].'/variables.inc';
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/config.php';
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/export/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/export/config.php';
include $_SERVER["DOCUMENT_ROOT"].'/zip/archive.php';

// ***** Recuperation des parameters en entree 

// On identifie si le traitement est exécutable ou non
if (isset($_GET['exec'])) {
	if ($_GET['exec'] == "false") {
		$pasdetraitement =  true;
		$Labelpasdetraitement ="non"; 
	} else {
		$pasdetraitement =  false;
		$Labelpasdetraitement ="oui";
	}
}


$ExecSQL = "n";
$nomBDSource = ""; 
$nomBDCible = ""; 
$allScriptSQL = "";
$nomFenetre="copieZip";
if (isset($_GET['log'])) {

	if ($_GET['log'] == "false") {
		$EcrireLogComp = false;// Ecrire dans le fichier de log complémentaire. Attention, cela prend de la ressource !
	} else {
		$EcrireLogComp = true;
	}
}

if (isset($_GET['tp'])) {
	$typePeche =$_GET['tp'];
} else {
	echo "Erreur : pas de parametre tp. <br/>";
	exit;
}



// ***** Test si arret processus lié à l'exécution du traitement précédent 	
// Si le traitement précédent a échoué, arrêt du traitement

if (isset($_SESSION['s_status_export'])) {
	if ($_SESSION['s_status_export'] == 'ko') {
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\"> ARRET du traitement car le processus pr&eacute;c&eacute;dent est en erreur</div>" ;
		exit;
	}
}

// ***** Variables de traitements
$CRexecution="";
$erreurProcess = false;
// On récupère les valeurs des paramètres pour les fichiers log
$dirLog = GetParam("repLogAccess",$PathFicConfAccess);
$nomLogLien = "/".$dirLog; // pour créer le lien au fichier dans le cr ecran
$dirLog = $_SERVER["DOCUMENT_ROOT"]."/".$dirLog;
$fileLogComp = GetParam("nomFicLogSuppAc",$PathFicConfAccess);
$fileCompression = GetParam("nomFicLogCompression",$PathFicConfAccess);

// ***** Debut du traitement

if (! $pasdetraitement ) { // Permet de sauter cette étape (choix de l'utilisateur ou debug)

// Traitements préliminaires : 
// *********************************************
//	Contrôle des répertoires et fichiers log
// 		Controle répertoire
	if (! file_exists($dirLog)) {
		if (! mkdir($dirLog) ) {
			$messageGen = " Erreur de cr&eacute;ation du r&eacute;pertoire de log";
			logWriteTo(8,"error","Erreur de cr&eacute;ation du r&eacute;pertoire de log dans comparaison.php.","","","0");
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">ERREUR .".$messageGen."</div>" ;
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
			$messageGen = " Erreur de cr&eacute;ation du fichier de log";
			logWriteTo(8,"error","Erreur de cr&eacute;ation du fichier de log ".$dirLog."/".date('y\-m\-d')."-".$fileLogComp." dans comparaison.php.","","","0");
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">ERREUR .".$messageGen."</div>" ;
			exit;		
		}
	}
	// Test de la connexion à la BD de ref (pour log entre autre)
	if (!$connectPPEAOACCESS) { 
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Erreur de connexion &agrave; la base de donn&eacute;es bdppeao pour maj des logs</div>" ; exit;
	}
	
	// Début du traitement de comparaison par table.
	// *********************************************

	// *************************************************
	// Traitement de compression
	// dans l'archive, on met la base de travail + la base supplémentaire
	// *************************************************
	switch ($typePeche) { 
		case "exp":
			$BDACCESS = GetParam("nomBDRefExp",$PathFicConfAccess);
			$filestoAdd = "";
			$nomPeche = "peches experimentales";
			$fileSQLSup = GetParam("sqlPechexp",$PathFicConfAccess); // fichier contenant les SQL supplementaires
			$fileMAJExcel = GetParam("ficxlsPechexp",$PathFicConfAccess); // fichier(s) excel contenant les données supplementaires à maj
			break;
		case "art":
			$BDACCESS = GetParam("nomBDRefArt",$PathFicConfAccess);
			$filestoAdd = "PechartPays.mdb,PechartSaisie.mdb";
			$nomPeche = "peches artisanales";
			$fileSQLSup = GetParam("sqlPechart",$PathFicConfAccess); // fichier contenant les SQL supplementaires
			$fileMAJExcel = GetParam("ficxlsPechart",$PathFicConfAccess); // fichier(s) excel contenant les données supplementaires à maj
			break;	
	}
	$BDreptravail = GetParam("nomRepBDtravail",$PathFicConfAccess);
	$BDrep = GetParam("nomRepBD",$PathFicConfAccess);
	$BDfic = $_SERVER["DOCUMENT_ROOT"]."/".$BDreptravail."/".$BDACCESS."_travail.mdb";
	$BDACCESSTravail = $BDACCESS."_travail";
	// Initialisation des logs
	logWriteTo(8,"notice","**- Debut lancement Zip","","","0");
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "*******************************************************",$pasdefichier);
		WriteCompLog ($logComp, "*- DEBUT lancement Zip peche ".$nomPeche,$pasdefichier);
		WriteCompLog ($logComp, "*------------------------------------------------------",$pasdefichier);
	}
	//0.a.mise à jour des tables a partir de fichiers EXCEL (Ajout Lot 5 YL 01-03-2001)
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "*- MAJ de tables a partir de fichiers excel",$pasdefichier);
	}
	// test de connexion a la base de travail
	$connectAccessTravail = odbc_connect($BDACCESSTravail,'Administrateurs','',SQL_CUR_USE_ODBC);
	if (!$connectAccessTravail) {
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "Erreur de la connexion &agrave; la base ACCESS de travail.".$BDACCESSTravail,$pasdefichier);
		}
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Erreur de connexion &agrave; la base ACCESS de travail. ".$BDACCESSTravail."</div>" ; exit;
	}

	$CRexecution .= "Avec MAJ de tables &agrave; partir de fichiers excel<br/>";
	$TableSFichierS = explode(",",$fileMAJExcel);
	$nbrFichiers = count($TableSFichierS)-1;
	$erreurSQLSup = "";
	for ($cptFic = 0;$cptFic <= $nbrFichiers;$cptFic++) {
		$TableFichier = explode("-",$TableSFichierS[$cptFic]); // chaque entree doit etre, dans le fichier de conf, du type nomTable-CheminFichier
		$fileXLS=$_SERVER["DOCUMENT_ROOT"]."/".$TableFichier[1];
		$nomTableAccess = $TableFichier[0];
		if (! file_exists($fileXLS)) {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "-- ERREUR: le fichier ".$TableFichier[1]." n'est pas pr&eacute;sent sur le serveur. Arr&ecirc;t du traitement.",$pasdefichier);
			}
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">--ERREUR : le fichier ".$TableFichier[1]." n'est pas pr&eacute;sent sur le serveur. Veuillez contacter votre administrateur.</div>" ;
			exit;
			$erreurProcess = true;
		} else {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "-- ok pour fichier ".$TableFichier[1]." maj de la table ".$TableFichier[0],$pasdefichier);
			}
			$fileXLSopen=fopen($fileXLS,'r');
			
			$cptLigne = 0;
			//insert into table (champ1,champ2) values (1,2));
			
			include $_SERVER["DOCUMENT_ROOT"].'/export/definitionImportXLS.php';
			while ( ($line = fgets($fileXLSopen)) !== false) {
				$cptLigne ++;
				//echo $line."<br/>";
				if ($cptLigne == 1) {
					// On recupere les noms des colonnes a maj
					$listeColonnes = str_replace(";",",",$line);
				} else {
					// On construit le SQL 
					$listeValeurs = explode(";",$line);
					$NbValeur = count($listeValeurs)-1;
					$listeValeursSQL = "";
					// Analyse de la ligne et des types associés pour construire la liste des valeurs
					for ($cptVal = 0;$cptVal <= $NbValeur;$cptVal++) {
						if ($typeChampTA[$cptVal] == "text") {
							$valReplace = str_replace ("'","''",$listeValeurs[$cptVal]);
							$ValAAjouter = "'".$valReplace."'";	
						} else {
							$valReplace = str_replace (",",".",$listeValeurs[$cptVal]);
							$ValAAjouter = $valReplace;
						}
						if ($listeValeursSQL == "") { $listeValeursSQL = $ValAAjouter; } else {$listeValeursSQL .= ",".$ValAAjouter;}
					}
					// Construction du SQL
					$SQL = "insert into ".$nomTableAccess." (".$listeColonnes .") values (".$listeValeursSQL.");";
					//echo $SQL."<br/>";
					// On exécute le SQL
					$ResultSQL = odbc_exec($connectAccessTravail,$SQL);
					$erreurSQL = odbc_errormsg($connectAccessTravail); //
					if (!$ResultSQL) {
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp, "Ligne ".$cptLigne." Erreur ex&eacute;cution SQL ".$line." - erreur compl&egrave;te = ".$erreurSQL."",$pasdefichier);
						}
						$erreurSQLSup.="Ligne ".$cptLigne." Erreur ex&eacute;cution SQL ".$line." - erreur compl&egrave;te = ".$erreurSQL."<br/>";
					} 
					odbc_free_result($ResultSQL);
				}// fin du else du if ($cptLigne == 1)
			}// fin du while
		} // fin du if (! file_exists($fileXLS))
	}// fin du for ($cptFic = 0;$cptFic <= $nbrFichiers;$cptFic++)
	if ($erreurSQLSup !="") {
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Erreur &agrave; l'ex&eacute;cution de script SQL = ".$erreurSQLSup."</div>" ; $erreurProcess = true; exit;
	}	
	
	//0.b. Exécution de scripts complémentaires avant le ZIP (Ajout Lot 5 YL 01-03-2001)
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp, "*- Ex&eacute;cution de scripts compl&eacute;mentaires",$pasdefichier);
	}
	$CRexecution .= "Avec ex&eacute;cution de scripts SQL<br/>";


	// lecture du fichier
	$fileSQL=$_SERVER["DOCUMENT_ROOT"]."/conf/".$fileSQLSup;
	$fileSQLopen=fopen($fileSQL,'r');
	$erreurSQLSup = executeSQLFichier($fileSQLopen,$connectAccessTravail,$EcrireLogComp,$logComp,$pasdefichier);

	if ($erreurSQLSup !="") {
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Erreur &agrave; l'execution de script SQL = ".$erreurSQLSup."</div>" ; $erreurProcess = true; exit;
	} 
	// 

	//1. copier et renomer la base de travail

	// On va utiliser un repertoire temp pour preparer l'archive
	// On teste si il existe
	$TempRep = $_SERVER["DOCUMENT_ROOT"]."/".$BDrep."/temp/";
	if (! file_exists($TempRep)) {
		if (! mkdir($TempRep) ) {
			$messageGen = " Erreur de cr&eacute;ation du r&eacute;pertoire temporaire /".$BDrep."/temp/ pour preparation archive.";
			logWriteTo(8,"error","Erreur de cr&eacute;ation du r&eacute;pertoire temporaire /".$BDrep."/temp/ pour preparation archive dans comparaison.php","","","0");
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">ERREUR .".$messageGen."</div>" ;
			exit;
		}
	}	

	if (!file_exists($BDfic)) {
		$CRexecution .= "le fichier de base de donn&eacute;es de r&eacute;ference n'existe pas.(".$BDfic.")<br/>";
		$erreurProcess = true;
	} else {
		$nouveauNomFic = $_SERVER["DOCUMENT_ROOT"]."/".$BDrep."/temp/".$BDACCESS.".mdb";
		//echo $BDfic." - ".$nouveauNomFic."<br/>";
		if (copy($BDfic,$nouveauNomFic)) {
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp,"Fichier de travail ".$BDACCESS."_travail renomm&eacute; en ".$BDACCESS.".mdb",$pasdefichier);
			}
		} else {
			$CRexecution .= "Impossible de renommer le fichier ".$BDfic." en ".$nouveauNomFic."<br/>";
			$erreurProcess = true;
		}
	}

	if (!$erreurProcess) {
		//2.ajouter cette base au fichier compresse
		//compression du fichier pour le telechargement
		
		$zipFilename = $_SERVER["DOCUMENT_ROOT"]."/".$BDrep."/".$fileCompression."-".$typePeche.".zip";
		$zipFilelien = "/".$BDrep."/".$fileCompression."-".$typePeche.".zip";
		$AjoutCR = "Le fichier est celui-ci : <b><a href=\"".$zipFilelien."\" target=\"\">".$zipFilelien."</a></b>.";
		if (file_exists($zipFilename)) {
		// pas forcement necessaire, verifier que le x+ vide le fichier
			unlink($zipFilename);
		}
		$theZipFile=new zip_file($zipFilename);	
		//setting the zip options: write to disk, do not recurse directories, do not store path and do not compress
		$theZipFile->set_options(array('inmemory' => 0, 'recurse' => 0, 'storepaths' => 0, 'method'=>0));			
		
		//3.ajouter la base et les autres fichiers
		//3.1 ajouter la base de données
		$theZipFile->add_files($nouveauNomFic);
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "Ajout de ".$nouveauNomFic." dans l'archive ".$zipFilename ,$pasdefichier);
		}
		//3.2 ajouter les autres fichiers
		if (!($filestoAdd == "")) {
			$filetoAdd = explode(",",$filestoAdd);
			$nbfiletoAdd = count($filetoAdd) - 1;
			for ($cpt = 0; $cpt <= $nbfiletoAdd; $cpt++) {
				$FileFullPath = $_SERVER["DOCUMENT_ROOT"]."/".$BDrep."/".$filetoAdd[$cpt];
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp, "Ajout de fichiers sup ".$FileFullPath." dans l'archive ".$zipFilename ,$pasdefichier);
				}
				$theZipFile->add_files($FileFullPath);
			}
		}
		// Creation effective de l'archive
		$theZipFile->create_archive();
		$CRexecution .= $AjoutCR;
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/completed.png\" alt=\"\"/></div><div //id=\"".$nomFenetre."_txt\">Zip ex&eacute;cut&eacute;e avec succ&egrave;s </div><div id=\"
	".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
		echo"<div id=\"vertical_slide4\">".$CRexecution."</div>";	

	} else {
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Traitement en erreur (voir d&eacute;tail ci-dessous)</div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
		echo"<div id=\"vertical_slide4\">".$CRexecution."</div>";
		logWriteTo(8,"error","**- Traitement en erreur : ".$CRexecution."","","","0");
		if ($EcrireLogComp ) {
				WriteCompLog ($logComp, "Erreur dans le traitement = ".$CRexecution,$pasdefichier);
		}
	}
	if ($EcrireLogComp ) {
		WriteCompLog ($logComp,"*------------------------------------------------------",$pasdefichier);
		WriteCompLog ($logComp,"*- FIN TRAITEMENT Zip",$pasdefichier);
		WriteCompLog ($logComp,"*******************************************************",$pasdefichier);
		logWriteTo(8,"notice","*-- Log plus complet disponible dans <a href=\"".$nomLogLien."\" target=\"log\">".$nomFicLogComp."</a>","","","0");
	}
	// Fin de traitement : Fermeture base de données et fichier log/SQL	
	// *********************************************	
	if (! $pasdefichier) {
		if ($EcrireLogComp ) {
			fclose($logComp);
		}
	}
} else {
	echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">&Eacute;tape de zip non ex&eacute;cut&eacute;e par choix de l'utilisateur</div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
	logWriteTo(8,"error","**- Etape de zip non execut&eacute;e par choix de l'utilisateur","","","0");
} // end if (! $pasdetraitement )

exit;

?>
