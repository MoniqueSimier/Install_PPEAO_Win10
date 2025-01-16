<?php 
//*****************************************
// finExport.php
//*****************************************
// Created by Yann Laurent
// 2008-12-08 : creation
//*****************************************
// Ce programme termine l'export.
//*****************************************
// Paramètres en entrée
// aucun

// Paramètres en sortie
// aucun

// Mettre les noms des fichiers dans un fichier texte
session_start();
$nomFenetre = "exportOK";
$pasdefichier = false;
include $_SERVER["DOCUMENT_ROOT"].'/process_auto/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/export/config.php';

if (isset($_GET['log'])) {

	if ($_GET['log'] == "false") {
		$EcrireLogComp = false;// Ecrire dans le fichier de log complémentaire. Attention, cela prend de la ressource !
	} else {
		$EcrireLogComp = true;
	}
}

// On récupère les valeurs des paramètres pour les fichiers log
$dirLog = GetParam("repLogAccess",$PathFicConfAccess);
$nomLogLien = "/".$dirLog; // pour créer le lien au fichier dans le cr ecran
$dirLog = $_SERVER["DOCUMENT_ROOT"]."/".$dirLog;
$fileLogComp = GetParam("nomFicLogSuppAc",$PathFicConfAccess);
if (! $pasdefichier) { // Pour test sur serveur linux
	if (! file_exists($dirLog)) {
		if (! mkdir($dirLog) ) {
			$messageGen = " erreur de cr&eacute;ation du r&eacute;pertoire de log";
			logWriteTo(8,"error","Erreur de creation du repertoire de log dans comparaison.php","","","0");
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
			$messageGen = " erreur de cr&eacute;ation du fichier de log";
			logWriteTo(8,"error","Erreur de creation du fichier de log ".$dirLog."/".date('y\-m\-d')."-".$fileLogComp." dans comparaison.php","","","0");
			echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">ERREUR .".$messageGen."</div>" ;
			exit;		
		}
	}
}	
	
	

// Si le traitement précédent a échoué, arrêt du traitement

if (isset($_SESSION['s_status_export'])) {
	if ($_SESSION['s_status_export'] == 'ko') {
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Fin du processus automatique en erreur</div>" ;
	} else {
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/completed.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Fin avec succes du processus automatique.</div>" ;
	}
} else {
		echo "<div id=\"".$nomFenetre."_img\"><img src=\"/assets/incomplete.png\" alt=\"\"/></div><div id=\"".$nomFenetre."_txt\">Pas d'information sur l'execution du process.</div>" ;
}

// Fin generale du traitement
if ($EcrireLogComp ) {
	WriteCompLog ($logComp, "#",$pasdefichier);
	WriteCompLog ($logComp, "#",$pasdefichier);
	WriteCompLog ($logComp, "*-#####################################################",$pasdefichier);
	WriteCompLog ($logComp, "*- FIN EXPORT ACCESS ".date('y\-m\-d\-His'),$pasdefichier);
	WriteCompLog ($logComp, "*-#####################################################",$pasdefichier);
	WriteCompLog ($logComp, "#",$pasdefichier);
	WriteCompLog ($logComp, "#",$pasdefichier);
}

 // end if (! $pasdetraitement )


exit;



?>
