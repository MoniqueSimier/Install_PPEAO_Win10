<?php 
//*****************************************
// comparaison.php
//*****************************************
// Created by Yann Laurent
// 2008-07-01 : creation
//*****************************************
// Ce programme lance la comparaison des deux bases BD_PPEAO et BD_PECHE
// En fonction du paramètre d'entrée, ce programme renvoie juste un compte rendu (avec un fichier contenant les scripts) soit 
// exécute les scripts de mise à jour en appelant les scripts majDonnesPeches.php et associés.
// Le résultat du traitement est envoyé à portage_auto.php dans deux div qui seront insérés dans le div général (id="comparaison")
// avec une icone de bonne ou mauvaise exécution (dans div id="comparaison_img") et l'explication
// de l'erreur dans div id = "comparaison_txt"
//*****************************************
// Paramètres en entrée
// comp : contient le type d'action 
// comp = 'comp' : on lance une comparaison sans maj (de la base de reference vers la base BDPECHE)
// comp = 'compinv' : on lance une comparaison sans maj (du parametrage de BDPECHE par rapport a la reference)
// comp = 'majsc'  : on lance une comparaison avec maj (donnees scientifiques) ==> majDonneesPeche.php
// comp = 'majrec'  : on lance une comparaison avec maj (donnees recomposees) ==> majDonneesPeche.php
// log : flag contenant la sélection sur le log supplémentaire ;
// numproc : numéro du processus (voir dans le fichier js aja;xProcessAuto.js) pour traiter les timeout ;
// exec : contient la valeur de la case à cocher pour lancer ou non le traitement ;
// adresse : contient l'adresse e-mail à laquelle envoyer le compte-rendu de traitement – Obsolète.

// Paramètres en sortie
// La liste des différences par table est affichée à l'écran et est stockée dans un fichier


// Attention l'activation de l'ecriture dans la table des logs peut amener a des performances catastrophiques (la table peut rapidement etre enorme
// Privilegier plutot l'ecriture dans le fichier log complémentaire


// Mettre les noms des fichiers dans un fichier texte
session_start();
// Variable qui permet d'identifier si le traitement est lancé
$pasdetraitement = true;
// Variable de test (en fonctionnement production, les deux variables sont false)
$pasdefichier = false; // Variable de test pour linux. 
$pasderevSQL = false; // Ne pas generer le fichier reverseSQL

$cptAjoutMaj = 0; // pour compatibilite

$debugAff = false; // variable globale pour lancer le programme en mode debug
// Variables de traitement
$ErreurProcess = false; // Flag si erreur process
$affichageDetail = false; // Pour afficher ou non le detail des traitements à l'écran
// Includes standard
include $_SERVER["DOCUMENT_ROOT"].'/variables.inc';
include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/config.php';
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/functions.php';


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

// On récupère le type d'action. Le même programme gère la comparaison et la mise à jour de données
if (isset($_GET['action'])) {
	$typeAction = $_GET['action'];
	switch($typeAction){
		case "comp":
			// Comparaison du referentiel / parametrage 
			$BDSource = "connectPPEAO";
			$BDCible = "connectBDPECHE";
			$nomFenetre = "comparaison";
			$nomAction = "comparaison du referentiel / param. peche scientifique";
			$nomFicSQL = "ref_param_ppeao";
			$numFen = 2;
			break;
		case "compinv":
			// Comparaison du parametrage de BDPECHE
			$BDSource = "connectBDPECHE";
			$BDCible = "connectPPEAO";
			$nomFenetre = "comparaisonInv";
			$nomAction = "comparaison du param. peche artisanale";
			$nomFicSQL = "param_bdpeche";
			$numFen = 3;
			break;
		case "majsc":
			// Données scientifiques à mettre à jour
			$BDSource = "connectBDPECHE";
			$BDCible = "connectPPEAO";
			$nomFenetre = "copieScientifique";
			$nomAction = "mise a jour donnees scientifiques";
			$nomFicSQL = "majdatascient";
			$numFen = 4;
			break;
		case "majrec":
			// Données recomposées à mettre à jour
			$BDSource = "connectBDPECHE";
			$BDCible = "connectPPEAO";
			$nomFenetre = "copieRecomp";
			$nomAction = "mise a jour donnees recomposees";
			$nomFicSQL = "majdatarecomp";
			$numFen = 7;
			break;
	}

} else { 
	$nomFenetre = "comparaison";
	echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Il manque le parametre action. Contactez votre admin PPEAO</div>" ;
	exit;
}
$ExecSQL = "n";
// On identifie si le traitement est train de traiter les fichiers SQL
if (isset($_GET['traitsql'])) {
	$ExecSQL = $_GET['traitsql'];
}

$nomBDSource = ""; 
$nomBDCible = ""; 
$allScriptSQL = "";
// Pour la gestion des timeout liés à l'utilisation d'AJAX.
// Parfois le temps de traitement d'une table est trop long.
// On doit interrompre le traitement, envoyer un message au javascript pour lui
// dire de relancer le process avec le nom de la table en cours et le numero
// de l'enregistrement en cours de lecture.
// comparaison.php est alors rappelé avec des paramètres.

// On récupère ici les paramètres de timeout.

$tableEnCours = "";
$IDEnCours = 0;

if (isset($_GET['table'])) {
	$tableEnCours = $_GET['table'];

}  
if (isset($_GET['numenreg'])) {
	// Est-ce que l'ID est un num ?
	$ListeTableIDPasNum = GetParam("listeTableIDPasNum",$PathFicConf);
	$testTtypeID = strpos($ListeTableIDPasNum ,$tableEnCours);
	if ($testTtypeID === false) {
		// L'ID est bien un numérique
		$IDEnCours = intval($_GET['numenreg']);
	} else {
		// L'ID est une chaine
		$IDEnCours = "'".$_GET['numenreg']."'";
	}
}
if (isset($_GET['numproc'])) {
	$numProcess = $_GET['numproc'];
}
if (isset($_GET['log'])) {

	if ($_GET['log'] == "false") {
		$EcrireLogComp = false;// Ecrire dans le fichier de log complémentaire. Attention, cela prend de la ressource !
	} else {
		$EcrireLogComp = true;
	}
}
 
// Deux variables pour stocker les tables / ID en cours de lecture pour être capable de les renvoyer si pb de timeout detecte
$tableEnLecture = "";
$IDEnLecture = 0 ;
$ArretTimeOut = false;
$dumpTable = false;

// Pour test...
// temps maximal d'exécution du script autorisé par le serveur
$max_time = ini_get('max_execution_time');
// 30 secondes par défaut:
if ($max_time == '') $max_time = 30;
// on prend 10% du temps maximal comme marge de sécurité
$ourtime = ceil(0.9*$max_time);
// fin test

// ***** Test si arret processus lié à l'exécution du traitement précédent 	
// Si le traitement précédent a échoué, arrêt du traitement

if (isset($_SESSION['s_status_process_auto'])) {
	if ($_SESSION['s_status_process_auto'] == 'ko') {
		logWriteTo(7,"error","**- ARRET du traitement ".$nomAction." car le processus precedent est en erreur.","","","0");
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\"> ARRET du traitement car le processus precedent est en erreur</div>" ;
		exit;
	}
}


// ***** Variables de traitements
$CRexecution = "<br/>"; 			// Variable contenant le résultat du traitement
$cptChampTotal = 0;			// Lecture d'une table, nombre d'enregistrements lus total
$cptChampDiff = 0; 			// Lecture d'une table, nombre d'enregistrements différents
$cptChampVide = 0;			// Lecture d'une table, nombre d'enregistrements vide
$cptTableTotal = 0;			// Nombre global de tables lues
$cptTableDiff = 0;			// Nombre global de tables différentes entre reference et cible
$cptTableEq = 0;			// Nombre global de tables identiques entre reference et cible
$cptTableVide = 0;			// Nombre global de tables vides dans cible 
$cptTableSourceVide = 0;	// Nombre global de tables vides dans source 
$cptTableLignesVidesDiff =0;// Nombre global de tables avec des enreg manquants ou diffenrets dans cible
$cptTableLignesVides = 0; 	// Nombre global de tables avec des enreg manquants dans cible
$cptSQLErreur = 0 ;			// Nombre d'erreur lors de la mise a jour de la table
$scriptSQL = "";			// Stockage du script SQL à exécuter pour créer ou maj les données
$logComp="";
$TotalLignesFichier = 0; 	// compteur pour gerer la taille des fichiers SQL
$SeuilLignesFichier = 5000; // constante contenant le nombre max de lignes par fichier


// *** Pour info, les variable de session utilisées pour stocker les valeurs
//$_SESSION['s_cpt_champ_total'] // Lecture d'une table, nombre d'enregistrements lus total
//$_SESSION['s_cpt_champ_diff']// Lecture d'une table, nombre d'enregistrements différents
//$_SESSION['s_cpt_champ_vide']// Lecture d'une table, nombre d'enregistrements vide
//$_SESSION['s_cpt_table_total']// Nombre global de tables lues
//$_SESSION['s_cpt_table_diff']// Nombre global de tables différentes entre reference et cible
//$_SESSION['s_cpt_table_egal']// Nombre global de tables identiques entre reference et cible
//$_SESSION['s_cpt_table_vide']// Nombre global de tables vides dans cible 
//$_SESSION['s_cpt_table_manquant']// Nombre global de tables avec des enreg manquants dans cible
//$_SESSION['s_cpt_table_diff_manquant'] // Nombre global de tables avec des enreg differents et manquants dans cible
//$_SESSION['s_cpt_lignes_fic_sql']// Nombre global de lignes mises dans le fichier SQL
//$_SESSION['s_cpt_erreurs_sql']// Nombre d'erreur lors de la mise a jour de la table

// On récupère les valeurs des paramètres pour les fichiers log
$dirLog = GetParam("repLogAuto",$PathFicConf);
$nomLogLien = "/".$dirLog; // pour créer le lien au fichier dans le cr ecran
$dirLog = $_SERVER["DOCUMENT_ROOT"]."/".$dirLog;
$fileLogComp = GetParam("nomFicLogSupp",$PathFicConf);



// Initialisation si on demarre un nouveau traitement
if ($tableEnCours == "") {
	$_SESSION['s_CR_processAuto'] = "";
	$_SESSION['s_cpt_champ_total'] = 0;
	$_SESSION['s_cpt_champ_diff'] = 0;
	$_SESSION['s_cpt_champ_vide'] = 0;	
	$_SESSION['s_cpt_table_diff'] = 0;
	$_SESSION['s_cpt_table_diff_manquant'] = 0;
	$_SESSION['s_cpt_table_egal'] = 0;
	$_SESSION['s_cpt_table_vide'] = 0;
	$_SESSION['s_cpt_table_manquant'] = 0; 
	$_SESSION['s_cpt_erreurs_sql'] = 0; 
}

// ***** Debut du traitement

if (! $pasdetraitement ) { // Permet de sauter cette étape (choix de l'utilisateur ou debug)

// Traitements préliminaires : 
// *********************************************
//	Contrôle des répertoires et fichiers log
// 		Controle répertoire
	if (! $pasdefichier) { // Pour test sur serveur linux
		if (! file_exists($dirLog)) {
			if (! mkdir($dirLog) ) {
				$messageGen = " erreur de creation du repertoire de log";
				logWriteTo(7,"error","Erreur de creation du repertoire de log dans comparaison.php","","","0");
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
				$messageGen = " erreur de creation du fichier de log";
				logWriteTo(7,"error","Erreur de creation du fichier de log ".$dirLog."/".date('y\-m\-d')."-".$fileLogComp." dans comparaison.php","","","0");
				echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">ERREUR .".$messageGen."</div>" ;
				exit;		
			}
		}
	//	Si en comparaison, on peut générer le SQL
		$numfic = str_pad($_SESSION['s_num_encours_fichier_SQL'], 3, "0", STR_PAD_LEFT);
		$SQLComp = fopen($dirLog."/".date('y\-m\-d')."-".$nomFicSQL."-".$numfic.".sql", "a+");
		if (! $SQLComp ) {
			$messageGen = " erreur de creation du fichier SQL contenant les scripts";
			logWriteTo(7,"error","Erreur de creation du fichier de SQL dans comparaison.php","","","0");
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">ERREUR .".$messageGen."</div>" ;
			exit;		
		}	
		// Gestion des SQL pour la restauration des fichiers
		$ficRevSQL = OpenFileReverseSQL ("ajout",$dirLog,$pasdefichier);
	}	
	// Récupération des tables à comparer
	// *********************************************
	// listes ci-dessous pour les tests...
	switch($typeAction){
		case "comp":
			// Comparaison
			$listTable = GetParam("listeTableComp",$PathFicConf);
			//$listTable="ref_espece"; //TEST
			 break;
		case "compinv":
			// Comparaison
			$listTable = GetParam("listeTableCompInv",$PathFicConf);
			$AjoutTable = GetParam("listeTableComp",$PathFicConf);
			$listTable = $listTable.",".$AjoutTable;
			//$listTable="ref_espece"; //TEST
			 break;
		case "majsc":
			// Données scientifiques à mettre à jour
			$listTable = GetParam("listeTableMajsc",$PathFicConf);
			//$listTable="exp_environnement,exp_campagne,exp_coup_peche,exp_fraction"; //TEST
			//$listTable="exp_campagne"; //TEST
			 break;
		case "majrec":
			// Données recomposées à mettre à jour
			$listTable = GetParam("listeTableMajrec",$PathFicConf);
			//$listTable="art_unite_peche,art_lieu_de_peche,art_debarquement,art_debarquement_rec,art_fraction_rec"; //TEST
			 break;
	}
	$NbrTableAlire = substr_count($listTable,",");
	if ($NbrTableAlire == 0) {
		$NbrTableAlire = 1;
	} else {
		$NbrTableAlire += 1;
	}
	// Connexion aux deux bases de données pour comparaison.
	// *********************************************
	// Pas besoin de se connecter à la base PPEAO, c'est deja fait dans l'include
	
	$connectBDPECHE =pg_connect ("host=".$host." dbname=".$bd_peche." user=".$user." password=".$passwd);
	if (!$connectBDPECHE) { 
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Erreur de connexion a la base de donnees ".$bd_peche."</div>" ; exit;
		}
		
	// Test de la connexion à la BD 
	if (!$connectPPEAO) { 
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Erreur de connexion a la base de donnees BD_PPEAO pour maj des logs</div>" ; exit;
	}
	
	// Gestion des noms des BD
	$nomBDSource = pg_dbname(${$BDSource});
	$nomBDCible = pg_dbname(${$BDCible});
	
	// Initialisation des logs
	if ($tableEnCours == "") {
		logWriteTo(7,"notice","**- Debut lancement ".$nomAction." (portage)","","","0");
		logWriteTo(7,"notice","**- source : ".$nomBDSource." cible : ".$nomBDCible,"","","0");
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "*******************************************************",$pasdefichier);
			WriteCompLog ($logComp, "*- DEBUT lancement ".$nomAction." (portage)",$pasdefichier);
			WriteCompLog ($logComp, "*- source : ".$nomBDSource." cible : ".$nomBDCible,$pasdefichier);
			WriteCompLog ($logComp, "*******************************************************",$pasdefichier);
		}
	} else {
		logWriteTo(7,"notice","**- Relance traitement pour la table ".$tableEnCours." a partir de l'enreg ID = ".$IDEnCours." (gestion TIMEOUT AJAX)","","","0");
		if ($EcrireLogComp ) {
			WriteCompLog ($logComp, "Relance traitement pour la table ".$tableEnCours." a partir de l'enreg ID = ".$IDEnCours." (gestion TIMEOUT AJAX)",$pasdefichier);
		}
	}
	// Paramètres  de comparaison.
	// *********************************************
	// Lancement de la comparaison. On met à jour la variable contenuDiv avec le résultat de la comparaison.
	// On met à jour le fichier de log spécifique avec plus de détails.

	$tables = explode(",",$listTable);
	$nbTables = count($tables) - 1;
	logWriteTo(7,"notice"," Nb tables = ".$nbTables ,"","","1");
	// Début du traitement de comparaison par table.
	// *********************************************
	$ListeTableIDPasNum = GetParam("listeTableIDPasNum",$PathFicConf);

	// *************************************************
	// Traitement des mise a jour de peche exp et peche art
	// *************************************************
	if (($typeAction == "majsc" || $typeAction =="majrec") ) {
		// Pour un gestion fine du timer
		$finmajDP = false;
		if ($ExecSQL=="n") {
			// Creation des scripts de mises à jour des données
			include $_SERVER["DOCUMENT_ROOT"].'/process_auto/majDonneesPeche.php';
		} 
		// On refait le test sur $execSQL (pas de else), ce qui permet a la fin de majDonnees.php de modifier execSQL a la fin du traitement
		if ($ExecSQL=="y") {
			// execution des scripts SQL
			include $_SERVER["DOCUMENT_ROOT"].'/process_auto/SQLDonneesPeche.php';
		}

	}
	// *************************************************
	// Traitement de comparaison
	// *************************************************
	if (!$ArretTimeOut && !($typeAction == "majsc" || $typeAction =="majrec")) {
	
	$start_while=timer(); // début du chronométrage du for
	for ($cpt = 0; $cpt <= $nbTables; $cpt++) {
		// controle de la table en cours si besoin (gestion TIMEOUT)
		if ((!$tableEnCours == "" && $tableEnCours == $tables[$cpt]) || $tableEnCours == "") {
		
		// Reinitialisation des compteurs
		$cptChampTotal = 0;
		$cptChampDiff = 0;
		$cptChampVide = 0;
		$cptSQLErreur = 0 ;
		$tableVide = false;
		$tableSourceVide = false;
		$dumpTable = false;
		if ($tableEnCours == "") {
			$cptTableTotal++;
			$ErreurProcess = false;
			$_SESSION['s_cpt_champ_total'] 	= 0;
			$_SESSION['s_cpt_champ_diff']	= 0;
			$_SESSION['s_cpt_champ_vide']	= 0;
			$_SESSION['s_en_erreur'] 		= false;
			$_SESSION['s_cpt_erreurs_sql'] 	= 0;
			$_SESSION['s_AllScriptSQL'] 	= "";	
		} else {
			// on reinitialise les valeurs avec les variables de session mise à jour lors du traitement précédent
			$CRexecution 	= $_SESSION['s_CR_processAuto'];
			$cptChampTotal 	= $_SESSION['s_cpt_champ_total'];
			$cptChampDiff	= $_SESSION['s_cpt_champ_diff'];
			$cptChampVide	= $_SESSION['s_cpt_champ_vide'];	
			$cptTableDiff	= $_SESSION['s_cpt_table_diff'];
			$cptTableLignesVidesDiff = $_SESSION['s_cpt_table_diff_manquant'];
			$cptTableEq		= $_SESSION['s_cpt_table_egal'];
			$cptTableVide	= $_SESSION['s_cpt_table_vide'];
			$cptTableLignesVides = $_SESSION['s_cpt_table_manquant']; 
			//$cptSQLErreur	= $_SESSION['s_cpt_erreurs_sql'] ; 
			$ErreurProcess 	= $_SESSION['s_erreur_process'];
			$allScriptSQL	= $_SESSION['s_AllScriptSQL'];
			// On reinitialise pour eviter de compter deux fois les memes donnees
			$_SESSION['s_CR_processAuto'] 	= "";
			$_SESSION['s_cpt_champ_total'] 	= 0;
			$_SESSION['s_cpt_champ_diff'] 	= 0;
			$_SESSION['s_cpt_champ_vide'] 	= 0;	
			$_SESSION['s_cpt_table_diff'] 	= 0;
			$_SESSION['s_cpt_table_diff_manquant'] = 0;
			$_SESSION['s_cpt_table_egal'] 	= 0;
			$_SESSION['s_cpt_table_vide'] 	= 0;
			$_SESSION['s_cpt_table_manquant'] = 0; 
			$_SESSION['s_cpt_erreurs_sql'] 	= 0; 
		
		}
		// Reinitialisation variable pour creation SQL
		$where="";
		$alias="";
    	logWriteTo(7,"notice","*-- Comparaison de la table ".$tables[$cpt],"","","0");
		
		// Gestion TIMEOUT
		$tableEnLecture = $tables[$cpt];

		// Construction des requetes SQL
		$continueControle = true ;
		
		if ($typeAction == "comp") {
		// Test si la table dans la BD cible (BD_PECHE dans le cas de la comparaison, BD_PPEAO dans le cas
		// de la mise à jour) n'est pas vide. 
		// Si c'est le cas, pas la peine de continuer la comparaison (ce n'est valable que dans le cas de la comparaison !)
		// On va lancer un dump complet de la table
			$testCibleReadSql = " select * from ".$tables[$cpt] ;
			$testCibleReadResult = pg_query(${$BDCible},$testCibleReadSql) or die('erreur dans la requete : '.pg_last_error());
			if (pg_num_rows($testCibleReadResult) == 0) {
				logWriteTo(7,"notice","table ".$tables[$cpt]." dans ".$nomBDCible." vide","","","0");
				$dumpTable = true;
			}
			// ==> faire un dump de la table source
			pg_free_result($testCibleReadResult);				
		}
		
		
		// Ce test est obsolete, on le laisse pour des tests
		if ($continueControle) {
			// On peut continuer la comparaison, on sait qu'on a des enregs dans la base cible.
			// Pour la mise à jour on passera toujours ici..
			// ************************************************

			// Gestion TIMEOUT : on reprend la ou on s'etait arrete
			// Comme on trie par ID, on ne va pas en perdre en route
			if ($tableEnCours == "") {
				$condWhere = "";
			} else {
				$condWhere = " where id > ".$IDEnCours;
			}
			// Lecture de la table $tables[$cpt] dans la base source (BD_PPEAO dans le cas de la comparaison, 
			// BD_PECHE dans le cas de la mise à jour)
			
			// Compteur 
			$compReadSqlC = " select count(id) from ".$tables[$cpt];
			$compReadResultC = pg_query(${$BDSource},$compReadSqlC) or die('erreur dans la requete : '.pg_last_error());
			$compRowC = pg_fetch_row($compReadResultC);
			$totalLignes = $compRowC[0];
			pg_free_result($compReadResultC);
			// Lecture de la table dans la base source
			$compReadSql = " select * from ".$tables[$cpt].$condWhere. " order by id ASC";
			$compReadResult = pg_query(${$BDSource},$compReadSql) or die('erreur dans la requete : '.pg_last_error());
			if (pg_num_rows($compReadResult) == 0) {
			// La table dans BD_PPEAO est vide
				//logWriteTo(7,"notice","Table de reference ".$tables[$cpt]." dans ".$nomBDSource." vide","","","0");
				if ($EcrireLogComp ) { WriteCompLog ($logComp,"Table de reference ".$tables[$cpt]." dans ".$nomBDSource." vide",$pasdefichier);}
				$tableSourceVide = true;

			} else {
				// La table dans la base source (de référence) n'est pas vide
				//logWriteTo(7,"notice",$cpt." ".$tables[$cpt]." nombre lignes = ".pg_num_rows($compReadResult)." dans ".$nomBDSource," ","","1");
				
				// On va balayer tous les enreg (ligne) de la table controlée
				while ($compRow = pg_fetch_row($compReadResult) ) {
					// Controle sur le nombre de ligne deja mise dans le fichier,
					// Creation d'un nouveau fichier si nécessaire
					if ($_SESSION['s_cpt_lignes_fic_sql'] > $SeuilLignesFichier ) {
						// Pour eviter le time out, on prend 3 secondes de marge
						if ( ceil(0.9*$max_time) - $ourtime < 2) {
							if ($EcrireLogComp ) { WriteCompLog ($logComp,"TIMEOUT: break pour cause de creation fichier",$pasdefichier);}
							$delai=number_format(timer() - $start_while,7);
							$ArretTimeOut =true;
							break;
						}
						if ($EcrireLogComp ) { WriteCompLog ($logComp," Changement de fichier. Creation d'un nouveau",$pasdefichier);}
						fclose($SQLComp);
						$_SESSION['s_num_encours_fichier_SQL'] ++;
						$_SESSION['s_cpt_lignes_fic_sql'] = 0;
						$numfic = str_pad($_SESSION['s_num_encours_fichier_SQL'], 3, "0", STR_PAD_LEFT);
						$SQLComp = fopen($dirLog."/".date('y\-m\-d').$typeAction."-".$numfic.".sql", "a+");
						if (! $SQLComp ) {
							$messageGen = " erreur de creation du fichier SQL contenant les scripts";
							logWriteTo(7,"error","Erreur de creation du fichier de SQL dans comparaison.php","","","0");
							echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">ERREUR .".$messageGen."</div>" ;
							exit;		
						}	
					}
					// Gestion du timeout
					$ourtime = (int)number_format(timer()-$start_while,7);
					$seuiltemps= ceil(0.9*$max_time);
					// On prend un peu de marge par rapport au temps max.
					if ($ourtime >= ceil(0.9*$max_time)) {
						if ($EcrireLogComp ) { WriteCompLog ($logComp,"TIMEOUT: break",$pasdefichier);}
						$delai=number_format(timer() - $start_while,7);
						$ArretTimeOut =true;
						break;
					}
					// Attention, l'ID n'est pas toujours en position 1 (donc 0 dans le tableau des donnees en sortie du pg_fetch_row
					$ListeTableIdpasRang0 = "art_type_activite";
					$ListeTablepasRang0ID = "3";
					$testTtypeID = strpos($ListeTableIdpasRang0 ,$tables[$cpt]);
					if ($testTtypeID === false) {
						$RangId = 0; 
					} else {
						$RangId = 2; /// pour l'instant qu'une table, on code un peu a la hussarde...
					}					
					if (! $dumpTable) {
						$cptChampTotal++;
						
						//echo "Traitement de l'enregistrement ".$cptChampTotal." sur ".$totalLignes;
						$IDEnLecture = $compRow[$RangId] ;
						
						$testTtypeID = strpos($ListeTableIDPasNum ,$tables[$cpt]);
						if ($testTtypeID === false) {
							// L'ID est bien un numérique
							$where = " where id = ".intval($compRow[$RangId]) ; 
						} else {
							// L'ID est une chaine
							$where = " where id = '".$compRow[$RangId]."'" ;
						}

						// comparaison avec l'enreg dans l'autre DB
						//logWriteTo(7,"notice",$cpt." lecture table ".$nomBDCible." ".$tables[$cpt]," select * from ".$tables[$cpt].$where,"","1");
						$compCibleReadSql = " select * from ".$tables[$cpt].$where ; //
						$compCibleReadResult = pg_query(${$BDCible},$compCibleReadSql) or die('erreur dans la requete : '.pg_last_error());
						$compCibleRow = pg_fetch_row($compCibleReadResult); // une seule ligne en retour, pas besoin de faire une boucle
						
						if (pg_num_rows($compCibleReadResult) == 0) {
							// L'enregistrement n'existe pas dans la base cible
							$cptChampVide++ ;
							//logWriteTo(7,"notice","id = ".$compRow[$RangId]." enreg manquant dans base cible","","","1");
							$scriptSQL = GetSQL('insert',  $tables[$cpt], $where, $compRow,${$BDSource},$nomBDSource,$typeAction,$PathFicConf,0,"n","","",$start_while,$EcrireLogComp,$logComp,$pasdefichier);
							
							if ($EcrireLogComp ) { WriteCompLog ($logComp," MANQUANT ".$tables[$cpt]." l'enreg id = ".$compRow[$RangId]." n'existe pas dans ".$nomBDCible.".",$pasdefichier);}
							// On génère un fichier de mise à jour utilisable
							WriteCompSQL ($SQLComp,$scriptSQL.";",$pasdefichier);
							$_SESSION['s_cpt_lignes_fic_sql'] ++;
							$ErreurProcess = true;
						
						} else {
						// On balaye tous les champs à comparer en ignorant les clés primaires id.
							$enregDiff = false;
							// On commence a 1, on evite le champs ID
							// Attention, a corriger pas vrai pour art_type_activite
							//echo "nb champs =".pg_num_fields($compCibleReadResult);
							for ($cpt1 = 1; $cpt1 < pg_num_fields($compCibleReadResult); $cpt1++) {
								// Comparaison
								if ($compCibleRow[$cpt1] == $compRow[$cpt1]) {
									// En fait, on s'en fout maintenant...
								
								}
								 else {
									// différent
									$cptChampDiff++ ;
									$enregDiff = true;
									$scriptSQL = GetSQL('update',  $tables[$cpt], $where, $compRow,${$BDSource},$nomBDSource,$typeAction,$PathFicConf,0,"n","","",$start_while,$EcrireLogComp,$logComp,$pasdefichier);
									
									if ($EcrireLogComp ) {WriteCompLog ($logComp," DIFF ".$tables[$cpt]." l'enreg id = ".$compRow[$RangId]." est different (ref= ".$compRow[$cpt1]." dans ".$nomBDCible." = ".$compCibleRow[$cpt1].")",$pasdefichier);}
									break;
								}
							} // end for ($cpt1 = 0; $cpt1 <= $nbChamp; $cpt1++)
							
							if 	($enregDiff) {
								// On génère un fichier de mise à jour utilisable
									WriteCompSQL ($SQLComp,$scriptSQL.";",$pasdefichier);
									$_SESSION['s_cpt_lignes_fic_sql'] ++;
									$ErreurProcess = true;
							} else {
								// identique

							}
						} // end if (pg_num_rows($compPecheReadResult) == 0)
					pg_free_result($compCibleReadResult);
					// *** fin du traitement de comparaison des tables
					} else { // fin du if (! $dumpTable)
						// On fait un dump bourrin de la table
						$tableVide = true;
						if ($EcrireLogComp ) { WriteCompLog ($logComp," TOUT MANQUANT ".$tables[$cpt]." l'enreg id = ".$compRow[$RangId]." n'existe pas dans ".$nomBDCible.".",$pasdefichier);}
						
						$scriptSQL = GetSQL('insert',  $tables[$cpt], $where, $compRow,${$BDSource},$nomBDSource,$typeAction,$PathFicConf,0,"n","","",$start_while,$EcrireLogComp,$logComp,$pasdefichier);

						// On génère un fichier de mise à jour utilisable
							WriteCompSQL ($SQLComp,$scriptSQL.";",$pasdefichier);
							$_SESSION['s_cpt_lignes_fic_sql'] ++;
							$ErreurProcess = true;

					}
				} // end while ($compRow = pg_fetch_row($compReadResult))
				// Controle si sortie par timeout ou 
				if ($ArretTimeOut) {
					// on sort de la la boucle for
					break;
				}
				// TIMEOUT, reinitialisation des variables EnCours
				$IDEnCours = 0;
				$tableEnCours = "";
			} // end if(pg_num_rows($compReadResult) == 0) table de ref vide ?
			// Libère le requete sur BD_PECHE
			pg_free_result($compReadResult);
		} // end if ($continueControle) 


		if (!$ArretTimeOut) {

			// On aura deux comptes-rendus selon si c'est une comparaison ou une mise à jour
			// Dans le cas de la comparaison, on indique les différents cas trouvés.
			// Dans le cas de la maj, on n'indique juste le type de maj
			if ($cptChampVide > 0 || $cptChampDiff > 0 || $tableSourceVide || $tableVide || $affichageDetail){
				$CRexecution = $CRexecution." <br/>*- <b>".$tables[$cpt]."</b> : ";			
			}
			if ($EcrireLogComp ) {
				WriteCompLog ($logComp,"TABLE ".$tables[$cpt]." : ".$nomAction,$pasdefichier);
				//WriteCompLog ($logComp,"TEST champvide = ".$cptChampVide." champDiff ".$cptChampDiff." tableVide ".$tableVide,$pasdefichier);
			}
			if ($tableSourceVide) {
				$cptTableSourceVide++;
				$CRexecution = $CRexecution." <img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;".$tables[$cpt]." source vide -";
				if ($EcrireLogComp ) {
					WriteCompLog ($logComp," Cette table source est vide.",$pasdefichier);
				}
			} else {

				// Cas d'une table ou il manque des données
				if ($cptChampVide > 0) {
					if ($cptChampDiff == 0) {
						$cptTableLignesVides++; 
					} else {
						$cptTableLignesVidesDiff++;
					}
					if ($typeAction == "comp" || $typeAction == "compinv") {
						$CRexecution = $CRexecution." <img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;".$cptChampVide." donnees manquantes - ";
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp,"   - donnees manquantes = ".$cptChampVide.". ",$pasdefichier);
						}
					} else {
						$CRexecution = $CRexecution." ".$cptChampVide." donnees traitees |";
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp,"   - donnees traitees = ".$cptChampVide.". ",$pasdefichier);
						}
					}
				}	
				// Cas d'enregistrements différents	
				if ($cptChampDiff > 0) {
					if ($cptChampVide == 0) {
						$cptTableDiff++;
					}
					if ($typeAction == "comp" || $typeAction == "compinv") {
						$CRexecution = $CRexecution." <img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>&nbsp;".$cptChampDiff." donnees differentes - ";
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp,"   - donnees differentes = ".$cptChampDiff." ",$pasdefichier);
						}
					} else {
						$CRexecution = $CRexecution." ".$cptChampDiff." donnees modifiees -";
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp,"   - donnees modifiees = ".$cptChampDiff." ",$pasdefichier);
						}					
					
					}
				} else {
				//	Cas de la table vide
					if ($tableVide) {
						$cptTableVide++;
						$CRexecution = $CRexecution." <img src=\"/assets/warning.gif\" alt=\"Avertissement\"/>".$tables[$cpt]." vide ==> dump total de la table depuis la base source (voir fichier sql).-";
						if ($EcrireLogComp ) {
							WriteCompLog ($logComp," Cette table est vide ==> dump total de la table depuis la base source.",$pasdefichier);
						}
					} else {
						if ($cptChampVide == 0) {
							$cptTableEq ++;
							if ($affichageDetail) {
								$CRexecution = $CRexecution." identique -";
							}
							if ($EcrireLogComp ) {			
								WriteCompLog ($logComp,"   -->  identique",$pasdefichier);
							}
						}
					}
				} // End for statement if ($cptChampDiff > 0)
				
				if ($ErreurProcess) {
					// On garde en memoire l'erreur pour cette table pour le refleter sur le traitement global
					if (!$_SESSION['s_erreur_process']){
						$_SESSION['s_erreur_process'] = $ErreurProcess;
					}
					if ($typeAction == "comp" || $typeAction == "compinv"){
					
					} else {
						$CRexecution = $CRexecution." <img src=\"/assets/warning.gif\" alt=\"Avertissement\"/> ".$cptSQLErreur." erreurs de traitement - ";
						if ($EcrireLogComp ) {			
								WriteCompLog ($logComp,"   - ATTENTION ".$cptSQLErreur." erreurs de traitement.",$pasdefichier);
						}
					}
				}
			}
			if ($cptChampVide > 0 || $cptChampDiff > 0 || $tableSourceVide || $tableVide || $affichageDetail){
				//$CRexecution = $CRexecution." <br/>" ;			
			} 

		} // End for statement if ((!$ArretTimeOut)
		
		} // End for statement if ((!$tableEnCours == "" && tableEnCours == $tables[$cpt]) || $tableEnCours == "")
	} // End for statement for ($cpt = 0; $cpt <= $nbTables; $cpt++)
	} // End if (!$ArretTimeOut)

	// Fin de traitement : affichage des résultats.
	// *********************************************
	// On faire le decompte total
	// Les valeurs sur les champs sont stockees dans le cas ou le process est relancé pour cause de time out.
	$_SESSION['s_CR_processAuto'] 	= $_SESSION['s_CR_processAuto'].$CRexecution;
	$_SESSION['s_cpt_champ_total'] 	+= 	$cptChampTotal;// Lecture d'une table, nombre d'enregistrements lus total
	$_SESSION['s_cpt_champ_diff']	+=	$cptChampDiff;// Lecture d'une table, nombre d'enregistrements différents
	$_SESSION['s_cpt_champ_vide']	+=	$cptChampVide;// Lecture d'une table, nombre d'enregistrements vide
	$_SESSION['s_cpt_table_total']	+=	$cptTableTotal; 	// Nombre global de tables lues
	$_SESSION['s_cpt_table_diff']	+=	$cptTableDiff;// Nombre global de tables différentes entre reference et cible
	$_SESSION['s_cpt_table_diff_manquant']+=$cptTableLignesVidesDiff; // Nombre global de tables avec des enreg differents et manquants dans cible
	$_SESSION['s_cpt_table_egal']	+=	$cptTableEq;// Nombre global de tables identiques entre reference et cible
	$_SESSION['s_cpt_table_vide']	+=	$cptTableVide;// Nombre global de tables vides dans cible
	$_SESSION['s_cpt_table_source_vide']+=	$cptTableSourceVide;// Nombre global de tables vides dans cible
	$_SESSION['s_cpt_table_manquant']	+=	$cptTableLignesVides;// Nombre global de tables avec des enreg manquants dans cible 
	$_SESSION['s_cpt_erreurs_sql']	+= $cptSQLErreur; //
	if (!$_SESSION['s_erreur_process']){
		$_SESSION['s_erreur_process'] = $ErreurProcess;
	}
	$_SESSION['s_AllScriptSQL'] = $allScriptSQL;
	$_SESSION['s_cpt_maj'] = $cptAjoutMaj;
	// Include qui gère à la fois les compte-rendus à l'écran et la mise à jour des logs avec les ditCR.
	include $_SERVER["DOCUMENT_ROOT"].'/process_auto/gestionCR.php';


	// Fin de traitement : Fermeture base de données et fichier log/SQL	
	// *********************************************	
	if (! $pasdefichier) {
		if ($EcrireLogComp ) {
			fclose($logComp);
		}
		fclose($SQLComp);
		
	}
	CloseFileReverseSQL($ficRevSQL,$pasdefichier);
		
	
} else {
	echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Etape de ".$nomAction." non executee par choix de l'utilisateur</div><div id=\"".$nomFenetre."_chk\">Exec= ".$Labelpasdetraitement."</div>" ;
	logWriteTo(7,"error","**- En Test Etape de ".$nomAction." non executee par choix de l'utilisateur","","","0");
} // end if (! $pasdetraitement )



exit;



?>
