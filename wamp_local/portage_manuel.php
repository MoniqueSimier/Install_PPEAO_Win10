<?php 
// Mis à jour par Olivier ROUX, 29-07-2008
// definit a quelle section appartient la page
$section="gerer";
$subsection="portage";
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';

$zone=2; // zone portage (voir table admin_zones) changement JME 03 2016
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
	<?php 
		// les balises head communes  toutes les pages
		include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
	?>
	<title>ppeao::manipulation de donn&eacute;es</title>
	

</head> 
 <body>
<?php 
// le menu horizontal
include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
?>
<div id="main_container" class="home">
	<?php
		if (isset($_SESSION['s_ppeao_user_id'])){ 
			$userID = $_SESSION['s_ppeao_user_id'];
		} else {
			$userID=null;
		}
		
		// on teste à quelle zone l'utilisateur a accès
		if (userHasAccess($userID,$zone)) {
	?>

		<div id="BDDetail">
		<?php  $subsection="manuel"; include $_SERVER["DOCUMENT_ROOT"].'/left_navbar.inc'; ?>
		</div>
		<div id="subContent">
		<h1>Recomposition des donn&eacute;es et calcul des statistiques</h1>
		<br/>
		<br/>
		<p>Cette section reprend les traitements manuels d&eacute;velopp&eacute;s dans le lot 2 PPEAO r&eacute;alis&eacute;s en 2007.</p>
		<ul class="list">
			<li class="listitem"><a href="/recomposition/rec_choix_base.php" ><b>Recomposition donn&eacute;es</b></a></li>
			<li class="listitem"><a href="/statistiques/stat_choix_base.php" ><b>Stats p&ecirc;che par agglom&eacute;ration</b></a></a>
		</ul>
		</div>	
	
<?php 
// note : on termine la boucle testant si l'utilisateur a acces a la page demandae

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas acces ou n'est pas connecte, on affiche un message l'invitant a contacter un administrateur pour obtenir l'acces
else {userAccessDenied($zone);}

?>
	</div>		<!-- end div id="main_container"-->
 </body>
</html>
