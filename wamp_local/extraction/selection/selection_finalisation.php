<?php
// script qui recupere la selection de l'utilisateur en fin de processus et  la transmet a l'extraction sous forme d'une variable contenant la selection au format XML

session_start();

include $_SERVER["DOCUMENT_ROOT"].'/connect.inc';
include $_SERVER["DOCUMENT_ROOT"].'/variables.inc';
include $_SERVER["DOCUMENT_ROOT"].'/functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/selection/selection_xml_functions.php';


// on stocke l'URL courante dans le tableau des variables superglobales sous la forme $_SERVER['FULL_URL']
storeUrl();
$parsed=parse_url($_SESSION["selection_url"]);
parse_str($parsed["query"],$selection);

// on commence a produire le contenu de la selection en XML
$xml='<?xml version="1.0" encoding="ISO-8859-15"?>';
$xml.='<selection>';


// le type d'exploitation
switch ($selection["exploit"]) {
	// extraction
	case "donnees":
		$xml.='<exploitation type="extraction" />';
	// le type de peche
		switch ($selection["donnees"]) {
			//peche experimentale
			case "exp":
			$xml.='<peche type="experimentale" />';
			$xml.=xmlFamilles($selection);
			$xml.=xmlEspeces($selection);
			$xml.=xmlPays($selection);
			$xml.=xmlSystemes($selection);
			$xml.=xmlSecteurs($selection);
			$xml.=xmlPeriode($selection);
			$xml.=xmlCampagnes($selection);
			$xml.=xmlengins($selection);
			// le script a utiliser pour l'extraction
			$script="extraction_filieres_exp.php?logsupp=0";
			break;
			// peche artisanale
			case "art":
			$xml.='<peche type="artisanale" />';
			$xml.=xmlFamilles($selection);
			$xml.=xmlEspeces($selection);
			$xml.=xmlPays($selection);
			$xml.=xmlSystemes($selection);
			$xml.=xmlSecteurs($selection);
			$xml.=xmlAgglomerations($selection);
			$xml.=xmlPeriode($selection);
			$xml.=xmlEnquetes($selection);
			$xml.=xmlGrandTypeEngins($selection);
			// le script a utiliser pour l'extraction
			$script="extraction_filieres_art.php?logsupp=0";
			break;
		}
	break;
	// statistiques de peche
	case "stats":
		$xml.='<exploitation type="statistiques" />';
		// le type de statistiques
		switch ($selection["stats"]) {
			//stats par agglomerations
			case "agglo":
			$xml.='<statistiques type="agglomeration" />';
			$xml.=xmlFamilles($selection);
			$xml.=xmlEspeces($selection);
			$xml.=xmlPays($selection);
			$xml.=xmlSystemes($selection);
			$xml.=xmlSecteurs($selection);
			$xml.=xmlAgglomerations($selection);
			$xml.=xmlPeriode($selection);
			$xml.=xmlEnquetes($selection);
			$xml.=xmlGrandTypeEngins($selection);
			break;
			// stats generales
			case "gen":
			$xml.='<statistiques type="generales" />';
			$xml.=xmlFamilles($selection);
			$xml.=xmlEspeces($selection);
			$xml.=xmlPays($selection);
			$xml.=xmlSystemes2($selection);
			$xml.=xmlSecteurs($selection);
			$xml.=xmlPeriode($selection);
			$xml.=xmlGrandTypeEngins($selection);
			// le script a utiliser pour l'extraction des stats
			break;
		
		}
	$script="extraction_filieres_stat.php?logsupp=0";
	break;
	
	
}
// on recupere la liste des eventuels documents selectionnes
$sel='aucune';
if (true) {$sel='selection';} 
$xml.='<documentsListe selection="'.$sel.'">';
	foreach ($_GET as $key=>$value) {
		if (substr($key,0,9)=='meta_pays') {
			$id=substr($key,10);
			$xml.='<document id="'.$id.'" type="meta_pays"/>';
			}
		if (substr($key,0,13)=='meta_systemes') {
			$id=substr($key,14);
			$xml.='<document id="'.$id.'" type="meta_systeme"/>';}
		if (substr($key,0,13)=='meta_secteurs') {
			$id=substr($key,14);
			$xml.='<document id="'.$id.'" type="meta_secteur"/>';}
	}
$xml.='</documentsListe>';

$xml.='</selection>';

// on stocke le XML dans la variable de session
$_SESSION["selection_xml"]=$xml;

// et on redirige vers le choix des filieres d'extraction
$url = 'http';
	$script_name = '';
	
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') {
	   $url .=  's';
	}
	$url .=  '://';
	
	if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {$theHost=$_SERVER['HTTP_X_FORWARDED_HOST'];} else {$theHost=$_SERVER['HTTP_HOST'];};
	
$url.=$theHost;


$url.='/extraction/extraction/'.$script;


// si l'utilisateur passe le parametre &xml=1 dans l'url, on affiche le fichier XML au lieu de poursuivre l'extraction
switch ($_GET["xml"]) {
	case 1:
	header('Content-Type: text/xml');echo($xml);
	break;
	default:
		header('location: ' . $url);
	break;

}?>