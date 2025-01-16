<?php

// fonctions PHP utilisées par le module de suppression de campagnes ou de périodes d'enquête

//******************************************************************************
// insere un <select> pour filtrer les campagnes ou enquetes a supprimer

function insertDeleteSelect($domaine,$thisLevel,$previousSelection) {
	
// $domaine : le domaine exp ou art
// $thisLevel : le niveau du <select> dans la cascade (1,2,3 etc)
// $previousSelection : les valeurs selectionnees dans le select precedent (liste de type : valeur1,valeur2,valeur3)
	
global $tablesDefinitions;
global $suppression_cascades;
global $connectPPEAO;


$previousLevel=$thisLevel-1;
$thePreviousSelect=$suppression_cascades[$domaine][$previousLevel-1];
$theNewSelect=$suppression_cascades[$domaine][$thisLevel-1];


// on definit la requete SQL pour lister les valeurs du <select>
if ($domaine=='exp') {
switch ($theNewSelect) {
	case "pays":
	$sql="	SELECT DISTINCT ref_pays.id as value, ref_pays.nom as text, lower(ref_pays.nom) as lower_nom 
			FROM  ref_pays 
			WHERE ref_pays.id IN (
				SELECT DISTINCT ref_systeme.ref_pays_id 
				FROM ref_systeme,exp_campagne
				WHERE ref_systeme.id=exp_campagne.ref_systeme_id
				)
			ORDER BY lower_nom";
	break;
	case "systeme":
	$sql="	SELECT DISTINCT ref_systeme.id as value, ref_systeme.libelle as text, lower(ref_systeme.libelle) as lower_libelle, ref_systeme.ref_pays_id
			FROM ref_systeme,exp_campagne 
			WHERE ref_systeme.id=exp_campagne.ref_systeme_id AND ref_systeme.ref_pays_id IN ($previousSelection) 
			ORDER BY lower_libelle
		";
	break;
	//note: les dates sont un cas particulier, il faut extraire l'annee de la table campagne/periode d'enquete
	case "annee_debut":
	$sql="	SELECT DISTINCT EXTRACT(YEAR FROM date_debut) as value, EXTRACT(YEAR FROM date_debut) as text
			FROM exp_campagne 
			WHERE exp_campagne.ref_systeme_id IN ($previousSelection) 
			ORDER BY value DESC 
			";
	break;
	
}// end switch thisSelect
} // end if exp
if ($domaine=='art') {
switch ($theNewSelect) {
	case "pays":
	$sql="	SELECT DISTINCT ref_pays.id as value, ref_pays.nom as text, lower(ref_pays.nom) as lower_nom 
			FROM  ref_pays 
			WHERE ref_pays.id IN (
				SELECT DISTINCT ref_systeme.ref_pays_id 
				FROM ref_systeme 
				WHERE ref_systeme.id IN (
					SELECT DISTINCT ref_secteur.ref_systeme_id FROM ref_secteur 
					WHERE ref_secteur.id IN (
						SELECT DISTINCT art_agglomeration.ref_secteur_id 
						FROM art_agglomeration, art_periode_enquete 
						WHERE art_agglomeration.id=art_periode_enquete.art_agglomeration_id
						)
					)
				)
			ORDER BY lower_nom";
	break;
	case "systeme":
	$sql="	SELECT DISTINCT ref_systeme.id as value, ref_systeme.libelle as text, lower(ref_systeme.libelle) as lower_libelle, ref_systeme.ref_pays_id
			FROM ref_systeme 
			WHERE ref_systeme.id IN (
					SELECT DISTINCT ref_secteur.ref_systeme_id FROM ref_secteur 
					WHERE ref_secteur.id IN (
						SELECT DISTINCT art_agglomeration.ref_secteur_id 
						FROM art_agglomeration, art_periode_enquete 
						WHERE art_agglomeration.id=art_periode_enquete.art_agglomeration_id
						)
					) 
				AND ref_systeme.ref_pays_id IN ($previousSelection) 
			ORDER BY lower_libelle
		";
	break;
	case "secteur":
	$sql="	SELECT DISTINCT ref_secteur.id as value, ref_secteur.nom as text, lower(ref_secteur.nom) as lower_nom 
			FROM ref_secteur 
			WHERE ref_secteur.id IN (
						SELECT DISTINCT art_agglomeration.ref_secteur_id 
						FROM art_agglomeration, art_periode_enquete 
						WHERE art_agglomeration.id=art_periode_enquete.art_agglomeration_id
						) 
				AND ref_secteur.ref_systeme_id IN ($previousSelection) 
			ORDER BY lower_nom";
	break;
	case "agglomeration":
	$sql="	SELECT DISTINCT art_agglomeration.id as value, art_agglomeration.nom as text, lower(art_agglomeration.nom) as lower_nom 
			FROM art_agglomeration, art_periode_enquete 
			WHERE art_agglomeration.id=art_periode_enquete.art_agglomeration_id 
			AND art_agglomeration.ref_secteur_id IN ($previousSelection) 
			ORDER BY lower_nom";
	break;
	//note: les dates sont un cas particulier, il faut extraire l'annee de la table campagne/periode d'enquete
	case "annee_debut":
	$sql="	SELECT DISTINCT EXTRACT(YEAR FROM date_debut) as value, EXTRACT(YEAR FROM date_debut) as text
			FROM art_periode_enquete 
			WHERE art_periode_enquete.art_agglomeration_id IN ($previousSelection) 
			ORDER BY value DESC 
			";
	break;
		
}// end switch thisSelect

}


$theNextLevel=$thisLevel+1;

$theTable=$tablesDefinitions[$theNewSelect];



$selectedValues=$_GET[$theNewSelect];

	// le selecteur
	// on effectue la requete SQL pour recuperer les valeurs
	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	
	$array=pg_fetch_all($result);
	pg_free_result($result);
	
	// on construit le <select>

	

	// le titre du <select>, avec cas particulier pour les dates
	if ($theNewSelect=='annee_debut') {$theSelectCode.='<p>d&eacute;but</p>';} else {
	$theSelectCode.='<p>'.$tablesDefinitions[$theNewSelect]["label"].'</p>';}
	$theSelectCode.='<div id="select_'.$thisLevel.'" name="select_'.$thisLevel.'">';
			
			// si il reste un ou des <select> a afficher dans $suppression_cascades[$domaine] on ajoute le onChange complet
			if ($thisLevel<count($suppression_cascades[$domaine])) {$onchange='onchange="javascript:showNextSelect(\''.$domaine.'\',\''.$theNextLevel.'\',\'\');"';} 
			// sinon on passe juste "last" pour uniquement raffraichir le lien d'affichage des campagnes
			else {$onchange='onchange="javascript:showNextSelect(\''.$domaine.'\',\'\',\'last\')"';}
			$theSelectCode.='<select id="'.$theNewSelect.'" class="level_select" '.$onchange.' multiple="multiple" size="10" name="'.$theNewSelect.'[]">';
				
				foreach ($array as $row) {
					// si une valeur est présente dans l'url, on la selectionne
					if (@in_array($row["value"], $selectedValues)) {$selected='selected="selected"';} else {$selected='';}
					$theSelectCode.='<option value="'.$row["value"].'" '.$selected.'>'.$row["text"].'</option>';
				}
			$theSelectCode.='</select>';
		$theSelectCode.='</div>'; // end div select_

return iconv('ISO-8859-15','UTF-8',$theSelectCode);
	
} // end function insertDeleteSelect




//******************************************************************************
// compte le nombre de campagnes ou enquetes a supprimer
function countMatchingUnits($domaine) {

global $connectPPEAO;

if ($domaine=='exp') {
$sql="SELECT DISTINCT COUNT(id) as total FROM exp_campagne WHERE TRUE ";
	// si des valeurs de pays ont ete passees dans l'url
	if (!empty($_GET["pays"])) {
		$sql.='AND exp_campagne.ref_systeme_id IN (SELECT DISTINCT ref_systeme.id FROM ref_systeme WHERE ref_systeme.ref_pays_id IN (\''.arrayToList($_GET["pays"],'\',\'','\'').')) ';
		}
	// si des valeurs de systeme ont ete passees dans l'url
	if (!empty($_GET["systeme"])) {
		$sql.='AND exp_campagne.ref_systeme_id IN (\''.arrayToList($_GET["systeme"],'\',\'','\'').')';
		}
	// si des valeurs de annee_debut ont ete passees dans l'url
	if (!empty($_GET["annee_debut"])) {
		$sql.=' AND EXTRACT(YEAR FROM exp_campagne.date_debut) in (\''.arrayToList($_GET["annee_debut"],'\',\'','\'').')';
		}
} // fin de if ($domaine=='exp') 

if ($domaine=='art') {
$sql="SELECT DISTINCT COUNT(art_periode_enquete.id) as total FROM art_periode_enquete WHERE TRUE ";
	// si des valeurs de pays ont ete passees dans l'url
	if (!empty($_GET["pays"])) {
		$sql.=' AND art_periode_enquete.art_agglomeration_id IN (SELECT DISTINCT art_agglomeration.id FROM art_agglomeration WHERE
 art_agglomeration.ref_secteur_id IN (SELECT DISTINCT ref_secteur.id FROM ref_secteur WHERE ref_secteur.id IN (SELECT DISTINCT ref_secteur.id FROM ref_secteur WHERE ref_secteur.ref_systeme_id IN (SELECT DISTINCT ref_systeme.id FROM ref_systeme WHERE ref_systeme.ref_pays_id IN (\''.arrayToList($_GET["pays"],'\',\'','\'').')))))';
		}
	// si des valeurs de systeme ont ete passees dans l'url
	if (!empty($_GET["systeme"])) {
		$sql.=' AND art_periode_enquete.art_agglomeration_id IN (SELECT DISTINCT art_agglomeration.id FROM art_agglomeration WHERE
 art_agglomeration.ref_secteur_id IN (SELECT DISTINCT ref_secteur.id FROM ref_secteur WHERE ref_secteur.id IN (SELECT DISTINCT ref_secteur.id FROM ref_secteur WHERE ref_secteur.ref_systeme_id IN  (\''.arrayToList($_GET["systeme"],'\',\'','\'').'))))';
		}
	// si des valeurs de secteur ont ete passees dans l'url
	if (!empty($_GET["secteur"])) {
		$sql.=' AND art_periode_enquete.art_agglomeration_id IN (SELECT DISTINCT art_agglomeration.id FROM art_agglomeration WHERE
 art_agglomeration.ref_secteur_id IN (SELECT DISTINCT ref_secteur.id FROM ref_secteur WHERE ref_secteur.id IN  (\''.arrayToList($_GET["secteur"],'\',\'','\'').')))';
		}
		// si des valeurs d'agglomeration ont ete passees dans l'url
	if (!empty($_GET["agglomeration"])) {
		$sql.=' AND art_periode_enquete.art_agglomeration_id IN (\''.arrayToList($_GET["agglomeration"],'\',\'','\'').')';
		}
	// si des valeurs de annee_debut ont ete passees dans l'url
	if (!empty($_GET["annee_debut"])) {
		$sql.=' AND EXTRACT(YEAR FROM art_periode_enquete.date_debut) IN  (\''.arrayToList($_GET["annee_debut"],'\',\'','\'').')';
		}
	
	
} // fin de if ($domaine=='art')

// debug echo($sql);
	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
	$totalArray=pg_fetch_array($result);
	pg_free_result($result);
$total=$totalArray["total"];


if (empty($total)) {$total=0;}

return $total;

}




?>