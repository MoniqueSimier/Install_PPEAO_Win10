<?php
// ********** FONCTIONS UTILISEES POUR GENERER LE XML CORRESPONDANT A LA SELECTION **********************


// **************************************************
// element familleListe
function xmlFamilles($selection) {
// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;
if ($selection["choix_especes"]==1) {
			if (!empty($selection["familles"])) {
				$tmpXml='<familleListe selection="selection">';
				// on construit le contenu de la balise familleListe
					$sql='SELECT id, libelle FROM ref_famille WHERE id IN(
						\''.arrayToList($selection["familles"],'\',\'','\'').'
						)';
					$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
					$array=pg_fetch_all($result);
					pg_free_result($result);
				foreach($array as $famille) {
					$tmpXml.='<famille id="'.$famille["id"].'">'.$famille["libelle"].'</famille>';
				} // end foreach
			$tmpXml.='</familleListe>';
			} // end !empty
			else {
				$tmpXml='<familleListe selection="aucune"></familleListe>';
			}
		}
			else {$tmpXml='';}
	return $tmpXml;
}

// **************************************************
// element especeListe
function xmlEspeces($selection) {
// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;
if ($selection["choix_especes"]==1) {
			if (!empty($selection["especes"])) {
				$tmpXml='<especeListe selection="selection">';
				// on construit le contenu de la balise especeListe
					$sql='SELECT id, libelle FROM ref_espece WHERE id IN(
						\''.arrayToList($selection["especes"],'\',\'','\'').'
						)';
					$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
					$array=pg_fetch_all($result);
					pg_free_result($result);
				foreach($array as $espece) {
					$tmpXml.='<espece id="'.$espece["id"].'">'.$espece["libelle"].'</espece>';
				}
			$tmpXml.='</especeListe>';
			}
			else {
				$tmpXml='<especeListe selection="aucune"></especeListe>';
			}
		}
		else {$tmpXml='';}

	return $tmpXml;
}	

// **************************************************
// element paysListe
function xmlPays($selection) {
// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;

if (!empty($selection["pays"])) {
	$tmpXml='<paysListe selection="selection">';
	// on construit le contenu de la balise paysListe
		$sql='SELECT id, nom FROM ref_pays WHERE id IN(
			\''.arrayToList($selection["pays"],'\',\'','\'').'
			)';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
	foreach($array as $pays) {
		$tmpXml.='<pays id="'.$pays["id"].'">'.$pays["nom"].'</pays>';
		}
	}
else {
	$tmpXml='<paysListe selection="aucune">';
	}
$tmpXml.='</paysListe>';
	
	return $tmpXml;
}
// **************************************************
// element systemeListe
function xmlSystemes($selection) {
// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;

if (!empty($selection["systemes"])) {
	$tmpXml='<systemeListe selection="selection">';
	// on construit le contenu de la balise paysListe
		$sql='SELECT id, libelle FROM ref_systeme WHERE id IN(
			\''.arrayToList($selection["systemes"],'\',\'','\'').'
			)';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
	foreach($array as $systeme) {
		$tmpXml.='<systeme id="'.$systeme["id"].'">'.$systeme["libelle"].'</systeme>';
		}
	}
else {
	$tmpXml='<systemeListe selection="aucune">';
	}
$tmpXml.='</systemeListe>';
	
	return $tmpXml;
}

// **************************************************
// element systemeListe : utilisee si l'on doit passer une sous selection de systemes pour les stats generales
function xmlSystemes2($selection) {
// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;

if (!empty($selection["systemes2"])) {
	$tmpXml='<systemeListe selection="selection">';
	// on construit le contenu de la balise paysListe
		$sql='SELECT id, libelle FROM ref_systeme WHERE id IN(
			\''.arrayToList($selection["systemes2"],'\',\'','\'').'
			)';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
	foreach($array as $systeme) {
		$tmpXml.='<systeme id="'.$systeme["id"].'">'.$systeme["libelle"].'</systeme>';
		}
	}
else {
	$tmpXml='<systemeListe selection="aucune">';
	}
$tmpXml.='</systemeListe>';
	
	return $tmpXml;
}
// **************************************************
// element secteurListe
function xmlSecteurs($selection) {
// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;

if (!empty($selection["secteurs"])) {
	$tmpXml='<secteurListe selection="selection">';
	// on construit le contenu de la balise paysListe
		$sql='SELECT id, nom FROM ref_secteur WHERE id IN(
			\''.arrayToList($selection["secteurs"],'\',\'','\'').'
			)';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
	foreach($array as $secteur) {
		$tmpXml.='<secteur id="'.$secteur["id"].'">'.$secteur["nom"].'</secteur>';
		}
	}
else {
	$tmpXml='<secteurListe selection="aucune">';
	}
$tmpXml.='</secteurListe>';
	
	return $tmpXml;
}

// **************************************************
// element intervalle
function xmlPeriode($selection) {
// $selection: tableau equivalent au $_GET de la requete de selection

$tmpXml='<intervalle>
		<dateDebut annee="'.$selection["d_a"].'" mois="'.$selection["d_m"].'"></dateDebut>
		<dateFin annee="'.$selection["f_a"].'" mois="'.$selection["f_m"].'"></dateFin>
		</intervalle>';
	return $tmpXml;
}

// **************************************************
// element campagneListe
function xmlCampagnes($selection) {
// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;

if (!empty($selection["camp"])) {
	$tmpXml='<campagneListe selection="selection">';
	// on construit le contenu de la balise campagneListe
		$sql='SELECT DISTINCT c.id, c.numero_campagne, c.date_debut, c.date_fin, c.libelle as campagne, s.libelle as systeme, lower(s.libelle) as lower_systeme, p.nom as pays, lower(p.nom) as lower_pays 
				FROM exp_campagne c, ref_systeme s, ref_pays p 
				WHERE c.id IN (\''.arrayToList($selection["camp"],'\',\'','\'').') 
				AND c.ref_systeme_id=s.id AND s.ref_pays_id=p.id 
				ORDER BY lower_pays,lower_systeme,date_debut, date_fin';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
	foreach($array as $campagne) {
		$tmpXml.='<campagne id="'.$campagne["id"].'">'.$campagne["pays"].':'.$campagne["systeme"].':'.$campagne["date_debut"].' au '.$campagne["date_fin"].'</campagne>';
		}
	}
else {
	$tmpXml='<campagneListe selection="aucune">';
	}
$tmpXml.='</campagneListe>';
	
	return $tmpXml;
}

// **************************************************
// element enginListe
function xmlEngins($selection) {
// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;

if (!empty($selection["eng"])) {
	$tmpXml='<enginListe selection="selection">';
	// on construit le contenu de la balise enginListe
		$sql='SELECT id, libelle FROM exp_engin WHERE id IN(
			\''.arrayToList($selection["eng"],'\',\'','\'').'
			)';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
	foreach($array as $engin) {
		$tmpXml.='<engin id="'.$engin["id"].'">'.$engin["libelle"].'</engin>';
		}
	}
else {
	$tmpXml='<enginListe selection="aucune">';
	}
$tmpXml.='</enginListe>';
	
	return $tmpXml;
}

// **************************************************
// element agglomerationListe
function
xmlAgglomerations($selection) {
	// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;

if (!empty($selection["agglo"])) {
	$tmpXml='<agglomerationListe selection="selection">';
	// on construit le contenu de la balise enginListe
		$sql='SELECT id, nom FROM art_agglomeration WHERE id IN(
			\''.arrayToList($selection["agglo"],'\',\'','\'').'
			)';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
	foreach($array as $agglo) {
		$tmpXml.='<agglomeration id="'.$agglo["id"].'">'.$agglo["nom"].'</agglomeration>';
		}
	}
else {
	$tmpXml='<agglomerationListe selection="aucune">';
	}
$tmpXml.='</agglomerationListe>';
	
	return $tmpXml;
}

// **************************************************
// element enqueteListe
function
xmlEnquetes($selection) {
	// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;

if (!empty($selection["enq"])) {
	$tmpXml='<enqueteListe selection="selection">';
	// on construit le contenu de la balise enginListe
		$sql='SELECT DISTINCT e.id, e.description, e.annee, e.mois, a.nom as agglo, lower(a.nom) as lower_agglo, s.nom as secteur, lower(s.nom) as lower_secteur, sy.libelle as systeme, lower(sy.libelle) as lower_systeme, p.nom as pays, lower(p.nom) as lower_pays FROM art_periode_enquete e, ref_pays p, ref_systeme sy, ref_secteur s, art_agglomeration a 
				WHERE e.id IN (\''.arrayToList($selection["enq"],'\',\'','\'').') 
				AND e.art_agglomeration_id=a.id AND a.ref_secteur_id=s.id 
				AND s.ref_systeme_id=sy.id AND sy.ref_pays_id=p.id  
				ORDER BY lower_pays,lower_systeme, lower_secteur, annee, mois';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
	foreach($array as $enquete) {
		$tmpXml.='<enquete id="'.$enquete["id"].'">'.$enquete["pays"].':'.$enquete["systeme"].':'.$enquete["secteur"].':'.$enquete["agglo"].':'.$enquete["annee"].'-'.number_pad($enquete["mois"],2).'</enquete>';
		}
	}
else {
	$tmpXml='<enqueteListe selection="aucune">';
	}
$tmpXml.='</enqueteListe>';
	
	return $tmpXml;
}

// **************************************************
// element grandTypeEnginListe
function
xmlGrandTypeEngins($selection) {
		// $selection: tableau equivalent au $_GET de la requete de selection
global $connectPPEAO;

if (!empty($selection["gteng"])) {
	$tmpXml='<grandTypeEnginListe selection="selection">';
	// on construit le contenu de la balise enginListe
		$sql='SELECT id, libelle FROM art_grand_type_engin WHERE id IN(
			\''.arrayToList($selection["gteng"],'\',\'','\'').'
			)';
		$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);
	foreach($array as $engin) {
		$tmpXml.='<grandTypeEngin id="'.$engin["id"].'">'.$engin["libelle"].'</grandTypeEngin>';
		}
	}
else {
	$tmpXml='<grandTypeEnginListe selection="aucune">';
	}
$tmpXml.='</grandTypeEnginListe>';
	
	return $tmpXml;
}

?>