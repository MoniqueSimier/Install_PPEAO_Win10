<?php 
// page de gestion des droits d'acces aux donnees
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
// definit a quelle section appartient la page
$section="gerer";
$subsection='administration';
$zone=2; // zone edition, par défaut (voir table admin_zones)
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_config.inc';
include $_SERVER["DOCUMENT_ROOT"].'/edition/edition_functions.php';
include $_SERVER["DOCUMENT_ROOT"].'/extraction/selection/selection_functions.php';






?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
<?php 
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
?>
	<title>ppeao::g&eacute;rer::droits d&#x27;acc&egrave;s aux donn&eacute;es</title>

<script src="/js/edition.js" type="text/javascript"  charset="iso-8859-15"></script>
<script src="/extraction/selection/ex_selection.js" type="text/javascript" charset="utf-8"></script>
<link href="/styles/edition.css" title="mainstyles" rel="stylesheet" type="text/css" />
<link href="/styles/ex_selection.css" title="mainstyles" rel="stylesheet" type="text/css" />



</head>

<body>

<?php 

// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';

// on teste à quelle zone l'utilisateur a accès
if (userHasAccess($_SESSION['s_ppeao_user_id'],$zone)) {
?>

<div id="main_droits" class="selection">
<h2 style="text-align:center">g&eacute;rer les droits d&#x27;acc&egrave;s aux donn&eacute;es</h2>
<p>Cette page vous permet d&#x27;accorder &agrave; un utilisateur ou &agrave; un groupe d&#x27;utilisateurs le droit d&#x27;acc&egrave;s &agrave; la totalit&eacute; d&#x27;un jeu de donn&eacute;es pour l&#x27;extraction des donn&eacute;es. Dans le cas contraire, un utilisateur ou groupe aura acc&egrave;s uniquement aux donn&eacute;es &quot;historiques&quot;.</p>

<?php



$acteur_type='';
$lActeur='';
if ($_GET["type"]=='') {$acteur_type=$_POST["acteur_type"];} else {$acteur_type=$_GET["type"];}
if ($_GET["acteur"]=='') {$acteur_id=$_POST["acteur"];} else {$acteur_id=$_GET["acteur"];}
// si on est en mode enregistrement, on sauvegarde les donnees dans la base et on remet a zero
if ($_POST["enregistrer"]=='oui') {

	$enregistrer='oui';
	// on enregistre les modifications
	$succes=0;
	// on commence par supprimer les droits existants (plus simple que de faire une mise a jour)
	// on cherche d'abord si cet acteur a des droits deja definis...
	$sql_test='SELECT COUNT(*) FROM admin_acces_donnees_acteurs WHERE ref_acteur_id='.$acteur_id.' AND acteur_type=\''.$acteur_type.'\'';
	
		$testResult=pg_query($connectPPEAO,$sql_test) or die('erreur dans la requete : '.$sql_test. pg_last_error());
		$testTable=pg_fetch_all($testResult);
		
		//debug 		echo('<pre>');print_r($testTable);echo('</pre>');
		
		
		if (empty($testTable)) {$succes=1;}
		if (!empty($testTable)) {
	
	$sql='DELETE FROM admin_acces_donnees_acteurs WHERE ref_acteur_id='.$acteur_id.' AND acteur_type=\''.$acteur_type.'\'';

	
	
	if ($result=@pg_query($connectPPEAO,$sql)) {
		pg_free_result($result);
		$succes=1;
	}
	}
	
	// puis on les remplace par les nouveaux 
	// on ne le fait que si on a des droits a ajouter
	$sql="INSERT INTO admin_acces_donnees_acteurs (ref_acteur_id,acteur_type,ref_systeme_id,type_donnees) VALUES" ;
	$ajoute=FALSE;
	foreach($_POST as $key=>$value) {
		if (substr($key,0,2)=='PE' || substr($key,0,2)=='PA' || substr($key,0,2)=='ST') {
			$ajoute=TRUE;
			$type_donnees=substr($key,0,2);
			$sys_id=substringAfter($key,'_');
			$sql.=" ($acteur_id,'$acteur_type',$sys_id,'$type_donnees'),";
			}
		
		} //end foreach $_POST
	// on enleve la derniere virgule
	$sql=substr($sql,0,-1);

	if ($ajoute) {

	// on genere un message de confirmation ou d'erreur selon que la requete est réussie ou pas
	if ($result=@pg_query($connectPPEAO,$sql)) {$succes=1;} else {$succes=0;}
	pg_free_result($result);
	}
	
	if ($succes==1 && $enregistrer=='oui') {$message='<p class="error small">modifications enregistr&eacute;es.</p>';} else {$message='<p class="small error">une erreur a emp&ecirc;ch&eacute; l&#x27;enregistrement des modifications.</p>';}
	//on remet a zero
	$_POST='';
	$enregistrer='';
}

if ($acteur_type=='') {
echo('<h5>g&eacute;rer les droits pour un <a href="?type=g">groupe</a> ou un <a href="?type=u">utilisateur</a></h5>');}
// si l'on a choisi quel type d'acteur (utilisateur ou groupe) on veut editer
else {
	switch ($acteur_type) {
		// groupe
		case "g":
		$typeCode='g';
		$type="groupe";
		echo('<h5>g&eacute;rer les droits pour un groupe (changer pour un <a href="?type=u">utilisateur</a>).</h5>');
		echo('<p>Vous pouvez &eacute;galement <a href="/edition/edition_table.php?selector=no&editTable=usergroups" alt="cr&eacute;er un groupe">cr&eacute;er un nouveau groupe</a>.</p>');
		// choix de l'acteur pour lequel on veut definir des droits
		// on ne propose pas les groupes "visiteurs" (aucun droit, 0) ni les groupes admin (1), gestionnaires 
		// des donnees (2) et exploitants des donnees (3) qui ont eux acces a toutes les donnees
		$sql='SELECT group_id as id, group_name as name FROM admin_usergroups WHERE group_active AND group_id!=0 AND group_id!=1 AND group_id!=2 AND group_id!=3 ORDER BY group_name';
		break;
		// par defaut, utilisateur
		default:
		$typeCode='u';
		$type="utilisateur";
		$choix_type_acteur_texte='<h5>g&eacute;rer les droits pour un utilisateur (changer pour un <a href="?type=g">groupe</a>).</h5>';
			// choix de l'acteur pour lequel on veut definir des droits
			$sql='SELECT DISTINCT user_id as id, user_longname as name FROM admin_users WHERE user_active AND user_id!=0  ORDER BY user_longname';
		break;
	} // end switch $type
	
	echo($choix_type_acteur_texte);
		echo('<form id="droits_acces" name="droits_acces" action="/edition/edition_droits_acces.php" method="post">');
			// cet input sert a savoir si on enregistre le formulaire ou pas
			echo('<input type="hidden" id="enregistrer" name="enregistrer" value="" />');
			// cet input stocke le type d'acteur
			echo('<input type="hidden" id="acteur_type" name="acteur_type" value="'.$typeCode.'"');
			echo('<div id="choix_acteur">');
			echo('<h5>'.$type.'s</h5>');
	
	$result=pg_query($connectPPEAO,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
			$array=pg_fetch_all($result);
			pg_free_result($result);
			
			
			if (empty($array)) {echo('<p>aucun '.$type.' dans la base.</p>');} 
			else {
									
			echo('<select id="acteur" name="acteur" onchange="javascript:changeActeur();">');
			//echo('<select id="acteur" name="acteur" onchange="javascript:alert($(acteur).value);">');

				echo('<option value="-1">-choisir un '.$type.'-</option>');
				$lActeur='';
				foreach ($array as $acteur) {
					if (!empty($acteur) && $acteur["id"]==$acteur_id) { $selected=' selected="selected"'; $lActeur=$acteur["name"];} else {$selected='';}
					echo('<option value='.$acteur["id"].' '.$selected.'>'.$acteur["name"].'</option>');
				}
				echo('</select>');
				?>
			<script type="text/javascript" charset="utf-8">
				function changeActeur() {
				var url='/edition/edition_droits_acces.php?type=';
				url+=gup('type');
				url+='&acteur='+$("acteur").value;
				//alert(url)
				document.location=url;
				}
			</script>
			<?php
			}
			echo($message);
			echo('</div>');
			// affichage du selecteur permettant de choisir un pays>systeme
			// si on a choisi un acteur
			if ($lActeur!='') {
				echo('<div id="choix_systemes">');
				echo('<h5>choix des syst&egrave;mes &agrave; autoriser</h5>');
				// on affiche le selecteur de pays
				echo('<div id="div_pays" class="level_div">');
				echo('<p>pays</p>');
				selectDistinctEXalpha ($connectPPEAO,"ref_pays","nom","id","pays","",'',10,0,"a","ref_pays.id!='0' AND ref_pays.id IN (SELECT DISTINCT ref_systeme.ref_pays_id FROM ref_systeme WHERE TRUE)","javascript:updateSystemes();","");
				echo('</div>'); // end div_pays
				echo('<div class="level_div"> &gt; </div>');

			echo('<div id="div_systemes" class="level_div">');
		echo('<p>syst&egrave;mes</p>');
		// si aucun pays n'est selectionne on affiche un select vide mais on doit definir
		echo('<select id="systemes" name="systemes[]" size="10" multiple="multiple" class="level_select" style="min-width:10em" onchange="javascript:refreshAddSystemLink(\''.$acteur_type.'\');">');
			// on n'affiche le contenu de ce select que si des valeurs de pays ont ete passees
			if ($pays!='') {
				$pays=$_POST["pays"];			
			$array_systemes=listSystemes($pays);
	
			foreach($array_systemes as $systeme) {
				// si la valeur est dans l'url, on la selectionne
				if (in_array($systeme["id"],$_POST["systemes"])) {$selected='selected="selected" ';} else {$selected='';}
				echo('<option value="'.$systeme["id"].'" '.$selected.'>'.$systeme["libelle"].'</option>');
			} // end foreach
		}// fin de  if if (!empty($_POST["pays"]))
		echo('</select>');
		echo('</div>'); // end div_systemes
		echo('</div>'); // end div choix_systemes


		echo('<div class="hint clear"><span class="hint_label" style="display:block;padding-top:8px;"><a href="#" onclick="toggleAide(\'aide_systemes\');return false;">aide &gt;&gt;</a></span><div class="hint_text" id="aide_systemes" style="display:none;">');
		echo('Vous pouvez s&eacute;lectionner ou d&eacute;s&eacute;lectionner plusieurs valeurs en cliquant tout en maintenant la touche &quot;CTRL&quot; (Windows, Linux) ou &quot;CMD&quot; (Mac) enfonc&eacute;e.<br />Pour effectuer une s&eacute;lection continue, cliquez sur la premi&egrave;re valeur puis sur la derni&egrave;re valeur en maintenant la touche MAJ enfonc&eacute;e');
		echo('</div></div>');
		echo('<p class="clear" id="add_systemes"></p>');
		// on affiche la liste des  droits d'acces accordes a cet acteur
		echo('<div id="droits_consulter">');
		echo('<h5>syst&egrave;mes pour lesquels "'.$lActeur.'" peut consulter la totalit&eacute; des donn&eacute;es</h5>');
		// on passe l'id de l'acteur et son type
		//echo('<input type="hidden" id="acteur" name="acteur" value="'.$acteur_id.'"/>');
		echo('<input type="hidden" id="type" name="type" value="'.$acteur_type.'"/>');
		
		// si l'utilisateur fait partie des groupes ayant acces a toutes les donnees, on le signale
		
		/*$sql='SELECT column FROM table WHERE condition';
		$result=pg_query(connection,$sql) or die('erreur dans la requete : '.$sql. pg_last_error());
		$array=pg_fetch_all($result);
		pg_free_result($result);*/
		
		//on compile les eventuels systemes supplementaires
		if (!empty($_POST["systemes"])) {
			$systemes_supp=arrayToList($_POST["systemes"],',','');
		}
		displayAccessRightsTable($acteur_id,$acteur_type,$systemes_supp);
		echo('</form>');
		echo('</div>'); // fin div droits_acces
		
;} // fin de if (!empty($acteur))

} // end !empty($acteur_type)

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
