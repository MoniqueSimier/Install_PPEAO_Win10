<?php
// Mis à jour Yann LAURENT, 01-07-2008
// Corrigé par JME 12 2008 : suppression pg_close et exit
// correction vers ligne 1500 d'une division par 0
// suppresion des lignes de commentaires inutiles
// rectification des ecarts types à n-1 
//
// correction du traitement des individus de grandes tailles (03/2010)

$bdd = $_GET['base'];
if ($bdd==""){
			$bdd=$db_default;
		}

$to = $_GET['adresse'];

//print("travail sur la base : ".$bdd);
if(! ini_set("max_execution_time", "360")) {echo "échec";}
//phpinfo();
?>



<?php


$connection = pg_connect ("host=".$host." dbname=".$bdd." user=".$user." password=".$passwd);
if (!$connection) { echo "Pas de connection"; exit;}


//tout d'abord, on efface les données statistiques déjà présente
$eff1 = "delete from art_taille_gt_sp;";
$result = pg_exec($connection, $eff1);
$eff2 = "delete from art_taille_sp;";
$result = pg_exec($connection, $eff2);
$eff3 = "delete from art_stat_gt_sp;";
$result = pg_exec($connection, $eff3);
$eff4 = "delete from art_stat_sp;";
$result = pg_exec($connection, $eff4);
$eff5 = "delete from art_stat_gt;";
$result = pg_exec($connection, $eff5);
$eff6 = "delete from art_stat_totale;";
$result = pg_exec($connection, $eff6);


// Sélection  des pays systèmes pouvant servir aux calculs

$query_systeme = " select distinct ref_pays.nom, ref_systeme.libelle 
from ref_pays, ref_systeme, ref_secteur, art_agglomeration , 
art_debarquement, art_debarquement_rec 
where ref_pays.id = ref_systeme.ref_pays_id 
and ref_systeme.id = ref_secteur.ref_systeme_id 
and ref_secteur.id = art_agglomeration.ref_secteur_id 
and art_agglomeration.id = art_debarquement.art_agglomeration_id 
and art_debarquement.id = art_debarquement_rec.art_debarquement_id 
and art_debarquement_rec.id is not null 
";
$result_systeme = pg_query($connection, $query_systeme);
$syst_etudie=array();
//print ($query_systeme);

while($row = pg_fetch_row($result_systeme))
	{
	$pays = $row[0];	//pays
	$systeme = $row[1];	//systeme
	$syst_etudie[$systeme] = $pays;
	}
pg_free_result($result_systeme);



reset($syst_etudie);
$id = 0;
while (list($key_syst_etudie, $val_syst_etudie) = each($syst_etudie))
{
$pays=$val_syst_etudie;
$systeme=$key_syst_etudie;

/*
print("<br><div align='center'>");
//print("<Font Color =\"#333366\">");
print("<br><br>Statistiques de Pêche pour le système : <Font Color =\"#333366\">".$systeme."</font> ( <Font Color =\"#333366\">".$pays."</font> )<br>");
print("</div>");
print("</Font>");
*/


//////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////STATISTIQUES GLOBALES//////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////


//il existe des guillemets dans la base de données sur les noms de pays et systèmes
$pays = str_replace("'","\'",$pays);
$systeme = str_replace("'","\'",$systeme);


//////////////////////////////////////////////////////////////////////////////////////////////
//                                 Estimation de pue_tot                                                                                            //
//////////////////////////////////////////////////////////////////////////////////////////////

$connection = pg_connect ("host=".$host." dbname=".$bdd." user=".$user." password=".$passwd);
if (!$connection) { echo "Pas de connection"; exit;}

$query = "select distinct AD.art_agglomeration_id, AD.annee, AD.mois 
from ref_pays as RP, ref_systeme as RSy, ref_secteur as RS, art_agglomeration as AA, art_debarquement as AD 
where RP.id = RSy.ref_pays_id 
and RSy.id = RS.ref_systeme_id 
and RS.id = AA.ref_secteur_id 
and AA.id = AD.art_agglomeration_id 
and RP.nom = '".$pays."' 
and RSy.libelle ='".$systeme."'";

$result = pg_query($connection, $query);
//print ($query);

$i=0;
while($row = pg_fetch_row($result))
	{
	$ST[$i][0] = $row[0];	//agglo
	$ST[$i][1] = $row[1];	//annee
	$ST[$i][2] = $row[2];	//periode
	$i=$i+1;
	}
pg_free_result($result);


reset($ST);
$id = 0;
while (list($key, $val) = each($ST))
	{
	
	$query = "select sum(AD_rec.poids_total), count(AD.id), 
	min(AD_rec.poids_total), max(AD_rec.poids_total), STDdev(AD_rec.poids_total), 
	count(distinct AD.date_debarquement) 
	from art_debarquement as AD left join art_debarquement_rec as AD_rec on AD_rec.art_debarquement_id = AD.id 
	where AD.art_agglomeration_id = ".$val[0]." 
	and AD.annee = ".$val[1]." 
	and AD.mois = ".$val[2]." ";
	
	//print ("<br>".$query);
	
	$result = pg_query($connection, $query);
	
	while($row = pg_fetch_row($result))
		{
		$id = $id +1;
		$pue_tot = round(($row[0]/$row[1]) , 3);
		$ecart_type = sqrt ($row[4] * $row[4] * $row[1] / ($row[1] - 1));   // JME 12 2008
			
		$query10 = "insert into art_stat_totale( id, annee, mois, nbre_obs, obs_min, obs_max, 
		pue, pue_ecart_type, art_agglomeration_id, nbre_jour_enq_deb) 
		values (".$id.", ".$val[1].", ".$val[2].", ".$row[1].", ".round($row[2],3).", ".round($row[3],3)
		.", ".$pue_tot.", ".round($ecart_type,3).", ".$val[0].", ".$row[5].")";
	
		//print ("<br>".$query10);
		$result10 = pg_exec($connection, $query10);
		//if (!$result10) {  echo "pb d'insertion "; print ("<br>".$query10); continue;}
		
		
		//construction du tableau $cle_tab_tot qui recupère l'id par rapport à l'agglo, l'annee et le mois
		$cle_tab_tot [$val[0]][$val[1]][$val[2]] = $id;
		}

	}
pg_free_result($result);


//////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////STATISTIQUES PAR ESPECE//////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////
//                                 Estimation de pue_sp                                                                                           //
//////////////////////////////////////////////////////////////////////////////////////////////

reset($ST);
$id_sp = 0;	//identifiant sp
while (list($key, $val) = each($ST))
	{
	
	$query1 = "select count(distinct AD.id) 
	from art_debarquement as AD 
	where AD.art_agglomeration_id = ".$val[0]." 
	and AD.annee = ".$val[1]." 
	and AD.mois = ".$val[2]; 
	
	
	$result = pg_query($connection, $query1);
	$row = pg_fetch_row($result);
	$nb_deb = $row[0];			//recuperation du nombre de debarquement pour chaque ST
	
	
	$tab_esp =Array();		//
	
	$query2 = "select AF_rec.ref_espece_id, AF_rec.poids, AD_rec.id 
	from art_debarquement as AD, art_debarquement_rec as AD_rec, 
	art_fraction_rec as AF_rec, art_fraction as AF 
	where AD.id = AD_rec.art_debarquement_id 
	and AF_rec.id = AF.id 
	and AF.art_debarquement_id = AD.id 
	and AD.art_agglomeration_id = ".$val[0]." 
	and AD.annee = ".$val[1]." 
	and AD.mois = ".$val[2]." 
	order by AD_rec.id"; 
	
	//print("<br>".$query2);

	$tab_esp=Array();
	$result2 = pg_query($connection, $query2);

	$intermediaire=Array();
	while($ligne = pg_fetch_row($result2))
		{
		$esp = $ligne[0];
		
		//si 2 fractions avec meme nom esp dans 1 debarquement, on cumule
		
		if(!isset($intermediaire[$ligne[2]][$esp]))$intermediaire[$ligne[2]][$esp] = $ligne[1];
		else $intermediaire[$ligne[2]][$esp] += $ligne[1];
		}
		reset($intermediaire);
		
	while (list($key_inter, $val_inter) = each($intermediaire))
		{
		while (list($key_inter2, $val_inter2) = each($val_inter))
		{
		$esp = $key_inter2;
		if (!isset($tab_esp[$esp]))$tab_esp[$esp][0]= $val_inter2;
		else
			{

			$tab_esp[$esp][]= $val_inter2;    //ok  12/2007
			}
		
		}
	}
	
	reset($tab_esp);

	while (list($key2, $val2) = each($tab_esp))
		{
		$id_sp ++;	//identifiant sp
		//print ("<br>".$key2." , ".$val2[0]." , ".$val2[1]." , ".$val2[2]." , ".$val2[3]." , ".$val2[4]." , ".$val2[5]." , ".$val2[6]." , ".$val2[7]." , ".$val2[8]." , ".$val2[9]." , ".$val2[10]." , ".$val2[11]);
							//$key= nom esp et $val[0],[1]... = poids des fractions
		
		//récupération du nombre de fois ou l'espece est présente
		$nb_presence = 0;
		while (list($key_nb, $val_nb) = each($val2))
			{
			$nb_presence ++;
			}
		reset($val2);

		$poids_total = 0;
		$min = 10000;
		$max = 0.00001;
		for ($i=0; $i<$nb_deb; $i++)
			{
			if(isset($val2[$i]))
				{
				$poids_total += $val2[$i];
				
				if ($val2[$i]<$min){$min = $val2[$i];}

				if ($val2[$i]>$max){$max = $val2[$i];}
				}
			}

		$pue_sp = round (($poids_total / $nb_deb) , 3);
		
		//calcul de l'ecart type = racine carré de (somme des (x - moy x)²/n)  corrigé n-1 JME 12/2008
		$temp = 0;
		for ($i=0; $i<$nb_deb; $i++)
			{
			if(isset($val2[$i]))
				{
				$temp += (($val2[$i] - $pue_sp) * ($val2[$i] - $pue_sp));
				}
			}
		if ($nb_deb > 1) {
			$ecart_type_sp = round ( sqrt($temp/($nb_deb-1)) , 3);
			} else {
			$ecart_type_sp= 0;
			}
		
		$cle_tab_sp [$val[0]][$val[1]][$val[2]][$key2]=$id_sp;
		
		$query11 = "insert into art_stat_sp ( id, nbre_enquete_sp, obs_sp_min, obs_sp_max, 
		pue_sp_ecart_type, pue_sp, ref_espece_id, art_stat_totale_id) 
		values (".$id_sp.", ".$nb_presence.", ".$min.", ".$max.", ".$ecart_type_sp.", ".$pue_sp.", '".$key2."', "
		.$cle_tab_tot [$val[0]][$val[1]][$val[2]].")"; 
		
		$result11 = pg_exec($connection, $query11);
		//print ("<br>".$query11);
	
		}
}
pg_free_result($result);
pg_free_result($result2);

//////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////STATISTIQUES PAR GRAND TYPE/////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////
//                                 Estimation de pue_gt                                                                                            //
//////////////////////////////////////////////////////////////////////////////////////////////

reset($ST);
$id_gt = 0;
$id_gt_sp =0;
$enplus=Array();
while (list($key, $val) = each($ST))
	{
	
	$query2 = "select sum(AD_rec.poids_total), count(AD_rec.poids_total), 
	min(AD_rec.poids_total), max(AD_rec.poids_total), avg(AD_rec.poids_total), 
	STDdev(AD_rec.poids_total), AD.art_grand_type_engin_id from art_debarquement as AD, art_debarquement_rec as AD_rec 
	where AD.id = AD_rec.art_debarquement_id 
	and AD.art_agglomeration_id = ".$val[0]." 
	and AD.annee = ".$val[1]." 
	and AD.mois = ".$val[2]." group by AD.art_grand_type_engin_id"; 

	//print ("<br>".$query2);
	
	
	$result2 = pg_query($connection, $query2);
	while($row = pg_fetch_row($result2))
		{
		
		//creation du tableau $enplus pour rajouter plus tard les engin non référencés dans les debarquements
		$enplus[$val[0]][$val[1]][$val[2]][$row[6]]="";
		
		
		
		$id_gt ++;
		
		$somme_pt_gt = $row[0];
		$nb_gt = $row[1];
		$min= $row[2];
		$max = $row[3];
		$pue_gt= round ($row[4],3);
		if ($nb_gt > 1) {
		$ecart_type_gt = round( sqrt( $row[5] * $row[5] * $nb_gt / ( $nb_gt-1)), 3); }	//JME 12 2008
		else { $ecart_type_gt = 0; }
		$gt = $row[6];

		$query12 = "insert into art_stat_gt ( id, nbre_enquete_gt, obs_gt_min, obs_gt_max, 
		pue_gt_ecart_type, pue_gt, art_grand_type_engin_id, art_stat_totale_id) 
		values (".$id_gt.", ".$nb_gt.", ".$min.", ".$max.", ".$ecart_type_gt.", ".$pue_gt.", '".$gt."', "
		.$cle_tab_tot [$val[0]][$val[1]][$val[2]].")"; 
		//print ("<br>".$query12);
		
		$cle_tab_gt [$val[0]][$val[1]][$val[2]][$gt]=$id_gt;  //pour les clefs de stat_gt_sp
		
		$result12 = pg_exec($connection, $query12);


//////////////////////////////////////////////////////////////////////////////////////////////
//                              Estimation de pue_gt_sp                                     //
//////////////////////////////////////////////////////////////////////////////////////////////

		
		$query3 = "select AF_rec.ref_espece_id, AF_rec.poids, AD_rec.id 
		from art_debarquement as AD, art_debarquement_rec as AD_rec, 
		art_fraction_rec as AF_rec, art_fraction as AF  
		where AD.id = AD_rec.art_debarquement_id 
		and AF.art_debarquement_id = AD.id 
		and AF.id = AF_rec.id 
		and AD.art_agglomeration_id = ".$val[0]." 
		and AD.annee = ".$val[1]." 
		and AD.mois = ".$val[2]." 
		and AD.art_grand_type_engin_id = '".$gt."'
		order by AD_rec.id, AF_rec.ref_espece_id";
		

		$result3 = pg_query($connection, $query3);
		
		$tab_gt_esp = Array();
		////////
	
		$intermediaire=Array();
		while($ligne = pg_fetch_row($result3))
			{
			$esp_gt = $ligne[0];
			
			//si 2fraction avec meme nom esp dans 1 debarquement, on cumul
			
			if(!isset($intermediaire[$ligne[2]][$esp_gt]))$intermediaire[$ligne[2]][$esp_gt] = $ligne[1];
			else $intermediaire[$ligne[2]][$esp_gt] += $ligne[1];
			}
			reset($intermediaire);
			
		while (list($key_inter, $val_inter) = each($intermediaire))
			{
			while (list($key_inter2, $val_inter2) = each($val_inter))
			{
			$esp_gt = $key_inter2;
			if (!isset($tab_gt_esp[$esp_gt]))$tab_gt_esp[$esp_gt][0]= $val_inter2;
			else
				{
				$tab_gt_esp[$esp_gt][]= $val_inter2;//ok  12/2007
				}
			}
		}
		
		reset($tab_gt_esp);
		while (list($key2, $val2) = each($tab_gt_esp))
			{
			
			//recuperation du nombre d'oservation
			$nb_presence = 0;
			while (list($key_nb, $val_nb) = each($val2))
				{
				$nb_presence ++;
				}
			reset($val2);
			//print ("<br>".$key2." , ".$nb_presence);//ok
			
			
					//$key= nom esp et $val[0],[1]... = poids des fractions
			$id_gt_sp ++;
			$poids_total = 0;

			$min = 10000;
			$max = 0.00001;
			for ($i=0; $i<$nb_gt; $i++)
				{
				if(isset($val2[$i]))
					{
					$poids_total += $val2[$i];
					
					if ($val2[$i]<$min){$min = $val2[$i];}
	
					if ($val[$i]>$max){$max = $val2[$i];}
					}
				}
			$pue_gt_sp = round (($poids_total / $nb_gt) , 3);
			
			//calcul de l'ecart type = racine carré de (somme des (x - moy x)²/n) corrigé n-1 JME 12/2008
			$temp = 0;
			for ($i=0; $i<$nb_gt; $i++)
				{
				if(isset($val2[$i]))
					{
					$temp += (($val2[$i] - $pue_gt_sp) * ($val2[$i] - $pue_gt_sp));
					}
				}
			if ($nb_gt > 1) {
				$ecart_type_gt_sp = round ( sqrt($temp/($nb_gt-1)) , 3);
				} else {
				$ecart_type_gt = 0;
				}
			
			$query12 = "insert into art_stat_gt_sp ( id, nbre_enquete_gt_sp, obs_gt_sp_min, obs_gt_sp_max, 
			pue_gt_sp_ecart_type, pue_gt_sp, ref_espece_id, art_stat_gt_id) 
			values (".$id_gt_sp.", ".$nb_presence.", ".$min.", ".$max.", ".$ecart_type_gt_sp.", ".$pue_gt_sp.", '".$key2."', "
			.$cle_tab_gt [$val[0]][$val[1]][$val[2]][$gt].")"; 
			
			
			$cle_tab_gt_sp [$val[0]][$val[1]][$val[2]][$gt][$key2] = $id_gt_sp;

			$result12 = pg_exec($connection, $query12);
			//print ("<br>".$query12);
			
			
			}
			$tab_gt_esp =Array();	//destruction du tableau

		}

$query_enplus = "select distinct art_activite.art_grand_type_engin_id 
from art_activite  
	where art_activite.art_agglomeration_id = ".$val[0]." 
	and art_activite.annee = ".$val[1]." 
	and art_activite.mois = ".$val[2]." 
	and art_activite.art_grand_type_engin_id is not null ";
$result_enplus = pg_query($connection, $query_enplus);
//print("<br>".$query_enplus);

while($ligne_enplus = pg_fetch_row($result_enplus))
	{
	if(!isset($enplus[$val[0]][$val[1]][$val[2]][$ligne_enplus[0]]))
		{
		$id_gt ++;
		$gt=$ligne_enplus[0];
		$query_enplus2= "insert into art_stat_gt ( id, art_grand_type_engin_id, art_stat_totale_id) 
		values (".$id_gt.", '".$gt."', "
		.$cle_tab_tot [$val[0]][$val[1]][$val[2]].")"; 
		//print ("<br>".$query_enplus2);
		
		$cle_tab_gt [$val[0]][$val[1]][$val[2]][$gt]=$id_gt;  //pour les clefs de stat_gt_sp
		
		$result_enplus2 = pg_exec($connection, $query_enplus2);
		}
	}


	}//fin de ST
pg_free_result($result2);
pg_free_result($result3);
pg_free_result($result12);


$enplus=Array();
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////CALCUL DES EFFORTS DE PECHE///////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////

reset($ST);
while (list($key, $val) = each($ST))
	{

	$NbEnqAct =0;
	$NbS =0;
	$Fpe = 0;
	$Fm = 0;
	$nb_date = 0;
	
	$query = "select AA.id, AA.art_unite_peche_id, AA.date_activite, 
	AA.art_type_activite_id, AA.nbre_unite_recencee, AA.art_type_sortie_id 
	from art_activite as AA 
	where AA.art_agglomeration_id = ".$val[0]." 
	and AA.annee = ".$val[1]." 
	and AA.mois = ".$val[2]." 
	order by AA.date_activite
	";
	
	//print("<br>".$query);
	
	$result = pg_query($connection, $query);
	
	$tab_effort = Array();
	
	while($ligne = pg_fetch_row($result))
		{
		$date_activite = $ligne[2];
		//if (($ligne[3]=="")||($ligne[3]=="NULL"))$tab_effort[$date_activite][] = $ligne[4];
		if (($ligne[3]=="")||($ligne[3]=="NULL"))$tab_effort[$date_activite][] = 0;
		else $tab_effort[$date_activite][] = $ligne[3];
		$nb_date = $nb_date +1;
		$NbUPr_def=$ligne[4];
		}
	
	reset($tab_effort);
	$Nbjoe = count(array_keys($tab_effort));
	
	while (list($key2, $val2) = each($tab_effort))
		{
		$i=0;
		
		$NbS_i = 0;
		$enlev = 0;
		while (isset($val2[$i]))
			{
			if ($val2[$i] == 111)
				{
				$NbS_i = $NbS_i+1; 
				}
			if ($val2[$i] == 114)
				{	
				$NbS_i = $NbS_i+1;
				$enlev = $enlev+1;
				}

				$i =$i+1;
				$NbUPr = $i - $enlev;
			}
		
		$NbEnqAct +=$NbUPr;
		$NbS += $NbS_i;
		}
	
	if ( ($val[2] == 1) || ($val[2] == 3) || ($val[2] == 5) || ($val[2] == 7) || ($val[2] == 8) || ($val[2] == 10) || ($val[2] == 12))
		{
		$Nbjo = 31; //nb jours ds mois
		}
	else if ( ($val[2] == 4) || ($val[2] == 6) || ($val[2] == 9) || ($val[2] == 11))
		{
		$Nbjo = 30;
		}
	else 	
		{
		$Nbjo = 28;	//fevrier
		}
	
	$Fpe = $NbS / $NbEnqAct * ($NbUPr_def * $Nbjoe);
	$Fm = round (($Fpe * $Nbjo / $Nbjoe) , 3);
	
	$query13 = "update art_stat_totale 
	set nbre_unite_recensee_periode = ".$NbUPr_def.", nbre_jour_activite = ".$Nbjoe.", fpe = ".$Fpe.", fm = ".$Fm." 
	where annee = ".$val[1]."  
	and mois = ".$val[2]." 
	and art_agglomeration_id = ".$val[0]." ";
	
	$result13 = pg_exec($connection, $query13);

	
//////////////////////////////////////////////////////////////////////////////////////////////
//                           CALCUL DES EFFORTS DE PECHE                                                                             //
//                                 par Grand Type                                                                                                    //
//////////////////////////////////////////////////////////////////////////////////////////////
	
	$tab_gt = Array();
	$query_ini = "select distinct AA.art_grand_type_engin_id 
	from art_activite as AA 
	where AA.art_agglomeration_id = ".$val[0]." 
	and AA.annee = ".$val[1]." 
	and AA.mois = ".$val[2]." ";

	$result_ini = pg_query($connection, $query_ini);
	while($ligne = pg_fetch_row($result_ini))
		{
		if ($ligne[0]=="")continue;
		else $tab_gt[] = $ligne[0];
		}
	
	reset($tab_gt);
	while (list($key_ini, $val_ini) = each($tab_gt))
		{
		$NbS_gt = 0;
		$NbS_gt_i = 0;
	
		$query_gt = "select AA.id, AA.art_unite_peche_id, AA.date_activite, 
		AA.art_type_activite_id, AA.nbre_unite_recencee, AA.art_type_sortie_id 
		from art_activite as AA 
		where AA.art_agglomeration_id = ".$val[0]." 
		and AA.annee = ".$val[1]." 
		and AA.mois = ".$val[2]." 
		and AA.art_grand_type_engin_id = '".$val_ini."'";

		$result = pg_query($connection, $query_gt);
		
		$tab_effort_gt = Array();
	
		while($ligne = pg_fetch_row($result))
			{
			$date_activite_gt = $ligne[2];
			if (($ligne[3]=="")||($ligne[3]=="NULL"))$tab_effort_gt[$date_activite_gt][] = 0;
			else $tab_effort_gt[$date_activite_gt][] = $ligne[3];

			$nb_date = $nb_date +1;
			$NbUPr_def=$ligne[4];
			}
		
		reset($tab_effort_gt);
	
		while (list($key3, $val3) = each($tab_effort_gt))
			{
			$i=0;

			
			$NbS_gt_i = 0;
			$enlev = 0;
			while (isset($val3[$i]))
				{
				if ($val3[$i] == 111)
					{
					$NbS_gt_i = $NbS_gt_i+1; 
					}
				if ($val3[$i] == 114)
					{
					$NbS_gt_i = $NbS_gt_i+1;
					}

					$i =$i+1;
				}
				
			$NbS_gt += $NbS_gt_i;
			}
	
		$Fpe_gt = $NbS_gt / $NbEnqAct * ($NbUPr_def * $Nbjoe);
		$Fm_gt = round (($Fpe_gt * $Nbjo / $Nbjoe) , 3);
		
		$query14 = "update art_stat_gt 
		set fpe_gt = ".$Fpe_gt.", fm_gt = ".$Fm_gt." 
		where art_grand_type_engin_id = '".$val_ini."' 
		and art_stat_totale_id = ".$cle_tab_tot [$val[0]][$val[1]][$val[2]]." ";

		$result14 = pg_exec($connection, $query14);
		}
	
	}//fin du while (list($key, $val) = each($ST))

pg_free_result($result);
pg_free_result($result13);
pg_free_result($result14);


//////////////////////////////////////////////////////////////////////////////////////////////
//                                                                                                                                                              //
//                           CALCUL DE LA CAPTURE TOTALE                                                                              //
//                                                                                                                                                             //
//////////////////////////////////////////////////////////////////////////////////////////////

reset($ST);
while (list($key, $val) = each($ST))
	{
	$query = "select distinct A_S_T.Fm, A_S_T.pue 
	from art_stat_totale as A_S_T 
	where A_S_T.art_agglomeration_id = ".$val[0]." 
	and A_S_T.annee = ".$val[1]." 
	and A_S_T.mois = ".$val[2]."";
	
	$result = pg_query($connection, $query);
		
	$capt_tot = 0;
	while($row = pg_fetch_row($result))
		{
		$capt_tot = round (($row[0] * $row[1]) ,1);
		
		$query15 = "update art_stat_totale 
		set cap = ".$capt_tot." 
		where annee = ".$val[1]." 
		and mois = ".$val[2]." 
		and art_agglomeration_id = ".$val[0]." ";
		
		$result15 = pg_exec($connection, $query15);
		
		}
	}
pg_free_result($result);
pg_free_result($result15);

/////////////////////////CALCUL DE LA CAPTURE PAR ESPECE////////////////////////	
reset($ST);
while (list($key, $val) = each($ST))
	{
	$query = "select distinct A_S_T.Fm, A_S_SP.pue_sp, A_S_SP.ref_espece_id 
	from art_stat_totale as A_S_T, art_stat_sp as A_S_SP 
	where A_S_T.art_agglomeration_id = ".$val[0]." 
	and A_S_T.annee = ".$val[1]." 
	and A_S_T.mois = ".$val[2]." 
	and A_S_SP.art_stat_totale_id = A_S_T.id ";

	$result = pg_query($connection, $query);
	
	$capt_sp = 0;	
	while($row = pg_fetch_row($result))
		{
		$capt_sp = round(($row[0] * $row[1]) , 1);
		
	
		$query16 = "update art_stat_sp 
		set cap_sp = ".$capt_sp." 
		where ref_espece_id = '".$row[2]."' 
		and art_stat_totale_id = ".$cle_tab_tot [$val[0]][$val[1]][$val[2]]." ";
		
		$result16 = pg_exec($connection, $query16);

		}
	}
pg_free_result($result);
pg_free_result($result16);

/////////////////////////CALCUL DE LA CAPTURE PAR GT////////////////////////

reset($ST);
while (list($key, $val) = each($ST))
	{

	$tab_effort = Array();
	$tab_pue = Array();
	$tab_pue_manquant = Array();
	
	$query_capture = "select A_S_T.cap 
	from art_stat_totale as A_S_T 
	where A_S_T.art_agglomeration_id = ".$val[0]." 
	and A_S_T.annee = ".$val[1]." 
	and A_S_T.mois = ".$val[2]."";
	
	$result = pg_query($connection, $query_capture);
	$row =Array();
	$row = pg_fetch_row($result);
	$capture_totale = $row[0];

	$query_effort = "select A_S_gt.fm_gt, A_S_gt.art_grand_type_engin_id 
	from art_stat_gt as A_S_gt, art_stat_totale as A_S_T 
	where A_S_T.id = A_S_gt.art_stat_totale_id 
	and A_S_T.art_agglomeration_id = ".$val[0]." 
	and A_S_T.annee = ".$val[1]." 
	and A_S_T.mois = ".$val[2]."";
	
	$result = pg_query($connection, $query_effort);
	$row =Array();
	while($row = pg_fetch_row($result))
		{
		if ($row[0] == null)continue;
		else $tab_effort[$row[1]]=$row[0];
		}
	
	$query_pue = "select A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
	from art_stat_gt as A_S_gt, art_stat_totale as A_S_T 
	where A_S_T.id = A_S_gt.art_stat_totale_id 
	and A_S_T.art_agglomeration_id = ".$val[0]." 
	and A_S_T.annee = ".$val[1]." 
	and A_S_T.mois = ".$val[2]."";
	
	
	$result = pg_query($connection, $query_pue);
	$row =Array();
	while($row = pg_fetch_row($result))
		{
		if ($row[0] == null)continue;
		else $tab_pue[$row[1]]=$row[0];
		}
	
	
	$tab_pue_manquant = array_diff_key($tab_effort ,$tab_pue);
	while (list($key2, $val2) = each($tab_pue_manquant))
		{
		//print("<br>".$key2." , ".$val2);	//gt , effort
		
		//on prend la moyenne de pue pour le mois précédent et suivant
		
		//si mois de janvier
		if ($val[2]==1)
			{ 
			$query_manquant1 = "select A_S_gt.pue_gt 
			from art_stat_gt as A_S_gt, art_stat_totale as A_S_T 
			where A_S_T.id = A_S_gt.art_stat_totale_id 
			and A_S_T.art_agglomeration_id = ".$val[0]." 
			and ((A_S_T.annee = ".$val[1]." and A_S_T.mois = 2 ) 
			or (A_S_T.annee = ".($val[1]-1)." and A_S_T.mois = 12)) 
			and A_S_gt.art_grand_type_engin_id = '".$key2."'";
			}
		
		//si mois de décembre
		else if ($val[2]==12)
			{ 
			$query_manquant1 = "select A_S_gt.pue_gt 
			from art_stat_gt as A_S_gt, art_stat_totale as A_S_T 
			where A_S_T.id = A_S_gt.art_stat_totale_id 
			and A_S_T.art_agglomeration_id = ".$val[0]." 
			and ((A_S_T.annee = ".$val[1]." and A_S_T.mois = 11 ) 
			or (A_S_T.annee = ".($val[1]+1)." and A_S_T.mois = 1)) 
			and A_S_gt.art_grand_type_engin_id = '".$key2."'";
			}
		
		//si mois autre que decembre et janvier
		else
			{
			$query_manquant1 = "select A_S_gt.pue_gt 
			from art_stat_gt as A_S_gt, art_stat_totale as A_S_T 
			where A_S_T.id = A_S_gt.art_stat_totale_id 
			and A_S_T.art_agglomeration_id = ".$val[0]." 
			and A_S_T.annee = ".$val[1]." 
			and (A_S_T.mois = ".($val[2]-1)." 
			or A_S_T.mois = ".($val[2]+1).") 
			and A_S_gt.obs_gt_max is not null 
			and A_S_gt.art_grand_type_engin_id = '".$key2."'";
			}
//print("<br>0 : ".$query_manquant1);
				
		
		$nb = 0;
		$res = 0;
		$row_manquant1 =Array();
		$result_manquant1=Array();
		$result_manquant1 = pg_query($connection, $query_manquant1);//11/2007************************
		while($row_manquant1 = pg_fetch_row($result_manquant1))
			{
			$res += $row_manquant1[0];
			$nb ++;
			}
		if ($nb == 2)		//on insere le resultat ds $tab_pue
			{
			$tab_pue[$key2]=($res / 2);
			//on insere le resultat dans la base
			$query_1 = "update art_stat_gt 
			set pue_gt = ".($res / 2)." 
			where art_grand_type_engin_id = '".$key2."' 
			and art_stat_totale_id = ".$cle_tab_tot [$val[0]][$val[1]][$val[2]]." ";
//print("<br>01 : ".$query_1);
			$result_1 = pg_exec($connection, $query_1);
			}
		
		else	{	// meme mois d'enquete ds les agglo du meme secteur avec GT id
			//recuperation de l'id du secteur
			$query_int1 = "select Rf.id from ref_secteur as RF, art_agglomeration as AA 
			where RF.id= AA.ref_secteur_id 
			and AA.id = ".$val[0]."";
			$result_int1=Array();
			$result_int1 = pg_query($connection, $query_int1);
			$row_int1 =Array();
			//$row = pg_fetch_row($result);
			while ($row_int1 = pg_fetch_row($result_int1))
				{
				$secteur = $row_int1[0];
				}
//print("<br>1 : ".$query_int1);

			//recuperation des pue_gt pour chaque agglomerations du secteur

			$query_int3 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
			from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, art_stat_totale as AST 
			where RF.id = ".$secteur." 
			and RF.id= AA.ref_secteur_id 
			and AA.id = AST.art_agglomeration_id 
			and AST.annee = ".$val[1]." 
			and AST.mois = ".$val[2]." 
			and AST.id = A_S_gt.art_stat_totale_id 
			and A_S_gt.obs_gt_max is not null 
			and A_S_gt.art_grand_type_engin_id = '".$key2."'";
	
//print("<br>1.2 : ".$query_int3);	
			$result_int3=Array();
			$result_int3 = pg_query($connection, $query_int3);
			$nb_query_int3 = 0;
			$pue_int3 = 0;
			$pue_query_int3 = 0;
			$row_int3 =Array();
			while ($row_int3 = pg_fetch_row($result_int3))
				{
				$pue_query_int3 += $row_int3[1];
				$nb_query_int3 ++;
				}	
			if ($nb_query_int3 >= 2)		
				{
				$pue_int3 = $pue_query_int3 / $nb_query_int3;
				$tab_pue[$key2]=$pue_int3;
				//on insere le resultat dans la base
				$query_2 = "update art_stat_gt 
				set pue_gt = ".$pue_int3." 
				where art_grand_type_engin_id = '".$key2."' 
				and art_stat_totale_id = ".$cle_tab_tot [$val[0]][$val[1]][$val[2]]." ";
				$result_2 = pg_exec($connection, $query_2);
//print("<br>1.22 : ".$query_2);
				}
			
			else	{	//ds enquetes de la même agglo avec GT id
					//quelque soit le mois sur une période de moi-12 à mois +12
				
				
				$annee_cour = $val[1];
				$annee_moins_un = $val[1]-1;
				$annee_plus_un = $val[1]+1;
				
				switch ($val[2])
					{
					case 1:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and ( (AST.annee = ".$annee_moins_un." ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and (AST.mois = 1) ) ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 2:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2)) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 3:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3)) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 4:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4)) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 5:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5)) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 6:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6)) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 7:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7)) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 8:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8)) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 9:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9)) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 10:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10)) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 11:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 11) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 12)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 12)) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					
					case 12:
					$query_int4 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt,
					art_stat_totale as AST 
					where RF.id = ".$secteur." 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					
					and (  ( (AST.annee = ".$annee_moins_un." ) and (AST.mois = 12) ) 
					or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11)) ) 
					or ( (AST.annee = ".$annee_plus_un." ) )  ) 
					
					and AST.art_agglomeration_id = ".$val[0]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";
					break;
					}
				
				
//print("<br>3 : ".$query_int4);
				$result4=Array();
				$result4 = pg_query($connection, $query_int4);
				$nb_query_int4 = 0;
				$pue_int4 = 0;
				$pue_query_int4 =0;
				
				$row =Array();
				while ($row4 = pg_fetch_row($result4))
					{
					$pue_query_int4 += $row4[1];
					$nb_query_int4 ++;
					}	
				if ($nb_query_int4 >= 2)	//on insere le resultat ds $tab_pue
					{
					$pue_int4 = $pue_query_int4 / $nb_query_int4;
					$tab_pue[$key2]=$pue_int4;
					//on insere le resultat dans la base
					$query_3 = "update art_stat_gt 
					set pue_gt = ".$pue_int4." 
					where art_grand_type_engin_id = '".$key2."' 
					and art_stat_totale_id = ".$cle_tab_tot [$val[0]][$val[1]][$val[2]]." ";
					$result_3 = pg_exec($connection, $query_3);
//print("<br>3.2 : ".$query_3);
					}
				
				else	{	//ds enquetes du meme mois et meme systeme avec GT id
					$query_int5 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
					from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
					ref_systeme as RS, art_stat_totale as AST 
					where RS.libelle = '".$systeme."' 
					and RS.id = RF.ref_systeme_id 
					and RF.id= AA.ref_secteur_id 
					and AA.id = AST.art_agglomeration_id 
					and AST.id = A_S_gt.art_stat_totale_id 
					and AST.annee = ".$val[1]." 
					and AST.mois = ".$val[2]." 
					and A_S_gt.obs_gt_max is not null 
					and A_S_gt.art_grand_type_engin_id = '".$key2."'";

//print("<br>4 : ".$query_int5);
					$result=Array();
					$result = pg_query($connection, $query_int5);
					$nb_query_int5 = 0;
					$pue_int5 = 0;
					$pue_query_int5 = 0;
					
					$row =Array();
					while ($row = pg_fetch_row($result))
						{
						$pue_query_int5 += $row[1];
						$nb_query_int5 ++;
						}	
					if ($nb_query_int5 >= 2)	//on insere le resultat ds $tab_pue
						{
						$pue_int5 = $pue_query_int5 / $nb_query_int5;
						$tab_pue[$key2]=$pue_int5;
						//on insere le resultat dans la base
						$query_4 = "update art_stat_gt 
						set pue_gt = ".$pue_int5." 
						where art_grand_type_engin_id = '".$key2."' 
						and art_stat_totale_id = ".$cle_tab_tot [$val[0]][$val[1]][$val[2]]." ";
						$result_4 = pg_exec($connection, $query_4);
//print("<br>4.2 : ".$query_4);
						}
					
					else	{	//ds enquetes du meme systeme avec GT id
							//quelque soit le mois  sur une période allant de mois-12 à mois +12
						
						
						$annee_cour = $val[1];
						$annee_moins_un = $val[1]-1;
						$annee_plus_un = $val[1]+1;
						
						switch ($val[2])
							{
							case 1: 
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and ( (AST.annee = ".$annee_moins_un." ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and (AST.mois = 1) ) ) 
							
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
					
							case 2:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2)) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							
							case 3:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3)) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							
							case 4:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4)) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							
							case 5:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5)) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							
							case 6:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6)) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							
							case 7:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7)) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							
							case 8:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8)) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							
							case 9:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9)) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							
							case 10:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 10) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10)) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							
							case 11:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and ((AST.mois = 11) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 12)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 12)) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							
							case 12:
							$query_int6 = "select AA.id, A_S_gt.pue_gt, A_S_gt.art_grand_type_engin_id 
							from ref_secteur as RF, art_agglomeration as AA, art_stat_gt as A_S_gt, 
							ref_systeme as RS, art_stat_totale as AST 
							where RS.libelle = '".$systeme."' 
							and RS.id = RF.ref_systeme_id 
							and RF.id= AA.ref_secteur_id 
							and AA.id = AST.art_agglomeration_id 
							and AST.id = A_S_gt.art_stat_totale_id 
							
							and (  ( (AST.annee = ".$annee_moins_un." ) and (AST.mois = 12) ) 
							or ( (AST.annee = ".$annee_cour." ) and ((AST.mois = 1) or (AST.mois = 2) or (AST.mois = 3) or (AST.mois = 4) or (AST.mois = 5) or (AST.mois = 6) or (AST.mois = 7) or (AST.mois = 8) or (AST.mois = 9) or (AST.mois = 10) or (AST.mois = 11)) ) 
							or ( (AST.annee = ".$annee_plus_un." ) )  ) 
					
							and A_S_gt.obs_gt_max is not null 
							and A_S_gt.art_grand_type_engin_id = '".$key2."'";
							break;
							}
						

//print("<br>5 : ".$query_int6);
						$result = pg_query($connection, $query_int6);
						$nb_query_int6 = 0;
						$pue_int6 = 0;
						$pue_query_int6 = 0;
						
						$row =Array();
						while ($row = pg_fetch_row($result))
							{
							$pue_query_int6 += $row[1];
							$nb_query_int6 ++;
							}	
						//on insere le resultat ds $tab_pue
						
						if ($nb_query_int6 == 0) {
						$pue_int6 = 0;
						}
						else{
						$pue_int6 = $pue_query_int6 / $nb_query_int6;
						}
						$tab_pue[$key2]=$pue_int6;
						//on insere le resultat dans la base
						$query_5 = "update art_stat_gt 
						set pue_gt = ".$pue_int6." 
						where art_grand_type_engin_id = '".$key2."' 
						and art_stat_totale_id = ".$cle_tab_tot [$val[0]][$val[1]][$val[2]]." ";
						$result_5 = pg_exec($connection, $query_5);
//print("<br>5.2 : ".$query_5);
						
						}//fin du else
					}
				}
			}
		}

	// calculs des efforts manquants
	$tab_effort_manquant = array_diff_key($tab_pue, $tab_effort);
	while (list($key3, $val3) = each($tab_effort_manquant))
		{
		//on prend la moyenne des efforts pour le mois précédent et suivant
		$query_effort_manquant1 = "select A_S_gt.fm_gt 
		from art_stat_gt as A_S_gt, art_stat_totale as A_S_T 
		where A_S_T.art_agglomeration_id = ".$val[0]." 
		and A_S_T.id = A_S_gt.art_stat_totale_id 
		and A_S_T.annee = ".$val[1]." 
		and (A_S_T.mois = ".($val[2]-1)." 
		or A_S_T.mois = ".($val[2]+1).") 
		and A_S_gt.art_grand_type_engin_id = '".$key3."'";
		
		$nb = 0;
		$res = 0;
		$row =Array();
		$result = pg_query($connection, $query_effort_manquant1);
		while($row = pg_fetch_row($result))
			{
			$res += $row[0];
			$nb = $nb +1;
			}
		if ($nb == 2)		//on insere le resultat ds $tab_effort
			{
			$tab_effort[$key3]=($res / 2);
			//on insere le resultat dans la base
			$query_fm_1 = "update art_stat_gt 
			set fm_gt = ".($res / 2)." 
			where art_grand_type_engin_id = '".$key3."' 
			and art_stat_totale_id = ".$cle_tab_tot [$val[0]][$val[1]][$val[2]]." ";
			$result_fm_1 = pg_exec($connection, $query_fm_1);
			//print("<br>fm_1 : ".$query_fm_1);
			}
	
		else	{	//moyenne des efforts queque soit le mois
			$query_effort_manquant2 = "select A_S_gt.fm_gt 
			from art_stat_gt as A_S_gt, art_stat_totale as A_S_T 
			where A_S_T.art_agglomeration_id = ".$val[0]." 
			and A_S_T.id = A_S_gt.art_stat_totale_id 
			and A_S_T.annee = ".$val[1]." 
			and A_S_gt.art_grand_type_engin_id = '".$key3."'";

//print("<br>query_effort_manquant2".$query_effort_manquant2);

			$nb = 0;
			$res = 0;
			$row =Array();
			$result_effort_manquant2 = pg_query($connection, $query_effort_manquant2);
			while($row = pg_fetch_row($result_effort_manquant2))
				{
				if ($row[0]==null) continue;
				else
					{
					$res += $row[0];
					$nb = $nb +1;
										}
				}
			//on insere le resultat ds $tab_effort
			if ($nb != 0)	$tab_effort[$key3]=($res / $nb);//$tab_effort[$key3]=($res / $nb);
			
			if(($res!=0)&&($nb!=0))
				{
				//on insere le resultat dans la base
				$query_fm_2 = "update art_stat_gt 
				set fm_gt = ".($res / $nb)." 
				where art_grand_type_engin_id = '".$key3."' 
				and art_stat_totale_id = ".$cle_tab_tot [$val[0]][$val[1]][$val[2]]." ";
				$result_fm_2 = pg_exec($connection, $query_fm_2);
				//print("<br>fm_2 : ".$query_fm_2);

				}
			}
		}
		
	$var = 0;
	reset($tab_effort);
	while (list($key5, $val5) = each($tab_effort))
		{
		 $var += $tab_pue[$key5] * $tab_effort[$key5];
		}
	reset($tab_effort);
	while (list($key6, $val6) = each($tab_effort))
		{

		$cap_gt = round((($tab_pue[$key6] * $tab_effort[$key6] / $var) * $capture_totale) , 1);

		
		$query_base = "update art_stat_gt 
		set cap_gt = ".$cap_gt." 
		where art_grand_type_engin_id = '".$key6."' 
		and art_stat_totale_id = ".$cle_tab_tot [$val[0]][$val[1]][$val[2]]." ";

		$result = pg_exec($connection, $query_base);

		}
	}
pg_free_result($result);


/////////////////////////CALCUL DE LA CAPTURE PAR GT_SP////////////////////////

reset($ST);
while (list($key, $val) = each($ST))
	{
	$query20 = "select distinct A_S_gt.cap_gt, A_S_gt_sp.pue_gt_sp, 
	A_S_gt.pue_gt, A_S_gt_sp.ref_espece_id, 
	A_S_gt.art_grand_type_engin_id 
	 from art_stat_gt as A_S_gt, 
	art_stat_gt_sp as A_S_gt_sp, art_stat_totale as A_S_T 
	where A_S_T.art_agglomeration_id = ".$val[0]."  
	and A_S_T.annee = ".$val[1]." 
	and A_S_T.mois = ".$val[2]." 
	and A_S_T.id = A_S_gt.art_stat_totale_id 
	and A_S_gt.id = A_S_gt_sp.art_stat_gt_id "; 

	$result20 = pg_query($connection, $query20);
	
	$capt_gt_sp = 0;
	$row = Array();
	while($row = pg_fetch_row($result20))
		{
		$capt_gt_sp = round(($row[0] * $row[1] / $row[2]) , 1 );
		
		$query21 = "update art_stat_gt_sp 
		set cap_gt_sp = ".$capt_gt_sp." 
		where ref_espece_id = '".$row[3]."' 
		and art_stat_gt_id = ".$cle_tab_gt [$val[0]][$val[1]][$val[2]][$row[4]]." ";

		$result = pg_exec($connection, $query21);
		
		}
	}
pg_free_result($result20);


//////////////////////////////////////////////////////////////////////	
/////////////                     CALCUL DES TAILLES_SP               /////////////
//////////////////////////////////////////////////////////////////////


////////////////Recuperation des coefficients necessaire aux calculs de Wdft//////////////
$query30 = "select id, coefficient_k, coefficient_b, ref_espece_id FROM ref_espece order by id";
$result30 = pg_query($connection, $query30);

$coef_esp = Array();
while($row = pg_fetch_row($result30)){
	$esp = $row[0];            //espece
	$k = $row[1];              //coef k
	$b = $row[2];              //coef b
	$ref = $row[3];            //ref
	//print ("espece :" . $esp . " , K : " . $k . " , B : " . $b . $ref ."<br>");
	
	$coef_esp[$esp][0]= $k;
	$coef_esp[$esp][1]= $b;
	$coef_esp[$esp][2]= $ref;
	}

//remise à zéro du pointeur
reset($coef_esp);

while (list($key30, $val30) = each($coef_esp))
	{
		
	// si k et b non renseignés ou renseignés avec les deux valeurs à 0 
	if ( (($val30[0] == 0)&&($val30[1] == 0))  ||  (($val30[0] == "")&&($val30[1] == ""))  ){
		
		//si il existe une espèce référence
		if ($val30[2] != "") { 
			$new = $val30[2];
			$coef_esp[$key30][0]=$coef_esp[$new][0];
			$coef_esp[$key30][1]=$coef_esp[$new][1];
			}
		//sinon k=1, b=3
		else {
			$coef_esp[$key30][0]= 1;
			$coef_esp[$key30][1]= 3;
			}
		}
	
	}// fin du while



/////////////////Recuperation des structure de tailles par fraction/////////////////////////////////////////////////


$tab_taille = Array();
reset($ST);
$i=1;
//on ne prend que les fractions correspondant à la strate ST
while (list($key_st, $val_st) = each($ST))
	{
	$query31 = "select distinct art_fraction_rec.nbre_poissons, 
	art_poisson_mesure.taille, art_stat_sp.cap_sp, art_stat_sp.ref_espece_id, 
	art_stat_sp.id, art_fraction_rec.id, art_debarquement.mois, 
	art_debarquement.annee, art_agglomeration.id 
	from ref_pays, ref_systeme, ref_secteur, 
	art_agglomeration, art_debarquement, art_fraction, 
	art_poisson_mesure, art_fraction_rec, art_stat_totale, art_stat_sp 
	where ref_pays.id=ref_systeme.ref_pays_id 
	and ref_systeme.id=ref_secteur.ref_systeme_id 
	and ref_secteur.id=art_agglomeration.ref_secteur_id 
	and art_agglomeration.id=art_debarquement.art_agglomeration_id 
	and art_stat_totale.art_agglomeration_id=art_agglomeration.id 
	and art_stat_totale.annee=art_debarquement.annee 
	and art_stat_totale.mois=art_debarquement.mois 
	and art_stat_totale.id=art_stat_sp.art_stat_totale_id 
	and art_stat_sp.ref_espece_id=art_fraction.ref_espece_id 
	and art_debarquement.id=art_fraction.art_debarquement_id 
	and art_fraction.id = art_poisson_mesure.art_fraction_id 
	and art_fraction.id=art_fraction_rec.id 
	and ref_pays.nom = '".$pays."' 
	and ref_systeme.libelle ='".$systeme."' 
	and art_agglomeration.id=".$val_st[0]." 
	and art_debarquement.annee= ".$val_st[1]." 
	and art_debarquement.mois= ".$val_st[2]." 
	order by art_stat_sp.ref_espece_id, art_poisson_mesure.taille ";
	
	//print("<br><br>".$query31);

	$result31 = pg_query($connection, $query31);
	$tab_taille=Array();
	while($row = pg_fetch_row($result31))
		{
		//rajouter test si taille > ou < à 100
		if ($row[1]<10)$taille_stand=5;
		else if ($row[1]<100)$taille_stand=((substr($row[1],0,1))*10)+5;//ex 87->85
		//si > :
		else if ($row[1]<1000) $taille_stand=((substr($row[1],0,2))*10)+5;//ex 123->125
		else $taille_stand=((substr($row[1],0,3))*10)+5; 
// enregistrement de longueur supérieure à 1000mm
		
		//on somme les effectifs de même tailles.

		if(!isset($tab_taille[$row[3]][$taille_stand][0]))$tab_taille[$row[3]][$taille_stand][0]=$row[0];if(!isset($tab_taille[$row[3]][$taille_stand][0]))$tab_taille[$row[3]][$taille_stand][0]=1;
		else $tab_taille[$row[3]][$taille_stand][0]+=1;//nb
		$tab_taille[$row[3]][$taille_stand][1]=$row[2];//cap_sp
		$tab_taille[$row[3]][$taille_stand][2]=$row[4];//art_stat_sp_id
		}
		
	pg_free_result($result31);

	reset($tab_taille);
	while (list($key_esp, $val_esp) = each($tab_taille))	//pour chaque sp
		{

		$wdft_sp = 0;
		while (list($key_taille, $val_taille) = each($val_esp))	//pour chaque taille
			{
			$wdft_sp += ($val_taille[0]*( $coef_esp[$key_esp][0] * pow(10, -5) * pow($key_taille,$coef_esp[$key_esp][1])))/1000;

			}

		reset($val_esp);
		while (list($key_taille, $val_taille) = each($val_esp))	//pour chaque taille
			{
			if($key_taille==5)$li=0;
			else if ($key_taille <99)$li=substr($key_taille,0,1);
			else if ($key_taille <999) $li=substr($key_taille,0,2);
			else  $li=substr($key_taille,0,3);  
// corr JME 03 2010, pour prendre en compte les poissons de plus d'un metre

			
			$xi=round(($val_taille[0]*($val_taille[1]/$wdft_sp)),1);//mettre à 1 pour réel 0.1, 
// la colonne xi de la table taille_sp est mise en reel dans bdppeao et non en integer. JME 03 2010

			if($xi != 0)
				{
				$query_taille = "insert into art_taille_sp ( id, li, xi, art_stat_sp_id) 
				values ($i, '".$li."', ".$xi.", ".$val_taille[2].")"; 
				$result_taille = pg_exec($connection, $query_taille);
				//print ("<br>".$query_taille);
				$i++;
				}
			}
		}
	}//fin du while $ST
	
	


//////////////////////////////////////////////////////////////////////////////////////////////
//                          tailles par gt et par espece                                    //
//////////////////////////////////////////////////////////////////////////////////////////////
reset($ST);
$id_taille_gt_sp =1;


while (list($key_st, $val_st) = each($ST))
	{
	$query_taille2 = "select distinct art_fraction_rec.nbre_poissons, 
	art_poisson_mesure.taille, art_stat_gt_sp.cap_gt_sp, art_stat_gt_sp.ref_espece_id, 
	art_stat_gt_sp.id, art_stat_gt_sp.art_stat_gt_id, art_stat_gt.art_grand_type_engin_id, art_fraction_rec.id, art_debarquement.mois, 
	art_debarquement.annee, art_agglomeration.id 
	from art_agglomeration, art_debarquement, art_fraction, 
	art_poisson_mesure, art_fraction_rec, art_stat_totale, art_stat_gt, art_stat_gt_sp  
	where art_agglomeration.id=art_debarquement.art_agglomeration_id 
	and art_stat_totale.art_agglomeration_id=art_agglomeration.id 
	and art_stat_totale.annee=art_debarquement.annee 
	and art_stat_totale.mois=art_debarquement.mois 
	and art_stat_totale.id=art_stat_gt.art_stat_totale_id 
	and art_stat_gt.id=art_stat_gt_sp.art_stat_gt_id 
	and art_debarquement.art_grand_type_engin_id=art_stat_gt.art_grand_type_engin_id 
	and art_stat_gt_sp.ref_espece_id=art_fraction.ref_espece_id 
	and art_debarquement.id=art_fraction.art_debarquement_id 
	and art_fraction.id = art_poisson_mesure.art_fraction_id 
	and art_fraction.id=art_fraction_rec.id 
	and art_agglomeration.id=".$val_st[0]." 
	and art_debarquement.annee=".$val_st[1]." 
	and art_debarquement.mois= ".$val_st[2]." 
	order by art_stat_gt.art_grand_type_engin_id, art_stat_gt_sp.ref_espece_id, art_poisson_mesure.taille 
	 ";
	
	//print("<br><br>".$query31);
	
	$result_taille2 = pg_query($connection, $query_taille2);
	
	$tab_taille_gt=Array();
	while($row = pg_fetch_row($result_taille2))
		{
		if ($row[1]<10)$taille_stand=5;
		else if ($row[1]<100)$taille_stand=((substr($row[1],0,1))*10)+5;//ex 87->85
		//si > :
		else if ($row[1]<1000) $taille_stand=((substr($row[1],0,2))*10)+5;//ex 123->125
		else $taille_stand=((substr($row[1],0,3))*10)+5; //JME correction 03 2010		
	
		
		//on somme les effectifs de même tailles.

		if(!isset($tab_taille_gt[$row[6]][$row[3]][$taille_stand][0]))$tab_taille_gt[$row[6]][$row[3]][$taille_stand][0]=1;
		else $tab_taille_gt[$row[6]][$row[3]][$taille_stand][0]+=1;//nb
		$tab_taille_gt[$row[6]][$row[3]][$taille_stand][1]=$row[2];//cap_gt_sp
		$tab_taille_gt[$row[6]][$row[3]][$taille_stand][2]=$row[4];//art_stat_gt_sp_id
		}
		
	pg_free_result($result_taille2);

	reset($tab_taille_gt);
	while (list($key_gt, $val_gt) = each($tab_taille_gt))	//pour chaque grand type
		{

		while (list($key_esp, $val_esp) = each($val_gt))	//pour chaque espece
			{

			$wdft_gt_sp = 0;
			while (list($key_taille, $val_taille) = each($val_esp))	//pour chaque taille
				{
				$wdft_gt_sp += ($val_taille[0]*( $coef_esp[$key_esp][0] * pow(10, -5) * pow($key_taille,$coef_esp[$key_esp][1])))/1000;

				}

			
			reset($val_esp);
			while (list($key_taille, $val_taille) = each($val_esp))	//pour chaque taille
				{
				if($key_taille==5)$li=0;
				else if ($key_taille <99)$li=substr($key_taille,0,1);
				else if ($key_taille <999) $li=substr($key_taille,0,2);
				else $li=substr($key_taille,0,3);  
// corr JME 03 2010, pour prendre en compte les poissons de plus d'un metre
							
				
				
				$xi=round(($val_taille[0]*($val_taille[1]/$wdft_gt_sp)),1);//mettre à 1 pour réel 0.1 
				
				if($xi != 0)
					{
					$query_taille2 = "insert into art_taille_gt_sp ( id, li, xi, art_stat_gt_sp_id) 
					values ($id_taille_gt_sp, '".$li."', ".$xi.", ".$val_taille[2].")"; 
					//print ("<br>".$query_taille2);
					$result_taille2 = pg_exec($connection, $query_taille2);
				
					$id_taille_gt_sp++;
					}
				}
			}
		}
	}//fin du while $ST


}//fin pour 1 systeme

print("<br><br><br>");
?>



