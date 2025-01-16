<?php 
//*****************************************
// definitionImportXLS.php
//*****************************************
// Created by Yann Laurent
// 2011-03-02 : creation
//*****************************************
// Contient des définitions pour les tables a impoorter depuis fichier xls
//*****************************************

unset ($typeChampTA) ;
switch ($nomTableAccess) {
	case "zone_postgre":
		$typeChampTA[0] = "text"; // CodeClair
		$typeChampTA[1] = "num"; // ZoneNew
		$typeChampTA[2] = "num"; //Systeme
		$typeChampTA[3] = "text"; //Zone
		
		break;
	case "Systeme_corresp":
		$typeChampTA[0] = "num"; // numéro
		$typeChampTA[1] = "num"; // IdSystem
		$typeChampTA[2] = "text"; //codePays
		$typeChampTA[3] = "text"; //NomSysteme
		$typeChampTA[4] = "num"; // SuperficieSysteme
		$typeChampTA[5] = "num"; // PechexpSYS_NUM
		$typeChampTA[6] = "text"; //PechexpSYS_NOM
		$typeChampTA[7] = "num"; //PechexpSYS_NUM_ancien	
		$typeChampTA[8] = "text"; //Champ8
		break;
	default:
		break;

}


?>
