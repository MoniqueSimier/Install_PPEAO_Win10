<?php 

// fonctions affichant le formulaire de login ou de logout

function showLoginForm() {
$theLoginForm='<form action="/session/login.php" method="post" id="sloginform" name="sloginform"><div>utilisateur<br /><input type="text" name="slogin" id="slogin" size="12" maxlength="250" /></div><div>mot de passe<br /><input type="password" name="spass" id="spass" size="12" /></div><p><a href="#" onclick="javascript:ajaxLogin();return false">connecter</a></p></form>';

return $theLoginForm;
}



function showLogoutForm($userLongname) {
if (isset($_SESSION['s_ppeao_longname']) && $_SESSION['s_ppeao_longname']!='') {$theName=$_SESSION['s_ppeao_longname'];} else {$theName=$userLongname;}


$theLogOutForm='<div><a href="#" onclick="javascript:ajaxLogout();return false;">d&eacute;connecter </a> '.$theName.'</div>';

return $theLogOutForm;
}





?>