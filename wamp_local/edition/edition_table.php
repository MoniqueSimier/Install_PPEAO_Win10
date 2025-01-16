<?php 
// page d'édition d'une table de la base
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
// definit a quelle section appartient la page
$section="gerer";
$zone=2; // zone edition, par défaut (voir table admin_zones)
$editTable=$_GET["editTable"];
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';


$tableType=$tablesDefinitions[$editTable]["type_table_description"];
if (!my_empty($tablesDefinitions[$editTable]["zone"])) {$zone=$tablesDefinitions[$editTable]["zone"];}

// on détermine à quelle subsection et à quelle zone appartient la table choisie

switch ($tablesDefinitions[$editTable]["type_table_nom"]) {
	case 'admin':
	$subsection='administration';
	break;
	case 'param':
	$subsection='parametrage';
	break;
	case 'admin':
	$subsection='administration';
	break;
	case 'ref':
	$subsection='reference';
	break;
	case 'data':
	$subsection='donnees';
	break;
} 

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
<?php 
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
?>
	<title>ppeao::g&eacute;rer::<?php echo($tablesDefinitions[$editTable]["type_table_description"]); ?>::&quot;<?php echo($tablesDefinitions[$editTable]["label"]); ?>&quot;</title>

<script src="/js/edition.js" type="text/javascript"  charset="iso-8859-15"></script>
<script type="text/javascript" src="/ckfinder/ckfinder.js"></script>

<?php

// on n'affiche le selecteur que si on ne spécifie pas autrement
$displaySelector=$_GET["selector"];

if ($displaySelector!='no') {
echo('<!-- l\'effet "tiroir" pour afficher/masquer le sélecteur -->
	<script type="text/javascript" charset="iso-8859-15">
	/* <![CDATA[ */
		window.addEvent(\'domready\', function(){
					// note: the onComplete is there to set an automatic height to the wrapper div
					var selectorSlide = new Fx.Slide(\'selector_content\',{duration: 500, mode: \'vertical\', onComplete: function(){if(this.wrapper.offsetHeight != 0) this.wrapper.setStyle(\'height\', \'auto\');}});
					// when the result page loads, the selector is displayed, then it slides out and is hidden
					//selectorSlide.slideOut.delay(500, selectorSlide);
					selectorSlide.hide();
					//since the selector hides away, display a "show" link
					$(\'showHideSelect\').innerHTML=\'[afficher la s&eacute;lection]\';
					// when the user clicks on the hide/show button, the slider\'s visibility is toggled
					$(\'showHideSelect\').addEvent(\'click\', function(e){
						e = new Event(e);
						selectorSlide.toggle();
						e.stop();
						// if the selector is displayed, the link reads "hide",
						//if it is hidden, the link reads "show"
						if(selectorSlide.wrapper.offsetHeight==0) {$(\'showHideSelect\').innerHTML=\'[masquer la s&eacute;lection]\';} else {$(\'showHideSelect\').innerHTML=\'[masquer la s&eacute;lection]\';}
					});
				});	


	/* ]]> */
	</script>');

}
?>
</head>

<body>

<?php 

// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';

// on teste à quelle zone l'utilisateur a accès
if (userHasAccess($_SESSION['s_ppeao_user_id'],$zone)) {
?>

<div id="main_container" class="home">


<?php
// on n'affiche le selecteur que si on ne spécifie pas autrement
if ($displaySelector!='no') {
echo('<!-- le SELECTEUR -->
<div id="selector_container">');
	// insertion du sélecteur, en mode "page de selection"
	createSelector("edition");

echo('</div> <!-- end div selector_container -->')
;}
?>

<!-- l'ÉDITEUR -->
<div id="editor_container">
<?php

// on compile les informations sur les colonnes de la table $editTable
$cDetails=getTableColumnsDetails($connectPPEAO,$tablesDefinitions[$editTable]["table"]);

// la liste des colonnes concernées
$theHeads=array_keys($cDetails);
// et leur nombre
$numberOfColumns=count($theHeads);


// si on a sélectionné des valeurs particulières à éditer
if (isset($_GET[$editTable])) {
	$theTableValues=implode($_GET[$editTable],"','");
	$whereClause=' AND '.$tablesDefinitions[$editTable]["id_col"].' IN (\''.$theTableValues.'\') ';
	}
	else {$whereClause=NULL;}
// si on a filtré les valeurs à afficher/éditer : on ajoute les valeurs du filtre à la clause WHERE
// note : on compare les valeurs en les passant en minuscules, afin de contourner la sensibilité à la casse
// des requêtes SQL
	foreach ($cDetails as $key=>$value) {
		if (isset($_GET['f_'.$key]) && !my_empty($_GET['f_'.$key])) {			
			//si la valeur passée est un booleen
			if ($cDetails[$key]["data_type"]=="boolean") {
				if ($_GET['f_'.$key]=="t") {$val='TRUE';} else {$val='FALSE';}
				$whereClause.=' AND '.$key.'='.$val;
			} 
			// si ce n'est pas un booleen
			else {
			
			// si la valeur passée est un nombre et que la colonne correspondante est numérique, on fait un =
			if (is_numeric($_GET['f_'.$key]) && ($cDetails[$key]["data_type"]=="real" || $cDetails[$key]["data_type"]=="integer")) {
				$whereClause.=' AND '.$key.' = '.$_GET['f_'.$key].'';}
			// si la valeur passée n'est pas nombre
			else {
				// si la valeur passée correspond à une clé secondaire, on fait un = (match unique)
				if ($cDetails[$key]["constraints"][$key]["constraint_type"]=='FOREIGN KEY') {
					$whereClause.=' AND lower('.$key.') LIKE \''.strtolower($_GET['f_'.$key]).'\'';}
				
				// sinon on fait un LIKE en tenant compte de l'utilisation d'une éventuelle wildcard "*" en début de chaine
				else {
					//on recherche la wildcard					
					if (strpos($_GET['f_'.$key],'*')===0) {$search='%'.ltrim($_GET['f_'.$key],'*');} else {$search=$_GET['f_'.$key];}
					// on nettoie les éventuels espaces et * avant et après la chaine
					$search=ltrim(rtrim($search,'* '),'* ');
					$whereClause.=' AND lower('.$key.') LIKE \''.strtolower($search).'%\'';
				}
			} // end else is_numeric
			}// end else de if (boolean)

		}// end if isset
	} // en foreach $cDetails

// si on a trié la table sur une clé
// on construit la clause SQL de tri
	if (isset($_GET["s_key"]) && in_array($_GET["s_key"],$theHeads)) {
		$s_key=$_GET["s_key"];
		$orderClause='ORDER BY '.$_GET["s_key"];
		if ($_GET["s_dir"]=='d') {$orderClause.=' DESC'; $s_dir='d';} else {$orderClause.=' ASC'; $s_dir='u';}
	}
	// sinon on trie par défaut sur la colonne de "libellé"
	else {
		$s_key=$tablesDefinitions[$editTable]["noms_col"];
		$orderClause='ORDER BY '.$tablesDefinitions[$editTable]["noms_col"].' ';
		$s_dir='u';
	}

// on construit la requête SQL pour obtenir le nombre total d'enregistrements dans la table
$countAllSql='	SELECT COUNT(*) FROM '.$tablesDefinitions[$editTable]["table"].'
				WHERE  TRUE';
			
$countAllResult=pg_query($connectPPEAO,$countAllSql) or die('erreur dans la requete : '.$countSql. pg_last_error());
$countAllRow=pg_fetch_row($countAllResult);
$countAllTotal=$countAllRow[0];
 /* Libération du résultat */ 
 pg_free_result($countAllResult);

// on construit la requête SQL pour obtenir le nombre total de valeurs de la table à afficher
$countSql='	SELECT COUNT(*) FROM '.$tablesDefinitions[$editTable]["table"].'
				WHERE  TRUE '.$whereClause.'
			';
$countResult=pg_query($connectPPEAO,$countSql) or die('erreur dans la requete : '.$countSql. pg_last_error());
$countRow=pg_fetch_row($countResult);
$countTotal=$countRow[0];
 /* Libération du résultat */ 
 pg_free_result($countResult);


// on prend en compte la pagination (utilise la fonction paginate() definie dans functions_generic.php)

/* Déclaration des variables */ 
    $rowsPerPage = 15; // nombre d'entrées à afficher par page (entries per page) 
    $countPages = ceil($countTotal/$rowsPerPage); // calcul du nombre de pages $countPages (on arrondit à l'entier supérieur avec la fonction ceil() ) 
 
    /* Récupération du numéro de la page courante depuis l'URL avec la méthode GET */ 
    if(!isset($_GET['page']) || !is_numeric($_GET['page']) ) // si $_GET['page'] n'existe pas OU $_GET['page'] n'est pas un nombre (petite sécurité supplémentaire) 
        $currentPage = 1; // la page courante devient 1 
    else 
    { 
        $currentPage = intval($_GET['page']); // stockage de la valeur entière uniquement 
        if ($currentPage < 1) $currentPage=1; // cas où le numéro de page est inférieure 1 : on affecte 1 à la page courante 
        elseif ($currentPage > $countPages) $currentPage=$countPages; //cas où le numéro de page est supérieur au nombre total de pages : on affecte le numéro de la dernière page à la page courante 
        else $currentPage=$currentPage; // sinon la page courante est bien celle indiquée dans l'URL 
    } 
 
    /* $start est la valeur de départ du LIMIT dans notre requête SQL (est fonction de la page courante) */ 
    $startRow = ($currentPage * $rowsPerPage - $rowsPerPage);


// on construit la requête SQL pour obtenir les valeurs de la table à afficher si il y en a
if ($countTotal!=0) {
$tableSql='	SELECT * FROM '.$tablesDefinitions[$editTable]["table"].'
				WHERE  TRUE '.$whereClause.' 
				'.$orderClause.' 
				LIMIT '.$rowsPerPage.' OFFSET '.$startRow.' 
				 
			';

$tableResult=pg_query($connectPPEAO,$tableSql) or die('erreur dans la requete : '.$tableSql. pg_last_error());
$tableArray=pg_fetch_all($tableResult);

// libération du résultat
pg_free_result($tableResult);
// fin de la préparation de la pagination
}


// on prépare l'affichage du titre
$paginationString='';
if ($countTotal>$rowsPerPage) {
	$from=intval($startRow)+1; // +1 car les tableaux commencent à zéro
	$to=intval($startRow)+intval($rowsPerPage);
		// si on arrive à la fin du tableau
		if ($to>$countTotal) {$to=$countTotal;}
	$paginationString=' (affichage : '.$from.'-'.$to.')';
	}

?>
<h2>votre s&eacute;lection : <?php echo('"'.$tablesDefinitions[$editTable]["label"].'" '.$countTotal.' sur '.$countAllTotal.' '.$paginationString);?><span class="showHide"><a id="add_new_record" href="#" onclick="modalDialogAddRecord(1,'<?php echo($editTable) ?>');return false;">[ajouter un enregistrement]</a></span></h2><br>
<p class="hint small">Aide : pour trier la table, cliquer sur un nom de colonne, cliquer &agrave; nouveau pour inverser l'ordre de tri ; pour filtrer la table, saisissez ou choisissez une valeur et appuyez sur ENTR&Eacute;E (le filtrage est cumulatif et de type "commence par" : vous pouvez ajouter * au début de la valeur de filtre pour faire un "contient") ; pour &eacute;diter une valeur, cliquer dessus.</p><br>
<?php 
// on affiche la table
echo('<form id="the_table_form" name="the_table_form" action="/edition/edition_table.php">');

echo('<table id="la_table" border="0" cellspacing="0" cellpadding="0">');

// on affiche l'en-tête de table (avec les liens de tri)
echo('<tr id="the_headers">');
echo('<td class="small tools">cliquer pour trier &gt; </td>');
foreach ($theHeads as $oneHead) {
	// on construit l'URL de tri
	$sortUrl=replaceQueryParam ($_SERVER['FULL_URL'],"s_key",$oneHead);


	// si l'on est sur la colonne de tri,
	// on détermine l'ordre inverse de l'ordre courant et on définit une classe CSS et une icône particulière
	if ($s_key==$oneHead) {
		$c_sort_class='c_sorted';
		switch ($s_dir) {
			case 'u':
				$newSortDir='d';
				$sortIcon='<img src="/assets/sort_up.gif" alt="tri&eacute; en ordre croissant" />';
			break;
			case 'd':
				$newSortDir='u';
				$sortIcon='<img src="/assets/sort_down.gif" alt="tri&eacute; en ordre d&eacute;croissant" />';
			break;
			default:
				$newSortDir='d';
				$sortIcon='<img src="/assets/sort_up.gif" alt="tri&eacute; en ordre croissant" />';
			break;
		}
	}
	else {$newSortDir='u';
		$c_sort_class='c_unsorted';
		$sortIcon='';
	} 
	$sortUrl=replaceQueryParam ($sortUrl,"s_dir",$newSortDir);
	echo('<td class="small"><a href="'.$sortUrl.'"  class="'.$c_sort_class.'" title="cliquer pour trier sur cette colonne">'.$oneHead.' '.$sortIcon.'</a></td>');
} // end foreach $theHeads
echo('</tr>');

// la ligne contenant le filtre
echo('<tr id="the_filter">');
	
	// les liens permettant de filtrer ou de remettre le filtre à zéro
	// l'URL pour filtrer
	$filterUrl=removeQueryStringParam($_SERVER['FULL_URL'], "page");
	//l'URL pour supprimer le filtre
	$unfilterUrl=$_SERVER['FULL_URL'];
	foreach ($theHeads as $oneHead) {
		// on enlève de l'url le paramètre de filtre correspondant à la colonne courante
		$unfilterUrl=removeQueryStringParam($unfilterUrl, 'f_'.$oneHead);}
		

echo('<td class="small tools"><div class="tools"><a href="'.$unfilterUrl.'" class="small link_button" title="cliquez pour remettre le filtre à zéro">effacer</a> <a href="#" onclick="javascript:filterTable(\''.$filterUrl.'\');return false;" class="small link_button" title="cliquez pour filtrer la table">filtrer&nbsp;&gt; </a></div></td>');
	
foreach ($theHeads as $oneHead) {
	// on enlève de l'url le paramètre de filtre correspondant à la colonne courante
	$filterUrl=removeQueryStringParam($_SERVER['FULL_URL'], 'f_'.$oneHead);
	$theFilterValue='';
	// on prépare la valeur de l'input du filtre pour la colonne courante
	if (isset($_GET['f_'.$oneHead]) && !is_null($_GET['f_'.$oneHead])) {$theFilterValue=$_GET['f_'.$oneHead];} else {$theFilterValue='';}
	echo('<td class="small">');
	echo(makeField($cDetails,$editTable,$oneHead,$theFilterValue,'filter',$filterUrl,''));
	echo('</td>');
	}
	
echo('</tr>'); // fin de la ligne de filtre

// on affiche les résultats si il y en a
if ($countTotal!=0) {
	$i=0;
	
	foreach ($tableArray as $theRow) {
		// affiche la ligne avec un style différent si c'est un rang pair ou impair 
		if ( $i&1 ) {$rowStyle='edit_row_odd';} else {$rowStyle='edit_row_even';}
		$id=$tablesDefinitions[$editTable]["id_col"];
		echo('<tr id="row_'.$theRow[$id].'" class="'.$rowStyle.'">');
			// la colonne d'outils
			echo('<td class="small tools"><div class="tools"><a href="#" class="small link_button" onclick="javascript:modalDialogDeleteRecord(1,\''.$tablesDefinitions[$editTable]["table"].'\',\''.$theRow[$id].'\');return false;">supprimer</a></div></td>');
			
		
			foreach ($theRow as $key=>$value) {
								
				echo('<td id="edit_cell_'.$key.'_'.$theRow[$id].'" name="edit_cell_'.$key.'_'.$theRow[$id].'" class="small">');
				// on doit d'abord rendre la valeur "safe"
				// on encode la chaine pour la passer au javascript
				//$value=htmlentities($value, ENT_QUOTES,  'ISO-8859-15', FALSE );

				echo(makeField($cDetails,$editTable,$key,$value,'display='.$theRow[$id],'',''));
				
				//echo($theColumn);
				echo('</td>');
			}
		echo('</tr>');
		$i++;
	} // end foreach $tableArray
} // end if ($countTotal!=0)
else {
	echo('<tr><td colspan="'.count($theHeads).'">aucun r&eacute;sultat correspondant &agrave; cette s&eacute;lection</td></tr>');
}
echo('</table>');
echo('</form>');

// on affiche la pagination
echo paginate($_SERVER['PHP_SELF'].'?'.removeQueryStringParam($_SERVER['QUERY_STRING'],'page'), '&amp;page=', $countPages, $currentPage);
	

?>

</div> <!-- end div id="editor_container" -->


<?php

logWriteTo(1,'notice','acc&egrave;s &agrave; la gestion des '.$tablesDefinitions[$editTable]["type_table_description"].'&nbsp;: '.$tablesDefinitions[$editTable]["domaine_description"],'','',0);

?>
	
</div> <!-- end div id="main_container"-->


<?php 
// note : on termine la boucle testant si l'utilisateur a accès à la page demandée

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
else {userAccessDenied($zone);}

?>

<?php 
include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';

?>
</body>
</html>
