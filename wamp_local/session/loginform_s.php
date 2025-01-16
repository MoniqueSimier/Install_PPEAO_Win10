<fieldset id="smalloginform">
<?php 
//*********************************************************************
// le formulaire de login / logout
//*********************************************************************


// affiche le formulaire de login si l'utilisateur n'est pas loggué


if ( !isset($_SESSION['s_ppeao_login_status']) || ( isset($_SESSION['s_ppeao_login_status']) && $_SESSION['s_ppeao_login_status']!='good')) 
	{
	

	// on affiche le formulaire de login si l'utilisateur n'est pas connecté
	echo(showLoginForm());
	
} // end if ($_SESSION['s_ppeao_login_status']!='good'); 
		
	// sinon on affiche le formulaire de déconnexion
	else {
		
		echo(showLogoutForm($_SESSION['s_ppeao_longname']));
		} // end else
					

?>
	</fieldset>