//**************************************************
// ajaxStat.js
//**************************************************
// Creation le 16-07-2008 par Yann Laurent
// Permet de lancer sans popup les scripts du lot 2
// pour le calcul des statistiques
//**************************************************

var xmlHttp

function runStat(nbEnreg,DBname)
{
	adresse = document.getElementById("adresse").value;
	base = document.getElementById("base").value;
	if (! confirm('Confirmez-vous l\'exécution de la recomposition des donnnées ?')) {
		exit;
	}
	
	document.getElementById("formStatResult").innerHTML="<br/><img src='/assets/ajax-loader.gif' alt=''/> Traitement en cours";
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	  {
	  	alert ("Your browser does not support AJAX!");
	  	return;
	  } 
	var url="stat_traitement.php";
	xmlHttp.onreadystatechange=stateChanged;
	url=url+"?base="+base+"&adresse="+adresse+"&aff=1";
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
} 


function stateChanged() 
{ 

	if (xmlHttp.readyState==4)
	{ 
		document.getElementById("formStatResult").innerHTML=xmlHttp.responseText;
	}
}

function GetXmlHttpObject()
{
	var xmlHttp=null;
	try
	  {
	  // Firefox, Opera 8.0+, Safari
	  xmlHttp=new XMLHttpRequest();
	  }
	catch (e)
	  {
	  // Internet Explorer
	  try
		{
		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
	  catch (e)
		{
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	  }
	return xmlHttp;
}