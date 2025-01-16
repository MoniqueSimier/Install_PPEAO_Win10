<?php 
//*****************************************
// stat_definition_position.php
//*****************************************
// Created by Yann Laurent
// 2010-02-23 : creation
//*****************************************
// Ce fichier contient une serie de definition de position pour des calculs complementaires de stats
// et pour gerer le stockage des variables complémentaires....
//*****************************************

// Valeurs pour les stats générales et par agglo pour garder les valeurs des champs additionnels.
// ordre = esp.ref_categorie_ecologique_id,cate.libelle,esp.ref_categorie_trophique_id,catt.libelle,fam.libelle
// art_stat_sp

// Les positions pour les variables supplementaires especes
// y compris pour ast meme si elle n'en a pas (d'ou le -1)
$posGenEcoIDast = "-1";
$posGenEcolibIDast = "-1";
$posGenTroIDast = "-1";
$posGenTrolibIDast = "-1";
$posGenfamlibIDast = "-1";

$posGenEcoIDasp = "23";
$posGenEcolibIDasp = "24";
$posGenTroIDasp = "25";
$posGenTrolibIDasp = "26";
$posGenfamlibIDasp = "27";
// art_taille_sp
$posGenEcoIDats = "23";
$posGenEcolibIDats = "24";
$posGenTroIDats = "25";
$posGenTrolibIDats = "26";
$posGenfamlibIDats = "27";

// art_stat_GT
$posGenEcoIDasgt = "-1";
$posGenEcolibIDasgt = "-1";
$posGenTroIDasgt = "-1";
$posGenTrolibIDasgt = "-1";
$posGenfamlibIDasgt = "-1";

// art_stat_gt_sp
$posGenEcoIDattgt = "26";
$posGenEcolibIDattgt = "27";
$posGenTroIDattgt = "28";
$posGenTrolibIDattgt = "29";
$posGenfamlibIDattgt = "30";
// art_taille_sp
$posGenEcoIDatgts = "26";
$posGenEcolibIDatgts = "27";
$posGenTroIDatgts = "28";
$posGenTrolibIDatgts = "29";
$posGenfamlibIDatgts = "30";

// Gestion des nbres enquetes min et max
$posSomNbenqueteast = "15" ;
$posValMinast = "16" ;
$posValMaxast = "17" ;

$posSomNbenqueteasp = "20" ;
$posValMinasp = "21" ;
$posValMaxasp = "22" ;


$posSomNbenqueteasgt = "18" ;
$posValMinasgt = "19" ;
$posValMaxasgt = "20" ;

$posSomNbenqueteattgt = "23" ;
$posValMinattgt = "24" ;
$posValMaxattgt = "25" ;

$posSomNbenqueteats = "-1" ;
$posValMinats = "-1" ;
$posValMaxats = "-1" ;

$posSomNbenqueteatgts = "-1" ;
$posValMinatgts = "-1" ;
$posValMaxatgts = "-1" ;

?>