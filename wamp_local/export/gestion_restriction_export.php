<?php 
//*****************************************
// gestion_restriction_export
//*****************************************
// Created by Yann Laurent
// 2011-02-02 : creation
//*****************************************
// Ce fichier contient des parametrages pour restreindre l'acces aux données

// Pour info
//	$listeTablesRefAvecRestriction = "ref_pays,ref_systeme,ref_secteur";
//	$listeTablesParamExpAvecRestriction = "";
//	$listeTablesParamArtAvecRestriction = "art_agglomeration";
//	$listeTablesDonneesExp="exp_environnement,exp_campagne,exp_coup_peche,exp_fraction,exp_biologie,exp_trophique";
//	$listeTablesDonneesArt="art_unite_peche,art_lieu_de_peche,art_debarquement,art_debarquement_rec,art_engin_peche,art_fraction,art_poisson_mesure,art_activite,art_engin_activite,art_fraction_rec,art_periode_enquete";
//$restrictionPays,$restrictionSysteme
switch ($TableEnCours[$cptTable]) {
	case "ref_pays":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where id ='".$restrictionPays."'";}
		break;
	case "ref_systeme":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where ref_pays_id ='".$restrictionPays."'";}
		break;
	case "ref_secteur":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where ref_systeme_id in (select id from ref_systeme where ref_pays_id ='".$restrictionPays."')";}
		break;
	case "art_agglomeration":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (select id from ref_systeme where ref_pays_id ='".$restrictionPays."'))";}
		break;
	case "exp_environnement":
			if (!$restrictionPays =="") {$restrictionEcosysteme = " where id in ( select exp_environnement_id from exp_coup_peche where exp_campagne_id in (select id from exp_campagne where ref_systeme_id in (".$restrictionSysteme.") ))";}
		break;
	case "exp_campagne":
			if (!$restrictionPays =="") {$restrictionEcosysteme = " where ref_systeme_id in (".$restrictionSysteme.")";}
		break;	
	case "exp_coup_peche":
			if (!$restrictionPays =="") {$restrictionEcosysteme = " where exp_campagne_id in (select id from exp_campagne where ref_systeme_id in (".$restrictionSysteme.") )";}
		break;
	case "exp_fraction":
			if (!$restrictionPays =="") {$restrictionEcosysteme = " where exp_coup_peche_id in ( select id from exp_coup_peche where exp_campagne_id in (select id from exp_campagne where ref_systeme_id in (".$restrictionSysteme.") ))";}
		break;
	case "exp_biologie":
			if (!$restrictionPays =="") {$restrictionEcosysteme = " where exp_fraction_id in (select id from exp_fraction where exp_coup_peche_id in ( select id from exp_coup_peche where exp_campagne_id in (select id from exp_campagne where ref_systeme_id in (".$restrictionSysteme.") )))";}
		break;
	case "exp_trophique":
			if (!$restrictionPays =="") {$restrictionEcosysteme = " where exp_biologie_id in (select id from exp_biologie where exp_fraction_id in (select id from exp_fraction where exp_coup_peche_id in ( select id from exp_coup_peche where exp_campagne_id in (select id from exp_campagne where ref_systeme_id in (".$restrictionSysteme.") ))))";}
		break;
	//case "art_unite_peche": Le probleme est qu'on peut avoir des unites de peches lies aux debarquements ou aux activites. On extrait tout, c'est pas une grosse table
	//	if (!$restrictionPays =="") {$restrictionEcosysteme = " where id in (select art_unite_peche_id from art_debarquement where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme."))))";}
	//	break;	
	case "art_lieu_de_peche":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where id in (select art_lieu_de_peche_id from art_debarquement where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme."))))";}
		break;
	case "art_debarquement":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme.")))";}
		break;
	case "art_debarquement_rec":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_debarquement_id in (select id from art_debarquement where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme."))))";}
		break;
	case "art_engin_peche":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_debarquement_id in (select id from art_debarquement where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme."))))";}
		break;
	case "art_fraction":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_debarquement_id in (select id from art_debarquement where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme."))))";}
		break;
	case "art_poisson_mesure":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_fraction_id in (select id from art_fraction where art_debarquement_id in (select id from art_debarquement where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme.")))))";}
		break;
	case "art_activite":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme.")))";}
		break;		
	case "art_engin_activite":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_activite_id in ( select id from art_activite where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme."))))";}
		break;
	case "art_fraction_rec":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where id in (select id from art_fraction where art_debarquement_id in (select id from art_debarquement where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme.")))))";}
		break;
	case "art_periode_enquete":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme.")))";}
		break;
	//	"art_stat_totale,art_stat_gt,art_stat_gt_sp,art_stat_sp,art_taille_gt_sp,art_taille_sp";
	case "art_stat_effort":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where ref_systeme_id in (".$restrictionSysteme.")";}
		break;	
	case "art_stat_totale":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme.")))";}
		break;	
	case "art_stat_sp":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_stat_totale_id in (select id from art_stat_totale where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme."))))";}
		break;
	case "art_taille_sp":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_stat_sp_id in (select id from art_stat_sp where art_stat_totale_id in (select id from art_stat_totale where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme.")))))";}
		break;		
	case "art_stat_gt":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_stat_totale_id in (select id from art_stat_totale where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme."))))";}
		break;	
	case "art_stat_gt_sp":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_stat_gt_id in (select id from art_stat_gt where art_stat_totale_id in (select id from art_stat_totale where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme.")))))";}
		break;
	case "art_taille_gt_sp":
		if (!$restrictionPays =="") {$restrictionEcosysteme = " where art_stat_gt_sp_id in (select id from art_stat_gt_sp where art_stat_gt_id in (select id from art_stat_gt where art_stat_totale_id in (select id from art_stat_totale where art_agglomeration_id in ( select id from art_agglomeration where ref_secteur_id in ( select id from ref_secteur where ref_systeme_id in (".$restrictionSysteme."))))))";}
		break;	
}



?>