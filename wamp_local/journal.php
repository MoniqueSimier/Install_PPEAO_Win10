<?php 
// Mis à jour par Olivier ROUX, 29-07-2008
// code commun à toutes les pages (demarrage de session, doctype etc.)
include $_SERVER["DOCUMENT_ROOT"].'/top.inc';
// definit a quelle section appartient la page
$section="gerer";
$subsection="journal";

$zone=2; // zone journal (voir table admin_zones) changement JME 03 2016
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	
<?php 
	// les balises head communes  toutes les pages
	include $_SERVER["DOCUMENT_ROOT"].'/head.inc';
?>
	<title>ppeao::journal des activit&eacute;s</title>
	
	<!-- l'effet "tiroir" pour afficher/masquer la liste des archives -->
	<script type="text/javascript" charset="iso-8859-15">
	/* <![CDATA[ */
		window.addEvent('domready', function(){
					// note: the onComplete is there to set an automatic height to the wrapper div
					var archivesSlide = new Fx.Slide('archives_list_div',{duration: 500, mode: 'vertical', onComplete: function(){if(this.wrapper.offsetHeight != 0) this.wrapper.setStyle('height', 'auto');}});
					archivesSlide.hide();
					//since the selector hides away, display a "show" link
					$('showHideArchives').innerHTML='[afficher les archives]';
					// when the user clicks on the hide/show button, the slider's visibility is toggled
					$('showHideArchives').addEvent('click', function(e){
						e = new Event(e);
						archivesSlide.toggle();
						e.stop();
						// if the selector is displayed, the link reads "hide",
						//if it is hidden, the link reads "show"
						if(archivesSlide.wrapper.offsetHeight==0) {$('showHideArchives').innerHTML='[masquer les archives]';} else {$('showHideArchives').innerHTML='[afficher les archives]';}
					});
				});	


	/* ]]> */
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

<h2 style="padding-left:200px">Journal des activit&eacute;s</h2>

<?php

//logWriteTo(4,'notice','acc&egrave;s au journal','','',0);
echo(logDisplayFull('','','','','','paginate'));
// on affiche la liste des journaux archivés
echo(logArchivesList(""));

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
