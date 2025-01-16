<?php 

// variable globale pour les niveaux de log : 0 pour "production" et 1 pour "déboguage" (plus de détails dans les logs)
$debug = 1;

$messageGen = "";


// variable indiquant où archiver les journaux
$logArchivePath='/work/journal_archives/';

// variable indiquant au bout de combien d'enregistrements il faut déclencher l'autoarchivage du journal
$logAutoArchiveEntriesNumber='1000';

// variable definissant le delai (en annees) avant que des donnees soient considerees comme "historiques"
// ex. : 3 signifie que toute campagne ou periode d'enquete dont l'annee est anterieure a (annee courante-3)
// sera consideree comme historique
$delai_butoir=3;

?>