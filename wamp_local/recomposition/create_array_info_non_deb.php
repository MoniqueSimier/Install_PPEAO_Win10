<?php
////////////////////////////////////////////////////////////////////////////////////////////////
//                   Récupération des informations nécessaires aux calculs                      //
//                        des données manquantes (pour chaque débarquement)                   // 
//                                    tableau $info_non_deb                                                   // 
////////////////////////////////////////////////////////////////////////////////////////////////

// Correction JME 12 2008 par comparaison avec create_array_info_deb
// correction JME 05 2009 pour prendre en compte les DBQ sans fractions (theoriquement inutil ici)

$query = "select AD.id, RF.ref_pays_id, RS.nom, AA.nom, AD.mois, AD.annee, AD.poids_total,
	AD.art_grand_type_engin_id, AF.ref_espece_id, AF.poids, AF.nbre_poissons, AF.id 
	from ref_systeme as RF, ref_secteur as RS, art_agglomeration as AA, art_debarquement as AD,
	art_fraction as AF 
	where RS.ref_systeme_id = RF.id 
	and AA.ref_secteur_id = RS.id 
	and AD.art_agglomeration_id = AA.id 
	and AD.id = AF.art_debarquement_id 
	and AF.debarquee != 1
	order by AD.id";
	
$result = pg_query($connection, $query);

$info_non_deb=array();

while($row = pg_fetch_row($result)){
	$cle = $row[0];                                //cle = identifiant du débarquement
	$cle2 = $row[11];                              //cle2 = identifiant de la fraction

	$info_non_deb[$cle][$cle2][0] = $row[1];           //pays
	$info_non_deb[$cle][$cle2][1] = $row[2];           //secteur
	$info_non_deb[$cle][$cle2][2] = $row[3];           //agglomeration
	$info_non_deb[$cle][$cle2][3] = $row[4];           //mois
	$info_non_deb[$cle][$cle2][4] = $row[5];           //année
	$info_non_deb[$cle][$cle2][5] = $row[6];           //poid total du débarquement
	$info_non_deb[$cle][$cle2][6] = $row[7];           //engin de peche
	$info_non_deb[$cle][$cle2][7] = $row[8];           //espece péchée = espece de la fraction
	$info_non_deb[$cle][$cle2][8] = $row[9];           //poids de la fraction = Wfdbq
	$info_non_deb[$cle][$cle2][9] = $row[10];          //nombre poisson de la fraction = Nfdbq        
}
pg_free_result($result);
?>