<?php
/***********
/ fonctions PHP utilisees dans la selection en vue de l'extraction
*/



//******************************************************************************
// compte le nombre de campagnes ou enquetes correspondnat a la selection en cours - evolution de countMatchingUnits()
function countMatchingUnits2($domaine,$exploit) {
// $domaine : "exp" ou "art" selon le type de peches
// $exploit : vide ou "stats"
global $connectPPEAO;

// on s'interesse aux peches experimentales
if ($domaine=='exp') {
$sql="SELECT DISTINCT id FROM exp_campagne WHERE TRUE ";
	
	if (!empty($_GET["familles"]) || !empty($_GET["especes"])) {
		$sql.=' AND (';
	}
	// si des valeurs de familles ont ete passees dans l'url
	if (!empty($_GET["familles"])) {
		
		$sql.=' exp_campagne.id IN (
			SELECT exp_coup_peche.exp_campagne_id FROM exp_coup_peche WHERE exp_coup_peche.id 
			IN (
			SELECT DISTINCT exp_fraction.exp_coup_peche_id 
			FROM exp_fraction WHERE exp_fraction.ref_espece_id 
			IN (
				SELECT DISTINCT ref_espece.id 
				FROM ref_espece 
				WHERE ref_espece.ref_famille_id 
				IN ('.arrayToList($_GET["familles"],',','').')
				)
			)
			)';
	} // fin de if (!empty($_GET["familles"]))
		if (!empty($_GET["familles"]) && !empty($_GET["especes"])) {
		$sql.=' OR ';
	}
	// si des valeurs d'especes ont ete passees dans l'url
	if (!empty($_GET["especes"]) && $_GET["step"]>1) {
		$sql.=' exp_campagne.id IN (
			SELECT DISTINCT exp_coup_peche.exp_campagne_id FROM exp_coup_peche WHERE exp_coup_peche.id 
			IN (
				SELECT DISTINCT exp_fraction.exp_coup_peche_id 
				FROM exp_fraction WHERE exp_fraction.ref_espece_id 
				IN (\''.arrayToList($_GET["especes"],'\',\'','\'').')
				)
			)';
	} // fin de if (!empty($_GET["especes"]))
	if (!empty($_GET["familles"]) || !empty($_GET["especes"])) {
		$sql.=')';
	}	
	// si des valeurs de pays ont ete passees dans l'url
	/*if (!empty($_GET["pays"]) && $_GET["step"]>3) {
		$sql.=' AND exp_campagne.ref_systeme_id IN (SELECT DISTINCT ref_systeme.id FROM ref_systeme WHERE ref_systeme.ref_pays_id IN (\''.arrayToList($_GET["pays"],'\',\'','\'').')) ';
		}*/
	// si des valeurs de systeme ont ete passees dans l'url
	if (!empty($_GET["systemes"])  && $_GET["step"]>3) {
		$sql.=' AND exp_campagne.ref_systeme_id IN (\''.arrayToList($_GET["systemes"],'\',\'','\'').')';
		}
		
		
	// si une valeur de debut_annee a ete passee dans l'url
	if (!empty($_GET["d_a"])  && $_GET["step"]>4) {
		$debut_annee=$_GET["d_a"];
		// si aucun mois n'a ete passe, on utilise janvier soit 1
		if (empty($_GET["d_m"])) {$debut_mois=1;} else {$debut_mois=$_GET["d_m"];}
		// on construit une date a partir de l'annee et du mois
		$debut_date=$debut_annee.'-'.$debut_mois.'-01';
		$sql.=' AND exp_campagne.date_debut>=\''.$debut_date.'\' ';
		}
	// si une valeur de fin_annee a ete passee dans l'url
	if (!empty($_GET["f_a"]) && $_GET["step"]>4) {
		$fin_annee=$_GET["f_a"];
		// si aucun mois n'a ete passe, on utilise janvier soit 1
		if (empty($_GET["f_m"])) {$fin_mois=1;} else {$fin_mois=$_GET["f_m"];}
		// on construit une date a partir de l'annee et du mois
		$fin_date=$fin_annee.'-'.$fin_mois.'-'.days_in_month($fin_annee,$fin_mois);
		
		$sql.=' AND exp_campagne.date_debut<=\''.$fin_date.'\' ';

		}
	
	// si des valeurs de secteurs ont ete passees dans l'url
	if (!empty($_GET["secteurs"])  && $_GET["step"]>7) {
		$sql.=' AND exp_campagne.id IN (
			SELECT DISTINCT exp_coup_peche.exp_campagne_id FROM exp_coup_peche WHERE exp_coup_peche.id IN (
				SELECT DISTINCT exp_coup_peche.id FROM exp_coup_peche WHERE exp_coup_peche.exp_station_id IN (
					SELECT DISTINCT exp_station.id FROM exp_station WHERE exp_station.ref_secteur_id IN (
					\''.arrayToList($_GET["secteurs"],'\',\'','\'').'
					)
				)
			)
			)';
		}
	
	// si des valeurs de campagnes ont ete passees dans l'url
	if (!empty($_GET["camp"]) && $_GET["step"]>8) {
		$sql.=' AND exp_campagne.id IN (\''.arrayToList($_GET["camp"],'\',\'','\'').')';
	}
	// si des valeurs d'engins ont ete passees dans l'url
	if (!empty($_GET["eng"])) {
		$sql.=' AND exp_campagne.id IN (
			SELECT exp_coup_peche.exp_campagne_id FROM exp_coup_peche WHERE exp_coup_peche.exp_engin_id IN
				(\''.arrayToList($_GET["eng"],'\',\'','\'').')
			)';
	}
	

	
} // fin de if ($domaine=='exp') 

// on s'interesse aux peches artisanales ou aux statistiques

if ($domaine=='art' || $domaine=='stats') {
$sql="SELECT DISTINCT id FROM art_periode_enquete WHERE TRUE ";

// on compile la liste d'especes passees dans l'url
$especes_array=array();
// on commence par tester si des familles ont ete selectionnees
if (!empty($_GET["familles"]) && $_GET["step"]>2) {
	// on recupere la liste de toutes les especes appartenant aux familles selectionnees
	$sql_esp='SELECT id FROM ref_espece WHERE ref_espece.ref_famille_id IN (\''.arrayToList($_GET["familles"],'\',\'','\'').')';
	$result_esp=pg_query($connectPPEAO,$sql_esp) or die('erreur dans la requete : '.$sql_esp. pg_last_error());
	$array_esp=pg_fetch_all($result_esp);
	pg_free_result($result_esp);
	$especes_array=array();
	foreach ($array_esp as $esp) {
		$especes_array[]=$esp["id"];
	}
} // fin if (!empty($_GET["familles"]) && $_GET["step"]>2)

// si des especes ont ete passees dans l'URL		
if (!empty($_GET["especes"])) {$especes_array=array_merge($especes_array,$_GET["especes"]);
array_unique($especes_array);}
	
// si des valeurs d'especes ont ete passees dans l'url
if (!empty($especes_array) && $_GET["step"]>1) {
	$sql.=' AND art_periode_enquete.art_agglomeration_id IN(
		SELECT d.art_agglomeration_id 
		FROM art_debarquement d 
		WHERE d.id IN (
			SELECT f.art_debarquement_id 
			FROM art_fraction f 
			WHERE f.ref_espece_id IN (\''.arrayToList($especes_array,'\',\'','\'').')
		)
	) 
	AND art_periode_enquete.annee IN (
	SELECT d.annee 
	FROM art_debarquement d 
	WHERE d.id IN (
		SELECT f.art_debarquement_id 
		FROM art_fraction f 
		WHERE f.ref_espece_id IN (\''.arrayToList($especes_array,'\',\'','\'').')
		)
	) 
	AND art_periode_enquete.mois IN (
	SELECT d.mois 
	FROM art_debarquement d 
	WHERE d.id IN (
		SELECT f.art_debarquement_id 
		FROM art_fraction f 
		WHERE f.ref_espece_id IN (\''.arrayToList($especes_array,'\',\'','\'').')
		)
	)';
} // fin de if (!empty($_GET["especes"]))

	
	// si des valeurs de pays ont ete passees dans l'url
	/*if (!empty($_GET["pays"]) && $_GET["step"]>3) {
		$sql.=' AND art_periode_enquete.art_agglomeration_id IN (SELECT DISTINCT art_agglomeration.id FROM art_agglomeration WHERE
 art_agglomeration.ref_secteur_id IN (SELECT DISTINCT ref_secteur.id FROM ref_secteur WHERE ref_secteur.id IN (SELECT DISTINCT ref_secteur.id FROM ref_secteur WHERE ref_secteur.ref_systeme_id IN (SELECT DISTINCT ref_systeme.id FROM ref_systeme WHERE ref_systeme.ref_pays_id IN (\''.arrayToList($_GET["pays"],'\',\'','\'').')))))';
		}*/
	// si des valeurs de systeme ont ete passees dans l'url
	if (!empty($_GET["systemes"]) && $_GET["step"]>3) {
		$sql.=' AND art_periode_enquete.art_agglomeration_id IN (SELECT DISTINCT art_agglomeration.id FROM art_agglomeration WHERE
 art_agglomeration.ref_secteur_id IN (SELECT DISTINCT ref_secteur.id FROM ref_secteur WHERE ref_secteur.id IN (SELECT DISTINCT ref_secteur.id FROM ref_secteur WHERE ref_secteur.ref_systeme_id IN  (\''.arrayToList($_GET["systemes"],'\',\'','\'').'))))';
		}
	// si des valeurs de secteur ont ete passees dans l'url
	if (!empty($_GET["secteurs"]) && $_GET["step"]>7) {
		$sql.=' AND art_periode_enquete.art_agglomeration_id IN (SELECT DISTINCT art_agglomeration.id FROM art_agglomeration WHERE
 art_agglomeration.ref_secteur_id IN (SELECT DISTINCT ref_secteur.id FROM ref_secteur WHERE ref_secteur.id IN  (\''.arrayToList($_GET["secteurs"],'\',\'','\'').')))';
		}
		// si des valeurs d'agglomeration ont ete passees dans l'url
	if (!empty($_GET["agglo"]) && $_GET["step"]>8) {
		$sql.=' AND art_periode_enquete.art_agglomeration_id IN (\''.arrayToList($_GET["agglo"],'\',\'','\'').')';
		}
		// si une valeur de debut_annee a ete passee dans l'url
	if (!empty($_GET["d_a"]) && $_GET["step"]>4) {
		$debut_annee=$_GET["d_a"];
		// si aucun mois n'a ete passe, on utilise janvier soit 1
		if (empty($_GET["d_m"])) {$debut_mois=1;} else {$debut_mois=$_GET["d_m"];}
		// on construit une date a partir de l'annee et du mois
		$debut_date=$debut_annee.'-'.$debut_mois.'-01';
		// 2010-05-04 dirty hack to go around the problem of records with mois=11 but date_debut=2002-10-31
		// we use the column date_stat which contains a concatenation of annee-mois-01 instead of date_debut
		//$sql.=' AND art_periode_enquete.date_debut>=\''.$debut_date.'\' ';
		$sql.=' AND art_periode_enquete.date_stat>=\''.$debut_date.'\' ';
		}
	// si une valeur de fin_annee a ete passee dans l'url
	if (!empty($_GET["f_a"]) && $_GET["step"]>4) {
		$fin_annee=$_GET["f_a"];
		// si aucun mois n'a ete passe, on utilise janvier soit 1
		if (empty($_GET["f_m"])) {$fin_mois=1;} else {$fin_mois=$_GET["f_m"];}
		// on construit une date a partir de l'annee et du mois
		$fin_date=$fin_annee.'-'.$fin_mois.'-'.days_in_month($fin_annee,$fin_mois);
		// 2010-05-04 dirty hack to go around the problem of records with mois=11 but date_debut=2002-10-31
		// we use the column date_stat which contains a concatenation of annee-mois-01 instead of date_debut
		//$sql.=' AND art_periode_enquete.date_debut<=\''.$fin_date.'\' ';
		$sql.=' AND art_periode_enquete.date_stat<=\''.$fin_date.'\' ';
		}
		
	// si des valeurs de periodes d'enquete ont ete passees dans l'url
	if (!empty($_GET["enq"]) && $_GET["step"]>9) {
		$sql.=' AND art_periode_enquete.id IN (\''.arrayToList($_GET["enq"],'\',\'','\'').')';
	}
	
	// si des valeurs de grands types d'engins ont ete passees dans l'url
	// le step differe selon que l'on a affaire aux stats par agglo (10), generales (8) ou aux peches artisanales (10)
	if ($_GET["stats"]=='gen') {$theStep=8;} else {$theStep=10;}
	if (!empty($_GET["gteng"]) && $_GET["step"]>$theStep) {
		$sql.=' 
		 AND art_periode_enquete.id IN (
			SELECT DISTINCT pe.id  
			FROM art_periode_enquete pe WHERE 
				
				(
					pe.art_agglomeration_id IN (
					SELECT DISTINCT d.art_agglomeration_id FROM art_debarquement d WHERE d.art_grand_type_engin_id IN (
						\''.arrayToList($_GET["gteng"],'\',\'','\'').')
				) 
				
				AND pe.annee IN (
					SELECT DISTINCT d.annee FROM art_debarquement d WHERE d.art_grand_type_engin_id IN (
						\''.arrayToList($_GET["gteng"],'\',\'','\'').')
				)
				 
				AND pe.mois IN (
					SELECT DISTINCT d.mois FROM art_debarquement d WHERE d.art_grand_type_engin_id IN (
						\''.arrayToList($_GET["gteng"],'\',\'','\'').')
				)
				) 
				
				OR
				
				(
					pe.art_agglomeration_id IN (
					SELECT DISTINCT a.art_agglomeration_id FROM art_activite a WHERE a.art_grand_type_engin_id IN (
						\''.arrayToList($_GET["gteng"],'\',\'','\'').')
				) 
				
				AND pe.annee IN (
					SELECT DISTINCT a.annee FROM art_activite a WHERE a.art_grand_type_engin_id IN (
						\''.arrayToList($_GET["gteng"],'\',\'','\'').')
				)
				 
				AND pe.mois IN (
					SELECT DISTINCT a.mois FROM art_activite a WHERE a.art_grand_type_engin_id IN (
						\''.arrayToList($_GET["gteng"],'\',\'','\'').')
				)
				)
			)
		';
	}

	
} // fin de if ($domaine=='art')



// on fait la requete
//debug echo('<pre>sql pour lister les unites disponibles<br>');print_r($sql);echo('</pre>');


	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	$totalArray=pg_fetch_all($result);
	pg_free_result($result);	

if (empty($totalArray)) {$total=0;$ids=array();} 
	else {
		$total=count($totalArray);
		foreach($totalArray as $row) {
			$ids[]=$row["id"];
		}
	}
$unites=array("total"=>$total,"ids"=>$ids);

//debug echo('<pre>');print_r($unites);echo('</pre>');




// si on a depasse le step 4 (donc on en est au moins au choix du type d'exploitation)
// on filtre selon les droits d'acces de l'utilisateur
// sauf si l'utilisateur fait partie du groupe ADMIN ou du groupe GESTIONNAIRE DONNEES qui ont acces a toutes les donnees
$user_admin='';
if (isset($_SESSION["s_ppeao_user_id"])) {$user_groups=userGetGroups($_SESSION["s_ppeao_user_id"]);


if (in_array(1,$user_groups) || in_array(2,$user_groups) || in_array(3,$user_groups)) {$user_admin=TRUE;} else {$user_admin=FALSE;}

}
if ($_GET["step"]>4 && $user_admin!=TRUE) {
	// on commence par stocker les donnees "avant" filtrage
	
	$unites["total_avant"]=$total;
	$unites["ids_avant"]=$ids;


	// on reinitialise total et ids pour les remplacer par les valeurs filtrees
	$unites["total"]='';
	$unites["ids"]=array();
	
	// on recupere les droits d'acces de l'utilisateur (les siens et ceux des groupes auxquels il appartient)
	$user_droits=array();
	// si il est connecte
	if (isset($_SESSION["s_ppeao_user_id"])) {
	$user_droits=getUserSystemRights($_SESSION["s_ppeao_user_id"]);}	
	
	// puis on filtre
	// pour chaque unite, on verifie si l'utilisateur y a acces ou pas
	// si oui, on garde l'unite, sinon on ne l'inclut que si elle fait partie des donnees "historiques"
	foreach ($unites["ids_avant"] as $unite) {
		
		$acces=userHasAccessToUnit($unite,$domaine,$exploit,$user_droits);
		
		// si l'utilisateur a acces a la totalite des donnees du systeme
		if ($acces["acces_complet"]) {
			$unites["ids"][]=$unite;
			$art["ids"][]=$unite;
			$stats["ids"][]=$unite;
			;}
			//sinon, on doit tester si l'unite peut etre qualifiee de donnees historiques
			else {
				// on recupere les donnees de delai_butoir du systeme concerne
				$systeme_id=$acces["ref_systeme_id"];
				$utilisation=$acces["utilisation"];
				
				// cas particulier : on n'a pas encore choisi de type d'exploitation
				// donc on doit compter les enquetes auxquelles l'utilisateur peut accéder pour PA et pour STATS
				if ($_GET["step"]==5 && $domaine=='art') {$utilisation='art_plus_stats';}
				
				$systeme_butoirs=getSystemAccessDate($systeme_id);
				
				// on compare la date de debut de la campagne ou periode d'enquete avec la date butoir
				$annee_courante=date('Y');
				switch ($utilisation) {
					case "exp":
					// on calcule l'annee butoir :
					$annee_butoir=$annee_courante-$systeme_butoirs["PE"]["delai_butoir"];
					break;
					case "art":
					// on calcule l'annee butoir :
					$annee_butoir=$annee_courante-$systeme_butoirs["PA"]["delai_butoir"];
					break;
					case "stats":
					// on calcule l'annee butoir :
					$annee_butoir=$annee_courante-$systeme_butoirs["ST"]["delai_butoir"];
					break;
					case "art_plus_stats":
					// on calcule l'annee butoir : on prend la plus recente de PA et ST
					$annee_butoir_art=$annee_courante-$systeme_butoirs["PA"]["delai_butoir"];
					$annee_butoir_stats=$annee_courante-$systeme_butoirs["ST"]["delai_butoir"];
					$annee_butoir=max($annee_butoir_art,$annee_butoir_stats);
					break;
				}
				// on autorise toutes les donnees dont annee_debut est AVANT $annee butoir
				if ($acces["annee"]<$annee_butoir) {$unites["ids"][]=$unite;}
				// on stocke separement les id pour art et stat
				if($acces["annee"]<$annee_butoir_art) {$art["ids"][]=$unite;}
				if($acces["annee"]<$annee_butoir_stats) {$stats["ids"][]=$unite;}
				
			}
	}
	
	if (!empty($unites["ids"])) {$unites["total"]=count($unites["ids"]);} else {$unites["total"]=0;}
	
}


if ($_GET["step"]>4 && $user_admin==TRUE) {
	
			$art["ids"]=$unites["ids"];
			$stats["ids"]=$unites["ids"];
	
	;}

// si des donnees ont ete exclues, on en stocke la liste
if (isset($unites["total_avant"]) && $unites["total_avant"]!=$unites["total"]) {
	$unites["ids_exclues"]=array_diff($unites["ids_avant"],$unites["ids"]);
	$unites["total_exclues"]=$unites["total_avant"]-$unites["total"];
} 


// maintenant on calcule le nombre de coups de peche (exp) ou de periodes d'enquete/activites
$coups=array();$debarquements=array();$activites=array();

if ($domaine=='exp') {
	if (!empty($unites["ids"])) {
	// exp : on cherche les coups de peche
	$sql='SELECT DISTINCT exp_coup_peche.id FROM exp_coup_peche WHERE exp_campagne_id IN (\''.arrayToList($unites["ids"],'\',\'','\'').')';
	// si on a passe des engins dans l'url
	if (!empty($_GET["eng"])) {
		$sql.=' AND exp_engin_id IN  (\''.arrayToList($_GET["eng"],'\',\'','\'').') ';
	}
	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	$coups_array=pg_fetch_all($result);
	pg_free_result($result);
	
if (empty($coups_array)) {$coups_total=0;$coups_ids=array();} 
	else {
		$coups_total=count($coups_array);
		foreach($coups_array as $row) {
			$coups_ids[]=$row["id"];
		}
	}	
	$coups=array("coups_total"=>$coups_total,"coups_ids"=>$coups_ids);}
else {
	$coups=array("coups_total"=>0,"coups_ids"=>array());
	}

} // fin de if ($domaine=='exp')

if ($domaine=='art') {
	// art : on cherche les debarquements
	if (!empty($unites["ids"])) {
	$debarquements_array=array();
	foreach($unites["ids"] as $enquete) {
	$sql='SELECT art_debarquement.id 
	FROM art_debarquement 
	WHERE art_agglomeration_id IN 
		(SELECT art_agglomeration_id FROM art_periode_enquete WHERE art_periode_enquete.id='.$enquete.') 
		AND art_debarquement.annee=(SELECT annee  FROM art_periode_enquete WHERE art_periode_enquete.id='.$enquete.') 
		AND art_debarquement.mois=(SELECT mois  FROM art_periode_enquete WHERE art_periode_enquete.id='.$enquete.')
		';
	// si on a passe des grands types d'engins dans l'url
		// le step differe selon que l'on a affaire aux stats par agglo (10) ou generales (8) ou les peches art (10)
	$theStep=10;
	if ($_GET["stats"]=='gen') {$theStep=8;} else {$theStep=10;}
	if (!empty($_GET["gteng"]) && $_GET["step"]>$theStep)
 		{
		$sql.=' AND art_grand_type_engin_id IN (\''.arrayToList($_GET["gteng"],'\',\'','\'').') ';
	}
	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	$array=pg_fetch_all($result);
	if (!empty($array)) {foreach($array as $row) {$debarquements_array[]=$row["id"];}}
	pg_free_result($result);
	} // end foreach $unite[ids]
if (empty($debarquements_array)) {$debarquements_total=0;$debarquements_ids=array();} 
	else {
		$debarquements_total=count($debarquements_array);
		foreach($debarquements_array as $row) {
			$debarquements_ids[]=$row;
		}
	$debarquements=array("debarquements_total"=>$debarquements_total,"debarquements_ids"=>$debarquements_ids);
	}
	
	
	// art : on cherche les activites
	$activites_array=array();
	foreach($unites["ids"] as $enquete) {
	$sql='SELECT art_activite.id 
	FROM art_activite 
	WHERE art_agglomeration_id IN 
		(SELECT art_agglomeration_id FROM art_periode_enquete WHERE art_periode_enquete.id='.$enquete.') 
		AND art_activite.annee=(SELECT annee  FROM art_periode_enquete WHERE art_periode_enquete.id='.$enquete.') 
		AND art_activite.mois=(SELECT mois  FROM art_periode_enquete WHERE art_periode_enquete.id='.$enquete.')
		';	
	// si on a passe des grands types d'engins dans l'url
		// le step differe selon que l'on a affaire aux stats par agglo (10) ou generales (8) ou les peches art (10)
	$theStep=10;
	if ($_GET["stats"]=='gen') {$theStep=8;} else {$theStep=10;}
	if (!empty($_GET["gteng"]) && $_GET["step"]>$theStep) {

		$sql.=' AND art_grand_type_engin_id IN (\''.arrayToList($_GET["gteng"],'\',\'','\'').') ';	
	}
		
	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	$array=pg_fetch_all($result);
	
	if (!empty($array)) {foreach($array as $row) {$activites_array[]=$row["id"];}}
	pg_free_result($result);
	} // end foreach $unite[ids]
if (empty($activites_array)) {$activites_total=0;$activites_ids=array();} 
	else {
		$activites_total=count($activites_array);
		foreach($activites_array as $row) {
			$activites_ids[]=$row;
		}
	}


$activites=array("activites_total"=>$activites_total,"activites_ids"=>$activites_ids);
	}
	else {
	$debarquements=array("debarquements_total"=>0,"debarquements_ids"=>array());
	$activites=array("activites_total"=>0,"activites_ids"=>array());
	}
} // fin de if ($domaine=='art')

	
// on ajoute les resultats au tableau $unites
$unites["coups"]=$coups;
$unites["debarquements"]=$debarquements;
$unites["activites"]=$activites;

$unites["art"]=$art["ids"];
$unites["stats"]=$stats["ids"];

return $unites;


}



//******************************************************************************
// prepare le compteur indiquant le nombre de campagnes/periodes d'enquete correspondant a la selection en cours
function prepareCompteur() {
// la connexion a la base
global $connectPPEAO;
// on prepare le compteur

// on commence par savoir si on se base sur un modele de peches exp ou art
$peches='';
if ($_GET["donnees"]=="exp") {$peches='exp';}
if ($_GET["donnees"]=="art") {$peches='art';}
// les statistiques ne sont realisees que sur les peches artisanales
$exploit='';
if ($_GET["exploit"]=="stats") {$peches='art';$exploit='stats';}

// si on a depasse la premiere etape, on affiche le lien permettant d'afficher ou masquer la selection
// et on affiche "votre selection correspond a :"
if ($_GET["step"]>1) {
	$link='<span class="showHide"><a id="selection_precedente_toggle_bas" onclick="javascript:toggleSelection();return false;" title="afficher ou masquer la selection" href="#">[modifier ma s&eacute;lection]</a></span>';
	
	$text='votre s&eacute;lection correspond &agrave; :';
} else {
	$link='';
	$text='donn&eacute;es disponibles :';
	}

$filtrees["campagnes"]=FALSE;
$filtrees["enquetes"]=FALSE;


switch ($peches) {
	case "exp":
	// Peches experimentales
	// on compte les campagnes
	$campagnes=countMatchingUnits2('exp','');
	// si on a des campagnes qui ne sont pas consultables par l'utilisateur, on change le texte du compteur
	$sur_campagnes='';
	if (isset($campagnes["total_avant"]) && $campagnes["total_avant"]!=$campagnes["total"]) {
		$text='votre compte utilisateur vous permet de consulter :';
		$sur_campagnes=' sur '.$campagnes["total_avant"].' disponibles ';
		$filtrees["campagnes"]=TRUE;
		$filtrees["enquetes"]=FALSE;
	}
	$total_campagnes=$campagnes["total"];
	if ($total_campagnes>0) {$texte_coups=' &ndash; '.$campagnes["coups"]["coups_total"].' coup(s) de p&ecirc;che'; } 
	else {$texte_coups='';}
	$compteur=array("campagnes_ids"=>$campagnes["ids"],
				"campagnes_total"=>$total_campagnes,
				"coups_ids"=>$campagnes["coups"]["coups_ids"],
				"coups_total"=>$campagnes["coups"]["coups_total"],
				"texte"=>'<p>'.$text.$link.'</p><ul><li>'.$total_campagnes.' campagne(s)'.$sur_campagnes.$texte_coups.'</li></ul>');
	break;
	
	case "art":
	// on compte les periodes d'enquete
	$enquetes=countMatchingUnits2('art',$exploit);
	
	// si on a des enquetes qui ne sont pas consultables par l'utilisateur, on change le texte du compteur
	$sur_enquetes='';
	if (isset($enquetes["total_avant"]) && $enquetes["total_avant"]!=$enquetes["total"]) {
		$text='votre compte utilisateur vous permet de consulter :';
		$sur_enquetes=' sur '.$enquetes["total_avant"].' disponibles ';
		$filtrees["campagnes"]=FALSE;
		$filtrees["enquetes"]=TRUE;
	}	
	
	$total_enquetes=$enquetes["total"];
	if ($total_enquetes>0) {$texte_deb_act=' &ndash;'.$enquetes["debarquements"]["debarquements_total"].' d&eacute;barquement(s) et '.$enquetes["activites"]["activites_total"].' activit&eacute;(s).'; } 
	else {$texte_deb_act='';}
	$compteur=array("enquetes_ids"=>$enquetes["ids"],
				"enquetes_total"=>$total_enquetes,
				"debarquements_total"=>$enquetes["debarquements"]["debarquements_total"],
				"debarquements_ids"=>$enquetes["debarquements"]["debarquements_ids"],
				"activites_total"=>$enquetes["activites"]["activites_total"],
				"activites_ids"=>$enquetes["activites"]["activites_ids"],
				"texte"=>'<p>'.$text.$link.'</p><ul><li>'.$total_enquetes.' p&eacute;riode(s) d&#x27;enqu&ecirc;te'.$sur_enquetes.$texte_deb_act.'</li></ul>',"art_ids"=>$enquetes["ids_art"],"stats_ids"=>$enquetes["ids_stats"]);
	break;
	
	default:
	// avant le choix de exp ou art : 
	// on compte les campagnes et les enquetes
		$campagnes=countMatchingUnits2('exp','');
		$enquetes=countMatchingUnits2('art','');
	
		
	// si on a des campagnes ou des enquetes qui ne sont pas consultables par l'utilisateur, on change le texte du compteur
	$sur_campagnes='';
	$sur_enquetes='';
	// on ne fait ça que si l'utilisateur est connecte
	if ($_SESSION["s_ppeao_login_status"]=='good') {
	if (isset($campagnes["total_avant"]) && $campagnes["total_avant"]!=$campagnes["total"]) {
		$text='votre compte utilisateur vous permet de consulter :';
		$sur_campagnes=' sur '.$campagnes["total_avant"].' disponibles ';
		$filtrees["campagnes"]=TRUE;
	}
	if (isset($enquetes["total_avant"]) && $enquetes["total_avant"]!=$enquetes["total"]) {
		$text='votre compte utilisateur vous permet de consulter :';
		$sur_enquetes=' sur '.$enquetes["total_avant"].' disponibles ';
		$filtrees["enquetes"]=TRUE;

	}
	}
	$total_campagnes=$campagnes["total"];
	if ($total_campagnes>0) {$texte_coups=' &ndash; '.$campagnes["coups"]["coups_total"].' coup(s) de p&ecirc;che'; } 
	else {$texte_coups='';}

	$total_enquetes=$enquetes["total"];
	
	if ($total_enquetes>0) {$texte_deb_act=' &ndash;'.$enquetes["debarquements"]["debarquements_total"].' d&eacute;barquement(s) et '.$enquetes["activites"]["activites_total"].' activit&eacute;(s)'; } 
	else {$texte_deb_act='';}
	$compteur=array("campagnes_ids"=>$campagnes["ids"],
				"campagnes_total"=>$total_campagnes,
				"coups_ids"=>$campagnes["coups"]["coups_ids"],
				"coups_total"=>$campagnes["coups"]["coups_total"],
				"enquetes_ids"=>$enquetes["ids"],
				"enquetes_total"=>$total_enquetes,
				"debarquements_total"=>$enquetes["debarquements"]["debarquements_total"],
				"debarquements_ids"=>$enquetes["debarquements"]["debarquements_ids"],
				"activites_total"=>$enquetes["activites"]["activites_total"],
				"activites_ids"=>$enquetes["activites"]["activites_ids"],
				"stats_ids"=>$enquetes["stats"],
				"art_ids"=>$enquetes["art"],
				);
				
	$texte='<p>'.$text.$link.'</p>
				<ul>
				<li>'.$total_campagnes.' campagne(s)'.$sur_campagnes.$texte_coups.'</li>
				<li>'.$total_enquetes.' p&eacute;riode(s) d&#x27;enqu&ecirc;te'.$sur_enquetes.$texte_deb_act.'</li>
				</ul>';
	break;
				
	} // end switch $exploit
				
// si on a eu des campagnes ou enquetes filtrees, on affiche plus d'informations en dessous du compteur
if ($filtrees["campagnes"] || $filtrees["enquetes"]) {
		$compteur["filtrees"]=TRUE;
		$texte.='<div id="infos_filtre">';
		$texte.='<span class="showHide"><a href="#" onclick="javascript:toggleInfosFiltre();return false;">[informations sur les donn&eacute;es auxquelles vous n&#x27;avez pas acc&egrave;s]</a></span>';
		$texte.='<div id="infos_filtre_contenu">';
		$texte.='<p>vous n&#x27;avez pas acc&egrave;s &agrave; la totalit&eacute; des donn&eacute;es suivantes pour la p&eacute;riode choisie :</p>';
		$texte.='<ul>';
		// si on a filtre des campagnes
		if ($filtrees["campagnes"]) {
			// on recupere les systemes qui ont ete exclus
			$sql='SELECT DISTINCT s.libelle 
					FROM ref_systeme s, exp_campagne c 
					WHERE c.ref_systeme_id=s.id AND c.id IN ('.arrayToList($campagnes["ids_exclues"],',','').')';
			$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
			$array=pg_fetch_all($result);
			pg_free_result($result);
			$systemes_exclus=array();
			foreach($array as $row) {$systemes_exclus[]=$row["libelle"];}
			if (count($systemes_exclus)>1) {$fin='des syst&egrave;mes';} else {$fin='du syst&egrave;me';}
			$texte.='<li>p&ecirc;ches exp&eacute;rimentales, donn&eacute;es '.$fin.' : '.arrayToList($systemes_exclus,', ','').'.</li>';
			;}
			// si on a filtre des enquetes
		if ($filtrees["enquetes"]) {
			// on recupere les systemes qui ont ete exclus
			$sql='SELECT DISTINCT sy.libelle 
			FROM art_periode_enquete pe, ref_secteur s, art_agglomeration a, ref_systeme sy
			WHERE pe.id IN ('.arrayToList($enquetes["ids_exclues"],',','').') 
			AND a.id=pe.art_agglomeration_id AND a.ref_secteur_id=s.id AND sy.id=s.ref_systeme_id';
			$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
			$array=pg_fetch_all($result);
			pg_free_result($result);
			$systemes_exclus=array();
			foreach($array as $row) {$systemes_exclus[]=$row["libelle"];}
			if (count($systemes_exclus)>1) {$fin='des syst&egrave;mes';} else {$fin='du syst&egrave;me';}
			$texte.='<li>p&ecirc;ches artisanales, donn&eacute;es '.$fin.' : '.arrayToList($systemes_exclus,', ','').'.</li>';
			;}
		$texte.='</ul>';
		$texte.='si vous d&eacute;sirez obtenir l&#x27;acc&egrave;s &agrave; ces donn&eacute;es, <a href="/contact.php">contactez-nous</a> en indiquant le type de donn&eacute;es et la liste des syst&egrave;mes qui vous int&eacute;ressent.';
		$texte.='</div>';
		$texte.='</div>';
	}
	$compteur["texte"].=$texte;


return $compteur;


}

//******************************************************************************
// recupere la liste des id des systemes correspondant aux campagnes, enquetes et pays selectionnes
function listSelectSystemes($pays,$campagnes_ids,$enquetes_ids) {
	// la connextion a la base
	global $connectPPEAO;
	//$pays: la liste des id des pays selectionnes
	//$campagnes_ids: la liste des id des campagnes filtrees
	//$enquetes_ids: la liste des id des enquetes filtrees

	

// on recupere la liste des systemes correspondant aux campagnes et enquetes correspondant a la selection precedente
	$sql_systemes='	SELECT DISTINCT ref_systeme.id, ref_systeme.libelle 
				FROM ref_systeme
				WHERE TRUE';
	// si on a choisi des valeurs de pays
	if (!empty($pays)) {
	$sql_systemes.=' AND ref_systeme.ref_pays_id IN (\''.arrayToList($pays,'\',\'','\'').')';
	}
	// si il reste des campagnes
	
	if(!empty($campagnes_ids[0]) || !empty($enquetes_ids[0])) {
	$sql_systemes.=' AND (';
	
	if (!empty($campagnes_ids[0])) {
	$sql_systemes.='ref_systeme.id IN ';
	$sql_systemes.=' (SELECT DISTINCT exp_campagne.ref_systeme_id FROM exp_campagne WHERE TRUE ';
		
		
		$sql_systemes.=' AND exp_campagne.id IN (\''.arrayToList($campagnes_ids,'\',\'','\'').')';
		$sql_systemes.=') ';
	}  //end if !empty($campagnes_ids[0])

		// si il reste des enquetes
		if (!empty($enquetes_ids[0])) {
			if (!empty($campagnes_ids[0])) {$boo=' OR ';} else {$boo='';}
		$sql_systemes.=$boo.'ref_systeme.id IN (SELECT DISTINCT ref_secteur.ref_systeme_id FROM ref_secteur WHERE ref_secteur.id IN (
		SELECT DISTINCT art_agglomeration.ref_secteur_id 
		FROM art_agglomeration 
		WHERE art_agglomeration.id IN (
			SELECT DISTINCT art_periode_enquete.art_agglomeration_id 
			FROM art_periode_enquete 
			WHERE TRUE ';
		$sql_systemes.=' AND art_periode_enquete.id IN ( 
												\''.arrayToList($enquetes_ids,'\',\'','\'').')';
		$sql_systemes.=')))';										
	} // end if !empty($enquetes_ids[0])
	//$sql_systemes.=')';
} // end if(!empty($campagnes_ids[0]) || !empty($enquetes_ids[0]))
		
	$sql_systemes.=')';
	
// >>> 26/03/2014 Restriction systeme par user F.WOEHL
	$systemeUser = getSystemeUserRight( $_SESSION[ "s_ppeao_user_id" ] );
	if( !empty( $systemeUser ) ) {
		$sql_systemes .= ' AND ref_systeme.id IN ( \''.arrayToList($systemeUser,'\',\'','\'').') ' ;
	}  
// <<< 26/03/2014 Restriction systeme par user F.WOEHL

	
	$sql_systemes.=' ORDER BY ref_systeme.libelle';
	
	$result_systemes=pg_query($connectPPEAO,$sql_systemes) or die('erreur dans la requete : '.$sql_systemes. pg_last_error());
	$array_systemes=pg_fetch_all($result_systemes);
	pg_free_result($result_systemes);
		
	
		return $array_systemes;
	} // end function listSelectSystemes()



//******************************************************************************
// recupere la liste des id des secteurs correspondant aux enquetes et systemes
function listSelectSecteurs($systemes,$enquetes_ids) {
	// la connextion a la base
	global $connectPPEAO;
	//$enquetes_ids (array): la liste des id des enquetes filtrees
	//$systemes (array): les systemes selectionnes
	
	$sql='SELECT rs.id, rs.nom as secteur, rsy.libelle as systeme FROM ref_secteur rs, ref_systeme as  rsy
			WHERE rs.id IN (
				SELECT aa.ref_secteur_id FROM art_agglomeration aa 
				WHERE aa.id IN (
					SELECT DISTINCT art_agglomeration_id FROM art_periode_enquete ape 
					WHERE ape.id IN (\''.arrayToList($enquetes_ids,'\',\'','\'').')
					)
			)';
	if (!empty($systemes)) {
	$sql.=' AND rs.ref_systeme_id IN (\''.arrayToList($systemes,'\',\'','\'').'
		)';
	}
	
	$sql.='AND rs.ref_systeme_id=rsy.id';
		
	
	$result_secteurs=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	$array_secteurs=pg_fetch_all($result_secteurs);
	pg_free_result($result_secteurs);
	
		return $array_secteurs;
} // end function listSelectSecteurs()

//******************************************************************************
// affiche le bloc permettant d'indiquer si l'on veut choisir ou non des especes
function prepareSelectionEditLink($step) {
//$step: l'etape a laquelle on veut revenir

// on remplace le param step avec l'etape cible
$edit_link=replaceQueryParam ($_SERVER['FULL_URL'],'step',$step);	


if ($step<10) {
	$edit_link=removeQueryStringParam($edit_link,'g_t_eng\[\]');
	$edit_link=removeQueryStringParam($edit_link,'eng\[\]');
}

if ($step<9) {
	$edit_link=removeQueryStringParam($edit_link,'p_enq\[\]');
}

if ($step<8) {
	$edit_link=removeQueryStringParam($edit_link,'camp\[\]');
	$edit_link=removeQueryStringParam($edit_link,'agglo\[\]');
}

if ($step<7) {
	$edit_link=removeQueryStringParam($edit_link,'secteurs\[\]');
	$edit_link=removeQueryStringParam($edit_link,'systemes2\[\]');
	$edit_link=removeQueryStringParam($edit_link,'g_t_eng\[\]');

}

if ($step<=6) {
	$edit_link=removeQueryStringParam($edit_link,'donnees');
	$edit_link=removeQueryStringParam($edit_link,'stats');
}

if ($step<=5) {
	$edit_link=removeQueryStringParam($edit_link,'exploit');
}

if ($step<4) {
	$edit_link=removeQueryStringParam($edit_link, 'd_a');
	$edit_link=removeQueryStringParam($edit_link, 'd_m');
	$edit_link=removeQueryStringParam($edit_link, 'f_a');
	$edit_link=removeQueryStringParam($edit_link, 'f_m');
}

if ($step<3) {
	$edit_link=removeQueryStringParam($edit_link,'pays\[\]');
	$edit_link=removeQueryStringParam($edit_link,'systemes\[\]');
}


return $edit_link;


}


//******************************************************************************
// affiche le texte d'aide a la selection selon l'etape actuelle
function afficheAide($topic) {
//$topic: le "theme" de l'etape de selection ("taxonomie", "geographie", "periode", "type_exploitation", "type_donnees", "exp", "art", "campagnes", "engins", "secteurs", "agglomerations", "periodes_enquete", "grands_types_engins", "type_stats", "stats_agglo", "stats_gen", "secteurs2", "filieres")
$hint='<div class="hint clear"><span class="hint_label"><a href="#" onclick="toggleAide(\'aide_'.$topic.'\');return false;">aide &gt;&gt;</a></span><div class="hint_text" id="aide_'.$topic.'" style="display:none;">';
$hint_multiple='Vous pouvez s&eacute;lectionner ou d&eacute;s&eacute;lectionner plusieurs &eacute;l&eacute;ments en cliquant dessus tout en maintenant la touche &quot;CTRL&quot; (Windows, Linux) ou &quot;CMD&quot; (Mac) enfonc&eacute;e.<br>Pour effectuer une s&eacute;lection continue, cliquez sur la premi&egrave;re valeur puis sur la derni&egrave;re valeur en maintenant la touche MAJ enfonc&eacute;e.';
$hint_empty='';
	switch ($topic) {
	case "taxonomie":
		$hint.="S&eacute;lectionnez les familles et/ou esp&egrave;ces qui vous int&eacute;ressent.<br />";
		$hint.=$hint_empty;
		$hint.=$hint_multiple;
	break;
	case "geographie":
		$hint.="Pour s&eacute;lectionner les syst&egrave;mes qui vous int&eacute;ressent, commencez par s&eacute;lectionner un ou plusieurs pays, puis s&eacute;lectionnez un ou plusieurs syst&egrave;mes parmi la liste qui s&rsquo;affiche alors dans la colonne de droite.<br>";
		$hint.=$hint_empty;
		$hint.="Cliquez alors sur &quot;ajouter et passer &agrave; la s&eacute;lection temporelle&quot;.<br />";
		$hint.=$hint_multiple;
	break;
	case "periode":
		$hint.="S&eacute;lectionnez les ann&eacute;es et mois de d&eacute;but et de fin de la p&eacute;riode qui vous int&eacute;resse.";
	break;
	case "type_exploitation":
		$hint.="Choisissez le type d&#x27;exploitation que vous voulez appliquer aux donn&eacute;es s&eacute;lectionn&eacute;es.";
	break;
	case "type_donnees":
		$hint.="Choisissez le type de donn&eacute;es &agrave; extraire.";
	break;
	case "secteurs":
		$hint.="S&eacute;lectionnez les secteurs qui vous int&eacute;ressent.<br />";
		$hint.=$hint_empty;
		$hint.=$hint_multiple;
	break;
	case "campagnes":
		$hint.="S&eacute;lectionnez les campagnes de p&ecirc;che exp&eacute;rimentale qui vous int&eacute;ressent.<br />";
		$hint.=$hint_empty;
		$hint.=$hint_multiple;
	break;
	case "engins":
		$hint.="S&eacute;lectionnez les engins de p&ecirc;che qui vous int&eacute;ressent.<br />";
		$hint.=$hint_empty;
		$hint.=$hint_multiple;
	break;
	case "filieres":
		$hint.="Votre s&eacute;lection est termin&eacute;e, vous allez maintenant choisir une fili&egrave;re d&#x27;extraction.";
	break;
	case "agglomerations":
		$hint.="S&eacute;lectionnez les agglomérations qui vous int&eacute;ressent.<br />";
		$hint.=$hint_empty;
		$hint.=$hint_multiple;
	break;
	case "periodes_enquete":
		$hint.="S&eacute;lectionnez les p&eacute;riodes d&#x27;enqu&ecirc;te qui vous int&eacute;ressent.<br />";
		$hint.=$hint_empty;
		$hint.=$hint_multiple;
	break;
	case "grands_types_engins":
		$hint.="S&eacute;lectionnez les grands types d&#x27;engins qui vous int&eacute;ressent.<br />";
		$hint.=$hint_empty;
		$hint.=$hint_multiple;
	break;
	case "type_stats":
		$hint.="Choisissez le type de statistiques qui vous int&eacute;ressent.";
	break;
	case "stats":
		$hint.="Votre s&eacute;lection est termin&eacute;e, vous allez maintenant pouvoir choisir les statistiques &agrave; extraire.";
	break;
		
	default:
		$hint.='';
	break;
	}
$hint.='</div></div>';

echo $hint;
}

//******************************************************************************
// affiche le bloc permettant d'indiquer si l'on veut choisir ou non des especes
function afficheChoixEspeces( $mode = 0 ) {

// on determine a quelle etape on en est (si step est vide on suppose que on est au step 1)
if (empty($_GET["step"])) {$step=1;} else {$step=$_GET["step"];}
// si l'on en est a la premiere etape, on affiche le choix

if ($step==1) {
	if( $mode == 0 ) {
		echo('<div id="step_1">');
		echo("<h2>Voulez-vous commencer par s&eacute;lectionner des esp&egrave;ces ?</h2>");
		echo('<p><a href="/extraction/selection/selection.php?choix_especes=1&step=2" class="">oui</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/extraction/selection/selection.php?choix_especes=0&step=3" class="">non</a></p>');
		echo('</div>');
	} else {
		echo('<div id="step_1">');
		echo('<br/>');
		echo('<p><a href="/extraction/selection/selection.php?choix_especes=0&step=3" class="next_step">Continuer</a></p>');
		echo('</div>');
	}
	// on reinitialise les parametres de selection stockes dans la session
	$_SESSION["selection_1"]=array();
}

else {
	echo('<div id="step_1">');
	echo('<h2><a href="/extraction/selection/selection.php">recommencer la s&eacute;lection du d&eacute;but</a></h2>');
	echo('</div>');
}

// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==2) {
echo('</div></div>');}

}


//******************************************************************************
// affiche le bloc permettant de selectionner des famille et/ou des especes
function afficheTaxonomie() {
// la connexion a la base
global $connectPPEAO;

//juste pour etre sur, on ne fait rien si choix_especes!=1

if ($_GET["choix_especes"]==1) {

// si l'on en est a l'etape en question, on affiche le selecteur
switch ($_GET["step"]) {
	case 1: 
	// on n'est pas encore arrive a cette etape, on n'affiche rien 
	break;
	case 2:
	// on est a cette etape, on affiche le selecteur 
	echo('<div id="step_2">');
	echo('<form id="step_2_form" name="step_2_form" target="/extraction/selection/selection.php?choix_especes=1" method="GET">');
		echo("<h2>s&eacute;lectionner des familles et/ou des esp&egrave;ces</h2>");
		
		// on recupere la liste des especes qui sont presentes dans les campagnes ou les enquetes
		$sql_especes='	SELECT DISTINCT id, libelle 
						FROM ref_espece 
						WHERE id IN (
									SELECT DISTINCT ref_espece_id 
									FROM exp_fraction 
									WHERE exp_coup_peche_id IN (
										SELECT DISTINCT exp_coup_peche.id 
										FROM exp_coup_peche, exp_campagne 
										WHERE exp_coup_peche.exp_campagne_id=exp_campagne.id))
							OR id IN (
									SELECT DISTINCT ref_espece_id 
									FROM art_fraction 
									WHERE TRUE
								)
						ORDER BY libelle';
		$result_especes=pg_query($connectPPEAO,$sql_especes) or die('erreur dans la requete : '.$sql_especes. pg_last_error());
		$array_especes=pg_fetch_all($result_especes);
		pg_free_result($result_especes);		
		
		// on recupere la liste des familles dont des especes sont presentes dans les campagnes ou les enquetes
		// on utilise pour cela la liste des especes recuperee ci-dessus
		foreach($array_especes as $espece) {$especes_id[]=$espece["id"];}
		
		$liste_especes='\''.arrayToList($especes_id,'\',\'','\'');
		$sql_familles='	SELECT DISTINCT id, libelle 
						FROM ref_famille 
						WHERE id IN (
									SELECT ref_famille_id 
									FROM ref_espece 
									WHERE id IN ('.$liste_especes.')
									)
						ORDER BY libelle';
		$result_familles=pg_query($connectPPEAO,$sql_familles) or die('erreur dans la requete : '.$sql_familles. pg_last_error());
		$array_familles=pg_fetch_all($result_familles);
		pg_free_result($result_familles);
		
		
		// on affiche le selecteur de familles
		echo('<div id="step_2_familles" class="level_div">');
		echo('<p>familles</p>');
		echo('<select id="familles" name="familles[]" size="10" multiple="multiple" class="level_select" onchange="javascript:toggleNextStepLink(\'familles\',\'especes\',\'step_2_link\');">');
			foreach($array_familles as $famille) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($famille["id"],$_GET["familles"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$famille["id"].'" '.$selected.'>'.$famille["libelle"].'</option>');
			}
		echo('</select>');
		echo('</div>');
		echo('<div class="level_div"> et/ou </div>');
		// on affiche le selecteur d'especes
		echo('<div id="step_2_especes" class="level_div">');
		echo('<p>esp&egrave;ces</p>');
		echo('<select id="especes" name="especes[]" size="10" multiple="multiple" class="level_select" onchange="javascript:toggleNextStepLink(\'familles\',\'especes\',\'step_2_link\');">');
			foreach($array_especes as $espece) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($espece["id"],$_GET["especes"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$espece["id"].'" '.$selected.'>'.$espece["libelle"].'</option>');
			}
		echo('</select>');
		echo('</div>');
	echo('</form>');
	// on affiche le lien permettant de passer a la selection geographique
	// on prepare l'url pour construire le lien : on enleve les familles et especes eventuellement selectionnees
	$url=$_SERVER["FULL_URL"];
	$url=removeQueryStringParam($url,'open');
	$url=removeQueryStringParam($url,'familles\[\]');
	$url=removeQueryStringParam($url,'especes\[\]');
	echo('<p id="step_2_link" class="clear" style="display:none;"><a href="#" class="next_step" onclick="javascript:goToNextStep(2,\''.$url.'\');return false;">ajouter et passer &agrave; la s&eacute;lection spatiale &gt;&gt;</a></p>');
	// on affiche le texte d'aide
	afficheAide("taxonomie");
	echo('</div>');// end div id="step_2"
	// on met a jour les parametres de selection stockes dans la session
	$_SESSION["selection_1"]=array();
	break;
	default:
	// on en est a une etape ulterieure, on affiche le resume textuel
	echo('<div id="step_2">');
		echo("<h2>familles et/ou esp&egrave;ces</h2>");
		if (!empty($_GET["familles"])) {
			// on recupere la liste des noms des familles selectionnees
			$familles_id='\''.arrayToList($_GET["familles"],'\',\'','\'');
			$sql='SELECT DISTINCT libelle FROM ref_famille WHERE id IN ('.$familles_id.')';
			$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
			$array=pg_fetch_all($result);
			pg_free_result($result);
			foreach ($array as $famille) {$familles_noms[]=$famille["libelle"];}
			$liste_familles=arrayToList($familles_noms,', ','.');
			
			echo("<p>familles : $liste_familles</p>");
		}
		if (!empty($_GET["especes"])) {
			// on recupere la liste des noms des especes selectionnees
			$especes_id='\''.arrayToList($_GET["especes"],'\',\'','\'');
			$sql='SELECT DISTINCT libelle FROM ref_espece WHERE id IN ('.$especes_id.')';
			$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
			$array=pg_fetch_all($result);
			pg_free_result($result);
			foreach ($array as $espece) {$especes_noms[]=$espece["libelle"];}
			$liste_especes=arrayToList($especes_noms,', ','.');
			echo("<p>esp&egrave;ces : $liste_especes</p>");
		}
	// le lien permettant d'editer la selection des especes
	$edit_link=prepareSelectionEditLink(2);
	echo('<p id="edit_especes" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection des esp&egrave;ces...</a></p>');	
	echo('</div>');
	
	break;
		
	} // fin de switch $_GET[step]

} // fin de if ($_GET["choix_especes"]==1)

// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==3) {
echo('</div></div>');}

}

// <<< 26/03/2014 systemes par user by F.WOEHL
function getSystemeUserRight( $user_id ) {
 global $connectPPEAO;

 $sql = "SELECT ref_systeme_id FROM admin_acces_users_systemes WHERE user_id=$user_id " ;
 $result = pg_query( $connectPPEAO, $sql ) or die( 'erreur dans la requete : '.$sql. pg_last_error() ) ;
 $array = pg_fetch_all( $result ) ;
 pg_free_result( $result ) ;

 return $array[ 0 ] ;
}
// <<< 26/03/2014 systemes par user by F.WOEHL

//******************************************************************************
// affiche le bloc permettant de selectionner des systemes
function afficheGeographie() {

global $connectPPEAO; // la connexion a la base
global $campagnes_ids; // la liste des campagnes deja selectionnees
global $enquetes_ids; // la liste des enquetes deja selectionnees


// on determine si on a commence par choisir des especes
if ($_GET["choix_especes"]==1) {$choix=1;} else {$choix=0;}

// si l'on en est a l'etape en question, on affiche le selecteur
switch ($_GET["step"]) {
	case '':
	case 1:
	case 2: 
	// on n'est pas encore arrive a cette etape, on n'affiche rien 
	break;
	case 3:
	// on est a cette etape, on affiche le selecteur 
	echo('<div id="step_3">');
	echo('<form id="step_3_form" name="step_3_form" target="/extraction/selection/selection.php?choix_especes='.$choix.'" method="GET">');
	echo("<h2>s&eacute;lectionner des syst&egrave;mes</h2>");
	
	// on recupere la liste des pays correspondant aux campagnes et enquetes correspondant a la selection precedente
	$sql_pays='	SELECT DISTINCT ref_pays.id, ref_pays.nom 
				FROM ref_pays, ref_systeme 
				WHERE ref_systeme.ref_pays_id=ref_pays.id AND ('; 
	
	// si il y a des campagnes disponibles
	if (!empty($campagnes_ids)) {
	$sql_pays.=' ref_systeme.id IN (SELECT DISTINCT exp_campagne.ref_systeme_id FROM exp_campagne WHERE exp_campagne.id IN (\''.arrayToList($campagnes_ids,'\',\'','\'').'))';
}
		if (!empty($campagnes_ids) && !empty($enquetes_ids)) { $sql_pays.=' OR ';}
	
	// si il y a des enquetes disponibles
	if (!empty($enquetes_ids))  {
		$sql_pays.=' ref_systeme.id IN (
		SELECT DISTINCT ref_secteur.ref_systeme_id FROM ref_secteur WHERE ref_secteur.id IN (
		SELECT DISTINCT art_agglomeration.ref_secteur_id 
		FROM art_agglomeration 
		WHERE art_agglomeration.id IN (
			SELECT DISTINCT art_periode_enquete.art_agglomeration_id 
			FROM art_periode_enquete 
			WHERE art_periode_enquete.id IN ( \''.arrayToList($enquetes_ids,'\',\'','\'').')
			)
			)
			)'
		;}
			
$sql_pays.=(')');
		
// >>> 26/03/2014 Restriction systeme par user F.WOEHL
	$systemeUser = getSystemeUserRight( $_SESSION[ "s_ppeao_user_id" ] );
	if( !empty( $systemeUser ) ) {
		$sql_pays .= ' AND ref_systeme.id IN ( \''.arrayToList($systemeUser,'\',\'','\'').') ' ;
	}  
// <<< 26/03/2014 Restriction systeme par user F.WOEHL

//debug echo('<pre>');print_r($sql_pays);echo('</pre>');

		
		
	$result_pays=pg_query($connectPPEAO,$sql_pays) or die('erreur dans la requete : '.$sql_pays. pg_last_error());
	$array_pays=pg_fetch_all($result_pays);
	pg_free_result($result_pays);
		
	// on affiche le selecteur de pays
		echo('<div id="step_3_pays" class="level_div">');
		echo('<p>pays</p>');
		echo('<select id="pays" name="pays[]" size="10" multiple="multiple" class="level_select" style="min-width:10em"
			onchange="javascript:refreshSystemes([\''.arrayToList($campagnes_ids,'\',\'','').'\'], [\''.arrayToList($enquetes_ids,'\',\'','').'\'])"
			>');
			foreach($array_pays as $pays) {
				// si la valeur est dans l'url, on la selectionne
				$urlPays=array();
				if (isset($_GET["pays"])) {$urlPays=$_GET["pays"];}
				if (in_array($pays["id"],$urlPays)) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$pays["id"].'" '.$selected.'>'.$pays["nom"].'</option>');
			} // end foreach
		echo('</select>');
		echo('</div>');
		echo('<div class="level_div"> &gt; </div>');
		// on affiche le selecteur de systemes
		echo('<div id="step_3_systemes" class="level_div">');
		echo('<p>syst&egrave;mes</p>');
		// si aucun pays n'est selectionne on affiche un select vide mais on doit definir
		//if (empty($_GET["pays"])) {}
		echo('<div id="systemes_div">');
			// on n'affiche le contenu de ce select que si des valeurs de pays ont ete passees dans l'url
			if (!empty($_GET["pays"])) {
			echo('<select id="systemes" name="systemes[]" size="10" multiple="multiple" class="level_select" style="min-width:10em" onchange="toggleNextStepLink(\'systemes\',\'systemes\',\'step_3_link\');">');
			$array_systemes=listSelectSystemes($_GET["pays"],$campagnes_ids,$enquetes_ids);
			foreach($array_systemes as $systeme) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($systeme["id"],$_GET["systemes"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$systeme["id"].'" '.$selected.'>'.$systeme["libelle"].'</option>');
			} // end foreach
		echo('</select>');
		}// fin de  if if (!empty($_GET["pays"]))
		
		echo('</div>');
		echo('</div>');
	echo('</form>');
	// on affiche le lien permettant de passer a la selection temporelle
	// on prepare l'url pour construire le lien : on enleve les pays et systemes eventuellement selectionnes
	$url=$_SERVER["FULL_URL"];
	$url=removeQueryStringParam($url,'open');
	$url=removeQueryStringParam($url,'pays\[\]');
	$url=removeQueryStringParam($url,'systemes\[\]');
	echo('<p id="step_3_link" class="clear" style="display:none;"><a href="#" class="next_step" onclick="javascript:goToNextStep(3,\''.$url.'\');return false;">ajouter et passer &agrave; la s&eacute;lection temporelle &gt;&gt;</a></p>');
		// on affiche le texte d'aide
	afficheAide("geographie");
	echo('</div>'); // end div id=step_3
	// on reinitialise les parametres de selection stockes dans la session
	$_SESSION["selection_1"]=array();
	break;
	default:
	// on en est a une etape ulterieure, on affiche le resume textuel
	echo('<div id="step_3">');
		echo("<h2>syst&egrave;mes</h2>");
		if (!empty($_GET["systemes"])) {
			// on recupere la liste des systemes selectionnes
			$systeme_id='\''.arrayToList($_GET["systemes"],'\',\'','\'');
			$sql='SELECT DISTINCT ref_systeme.id, ref_systeme.libelle, ref_systeme.ref_pays_id, ref_pays.nom FROM ref_systeme,ref_pays WHERE ref_systeme.id IN ('.$systeme_id.') AND (ref_pays_id=ref_pays.id) ORDER BY ref_pays_id';
			$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
			$array=pg_fetch_all($result);
			pg_free_result($result);
			
			$lePays='';
			$array_pays_systemes=array();
			$array_pays=$_GET["pays"];
			// on recupere les noms des pays
			$sql='SELECT id,nom FROM ref_pays WHERE id IN (\''.arrayToList($_GET["pays"],'\',\'','\'').')';
			$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
			$array2=pg_fetch_all($result);
			pg_free_result($result);
			foreach($array2 as $row) {
				$pays[$row["id"]]=$row["nom"];
			}
		
			// on groupe les systemes d'un meme pays
			foreach($array as $systeme) {
				if ($systeme["ref_pays_id"]!=$lePays) {
					$lePays=$systeme["ref_pays_id"];
					// on stocke la liste des pays pour lesquels des systemes ont ete selectionnes
					$array_pays_systemes[]=$lePays;
					}
				$array_systemes[$lePays][]=$systeme["libelle"];
			}
			
			$liste_systemes='';
			foreach($array_systemes as $key=>$value) {				
				if (empty($value)) {$liste_systemes.=$pays[$key].' : tous; ';}
				else {$liste_systemes.=$pays[$key].' : '.arrayToList($value,', ','; ');}
			}
						
			echo("<p>syst&egrave;mes : $liste_systemes</p>");
		}
	// le lien permettant d'editer la selection des systemes
	$edit_link=prepareSelectionEditLink(3);
	echo('<p id="edit_systemes" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection des syst&egrave;mes...</a></p>');	
	echo('</div>');
	break;

	} // end switch($_GET["step"])
// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==4) {
echo('</div></div>');}
}

//******************************************************************************
// on affiche le selecteur de periode
function affichePeriode() {

global $connectPPEAO; // la connexion a la base
global $campagnes_ids; // la liste des campagnes deja selectionnees
global $enquetes_ids; // la liste des enquetes deja selectionnees	

// si l'on en est a l'etape en question, on affiche le selecteur
switch ($_GET["step"]) {
	case NULL:
	case 0:
	case 1:
	case 2: 
	case 3:
	// on n'est pas encore arrive a cette etape, on n'affiche rien 
	break;
	case 4:
	// on est arrive a cette etape, on affiche le formulaire	
	echo('<div id="step_4">');	
	// si il y a des campagnes ou des enquetes
	
	if (!empty($campagnes_ids) || !empty($enquetes_ids)) {
	echo('<form id="step_4_form" name="step_4_form" target="/extraction/selection/selection.php?choix_especes='.$choix.'" method="GET">');
	echo("<h2>s&eacute;lectionner une periode d&#x27;int&eacute;r&ecirc;t</h2>");
	// on determine les periodes couvertes par les campagnes filtrees
	if (!empty($campagnes_ids[0])) {
	$sql_c='SELECT MIN(c.date_debut) as campagne_debut, MAX(c.date_fin) as campagne_fin 
			FROM exp_campagne c 
			WHERE c.id IN (\''.arrayToList($campagnes_ids,'\',\'','').'\')';
	$result_c=pg_query($connectPPEAO,$sql_c) or die('erreur dans la requete : '.$sql_c. pg_last_error());
	$array_c=pg_fetch_all($result_c);
	pg_free_result($result_c);} else {$array_c[]=array("campagne_debut"=>'9999-99-99',"campagne_fin"=>'0000-00-00');}
	
	// on determine les periodes couvertes par les campagnes filtrees
	if (!empty($enquetes_ids[0])) {
	$sql_e='SELECT MIN(e.date_debut) as enquete_debut, MAX(e.date_fin) as enquete_fin 
			FROM art_periode_enquete e 
			WHERE e.id IN (\''.arrayToList($enquetes_ids,'\',\'','').'\')';
	$result_e=pg_query($connectPPEAO,$sql_e) or die('erreur dans la requete : '.$sql_e. pg_last_error());
	$array_e=pg_fetch_all($result_e);
	pg_free_result($result_e);} else {$array_e[]=array("enquete_debut"=>'9999-99-99',"enquete_fin"=>'0000-00-00');}
	// on choisit la date de debut la plus ancienne et la date de fin la plus recente
	$from=array();
	$to=array();
	if ($array_c[0]["campagne_debut"]<$array_e[0]["enquete_debut"]) {$from=getdate(strtotime($array_c[0]["campagne_debut"]));} else {$from=getdate(strtotime($array_e[0]["enquete_debut"]));}
	if ($array_c[0]["campagne_fin"]>$array_e[0]["enquete_fin"]) {$to=getdate(strtotime($array_c[0]["campagne_fin"]));} else {$to=getdate(strtotime($array_e[0]["enquete_fin"]));}


	$debut["annee"]=$from["year"];
	$debut["mois"]=$from["mon"];
	$debut["jour"]=$from["mday"];
	$fin["annee"]=$to["year"];
	$fin["mois"]=$to["mon"];
	$fin["jour"]=$to["mday"];

	echo('<p>(p&eacute;riode couverte : de '.$debut["annee"].'-'.number_pad($debut["mois"],2).'-'.$debut["jour"].' &agrave; '.$fin["annee"].'-'.number_pad($fin["mois"],2).'-'.$fin["jour"].')</p>');

	
	// la ligne pour la date de debut
	echo('<div id="debut">de ');
	// les annees
	echo('<div id="div_d_a">');
	echo('<select id="d_a", name="d_a", onchange="javascript:refreshPeriode(\'d_a\',\''.$debut["annee"].'\',\''.$debut["mois"].'\',\''.$fin["annee"].'\',\''.$fin["mois"].'\');">');
	// la premiere ligne est "vide"
	echo('<option value="-1">-ann&eacute;e-</option>');
	$i=$debut["annee"];$end=$fin["annee"];
	// on cree un <option>  par annee
	while ($i<=$end) {
		// si l'annee a ete passee dans l'url
		if ($i==$_GET["d_a"]) {$selected=' selected="selected" ';} else {$selected='';} 
		echo('<option value="'.$i.'" '.$selected.'>'.$i.'</option>');
		$i++;
	}
	echo('</select>');
	echo("</div>"); // fin de div div_d_a
	// les mois sont affiches uniquement si une annee a ete choisie
	echo('<div id="div_d_m">');
	if (!empty($_GET["d_a"])) {
	echo('<select id="d_m" name="d_m" onchange="javascript:refreshPeriode(\'d_m\',\''.$debut["annee"].'\',\''.$debut["mois"].'\',\''.$fin["annee"].'\',\''.$fin["mois"].'\');"">');
	
	// la premiere ligne est "vide"
	echo('<option value="-1">-mois-</option>');	
	$premier_mois=1;$dernier_mois=12;
	// cas particuliers des annees limites : il se peut que les douze mois de ces annees ne soient pas disponibles
	if ($_GET["d_a"]==$fin["annee"]) {$premier_mois=1;$dernier_mois=$fin["mois"];}
	if ($_GET["d_a"]==$debut["annee"]) {$premier_mois=$debut["mois"];$dernier_mois=12;}
	if ($_GET["d_a"]==$fin["annee"] && $_GET["d_a"]==$debut["annee"])
		{$premier_mois=$debut["mois"];$dernier_mois=$fin["mois"];}
	$i=$premier_mois;
	while ($i<=$dernier_mois) {
		// si le mois a ete passe dans l'url
		if ($i==$_GET["d_m"]) {$selected=' selected="selected" ';} else {$selected='';} 
		echo('<option value="'.$i.'" '.$selected.'>'.number_pad($i,2).'</option>');
		$i++;
	}
	echo('</select>');
} // fin de if (!empty($_GET["d_a"]))
	echo('</div>'); // fin de div div_d_m
	echo('</div>'); // fin de div debut
	
	// la ligne pour la date de fin, dont on n'on n'affiche le contenu que si une annee et un mois de debut ont ete choisis
	echo('<div id="fin">&nbsp;&nbsp;&agrave; ');
	echo('<div id="div_f_a">');
	if (!empty($_GET["d_a"]) && !empty($_GET["d_m"])) {
	// les annees
	echo('<select id="f_a", name="f_a" onchange="javascript:refreshPeriode(\'f_a\',\''.$debut["annee"].'\',\''.$debut["mois"].'\',\''.$fin["annee"].'\',\''.$fin["mois"].'\');"">');
	// la premiere ligne est "vide"
	echo('<option value="-1">-ann&eacute;e-</option>');
	$i=$_GET["d_a"];$end=$fin["annee"];
	while ($i<=$end) {
		// si l'annee a ete passee dans l'url
		if ($i==$_GET["f_a"]) {$selected=' selected="selected" ';} else {$selected='';} 
		echo('<option value="'.$i.'" '.$selected.'>'.$i.'</option>');
		$i++;
	}
	echo('</select>');
	} //fin de 	if (!empty($_GET["d_a"]) && !empty($_GET["d_m"]))
	echo ("</div>"); //	div id="div_f_a"
	
	// les mois sont affiches uniquement si une annee a ete choisie
	echo('<div id="div_f_m">');
	if (!empty($_GET["d_a"]) && !empty($_GET["d_m"]) && !empty($_GET["f_a"])) {
	echo('<select id="f_m" name="f_m" onchange="refreshPeriode(\'f_m\',\'\',\'\',\'\',\'\');">');
	// la premiere ligne est "vide"
	echo('<option value="-1">-mois-</option>');
	$premier_mois=1;$dernier_mois=12;
	// cas particuliers des annees limites : il se peut que les douze mois de ces annees ne soient pas disponibles
	if ($_GET["f_a"]==$fin["annee"]) {$premier_mois=1;$dernier_mois=$fin["mois"];}
	if ($_GET["f_a"]==$debut["annee"]) {$premier_mois=$debut["mois"];$dernier_mois=12;}
	if ($_GET["f_a"]==$fin["annee"] && $_GET["f_a"]==$debut["annee"])
		{$premier_mois=$_GET["d_m"];$dernier_mois=$fin["mois"];}
	if ($_GET["f_a"]==$_GET["d_a"])
		{$premier_mois=$_GET["d_m"];$dernier_mois=$fin["mois"];}
	$i=$premier_mois;
	while ($i<=$dernier_mois) {
		// si le mois a ete passe dans l'url
		if ($i==$_GET["f_m"]) {$selected=' selected="selected" ';} else {$selected='';} 
		echo('<option value="'.$i.'" '.$selected.'>'.number_pad($i,2).'</option>');
		$i++;
	}
	echo('</select>');
	} // fin de  if (!empty($_GET["d_a"]) && !empty($_GET["d_m"]) && !empty($_GET["f_a"]))
	echo("</div>"); // fin de div id="div_f_m"
	echo('</div>'); // fin de div id=fin
	echo('</form>');
	// si la selection de periode est terminee (i.e. une valeur de f_m est choisie)
	// on affiche le lien permettant de passer a la suite	
	$url=$_SERVER['FULL_URL'];
	$url=removeQueryStringParam($url,'d_a');
	$url=removeQueryStringParam($url,'d_m');
	$url=removeQueryStringParam($url,'f_a');
	$url=removeQueryStringParam($url,'f_m');
	
	echo('<p id="step_4_link"  class="clear">');
	if (!empty($_GET["f_m"])) {
	echo('<a href="#" class="next_step" onclick="javascript:goToNextStep(4,\''.$url.'\');return false;">ajouter et choisir un type d&#x27;exploitation &gt;&gt;</a>');}
	echo('</p>');
	// on affiche le texte d'aide
	afficheAide("periode");
}
	echo('</div>'); // fin de div id="step_4"
	echo('<br class="clear" />');
	break;
	default:
	// on a depasse cette etape, on affiche le resume textuel
	echo('<div id="step_4">');
	echo("<h2>p&eacute;riode d&#x27;int&eacute;r&ecirc;t</h2>");
	echo('<p>de '.number_pad($_GET["d_m"],2).'/'.$_GET["d_a"].' &agrave; '.number_pad($_GET["f_m"],2).'/'.$_GET["f_a"].'</p>');
	// le lien permettant d'editer la selection de la periode
	$edit_link=prepareSelectionEditLink(4);
	echo('<p id="edit_periode" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection de la p&eacute;riode...</a></p>');	
	echo('</div>');
	break;

	} // end switch $_GET["step"]
// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==5) {
echo('</div></div>');}
}


//******************************************************************************
// on affiche le choix du type d'exploitation
function afficheTypeExploitation() {


global $connectPPEAO; // la connexion a la base
global $campagnes_ids; // la liste des campagnes deja selectionnees
global $enquetes_ids; // la liste des enquetes deja selectionnees
global $art_ids; // la liste des enquetes disponibles pour art
global $stats_ids; // la liste des enquetes disponibles pour stats


// si l'on en est a l'etape en question, on affiche le selecteur
switch ($_GET["step"]) {
	case NULL:
	case 0:
	case 1:
	case 2: 
	case 3:
	case 4:
	// on n'est pas encore arrive a cette etape, on n'affiche rien 
	break;
	case 5:
	// on en est a cette etape, on affiche le selecteur
	echo('<div id="step_5">');
	echo('<h2>s&eacute;lectionner un type d&#x27;exploitation</h2>');
	echo('<ul>');
	$donnees_link=replaceQueryParam($_SERVER["FULL_URL"],'step',6);
	$donnees_link.='&exploit=donnees';
	$stats_link=replaceQueryParam($_SERVER["FULL_URL"],'step',6);
	$stats_link.='&exploit=stats';
	$cartes_link=replaceQueryParam($_SERVER["FULL_URL"],'step',6);
	$cartes_link.='#';
		if (!empty($campagnes_ids) || !empty($art_ids)) {
		echo('<li><a href="'.$donnees_link.'">extraction de donn&eacute;es</a></li>');}
		if (!empty($stats_ids)) {
		echo('<li><a href="'.$stats_link.'">statistiques de p&ecirc;che</a></li>');}
		/*echo('<li>graphiques</li>');
		echo('<li>indicateurs &eacute;cologiques</li>');*/
	echo('</ul>');
	// on affiche le texte d'aide
	afficheAide("type_exploitation");
	echo('</div>');
	// et on stocke les paramètres de l'URL actuelle dans une variable de session pour les passer au script suivant
	$_SESSION["selection_1"]=$_GET;
	// on enleve le param "step" puisque on le passe via l'url
	unset($_SESSION["selection_1"]["step"]);
	
	break;
	default:
	// on en est a une etape ulterieure, on affiche le resume textuel
	echo('<div id="step_5">');
	echo('<h2>type d&#x27;exploitation</h2>');
	switch ($_GET["exploit"]) {
		case "donnees":
			echo("<p>extraction de donn&eacute;es</p>");
		break;
		case "stats":
			echo("<p>statistiques de p&ecirc;che</p>");
		break;
		case "cartes":
			echo("<p>fonds de cartes</p>");
		break;
		case "graphes":
			echo("<p>graphiques</p>");
		break;
		case "indics":
			echo("<p>indicateurs &eacute;cologiques</p>");
		break;
	}
	// le lien permettant d'editer la selection du type d'exploitation
	$edit_link=prepareSelectionEditLink(5);
	echo('<p id="edit_exploit" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection du type d&#x27;exploitation...</a></p>');	
	echo('</div>');
	} // end switch

// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==6) {
echo('</div></div>');}
}

//******************************************************************************
// on affiche le choix du type de donnees
function afficheTypeDonnees() {
	
global $connectPPEAO; // la connexion a la base
global $campagnes_ids; // la liste des campagnes deja selectionnees
global $enquetes_ids; // la liste des enquetes deja selectionnees
global $compteur;
	switch($_GET["step"]) {
		// on n'est pas encore a cette etape, on n'affiche rien
		case ($_GET["step"]<6):
		break;
		case 6:
		// on en est a cette etape, on affiche le selecteur
		echo('<div id="step_6">');
		echo('<h2>s&eacute;lectionner le type de donn&eacute;es &agrave; extraire</h2>');
		if ($compteur["campagnes_total"]!=0 || $compteur["enquetes_total"]!=0) {
		echo('<ul>');
		//si il reste des campagnes
		if ($compteur["campagnes_total"]!=0) {
			$exp_link=replaceQueryParam($_SERVER["FULL_URL"],'step',7);
			$exp_link.='&donnees=exp';
		echo('<li><a href="'.$exp_link.'">donn&eacute;es de p&ecirc;che exp&eacute;rimentale</a></li>');}
		//si il reste des enquetes
		if ($compteur["enquetes_total"]!=0) {
			$art_link=replaceQueryParam($_SERVER["FULL_URL"],'step',7);
			$art_link.='&donnees=art';
		echo('<li><a href="'.$art_link.'">donn&eacute;es de p&ecirc;che artisanale</a></li>');}
		echo('</ul>');
		} else {echo('<p>aucune campagne ou p&eacute;riode d&#x27;enqu&ecirc;te disponible, veuillez modifier votre s&eacute;lection.</p>');} 
		// on affiche le texte d'aide
		afficheAide("type_donnees");
		break;
		// on a depasse cette etape, on affiche le resume textuel
		default:
		echo('<div id="step_6">');
		echo('<h2>type de donn&eacute;es &agrave; extraire</h2>');
		switch($_GET["donnees"]) {
			case "exp":
			echo('<p>donn&eacute;es de p&ecirc;che exp&eacute;rimentale</p>');
			break;
			case "art":
			echo('<p>donn&eacute;es de p&ecirc;che artisanale</p>');
			break;
		}
		// le lien permettant d'editer la selection du type de donnees a extraire
		$edit_link=prepareSelectionEditLink(6);
		echo('<p id="edit_donnees" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection du type de donn&eacute;es &agrave; extraire...</a></p>');	
		echo('</div>');
		break;
	}
// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==7) {
echo('</div></div>');}	
	
}


//******************************************************************************
// on affiche le choix des secteurs, en fonction du type de donnees a extraire (exp ou art)
function afficheSecteurs($donnees) {
	
	global $connectPPEAO;
	global $campagnes_ids;
	global $enquetes_ids;
	
	switch($_GET["step"]) {
		case 7: 
		// on en est a cette etape, on affiche le selecteur
		echo('<div id="step_7">');
			echo('<form id="step_7_form" name="step_7_form" target="/extraction/selection/selection.php" method="GET">');
		echo('<h2>s&eacute;lectionner des secteurs</h2>');
		
		// on recupere la liste des secteurs pour les campagnes ou periodes d'enquetes selectionnees
		switch($donnees) {
		// on s'interesse aux peches experimentales
			case "exp":
			$sql='SELECT DISTINCT ref_secteur.id, ref_secteur.nom as secteur, ref_systeme.libelle as systeme, ref_pays.nom as pays FROM ref_secteur, ref_systeme,ref_pays WHERE ref_secteur.ref_systeme_id IN 
				(SELECT DISTINCT ref_systeme_id FROM exp_campagne WHERE exp_campagne.id IN
					(\''.arrayToList($campagnes_ids,'\',\'','\'').')
				) 
				 AND ref_systeme.id=ref_secteur.ref_systeme_id AND ref_pays.id=ref_systeme.ref_pays_id ';
				
		
		// ajout 17/10/2010 : oubli de filtrer les secteurs selon les coups de peche des campagnes restantes
		$sql.=' AND ref_secteur.id IN (
			SELECT exp_station.ref_secteur_id FROM exp_station WHERE exp_station.id IN (
				SELECT exp_coup_peche.exp_station_id FROM exp_coup_peche WHERE exp_coup_peche.exp_campagne_id IN (
					\''.arrayToList($campagnes_ids,'\',\'','\'').')
				)
			
			)
		';
		
		// on ne retient que les secteurs des coups de peche ayant ramene les especes choisies
			if (!empty($_GET["familles"]) || !empty($_GET["especes"])) {
			// on recupere les especes correspondant aux familles selectionnees
			$fam_especes=array();
			if (isset($_GET["familles"]) && !empty($_GET["familles"])) {
				$sql_fam='SELECT DISTINCT ref_espece.id 
				FROM ref_espece 
				WHERE ref_espece.ref_famille_id 
				IN ('.arrayToList($_GET["familles"],',','').')';
			$result_fam=pg_query($connectPPEAO,$sql_fam) or die('erreur dans la requete : '.$sql_fam. pg_last_error());
			$array_fam=pg_fetch_all($result_fam);
			pg_free_result($result_fam);
			foreach($array_fam as $spec) {
				$fam_especes[]=$spec["id"];
			}
			}
			$especes_liste=array();
			if (isset($_GET["especes"])) {$especes_liste=$_GET["especes"];}
			$especes_liste=array_merge($fam_especes,$especes_liste);
			array_unique($especes_liste);
			$sql.=' AND ref_secteur.id IN (
				SELECT exp_station.ref_secteur_id FROM exp_station, exp_coup_peche WHERE exp_coup_peche.exp_station_id=exp_station.id AND exp_coup_peche.id IN (SELECT exp_fraction.exp_coup_peche_id FROM exp_fraction WHERE exp_fraction.ref_espece_id IN (\''.arrayToList($especes_liste,'\',\'','\'').')
				))';}
			
			$sql.=' ORDER BY ref_pays.nom, ref_systeme.libelle, ref_secteur.nom';
			$nextSelectionStep='campagnes';
			break; // end case exp
			
		// on s'interesse aux peches artisanales
			case "art":
			$sql='SELECT DISTINCT ref_secteur.id, ref_secteur.nom as secteur, ref_systeme.libelle as systeme, ref_pays.nom as pays FROM ref_secteur, ref_systeme,ref_pays WHERE ref_secteur.id IN 
				(SELECT DISTINCT ref_secteur_id FROM art_agglomeration WHERE art_agglomeration.id IN (
					SELECT DISTINCT art_periode_enquete.art_agglomeration_id FROM art_periode_enquete WHERE art_periode_enquete.id IN (
					\''.arrayToList($enquetes_ids,'\',\'','\'').'
						)
					)
				) 
				 AND ref_systeme.id=ref_secteur.ref_systeme_id AND ref_pays.id=ref_systeme.ref_pays_id';
				
				
			// on ne retient que les secteurs des coups de peche ayant ramene les especes choisies
			if (!empty($_GET["familles"]) || !empty($_GET["especes"])) {
			// on recupere les especes correspondant aux familles selectionnees
			$fam_especes=array();
			if (isset($_GET["familles"]) && !empty($_GET["familles"])) {
				$sql_fam='SELECT DISTINCT ref_espece.id 
				FROM ref_espece 
				WHERE ref_espece.ref_famille_id 
				IN ('.arrayToList($_GET["familles"],',','').')';
			$result_fam=pg_query($connectPPEAO,$sql_fam) or die('erreur dans la requete : '.$sql_fam. pg_last_error());
			$array_fam=pg_fetch_all($result_fam);
			pg_free_result($result_fam);
			foreach($array_fam as $spec) {
				$fam_especes[]=$spec["id"];
			}
			}
			$especes_liste=array();
			if (isset($_GET["especes"])) {$especes_liste=$_GET["especes"];}
			$especes_liste=array_merge($fam_especes,$especes_liste);
			array_unique($especes_liste);
			$sql.=' AND ref_secteur.id IN (
				SELECT art_agglomeration.ref_secteur_id FROM art_agglomeration, art_debarquement WHERE art_debarquement.art_agglomeration_id=art_agglomeration.id AND art_debarquement.id IN (SELECT art_fraction.art_debarquement_id FROM art_fraction WHERE art_fraction.ref_espece_id IN (\''.arrayToList($especes_liste,'\',\'','\'').')
				))';}
				
				
				$sql.=' ORDER BY ref_pays.nom, ref_systeme.libelle, ref_secteur.nom
				';
			$nextSelectionStep='agglom&eacute;rations';
			break; // end case art
			
		}
			
			//debug 			echo('<pre>sql pour secteurs <br>');print_r($sql);echo('</pre>');
			
			
			$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
			$secteurs=pg_fetch_all($result);
			pg_free_result($result);
			

			// on affiche le select
			echo('<select id="secteurs" name="secteurs[]" size="10" multiple="multiple" class="level_select" onchange="javascript:toggleNextStepLink(\'secteurs\',\'secteurs\',\'step_7_link\');">');
			foreach($secteurs as $secteur) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($secteur["id"],$_GET["secteurs"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$secteur["id"].'" '.$selected.'>('.$secteur["pays"].'/'.$secteur["systeme"].') '.$secteur["secteur"].'</option>');
			} // end foreach
			echo('</select>');
			
			// le bouton permettant de tout/rien selectionner
			echo('<p style="clear:left;display:block;padding:4px 0px"><a href="#" onclick="toggleSelectSelection(\'secteurs\',\'all\',\'step_7_link\');return false;" class="link_button small">tout s&eacute;lectionner</a>&nbsp;<a href="#" onclick="toggleSelectSelection(\'secteurs\',\'none\',\'step_7_link\');return false;" class="link_button small">tout d&eacute;s&eacute;lectionner</a></p>');
			
			// on affiche le lien permettant de passer a la selection temporelle
			// on prepare l'url pour construire le lien : on enleve les secteurs eventuellement selectionnes
			$url=$_SERVER["FULL_URL"];
			$url=removeQueryStringParam($url,'open');
			$url=removeQueryStringParam($url,'secteurs\[\]');
			echo('<p id="step_7_link" class="clear" style="display:none;"><a href="#" class="next_step" onclick="javascript:goToNextStep(7,\''.$url.'\');return false;">ajouter et passer &agrave; la s&eacute;lection des '.$nextSelectionStep.' &gt;&gt;</a></p>');
		echo('</form>');
		// on affiche le texte d'aide
		afficheAide("secteurs");
		echo('</div>');
		break; // end case step=7
		
		// on a depasse cette etape, on affiche le resume textuel
		case ($_GET["step"]>7):
		echo('<div id="step_7">');
		echo('<h2>secteurs</h2>');
		if (!empty($_GET["secteurs"])) {
		$secteurs_id='\''.arrayToList($_GET["secteurs"],'\',\'','\'');
		$sql='SELECT DISTINCT ref_secteur.nom as secteur, ref_systeme.libelle as systeme, ref_pays.nom as pays 
				FROM ref_secteur , ref_systeme, ref_pays
				WHERE ref_secteur.id IN ('.$secteurs_id.') 
				 AND ref_systeme.id=ref_secteur.ref_systeme_id AND ref_pays.id=ref_systeme.ref_pays_id 
				 ORDER BY ref_pays.nom, ref_systeme.libelle, ref_secteur.nom';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
		foreach ($array as $secteur) {$secteurs_noms[]=$secteur["secteur"].' ('.$secteur["pays"].'/'.$secteur["systeme"].')';}
		$liste_secteurs=arrayToList($secteurs_noms,', ','.');}
		else {
			$liste_secteurs="tous";
		}
		echo("<p>$liste_secteurs</p>");
		// le lien permettant d'editer la selection des secteurs
		$edit_link=prepareSelectionEditLink(7);
		echo('<p id="edit_secteurs" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection des secteurs...</a></p>');
		echo('</div>');
		break;
	}
	
// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==8) {
echo('</div></div>');}	
	
}



//******************************************************************************
// on affiche le choix des campagnes
function afficheCampagnes() {

global $compteur;
global $connectPPEAO;
	switch ($_GET["step"]) {
		// on n'est pas encore la, on n'affiche rien
		case ($_GET["step"]<8):
		break;
		// on en est a cette etape on affiche le selecteur de campagnes
		case 8:
		echo('<div id="step_8">');
			echo('<form id="step_8_form" name="step_8_form" target="/extraction/selection/selection.php" method="GET">');
				echo('<h2>s&eacute;lectionner des campagnes</h2>');
				// on selectionne les campagnes disponibles
				$sql='SELECT DISTINCT c.id, c.numero_campagne, c.date_debut, c.date_fin, c.libelle as campagne, s.libelle as systeme, lower(s.libelle) as lower_systeme, p.nom as pays, lower(p.nom) as lower_pays 
				FROM exp_campagne c, ref_systeme s, ref_pays p 
				WHERE c.id IN (\''.arrayToList($compteur["campagnes_ids"],'\',\'','\'').') 
				AND c.ref_systeme_id=s.id AND s.ref_pays_id=p.id 
				ORDER BY lower_pays,lower_systeme,date_debut, date_fin';
				$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
				$array=pg_fetch_all($result);
				pg_free_result($result);
				// on affiche le select
			if (count($array)>15) {$size=15;} else {$size=10;}
			echo('<select id="campagnes" name="camp[]" size="'.$size.'" multiple="multiple" class="level_select" onchange="javascript:toggleNextStepLink(\'campagnes\',\'campagnes\',\'step_8_link\');">');
			foreach($array as $campagne) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($campagne["id"],$_GET["camp"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$campagne["id"].'" '.$selected.'>'.$campagne["pays"].':'.$campagne["systeme"].':'.$campagne["date_debut"].' au '.$campagne["date_fin"].' (n&ordm;'.$campagne["numero_campagne"].')</option>');
			} // end foreach
			echo('</select>');
			// le bouton permettant de tout/rien selectionner
			echo('<p style="clear:left;display:block;padding:4px 0px"><a href="#" onclick="toggleSelectSelection(\'campagnes\',\'all\',\'step_8_link\');return false;" class="link_button small">tout s&eacute;lectionner</a>&nbsp;<a href="#" onclick="toggleSelectSelection(\'campagnes\',\'none\',\'step_8_link\');return false;" class="link_button small">tout d&eacute;s&eacute;lectionner</a></p>');
			// on affiche le lien permettant de passer a la selection des engins de peche
			// on prepare l'url pour construire le lien : on enleve les campagnes eventuellement selectionnees
			$url=$_SERVER["FULL_URL"];
			$url=removeQueryStringParam($url,'open');
			$url=removeQueryStringParam($url,'camp\[\]');
			echo('<p id="step_8_link" class="clear" style="display:none;"><a href="#" class="next_step" onclick="javascript:goToNextStep(8,\''.$url.'\');return false;">ajouter et passer &agrave; la s&eacute;lection des engins de p&ecirc;che &gt;&gt;</a></p>');
			echo('</form>');
		// on affiche le texte d'aide
		afficheAide("campagnes");
		echo('</div>'); // end div step_8
		break;
		// on a depasse cette etape, on affiche le resume textuel
		default:
		echo('<div id="step_8">');
			echo('<h2>campagnes</h2>');
			if (!empty($_GET["camp"])) {
				$sql='SELECT DISTINCT c.id, c.numero_campagne, c.date_debut, c.date_fin, c.libelle as campagne, s.libelle as systeme, lower(s.libelle) as lower_systeme, p.nom as pays, lower(p.nom) as lower_pays 
				FROM exp_campagne c, ref_systeme s, ref_pays p 
				WHERE c.id IN (\''.arrayToList($_GET["camp"],'\',\'','\'').') 
				AND c.ref_systeme_id=s.id AND s.ref_pays_id=p.id 
				ORDER BY lower_pays,lower_systeme,date_debut, date_fin';
				$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
				$array=pg_fetch_all($result);
				pg_free_result($result);
				echo('<ul>');
				foreach($array as $campagne) {
				// si la valeur est dans l'url, on la selectionne
				echo('<li>'.$campagne["pays"].':'.$campagne["systeme"].':'.$campagne["date_debut"].' au '.$campagne["date_fin"].'</li>');
			} // end foreach
			echo('</ul>');
			} // end if !empty($_GET["camp"])
			else {
				echo('<p>toutes</p>');
			}
		// le lien permettant d'editer la selection des campagnes
		$edit_link=prepareSelectionEditLink(8);
		echo('<p id="edit_campagnes"><a href="'.$edit_link.'">modifier la s&eacute;lection des campagnes...</a></p>');
		echo('</div>'); // end div step_8
		break;
		
		
	} // end switch $step
	
// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==9) {
echo('</div></div>');}

}



//******************************************************************************
// on affiche le choix des engins
function afficheEngins() {
global $compteur;
global $connectPPEAO;

	switch($_GET["step"]) {
		// on n'est pas encore la, on n'affiche rien
		case ($_GET["step"]<9):
		break;
		case 9:
		echo('<div id="step_9">');
			echo('<form id="step_9_form" name="step_9_form" target="/extraction/selection/selection.php" method="GET">');
				echo('<h2>s&eacute;lectionner des engins de p&ecirc;che</h2>');
				$sql='SELECT e.id, e.libelle FROM exp_engin e 
				WHERE e.id IN (
					SELECT exp_engin_id FROM exp_coup_peche 
					WHERE exp_coup_peche.id IN (\''.arrayToList($compteur["coups_ids"],'\',\'','\'').')
				)';
				$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
				$array=pg_fetch_all($result);
				pg_free_result($result);
				echo('<select id="engins" name="eng[]" size="10" multiple="multiple" class="level_select" onchange="javascript:toggleNextStepLink(\'engins\',\'engins\',\'step_9_link\');">');
			foreach($array as $engin) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($engin["id"],$_GET["eng"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$engin["id"].'" '.$selected.'>'.$engin["libelle"].'</option>');
			} // end foreach
			echo('</select>');
			// le bouton permettant de tout/rien selectionner
			echo('<p style="clear:left;display:block;padding:4px 0px"><a href="#" onclick="toggleSelectSelection(\'engins\',\'all\',\'step_9_link\');return false;" class="link_button small">tout s&eacute;lectionner</a>&nbsp;<a href="#" onclick="toggleSelectSelection(\'engins\',\'none\',\'step_9_link\');return false;" class="link_button small">tout d&eacute;s&eacute;lectionner</a></p>');
			// on affiche le lien permettant de passer au choix des filieres
			// on prepare l'url pour construire le lien : on enleve les campagnes eventuellement selectionnees
			$url=$_SERVER["FULL_URL"];
			$url=removeQueryStringParam($url,'open');
			$url=removeQueryStringParam($url,'eng\[\]');
			echo('<p id="step_9_link" class="clear" style="display:none;"><a href="#" class="last_step" onclick="javascript:goToNextStep(9,\''.$url.'\');return false;">finaliser la s&eacute;lection...</a></p>');
			echo('</form>');
		// on affiche le texte d'aide
		afficheAide("engins");
		echo('</div>'); // end div step_9
		break;
		// on a depasse cette etape, on affiche le resume textuel
		default:
		echo('<div id="step_9">');
			echo('<h2>engins de p&ecirc;che</h2>');
			$sql='SELECT e.id, e.libelle FROM exp_engin e 
				WHERE e.id IN (
					SELECT exp_engin_id FROM exp_coup_peche 
					WHERE exp_coup_peche.id IN (\''.arrayToList($compteur["coups_ids"],'\',\'','\'').')
				)';
				$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
				$array=pg_fetch_all($result);
				pg_free_result($result);
				
				foreach($array as $row) {$engins[]=$row["libelle"];}
				$engins_liste=arrayToList($engins,', ','.');
				echo('<p>'.$engins_liste.'</p>');
		echo('</div>'); // end div step_9
		// on stocke l'URL de la selection dans une variable de session
		$_SESSION["selection_url"]=$_SERVER["FULL_URL"];
		// le lien permettant d'editer la selection des campagnes
		$edit_link=prepareSelectionEditLink(9);
		echo('<p id="edit_engins" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection des engins de p&ecirc;che...</a></p>');
		break;
	} // end switch step
	
// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==10) {
echo('</div></div>');}
if ($_GET["step"]>9) {
	
	$url='/extraction/selection/selection_finalisation.php?'.$_SERVER["QUERY_STRING"];
	
	echo('<p>Le processus de s&eacute;lection des donn&eacute;es est termin&eacute;, cliquez sur le lien ci-dessous pour choisir les variables dont vous voulez exporter les valeurs.</p>Vous pouvez &eacute;galement revoir ou modifier votre s&eacute;lection en cliquant sur l&#x27;un des liens [modifier ma s&eacute;lection].<p>');
	echo('<div id="choix_filiere" class="clear"><a id="link_filieres" href="#" class="last_step" onclick="javascript:goToChoixFilieres(\''.$url.'\');return false;">choisir une fili&egrave;re d&#x27;exploitation...</a><br /><br />');
// on affiche le texte d'aide
afficheAide("filieres");

// on affiche la liste des documents disponibles relatifs a la selection geographique de l'utilisateur
afficheMetadonnees();

echo('</div>');}

}



//******************************************************************************
function afficheAgglomerations() {
global $compteur;
global $connectPPEAO;
	switch($_GET["step"]) {
	// on n'est pas encore la, on n'affiche rien
		case ($_GET["step"]<8):
		break;
		// on en est a cette etape, on affiche le selecteur d'agglomerations
		case 8:
		echo('<div id="step_8">');
			
			if (!empty($compteur["enquetes_ids"])) {
			echo('<form id="step_8_form" name="step_8_form" target="/extraction/selection/selection.php" method="GET">');
				echo('<h2>s&eacute;lectionner des agglom&eacute;rations</h2>');
				$sql='SELECT DISTINCT a.nom as agglo, a.id, p.nom as pays, s.nom as secteur, sy.libelle as systeme
				FROM art_agglomeration a, ref_pays p, ref_secteur s, ref_systeme sy
				WHERE a.id IN (
					SELECT art_agglomeration_id FROM art_periode_enquete 
					WHERE art_periode_enquete.id IN (\''.arrayToList($compteur["enquetes_ids"],'\',\'','\'').')
				)
				AND a.ref_secteur_id=s.id AND s.ref_systeme_id=sy.id AND sy.ref_pays_id=p.id 
				';
				$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
				$array=pg_fetch_all($result);
				pg_free_result($result);
				echo('<select id="agglo" name="agglo[]" size="10" multiple="multiple" class="level_select" onchange="javascript:toggleNextStepLink(\'agglo\',\'agglo\',\'step_8_link\');">');
			foreach($array as $agglo) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($agglo["id"],$_GET["agglo"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$agglo["id"].'" '.$selected.'>('.$agglo["pays"].'/'.$agglo["systeme"].'/'.$agglo["secteur"].') '.$agglo["agglo"].'</option>');
			} // end foreach
			echo('</select>');
			// le bouton permettant de tout/rien selectionner
			echo('<p style="clear:left;display:block;padding:4px 0px"><a href="#" onclick="toggleSelectSelection(\'agglo\',\'all\',\'step_8_link\');return false;" class="link_button small">tout s&eacute;lectionner</a>&nbsp;<a href="#" onclick="toggleSelectSelection(\'agglo\',\'none\',\'step_8_link\');return false;" class="link_button small">tout d&eacute;s&eacute;lectionner</a></p>');
			// on affiche le lien permettant de passer au choix des filieres
			// on prepare l'url pour construire le lien : on enleve les campagnes eventuellement selectionnees
			$url=$_SERVER["FULL_URL"];
			$url=removeQueryStringParam($url,'open');
			$url=removeQueryStringParam($url,'agglo\[\]');
			echo('<p id="step_8_link" class="clear" style="display:none;"><a href="#" class="next_step" onclick="javascript:goToNextStep(8,\''.$url.'\');return false;">ajouter et passer au choix des p&eacute;riodes d&#x27;enqu&ecirc;te &gt;&gt;</a></p>');
			echo('</form>');}
			// sinon on demande a l'utilisateur de modifier sa selection
			else {
				echo('<p>aucune p&eacute;riode d&#x27;enqu&ecirc;te ne correspond, veuillez modifier votre s&eacute;lection.</p>');
			}
			// on affiche le texte d'aide
		afficheAide("agglomerations");
		echo('</div>'); // end div step_8
		break;
		// on a depasse cette etape, on affiche le resume textuel
		default:
		echo('<div id="step_8">');
			echo('<h2>agglom&eacute;rations</h2>');
			if (!empty($_GET["agglo"])) {
			$sql='SELECT DISTINCT a.nom as agglo, a.id, p.nom as pays, s.nom as secteur, sy.libelle as systeme
				FROM art_agglomeration a, ref_pays p, ref_secteur s, ref_systeme sy
				WHERE a.id IN (
					SELECT art_agglomeration_id FROM art_periode_enquete 
					WHERE art_periode_enquete.id IN (\''.arrayToList($compteur["enquetes_ids"],'\',\'','\'').')
				)
				AND a.ref_secteur_id=s.id AND s.ref_systeme_id=sy.id AND sy.ref_pays_id=p.id 
				';
				$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
				$array=pg_fetch_all($result);
				pg_free_result($result);
				;
				foreach ($array as $agglo) 
					{$agglo_noms[]=$agglo["agglo"].' ('.$agglo["pays"].'/'.$agglo["systeme"].'/'.$agglo["secteur"].')';}
				$liste_agglos=arrayToList($agglo_noms,', ','.');
			}
			else {
			$liste_agglos="toutes";
			}
		echo('<p>'.$liste_agglos.'</p>');
			// le lien permettant d'editer la selection des agglos
		$edit_link=prepareSelectionEditLink(8);
		echo('<p id="edit_agglos" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection des agglom&eacute;rations...</a></p>');
		echo('</div>'); // end div step_8
		break;
		
	}
// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==9) {
echo('</div></div>');}
}

//******************************************************************************
// affiche le selecteur de periodes d'enquete
function affichePeriodeEnquetes() {
global $compteur;
global $connectPPEAO;
	switch ($_GET["step"]) {
		// on n'est pas encore la, on n'affiche rien
		case ($_GET["step"]<9):
		break;
		// on en est a cette etape on affiche le selecteur d'enquetes
		case 9:
		echo('<div id="step_9">');
			echo('<form id="step_9_form" name="step_9_form" target="/extraction/selection/selection.php" method="GET">');
				echo('<h2>s&eacute;lectionner des p&eacute;riodes d&#x27;enqu&ecirc;te</h2>');
				// on selectionne les enquetes disponibles
				$sql='SELECT DISTINCT e.id, e.description, e.annee, e.mois, a.nom as agglo, lower(a.nom) as lower_agglo, s.nom as secteur, lower(s.nom) as lower_secteur, sy.libelle as systeme, lower(sy.libelle) as lower_systeme, p.nom as pays, lower(p.nom) as lower_pays FROM art_periode_enquete e, ref_pays p, ref_systeme sy, ref_secteur s, art_agglomeration a 
				WHERE e.id IN (\''.arrayToList($compteur["enquetes_ids"],'\',\'','\'').') 
				AND e.art_agglomeration_id=a.id AND a.ref_secteur_id=s.id ';
				if (!empty($_GET["agglo"])) {
				$sql.='AND e.art_agglomeration_id IN (\''.arrayToList($_GET["agglo"],'\',\'','\'').') ';
				}
				$sql.='	AND s.ref_systeme_id=sy.id AND sy.ref_pays_id=p.id  
				ORDER BY lower_pays,lower_systeme, lower_secteur, annee, mois';
				$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
				$array=pg_fetch_all($result);
				pg_free_result($result);
				// on affiche le select
			if (count($array)>15) {$size=15;} else {$size=10;}
			echo('<select id="enquetes" name="enq[]" size="'.$size.'" multiple="multiple" class="level_select" onchange="javascript:toggleNextStepLink(\'enquetes\',\'enquetes\',\'step_9_link\');">');
			foreach($array as $enquete) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($enquete["id"],$_GET["enq"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$enquete["id"].'" '.$selected.'>'.$enquete["pays"].':'.$enquete["systeme"].':'.$enquete["secteur"].':'.$enquete["agglo"].':'.$enquete["annee"].'-'.number_pad($enquete["mois"],2).'</option>');
			} // end foreach
			echo('</select>');
			// le bouton permettant de tout/rien selectionner
			echo('<p style="clear:left;display:block;padding:4px 0px"><a href="#" onclick="toggleSelectSelection(\'enquetes\',\'all\',\'step_9_link\');return false;" class="link_button small">tout s&eacute;lectionner</a>&nbsp;<a href="#" onclick="toggleSelectSelection(\'enquetes\',\'none\',\'step_9_link\');return false;" class="link_button small">tout d&eacute;s&eacute;lectionner</a></p>');
			// on affiche le lien permettant de passer a la selection des grands types d'engins de peche
			// on prepare l'url pour construire le lien : on enleve les enquetes eventuellement selectionnees
			$url=$_SERVER["FULL_URL"];
			$url=removeQueryStringParam($url,'open');
			$url=removeQueryStringParam($url,'enq\[\]');
			echo('<p id="step_9_link" class="clear" style="display:none;"><a href="#" class="next_step" onclick="javascript:goToNextStep(9,\''.$url.'\');return false;">ajouter et passer &agrave; la s&eacute;lection des grands types d&#x27;engins de p&ecirc;che &gt;&gt;</a></p>');
			echo('</form>');
			// on affiche le texte d'aide
		afficheAide("periodes_enquete");
		echo('</div>'); // end div step_8
		break;
		// on a depasse cette etape, on affiche le resume textuel
		default:
		echo('<div id="step_9">');
			echo('<h2>p&eacute;riodes d&#x27;enqu&ecirc;te</h2>');
			if (!empty($_GET["enq"])) {
				// on selectionne les enquetes disponibles
				$sql='SELECT DISTINCT e.id, e.description, e.annee, e.mois, a.nom as agglo, lower(a.nom) as lower_agglo, s.nom as secteur, lower(s.nom) as lower_secteur, sy.libelle as systeme, lower(sy.libelle) as lower_systeme, p.nom as pays, lower(p.nom) as lower_pays FROM art_periode_enquete e, ref_pays p, ref_systeme sy, ref_secteur s, art_agglomeration a 
				WHERE e.id IN (\''.arrayToList($_GET["enq"],'\',\'','\'').') 
				AND e.art_agglomeration_id=a.id AND a.ref_secteur_id=s.id 
				AND s.ref_systeme_id=sy.id AND sy.ref_pays_id=p.id  
				ORDER BY lower_pays,lower_systeme, lower_secteur, annee, mois';
				$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
				$array=pg_fetch_all($result);
				pg_free_result($result);
				foreach ($array as $enquete) {$enquetes_noms[]=$enquete["pays"].':'.$enquete["systeme"].':'.$enquete["secteur"].':'.$enquete["agglo"].':'.$enquete["annee"].'-'.number_pad($enquete["mois"],2);}
				$liste_enquetes=arrayToList($enquetes_noms,', ','.');
			}
			else {
			$liste_enquetes="toutes";
			}
		echo('<p>'.$liste_enquetes.'</p>');
			// le lien permettant d'editer la selection des enquetes
		$edit_link=prepareSelectionEditLink(9);
		echo('<p id="edit_enquetes"><a href="'.$edit_link.'">modifier la s&eacute;lection des p&eacute;riodes d&#x27;enqu&ecirc;te...</a></p>');
		echo('</div>'); // end div step_9
		break;
	} // end switch $_GET

// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==10) {
echo('</div></div>');}
}


//******************************************************************************
// affiche le selecteur de grands types d'engins
function afficheGrandsTypesEngins($exploit) {
// $ exploit : le type d'exploitation choisi (donnees, stats)
global $compteur;
global $connectPPEAO;

$theStep=10;

// le step differe selon que l'on a affaire aux stats par agglo (10) ou generales (8) ou les peches art (10)
if ($_GET["stats"]=='gen') {$theStep=8;} else {$theStep=10;}
	switch ($_GET["step"]) {
		// on n'est pas encore la, on n'affiche rien
		case ($_GET["step"]<$theStep):
		break;
		// on en est a cette etape on affiche le selecteur de grands types d'engins
		case ($_GET["step"]==$theStep):
		echo('<div id="step_'.$theStep.'">');
			echo('<form id="step_'.$theStep.'_form" name="step_'.$theStep.'_form" target="/extraction/selection/selection.php" method="GET">');
				echo('<h2>s&eacute;lectionner des grands types d&#x27;engins de p&ecirc;che</h2>');
				// on recupere la liste des grands types d'engins correspondants aux debarquements
				$sql_gte_d='SELECT DISTINCT g.id, g.libelle FROM art_grand_type_engin g, art_debarquement d WHERE 
				d.id IN (\''.arrayToList($compteur["debarquements_ids"],'\',\'','\'').') AND d.art_grand_type_engin_id=g.id
				ORDER BY g.libelle
				';
				
				$result_gte_d=pg_query($connectPPEAO,$sql_gte_d) or die('erreur dans la requete : '.$sql_gte_d. pg_last_error());

				$array_gte_d=pg_fetch_all($result_gte_d);
				pg_free_result($result_gte_d);
				
				// test sur le nombre d'enquetes d'activites ajoute par Julien Lebranchu le 22/07/16
				// si nb>0 alors ajout de l'instruction à la requête SQL
				$activites_ids_sql = "";
				if(count($compteur["activites_ids"]) > 0){
					$activites_ids_sql = 'a.id IN (\''.arrayToList($compteur["activites_ids"],'\',\'','\'').') AND';
				}
				// on recupere la liste des grands types d'engins correspondants aux activites
				$sql_gte_a='SELECT DISTINCT g.id, g.libelle FROM art_grand_type_engin g, art_activite a WHERE 
				'.$activites_ids_sql.' a.art_grand_type_engin_id=g.id
				ORDER BY g.libelle
				';
				// fin correction du 22/07/16
				
				$result_gte_a=pg_query($connectPPEAO,$sql_gte_a) or die('erreur dans la requete : '.$sql_gte_a. pg_last_error());

				$array_gte_a=pg_fetch_all($result_gte_a);
				pg_free_result($result_gte_a);
				
				// ensuite on fusionne les deux listes de grands types d'engins
				$array_gte=array_merge($array_gte_a,$array_gte_d);
				// et enfin on elimine les valeurs en double
				$array_gte=array_unique_multidimensionnal($array_gte);
				// puis on trie le tableau en ordre alphabétique
				array_csort($array_gte,'libelle', 'SORT_ASC');
				
				echo('<select id="gteng" name="gteng[]" size="10" multiple="multiple" class="level_select" onchange="javascript:toggleNextStepLink(\'gteng\',\'gteng\',\'step_8-10_link\');">');
			foreach($array_gte as $gteng) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($gteng["id"],$_GET["gteng"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$gteng["id"].'" '.$selected.'>'.$gteng["libelle"].' ('.$gteng["id"].')</option>');
			} // end foreach
			echo('</select>');
			
			// le bouton permettant de tout/rien selectionner
			echo('<p style="clear:left;display:block;padding:4px 0px"><a href="#" onclick="toggleSelectSelection(\'gteng\',\'all\',\'step_8-10_link\');return false;" class="link_button small">tout s&eacute;lectionner</a>&nbsp;<a href="#" onclick="toggleSelectSelection(\'gteng\',\'none\',\'step_8-10_link\');return false;" class="link_button small">tout d&eacute;s&eacute;lectionner</a></p>');
			
			// on affiche le lien permettant de passer au choix des filieres
			// on prepare l'url pour construire le lien : on enleve les campagnes eventuellement selectionnees
			$url=$_SERVER["FULL_URL"];
			$url=removeQueryStringParam($url,'open');
			$url=removeQueryStringParam($url,'gteng\[\]');
			echo('<p id="step_8-10_link" class="clear" style="display:none;"><a href="#" class="last_step" onclick="javascript:goToNextStep('.$theStep.',\''.$url.'\');return false;">finaliser la s&eacute;lection...</a></p>');
			echo('</form>');
			// on affiche le texte d'aide
		afficheAide("grands_types_engins");
		echo('</div>'); // end div step_'.$theStep.'
		break;
		// on a depasse cette etape, on affiche le resume textuel
		default:
		echo('<div id="step_'.$theStep.'">');
			echo('<h2>grands types d&#x27;engins de p&ecirc;che</h2>');
			if (!empty($_GET["gteng"])) {
			
			$debs='';
			if (!empty($compteur["debarquements_ids"])) {$debs=' AND d.id IN (\''.arrayToList($compteur["debarquements_ids"],'\',\'','\'').') ';}
			$acts='';
			if (!empty($compteur["activites_ids"])) {$acts=' AND a.id IN (\''.arrayToList($compteur["activites_ids"],'\',\'','\'').') ';}
			
			$sql='SELECT DISTINCT g.id, g.libelle FROM art_grand_type_engin g, art_activite a, art_debarquement d 
				WHERE TRUE 
				'.$acts.'
				'.$debs.'
				
				AND a.art_grand_type_engin_id=g.id AND d.art_grand_type_engin_id=g.id
				ORDER BY g.libelle
				';
				$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
				$array=pg_fetch_all($result);
				pg_free_result($result);
				
				foreach($array as $row) {$gtengins[]=$row["libelle"];}
				$gtengins_liste=arrayToList($gtengins,', ','.');}
			else {
				$gtengins_liste="tous";
			}
				echo('<p>'.$gtengins_liste.'</p>');
		// le lien permettant d'editer la selection des grands types d'engins
		$edit_link=prepareSelectionEditLink($theStep);
		echo('<p id="edit_gteng" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection des grands types d&#x27;engins...</a></p>');	
		echo('</div>'); // end div step_'.$theStep.'
		

		echo('</p></div>');
		// on stocke l'URL de la selection dans une variable de session
		$_SESSION["selection_url"]=$_SERVER["FULL_URL"];	
		break; 
	} // end switch $_GET

// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
$theNextStep=$theStep+1;
if ($_GET["step"]==$theNextStep) {
echo('</div></div>');}
if ($_GET["step"]>$theStep) {
echo('<div id="choix_tables_stats">');
		echo('<p>Le processus de s&eacute;lection des donn&eacute;es est termin&eacute;, cliquez sur le lien ci-dessous pour choisir les variables dont vous voulez exporter les valeurs.<br />Vous pouvez &eacute;galement revoir ou modifier votre s&eacute;lection en cliquant sur l&#x27;un des liens [modifier ma s&eacute;lection].<p>');
		switch ($exploit) {
			case "donnees" : 
			//echo('<a id="link_filieres" class="last_step"  href="/extraction/selection/selection_finalisation.php?'.$_SERVER["QUERY_STRING"].'">choisir une fili&egrave;re d&#x27;extraction...</a>');
			// FW 20200221 use of goToChoix Filieres oublié dans le cas art
			$url='/extraction/selection/selection_finalisation.php?'.$_SERVER["QUERY_STRING"];
			echo('<div id="choix_filiere" class="clear"><a id="link_filieres" href="#" class="last_step" onclick="javascript:goToChoixFilieres(\''.$url.'\');return false;">choisir une fili&egrave;re d&#x27;exploitation...</a><br /><br />');
		// on affiche le texte d'aide
		afficheAide("filieres");
			break;
			case "stats":
			$url='/extraction/selection/selection_finalisation.php?'.$_SERVER["QUERY_STRING"];
			echo('<div id="choix_filiere" class="clear"><a id="link_filieres" href="#" class="last_step" onclick="javascript:goToChoixFilieres(\''.$url.'\');return false;">choisir une fili&egrave;re d&#x27;exploitation...</a><br /><br />');
			//echo('<a id="link_filieres"  class="last_step"  href="/extraction/selection/selection_finalisation.php?'.$_SERVER["QUERY_STRING"].'">Affiner les s&eacute;lections pour les statistiques...</a>');
		// on affiche le texte d'aide
		afficheAide("stats");
			break;
		} // end switch $exploit
echo('</div>');
// on affiche la liste des documents disponibles relatifs a la selection geographique de l'utilisateur
afficheMetadonnees();

}
}



//******************************************************************************
// on affiche le choix du type de statistiques
function afficheTypeStats() {

global $connectPPEAO; // la connexion a la base
global $campagnes_ids; // la liste des campagnes deja selectionnees
global $enquetes_ids; // la liste des enquetes deja selectionnees
global $compteur;

	switch($_GET["step"]) {
		// on n'est pas encore a cette etape, on n'affiche rien
		case ($_GET["step"]<6):
		break;
		case 6:
		// on en est a cette etape, on affiche le selecteur
		echo('<div id="step_6">');
		echo('<h2>s&eacute;lectionner le type de statistiques &agrave; extraire</h2>');
		// si il reste des enquetes
		if ($compteur["enquetes_total"]!=0) {
			$stats_link=replaceQueryParam($_SERVER["FULL_URL"],'step',7);
			$stats_gen_link=$stats_link.'&stats=gen';
			$stats_agglo_link=$stats_link.'&stats=agglo';
			echo('<ul>');
				echo('<li><a href="'.$stats_agglo_link.'">statistiques par agglom&eacute;rations</a></li>');
				echo('<li><a href="'.$stats_gen_link.'">statistiques g&eacute;n&eacute;rales</a></li>');
			echo('</ul>');
		} else {echo('<p>aucune p&eacute;riode d&#x27;enqu&ecirc;te disponible, veuillez modifier votre s&eacute;lection.</p>');} 
		// on affiche le texte d'aide
		afficheAide("type_stats");
		break;
		// on a depasse cette etape, on affiche le resume textuel
		default:
		echo('<div id="step_6">');
		echo('<h2>type de statistiques &agrave; extraire</h2>');
		switch($_GET["stats"]) {
			case "gen":
			echo('<p>statistiques g&eacute;n&eacute;rales</p>');
			break;
			case "agglo":
			echo('<p>statistiques par agglom&eacute;rations</p>');
			break;
		}
		// le lien permettant d'editer la selection du type de donnees a extraire
		$edit_link=prepareSelectionEditLink(6);
		echo('<p id="edit_stats" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection du type de statistiques &agrave; extraire...</a></p>');	
		echo('</div>');
		break;
	}

// si on est au step suivant, on ferme le div id=selection_precedente, ouvert dans selection.php
if ($_GET["step"]==7) {
echo('</div></div>');}
}


//******************************************************************************
// affiche le second selecteur de secteurs (pour les statistiques de peche)
function afficheSecteurs2() {
	
	global $connectPPEAO;
	global $enquetes_ids;
	
	switch($_GET["step"]) {
		case 7: 
		// on en est a cette etape, on affiche le selecteur
		echo('<div id="step_7">');
			echo('<form id="step_7_form" name="step_7_form" target="/extraction/selection/selection.php" method="GET">');
		echo('<h2>s&eacute;lectionner des syst&egrave;mes ou des secteurs</h2>');
		
		// on recupere la liste des systemes selectionnes
			$sql='SELECT DISTINCT ref_systeme.id, ref_systeme.libelle as systeme, ref_pays.nom as pays FROM ref_systeme, ref_pays WHERE TRUE
				';
			if (!empty($_GET["systemes"])) {
				$sql.=' AND ref_systeme.id IN (\''.arrayToList($_GET["systemes"],'\',\'','\'').')';
				}
			// on filtre la liste des systemes selectionnes en fonction des enquetes selectionnees
			if (!empty($enquetes_ids)) {
				$sql.=' AND ref_systeme.id IN 
				(SELECT DISTINCT ref_systeme_id FROM ref_secteur rs 
				WHERE rs.id IN (
					SELECT DISTINCT ref_secteur_id FROM art_agglomeration aa 
						WHERE aa.id IN (
							SELECT DISTINCT art_agglomeration_id FROM art_periode_enquete ape 
							WHERE ape.id IN (\''.arrayToList($enquetes_ids,'\',\'','\'').')
							)
				)
				)
';
			}
			$sql.=' AND ref_systeme.ref_pays_id=ref_pays.id 
				';
			$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
			$systemes2=pg_fetch_all($result);
			pg_free_result($result);
			
			// on affiche le selecteur de systemes
			echo('<div id="step_7_systemes" class="level_div">');
			echo('<p>syst&egrave;mes</p>');
			echo('<select id="systemes2" name="systemes2[]" size="10" multiple="multiple" class="level_select" style="min-width:10em" onchange="javascript:refreshSecteurs([\''.arrayToList($enquetes_ids,'\',\'','').'\']);">');
			foreach($systemes2 as $systeme2) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($systeme2["id"],$_GET["systemes2"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$systeme2["id"].'" '.$selected.'>('.$systeme2["pays"].') '.$systeme2["systeme"].'</option>');
			} // end foreach
			echo('</select>');
			
			echo('</div>');
			echo('<div class="level_div"> &gt; </div>');
			
			// on affiche le selecteur de secteurs
			echo('<div id="step_7_secteurs" class="level_div">');
			echo('<p>secteurs</p>');
			// si aucun secteur n'est selectionne on affiche un select vide
			echo('<select id="secteurs" name="secteurs[]" size="10" multiple="multiple" class="level_select" style="min-width:10em">');
			// on n'affiche le contenu de ce select que si des valeurs de systemes2 ont ete passees dans l'url
			if (!empty($_GET["systemes2"])) {
				$array_secteurs=listSelectSecteurs($_GET["systemes2"],$enquetes_ids);
				
				foreach($array_secteurs as $secteur) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($secteur["id"],$_GET["secteurs"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$secteur["id"].'" '.$selected.'>('.$secteur["systeme"].') '.$secteur["secteur"].'</option>');
			} // end foreach
			}
			
			echo('</select>');
			
			echo('</div>');
			// on affiche le lien permettant de passer a la selection temporelle
			// on prepare l'url pour construire le lien : on enleve les systèmes et secteurs eventuellement selectionnes
			$url=$_SERVER["FULL_URL"];
			$url=removeQueryStringParam($url,'open');
			$url=removeQueryStringParam($url,'systemes2\[\]');
			$url=removeQueryStringParam($url,'secteurs\[\]');
			echo('<p id="step_7_link" class="clear" style="display:none;"><a href="#" class="next_step" onclick="javascript:goToNextStep(7,\''.$url.'\');return false;">ajouter et passer &agrave; la s&eacute;lection des grands types d&#x27;engins &gt;&gt;</a></p>');
		echo('</form>');
		echo('</div>');
		break; // end case step=7
		
		// on a depasse cette etape, on affiche le resume textuel
		case ($_GET["step"]>7):
		echo('<div id="step_7">');
		echo('<h2>syst&egrave;mes ou secteurs</h2>');
		
		if (!empty($_GET["systemes2"])) {
		$systemes2_id='\''.arrayToList($_GET["systemes2"],'\',\'','\'');
		$sql='SELECT DISTINCT ref_systeme.libelle as systeme, ref_pays.nom as pays 
				FROM ref_systeme, ref_pays
				WHERE ref_systeme.id IN ('.$systemes2_id.') 
				  AND ref_pays.id=ref_systeme.ref_pays_id 
				 ORDER BY ref_pays.nom, ref_systeme.libelle';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
		foreach ($array as $systeme2) {$systemes2_noms[]=$systeme2["systeme"].' ('.$systeme2["pays"].')';}
		$liste_systemes2=arrayToList($systemes2_noms,', ','.');}
		else {
			$liste_systemes2="tous";
		}
		echo("<p>syst&egrave;mes : $liste_systemes2</p>");
		
		if (!empty($_GET["secteurs"])) {
		$secteurs_id='\''.arrayToList($_GET["secteurs"],'\',\'','\'');
		$sql='SELECT DISTINCT ref_secteur.nom as secteur, ref_systeme.libelle as systeme, ref_pays.nom as pays 
				FROM ref_secteur , ref_systeme, ref_pays
				WHERE ref_secteur.id IN ('.$secteurs_id.') 
				 AND ref_systeme.id=ref_secteur.ref_systeme_id AND ref_pays.id=ref_systeme.ref_pays_id 
				 ORDER BY ref_pays.nom, ref_systeme.libelle, ref_secteur.nom';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
		foreach ($array as $secteur) {$secteurs_noms[]=$secteur["secteur"].' ('.$secteur["pays"].'/'.$secteur["systeme"].')';}
		$liste_secteurs=arrayToList($secteurs_noms,', ','.');}
		else {
			$liste_secteurs="tous";
		}
		echo("<p>secteurs : $liste_secteurs</p>");
		// le lien permettant d'editer la selection des systemes et secteurs
		$edit_link=prepareSelectionEditLink(7);
		echo('<p id="edit_secteurs" class="edit_selection"><a href="'.$edit_link.'">modifier la s&eacute;lection des syst&egrave;mes et des secteurs...</a></p>');
		echo('</div>');
		
		// si on est juste au step suivant on ferme les div contenant la selection precedente, ouverts dans selection.php
		if ($_GET["step"]==8) {echo('</div></div>');}
		
		break;
	}
}


//******************************************************************************
// permet de recuperer les droits particuliers associes a un systeme donne
function getSystemAccessDate($systeme_id) {
// la connexion a la base
global $connectPPEAO;
// la date butoir par defaut definie dans variable.inc
global $delai_butoir;
// $systeme_id : l'id du systeme concerne
// on cherche les entrees de la table admin_acces_donnees_systemes pour $systeme_id
$sql='SELECT ref_systeme_id, date_butoir as delai_butoir, type_donnees 
		FROM admin_acces_donnees_systemes 
		WHERE ref_systeme_id='.$systeme_id;
$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$array=pg_fetch_all($result);
pg_free_result($result);
$array2=array();
if (!empty($array)) {
foreach ($array as $row) {
	$array2[$row["type_donnees"]]=$row;
}
}

$system_dates=array();


if (!empty($array2)) {
	if (!empty($array2["PE"])) {$system_dates["PE"]=array("ref_systeme_id"=>$systeme_id, "delai_butoir"=>$array2["PE"]["delai_butoir"],"type_donnees"=>"PE");}
		if (!empty($array2["PA"])) {$system_dates["PA"]=array("ref_systeme_id"=>$systeme_id, "delai_butoir"=>$array2["PA"]["delai_butoir"],"type_donnees"=>"PA");} 
			if (!empty($array["ST"])) {$system_dates["ST"]=array("ref_systeme_id"=>$systeme_id, "delai_butoir"=>$array2["ST"]["delai_butoir"],"type_donnees"=>"ST");}
	
	if (!array_key_exists('PE',$system_dates)) {$system_dates["PE"]=array("ref_systeme_id"=>$systeme_id, "delai_butoir"=>$delai_butoir,"type_donnees"=>'PE');}
	if (!array_key_exists('PA',$system_dates)) {$system_dates["PA"]=array("ref_systeme_id"=>$systeme_id, "delai_butoir"=>$delai_butoir,"type_donnees"=>'PA');}
	if (!array_key_exists('ST',$system_dates)) {$system_dates["ST"]=array("ref_systeme_id"=>$systeme_id, "delai_butoir"=>$delai_butoir,"type_donnees"=>'ST');}

	} 
	// si on n'a aucun resultat, on retourne la date butoir par defaut
	else {
		$system_dates["PE"]=array("ref_systeme_id"=>$systeme_id, "delai_butoir"=>$delai_butoir,"type_donnees"=>'PE');
		$system_dates["PA"]=array("ref_systeme_id"=>$systeme_id, "delai_butoir"=>$delai_butoir,"type_donnees"=>'PA');
		$system_dates["ST"]=array("ref_systeme_id"=>$systeme_id, "delai_butoir"=>$delai_butoir,"type_donnees"=>'ST');
		}


// $system_dates=array("ref_systeme_id","delai_butoir","type_donnees")
return $system_dates;

}


//******************************************************************************
// permet de recuperer la liste des systemes pour lesquels un acteur (utilisateur ou groupe) a acces a toutes les donnees
function getActorSystemRights($acteur_id, $acteur_type) {
// $acteur_id : id unique de l'acteur
// $acteur_type: u (utilisateur) ou g (groupe)

// la connexion a la base
global $connectPPEAO;
// on cherche les entrees de la table admin_acces_donnees_acteurs pour $acteur_id et $acteur_type
$sql='SELECT ref_systeme_id, type_donnees 
		FROM admin_acces_donnees_acteurs 
		WHERE ref_acteur_id='.$acteur_id.' AND acteur_type=\''.$acteur_type.'\'';
$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$array=pg_fetch_all($result);
pg_free_result($result);

$acteur_droits=array();

if (!empty($array)) {
	$acteur_droits=$array;
	;
} else {
	$acteur_droits=array("ref_systeme_id"=>"","type_donnees"=>"");
}

// $acteur_droits
return $acteur_droits;

}

//******************************************************************************
// permet de recuperer la liste des systemes pour lesquels un utilisateur a acces a toutes les donnees
// combine les droits de l'utilisateur et du/des groupe(s) au(x)quel(s) il appartient
function getUserSystemRights($user_id) {
// $ user_id : id unique de l'utilisateur

// la connexion a la base
global $connectPPEAO;

// on commence par recuperer les droits de l'utilisateur
$user_droits=getActorSystemRights($user_id, 'u');



// puis on recupere les droits des groupes auxquels appartient cet utilisateur
$sql='SELECT DISTINCT group_id FROM admin_j_user_group WHERE user_id='.$user_id;
$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$array=pg_fetch_all($result);
pg_free_result($result);

if (!empty($array)) {
// pour chaque groupe, on determine les droits d'acces
$groupes_droits=array();
foreach ($array as $groupe) {
	$groupe_droits=getActorSystemRights($groupe["group_id"], 'g');
	array_push($groupes_droits,$groupe_droits[0]);
} 

// ensuite on fusionne les deux listes de droits
$user_droits=array_merge($user_droits,$groupes_droits);

// et enfin on elimine les valeurs en double
$user_droits=array_unique_multidimensionnal($user_droits);

// on trie le tableau pour faire plus propre
array_csort($user_droits, "ref_systeme_id","SORT_ASC");


return $user_droits;

}
	
}

//******************************************************************************
// permet de savoir si un utilisateur a acces ou pas a une campagne ou periode d'enquete
function userHasAccessToUnit($unit_id, $domaine, $exploit,$user_droits) {
// $domaine : "exp" pour les pechex experimentales ou "art" pour les peches artisanales
// $exploit : vide ou "stats" pour les statistiques de peche
// $unit_id l'id unique de la campagne (si $domaine=exp) ou de la periode d'enquete (si $domaine=art)
// $user_droits : le tableau contenant les droits d'acces particuliers de l'utilisateur connecte

// la connexion a la base
global $connectPPEAO;

// que veut-on faire des donnees?
$utilisation='';
if ($domaine=='exp') {$utilisation='exp';}
if ($domaine=='art' && $exploit=='stats') {$utilisation='stats';}
if ($domaine=='art' && $exploit!='stats') {$utilisation='art';}

// pour l'unite en cours, on recupere la date et le systeme, 
// de facon differente selon que l'on a affaire a une campagne ou une periode d'enquete
switch($utilisation) {
	// peche experimentale
	case "exp":
	$sql='SELECT ref_systeme_id as systeme, date_debut FROM exp_campagne WHERE id='.$unit_id;
	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	$array=pg_fetch_all($result);
	pg_free_result($result);
	
	$unit_details=$array[0];
	$date=getdate(strtotime($unit_details["date_debut"]));
	$unit_details["annee"]=$date["year"];
	// on initialise la variable qui stocke si le user a acces a toutes les donnees ou pas	
	$acces_complet=FALSE;
	// on recupere les droits de l'utilisateur sur le systeme concerne
	// on s'interesse aux donnees de PE
	$ce_systeme=array("ref_systeme_id"=>$unit_details["systeme"],"type_donnees"=>"PE");

	if (!empty($user_droits)) {
	if (in_array($ce_systeme,$user_droits)) {$acces_complet=TRUE;} }
	else {$acces_complet=FALSE;}
	break;

	// peche artisanale
	case "art":
	$sql='SELECT DISTINCT s.ref_systeme_id as systeme, pe.annee, pe.mois 
			FROM art_periode_enquete pe, ref_secteur s, art_agglomeration a
			WHERE pe.id='.$unit_id.' AND a.id=pe.art_agglomeration_id AND a.ref_secteur_id=s.id
		';
	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	$array=pg_fetch_all($result);
	pg_free_result($result);
	
	$unit_details=$array[0];
	
	// on initialise la variable qui stocke si le user a acces a toutes les donnees ou pas	
	$acces_complet=FALSE;
	// on recupere les droits de l'utilisateur sur le systeme concerne
	// on s'interesse aux donnees de PA
	$ce_systeme=array("ref_systeme_id"=>$unit_details["systeme"],"type_donnees"=>"PA");
	if (!empty($user_droits)) {
	if (in_array($ce_systeme,$user_droits)) {$acces_complet=TRUE;} }
	else {$acces_complet=FALSE;}
	break;
	
	// statistiques de peche
	case "stats":
	$sql='SELECT DISTINCT s.ref_systeme_id  as systeme, pe.annee, pe.mois 
			FROM art_periode_enquete pe, ref_secteur s, art_agglomeration a
			WHERE pe.id='.$unit_id.' AND a.id=pe.art_agglomeration_id AND a.ref_secteur_id=s.id
		';
	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	$array=pg_fetch_all($result);
	pg_free_result($result);
		
	$unit_details=$array[0];
	// on initialise la variable qui stocke si le user a acces a toutes les donnees ou pas	
	$acces_complet=FALSE;
	// on recupere les droits de l'utilisateur sur le systeme concerne
	// on s'interesse aux donnees de ST
	$ce_systeme=array("ref_systeme_id"=>$unit_details["systeme"],"type_donnees"=>"ST");
	if (!empty($user_droits)) {
	if (in_array($ce_systeme,$user_droits)) {$acces_complet=TRUE;} }
	else {$acces_complet=FALSE;}
	break;
}

$acces_array=$ce_systeme;
$acces_array["acces_complet"]=$acces_complet;
$acces_array["utilisation"]=$utilisation;
$acces_array["annee"]=$unit_details["annee"];


return $acces_array;

}


//******************************************************************************
// affiche la liste des documents disponibles pour la selection geographique faite par l'utilisateur
function afficheMetadonnees() {
// la connexion à la base
global $connectPPEAO;
// on n'affiche le div que si des documents sont effectivement disponibles
$documents=FALSE;

$typeDonnees = $_GET["donnees"];

$moreMetaPays = ' AND meta_pays.b_art = TRUE ' ;
$moreMetaSecteurs = ' AND meta_secteurs.b_art = TRUE ' ;
$moreMetaSystemes = ' AND meta_systemes.b_art = TRUE ' ;

// FW 20180306 >>>
// Add ext|art selection of meta_ docs...
if( $typeDonnees === "exp" ) {
	$moreMetaPays = ' AND meta_pays.b_exp = TRUE ' ;
	$moreMetaSecteurs = ' AND meta_secteurs.b_exp = TRUE ' ;
	$moreMetaSystemes = ' AND meta_systemes.b_exp = TRUE ' ;
}
if( $typeDonnees === "art" ) {
	$moreMetaPays = ' AND meta_pays.b_art = TRUE ' ;
	$moreMetaSecteurs = ' AND meta_secteurs.b_art = TRUE ' ;
	$moreMetaSystemes = ' AND meta_systemes.b_art = TRUE ' ;
}
// FW 20180306 <<<


// on cherche les documents disponibles selon les diverses unites geographiques
$meta_files=array();
// on recupere la liste des documents pour les pays selectionnes
$pays=array();
if (isset($_GET["pays"])) {$pays=$_GET["pays"];}

if (!empty($pays)) {
$sql='SELECT DISTINCT meta_id, ref_pays_id, doc_type,file_path,doc_titre,doc_description FROM meta_pays WHERE ref_pays_id IN (\''.arrayToList($pays,'\',\'','\'').')';
$sql .= $moreMetaPays ; // FW 20180306
$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$array_pays=pg_fetch_all($result);
pg_free_result($result);
if (!empty($array_pays)) {
	$documents=TRUE;
	foreach($array_pays as $unpays) {
		$unpays["meta_table"]="meta_pays";
		$meta_files[]=array(
			"pays"=>$unpays["ref_pays_id"],
			"systeme"=>'',
			"secteur"=>'',
			"document"=>$unpays
			);
		}
	}
}

// on recupere la liste des documents pour les systemes selectionnes
$systemes=array();
if (isset($_GET["systemes"])) {$systemes=$_GET["systemes"];}
if (!empty($pays)) {
// on doit aussi recuperer tous les systemes appartenant aux pays selectionnes
if (isset($_GET["pays"])) {
	$sql='SELECT DISTINCT id, ref_pays_id FROM ref_systeme WHERE ref_pays_id IN (\''.arrayToList($pays,'\',\'','\'').')'; // FW add donnees=exp|art (ajout col ref_systeme)
	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	$array=pg_fetch_all($result);
	pg_free_result($result);
	if (!empty($array)) {
		foreach ($array as $systeme) {
			if (!in_array($systeme["id"],$systemes)) {
			$systemes[]=$systeme["id"];
			}
		}
	}
}

$sql='SELECT DISTINCT  meta_id, ref_systeme_id, doc_type,file_path,doc_titre,doc_description, ref_pays_id FROM meta_systemes, ref_systeme 
	WHERE ref_systeme_id IN ('.arrayToList($systemes,',','').') 
		AND ref_systeme_id=ref_systeme.id
	';
$sql .= $moreMetaSystemes ; // FW 20180306
$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$array_systemes=pg_fetch_all($result);
pg_free_result($result);

if (!empty($array_systemes)) {
	$documents=TRUE;
	foreach($array_systemes as $unsysteme) {
		$unsysteme["meta_table"]="meta_systemes";
		$meta_files[]=array(
			"pays"=>$unsysteme["ref_pays_id"],
			"systeme"=>$unsysteme["ref_systeme_id"],
			"secteur"=>'',
			"document"=>$unsysteme
			);
		}
}

// on recupere la liste des documents pour les secteurs selectionnes
$secteurs=array();
if (isset($_GET["secteurs"])) {$secteurs=$_GET["secteurs"];}
if (!empty($systemes)) {
// on doit aussi recuperer tous les secteurs appartenant aux pays>systemes selectionnes
$sql='SELECT s.id,ref_systeme_id,ref_pays_id 
	FROM ref_secteur s, ref_systeme sy, ref_pays p 
	WHERE ref_systeme_id IN ('.arrayToList($systemes,",","").')';
$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$array=pg_fetch_all($result);
pg_free_result($result);
if (!empty($array)) {
		foreach ($array as $secteur) {
			if (!in_array($secteur["id"],$secteurs)) {
			$secteurs[]=$secteur["id"];
			}
		}
	}

$sql='SELECT DISTINCT meta_id, ref_secteur_id,doc_type,file_path,doc_titre,doc_description, ref_pays_id, ref_systeme_id, ref_secteur.id FROM ref_systeme, ref_secteur, meta_secteurs WHERE meta_secteurs.ref_secteur_id IN ('.arrayToList($secteurs,',','').') AND ref_systeme.id=ref_secteur.ref_systeme_id AND ref_secteur.id=meta_secteurs.ref_secteur_id
	';
$sql .= $moreMetaSecteurs ; // FW 20180306
$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
$array_secteurs=pg_fetch_all($result);
pg_free_result($result);


if (!empty($array_secteurs)) {
	$documents=TRUE;
	foreach($array_secteurs as $unsecteur) {
		$unsecteur["meta_table"]="meta_secteurs";
		$meta_files[]=array(
			"pays"=>$unsecteur["ref_pays_id"],
			"systeme"=>$unsecteur["ref_systeme_id"],
			"secteur"=>$unsecteur["ref_secteur_id"],
			"document"=>$unsecteur
			);
		}
}

}

} // if !empty $pays


if ($documents) {
	// on trie le tableau des documents par pays, systeme et secteur
	$meta_files=array_msort($meta_files,array('pays'=>array(SORT_ASC,SORT_REGULAR),'systeme'=>array(SORT_ASC,SORT_REGULAR),'secteur'=>array(SORT_ASC,SORT_REGULAR)));
	// on eclate le tableau en trois : documents, cartes et figures
	$meta_docs=array();
	$meta_maps=array();
	$meta_figs=array();

	
	foreach ($meta_files as $file) {
		switch($file["document"]["doc_type"]) {
			case 'carte':
				$meta_maps[]=$file;
			break;
			case 'document':
				$meta_docs[]=$file;
			break;
			case 'figure':
				$meta_figs[]=$file;
			break;
		}		
	}
	echo('<div id="metadata">');
		echo('<h2>des documents descriptifs sont disponibles pour les donn&eacute;es que vous avez s&eacute;lectionn&eacute;es</h2>');
		echo('<span class="showHide"><a href="#" onclick="javascript:toggleMetadataList();return false;">[afficher les documents disponibles]</a></span>');
		echo('<div id="metadataList">');
		echo('<form id="metadataForm">');
			echo('<p>cochez les cases en regard des documents qui vous int&eacute;ressent : ceux-ci seront ajout&eacute;s &agrave; l&#x27;archive t&eacute;l&eacute;chargeable contenant vos donn&eacute;es; passez la souris sur les liens <a>[info]</a> pour lire une description du document correspondant</p>');
			
			// les documents
			if (!empty($meta_docs)) {
				echo('<div id="meta_docs">');
				echo('<h3>documents :</h3>');
				echo('<ul>');
				foreach ($meta_docs as $doc) {
					echo('<li>');
					$tip='';
					$description='';
					if (!empty($doc["document"]["doc_description"])) {$tip='class="toolTipSpan" title="description de ce document&nbsp;: ::'.$doc["document"]["doc_description"].'"'; $description=' <a>[infos]</a>';}
					echo('<input type="checkbox" name="'.$doc["document"]["meta_table"].'_'.$doc["document"]["meta_id"].'" id="'.$doc["document"]["meta_table"].'_'.$doc["document"]["meta_id"].'" /> <span id="'.$doc["document"]["meta_table"].'_'.$doc["document"]["meta_id"].'_titre" '.$tip.' >'.$doc["document"]["doc_titre"]).$description.'</span>';
					echo('</li>');
				}
				echo('</ul>');
				echo('</div>');
			}
			
			// les cartes
			if (!empty($meta_maps)) {
				echo('<div id="meta_maps">');
				echo('<h3>cartes :</h3>');
				echo('<ul>');
				foreach ($meta_maps as $doc) {
					echo('<li>');
					$tip='';
					$description='';
					if (!empty($doc["document"]["doc_description"])) {$tip='class="toolTipSpan" title="description de ce document&nbsp;: ::'.$doc["document"]["doc_description"].'"'; $description=' <a>[infos]</a>';}
					echo('<input type="checkbox" name="'.$doc["document"]["meta_table"].'_'.$doc["document"]["meta_id"].'" id="'.$doc["document"]["meta_table"].'_'.$doc["document"]["meta_id"].'" /> <span id="'.$doc["document"]["meta_table"].'_'.$doc["document"]["meta_id"].'_titre" '.$tip.' >'.$doc["document"]["doc_titre"]).$description.'</span>';
					echo('</li>');
				}
				echo('</ul>');
				echo('</div>');
			}
			
			// les figures
			if (!empty($meta_figs)) {
				echo('<div id="meta_figs">');
				echo('<h3>figures :</h3>');
				echo('<ul>');
				foreach ($meta_figs as $doc) {
					echo('<li>');
					$tip='';
					$description='';
					if (!empty($doc["document"]["doc_description"])) {$tip='class="toolTipSpan" title="description de ce document&nbsp;: ::'.$doc["document"]["doc_description"].'"'; $description=' <a>[infos]</a>';}
					echo('<input type="checkbox" name="'.$doc["document"]["meta_table"].'_'.$doc["document"]["meta_id"].'" id="'.$doc["document"]["meta_table"].'_'.$doc["document"]["meta_id"].'" /> <span id="'.$doc["document"]["meta_table"].'_'.$doc["document"]["meta_id"].'_titre" '.$tip.' >'.$doc["document"]["doc_titre"]).$description.'</span>';
					echo('</li>');
				}
				echo('</ul>');
				echo('</div>');
			}
			
		echo('</form>');
		
		echo('<br/></div>'); // div metadataList
		echo('<script type="text/javascript" charset="utf-8">
	var mySlider3 = new Fx.Slide(\'metadataList\', {duration: 500});
	mySlider3.hide();
	// affiche ou masque le DIV contenant la selection precedente
	function toggleMetadataList() {
		mySlider3.toggle() //toggle the slider up and down.
	}
</script>');
echo('</div>'); // div metadata


echo('<script charset="utf-8" type="text/javascript">
    window.onDomReady(function () {var myTips = new Tips($$(\'.toolTipSpan\'), {
        maxTitleChars: 30,   //I like my captions a little long
		fixed: true
    })});
</script>
');
	
}
}

?>