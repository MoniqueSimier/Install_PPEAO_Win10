<?php 
// Mis à jour par Olivier ROUX, 29-07-2008
// Mis à jour Yann LAURENT, 07-07-2008
// definit a quelle section appartient la page
$section="portage";
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
	<title>ppeao::recomposition des donn&eacute;es</title>
	
	<script type="text/javascript">
	
	function pop_it3(the_form) {
	   my_form = eval(the_form);
	   window.open("blanc.html", "popup", "height=300,width=500,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes,resizable=yes");
	   my_form.target = "popup";
	   my_form.submit();
	}
	
	
	</script>
	</head>
	<body>
		 <?php 
		// le menu horizontal
		include $_SERVER["DOCUMENT_ROOT"].'/top_nav.inc';
			// on teste à quelle zone l'utilisateur a accès
			if (userHasAccess($_SESSION['s_ppeao_user_id'],$zone)) {
		?>
		<div id="main_container" class="home">
			<div id="BDDetail">
			<?php  $subsection="manuel_recomp"; include $_SERVER["DOCUMENT_ROOT"].'/left_navbar.inc'; ?>
			</div>
			<div id="subContent">
				<h1>Recomposition des donn&eacute;es</h1>
				<h2>Choix de la base</h2>
				<br/>
				Entrez le nom de la base:
				<br/>
				<form name="form" method="post" action="rec_appel.php" >
					<p>
					<input type="text" name="base"/>
					<br/>
					<input type="submit" name="sss" value="valider"/>
					 </p>
				</form>
		</div>
	</div>		<!-- end div id="main_container"-->


<?php 
// note : on termine la boucle testant si l'utilisateur a accès à la page demandée

;} // end if (userHasAccess($_SESSION['user_id'],$zone))

// si l'utilisateur n'a pas accès ou n'est pas connecté, on affiche un message l'invitant à contacter un administrateur pour obtenir l'accès
else {userAccessDenied($zone);}

?>
	



</body>
</html>