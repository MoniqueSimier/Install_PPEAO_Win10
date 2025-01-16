<?php 
// Mis à jour par Olivier ROUX, 29-07-2008
// definit a quelle section appartient la page
$section="gerer";
$subsection="portage";
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';

$zone=3; // zone portage (voir table admin_zones)


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
<div id="main_container" class="home" style="width:100%">
<?php 
	if (isset($_SESSION['s_ppeao_user_id'])){ 
		$userID = $_SESSION['s_ppeao_user_id'];
	} else {
		$userID=null;
	}
	// on teste à quelle zone l'utilisateur a accès
	if (userHasAccess($userID,$zone)) {
				
	?>

		<div id="subContent">
		<h2 style="padding-left:200px">Importation de donn&eacute;es dans la base bdpeche</h2>
<!-- 		<p>Cette section vous permet d&#x27;acc&eacute;der au module d&#x27;importation de donn&eacute;es dans la base bdpeche (application &quot;SINTI&quot;).</p>
 -->		
<iframe src="/bienvenue" width="1100px" height="800px" frameborder="0" scrolling="auto">application SINTI</iframe>
		</div>	


<?php 
// note : on termine la boucle testant si l'utilisateur a accès à la page demandée

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
else {userAccessDenied($zone);}

?>

<?php 
include $_SERVER["DOCUMENT_ROOT"].'/footer.inc';

?>
</div> <!-- end div id="main_container"-->
</body>
</html>
