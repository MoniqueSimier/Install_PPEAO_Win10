
<?php

$home="";
$gerer="";
$consulter="";
$contacter="";
$sinformer="";
$portage="";
$donnees="";
$reference="";
$parametrage="";
$administration="";
$journal="";
$documentation="";
$export="";
//ajout liens
$liens="";
// adds " active" to the style of each menu entry to highlight the current one in the navigation menu
switch ($section) {
	case "home" : $home=" active";
	break;
	case "gerer" : $gerer=" active";
	break;
	case "consulter" : $consulter=" active";
	break;
	case "contacter" : $contacter=" active";
	break;
	case "sinformer" : $sinformer=" active";
	break;
	//ajout de case liens
	case "liens" : $liens=" active"; 
	break;
}

// le logo IRD fait 145 par 25 pixels. Il est agrandi ici proportionnellement à 203 par 35 pour remplir
// le bandeau du haut. Il est aligné à droite avec align='right'

// 10 octobre 2016 : mise à jour avec le nouveau logo de l'IRD à la place de ird-logo2.jpg
// Ce logo (logo_IRD_2016_LONGUEUR_FR_COUL.png) est place dans /assets/home/
?>
<!-- le menu de navigation horizontal -->
<div id="nav_container">
	<div id="nav_menu"><a href="/index.php" class="menu<?php echo($home)?>">accueil</a><a href="/extraction/selection/selection.php" class="menu<?php echo($consulter)?>">consulter</a><a href="/gerer.php" class="menu <?php echo($gerer)?>">g&eacute;rer</a><a href="/apropos.php" class="menu<?php echo($sinformer)?>">s&#x27;informer</a><a href="/liens.php" class="menu<?php echo($liens)?>">liens</a><a href="/contact.php" class="menu<?php echo($contacter)?>">contacter</a>
	</div><img src="/assets/home/logo_IRD_2016_LONGUEUR_FR_COUL.png" width="203" height="35" align="right"> </div> <!-- end div id="nav_menu" -->
</div> <!-- end div id="nav_container" -->

<!-- le sous-menu de navigation, si besoin -->
<?php
if ($section=='gerer') {
	switch ($subsection) {
		case "portage" : $portage=" active";
		break;
		case "maintenance" : $maintenance=" active";
		break;
		case "donnees" : $donnees=" active";
		break;
		case "reference" : $reference=" active";
		break;
		case "parametrage" : $parametrage=" active";
		break;
		case "administration" : $administration=" active";
		break;
		case "journal" : $journal=" active";
		break;
		case "documentation" : $documentation=" active";
		break;
		case "export" : $export=" active";
		break;
	}
?>
<div id="nav_sousmenu"><a href="/portage.php" title="portage" class="sousmenu<?php echo($portage)?>">portage</a><a href="/edition_maintenance.php" title="maintenance" class="sousmenu<?php echo($maintenance)?>">maintenance</a><a href="/edition_donnees.php" title="donn&eacute;es" class="sousmenu<?php echo($donnees)?>">donn&eacute;es</a><a href="/edition_reference.php" title="tables de r&eacute;f&eacute;rence" class="sousmenu<?php echo($reference)?>">r&eacute;f&eacute;rence</a><a href="/edition_param.php" title="tables de param&eacute;trage" class="sousmenu<?php echo($parametrage)?>">param&eacute;trage</a><a href="/edition_admin.php" title="tables d&#x27;administration" class="sousmenu<?php echo($administration)?>">administration</a><a href="/export.php" title="export" class="sousmenu<?php echo($export)?>">export</a><a href="/journal.php" title="journal" class="sousmenu<?php echo($journal)?>">journal</a><a href="/gestion_doc.php" title="documentation" class="sousmenu<?php echo($documentation)?>">documentation</a>
</div> <!-- end div id="nav_sousmenu" -->
<?php

}

?>

<div id="login_block">
			<?php 
			// inclut le script de formulaire de login compact
			include ($_SERVER["DOCUMENT_ROOT"].'/session/loginform_s.php');
			?>
		</div> <!-- end div id="login_block" -->

