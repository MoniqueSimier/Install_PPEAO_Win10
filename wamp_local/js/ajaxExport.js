var xmlHttp
var fenID
var numProcess = 1
var progPhp
var nomFen
var nomURL 
var AddURL
var finTrt = false;
var portageOK = "ok";
// Variable pour lancement recomposition données
var checkLog;
var typepeche ;
var relancetrt = false;
// Variables identifiant les différents process a executer ou non
var ctrlExec = true; 	// sauvegarde
var videExec = true; 	// Comparaison entre devppeao et bdpeche pour param et ref
var copACExec = true;	// Comparaison entre bdpeche et devppeao  pour param art
var copPPEAOExec = true;	// Copie données scientifiques
var testBDExec = true;	// Copie données recomposées
var zipExec = true;		// Exécution recompistion données



function runProcess()
{
	// On récupère les valeurs des différents paramètres entrée
	checkLog = document.getElementById("logsupp").checked;
	if (document.getElementById("typepecheart").checked == true) {
		typepeche = document.getElementById("typepecheart").value;
	} else {
		typepeche = document.getElementById("typepecheexp").value;
	}
	
	// On identifie les processus à exécuter.
	videExec = document.getElementById("videcheck").checked;
	copACExec = document.getElementById("copAcccheck").checked;
	copPPEAOExec = document.getElementById("copPPEAOcheck").checked;
	zipExec = document.getElementById("zipcheck").checked;

	
	// Initialisation des affichages en début de traitement.
	document.getElementById('titleProcess').innerHTML="Export access en cours";
	document.getElementById("vidage_img").innerHTML="<img src='/assets/ajax-loader_32px.gif' alt=''/>";
	document.getElementById("vidage_txt").innerHTML="Vidage de la base ACCESS de travail en cours...";
	document.getElementById('exportOK_img').innerHTML="<img src='/assets/ajax-loader_32px_gris.gif' alt=''/>";
	document.getElementById('exportOK_txt').innerHTML="Status de l'export = en cours de traitement";
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	  {
	  	alert ("Your browser does not support AJAX!");
	  	return;
	  } 
	var url="/export/export_gestionBD.php?tp="+typepeche+"&action=vide&log="+checkLog+"&numproc="+numProcess+"&exec="+videExec;
	fenID = "vidage";
	xmlHttp.onreadystatechange=stateChanged1;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
} 

function runProcessNext(phpProcess,locFenID,locURL,locTexte)
{
	fenID = locFenID;
	fenIDImg = locFenID+"_img";
	fenIDText = locFenID+"_txt";
	document.getElementById(fenIDImg).innerHTML="<img src='/assets/ajax-loader_32px.gif' alt=''/>";	
	if (!relancetrt) {
		document.getElementById(fenIDText).innerHTML=locTexte;
	}
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	  {
	  	alert ("Your browser does not support AJAX!");
	  	return;
	  } 
	if (locURL == "") {
		AddURL = "";
	} else {
		AddURL = "?"+locURL;
	}
	var url=phpProcess+AddURL;
	xmlHttp.onreadystatechange=stateChanged1;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function runProcessEnd()
{
	fenID = "exportOK";
	fenIDImg = fenID+"_img";
	fenIDText = fenID+"_txt";
	document.getElementById(fenIDImg).innerHTML="<img src='/assets/ajax-loader_32px.gif' alt=''/>";
	document.getElementById(fenIDText).innerHTML="Analyse Status process";
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	  {
	  	alert ("Your browser does not support AJAX!");
	  	return;
	  } 
	xmlHttp.onreadystatechange=stateChanged2;
	xmlHttp.open("GET","/export/finExport.php?log="+checkLog,true);
	xmlHttp.send(null);
}

function stateChanged1() 
{ 
	if (xmlHttp.readyState==4)
	{ 		
		document.getElementById(fenID).innerHTML=xmlHttp.responseText;
		URLSupp = "";
		if (document.getElementById("nomtable")) {
			// One of the processes needs to be restarted with some parameters because of server TIMEOUT
			numProcess = parseInt(document.getElementById("numproc").value);
			URLSupp = "&table="+document.getElementById("nomtable").value+"&numenreg="+document.getElementById("numID").value+"&traitsql="+document.getElementById("execsql").value;
			relancetrt = true;
		} else {
			relancetrt = false;
		}
		switch(numProcess)
		{
			case 1:
				progPhp = "/export/export_process.php" ;
				nomFen = "copiePPEAO" ;
				nomURL = "tp="+typepeche+"&action=copPPEAO&log="+checkLog+"&numproc="+numProcess+"&exec="+copPPEAOExec+URLSupp;
				texte = "Compte rendu de copie depuis base PPEAO de reference en cours ...";
			  break;			  
			case 2:
				progPhp = "/export/export_process.php" ;
				nomFen = "copieACCESS" ;
				nomURL = "tp="+typepeche+"&action=copAC&log="+checkLog+"&numproc="+numProcess+"&exec="+copACExec+URLSupp;
				texte = "Copie des donnees depuis la base ACCESS de reference en cours...";
			  break;
			case 3:
				progPhp = "/export/export_compress.php" ;
				nomFen = "copieZip" ;
				nomURL = "tp="+typepeche+"&log="+checkLog+"&exec="+zipExec;
				texte = "Zip des bases en cours...";
			  break;
			case 4:
			  finTrt = true;
			  break;
		}
		if (!finTrt) {
			numProcess++;
			suivant=runProcessNext(progPhp,nomFen,nomURL,texte);
		} else {
			// fin du traitement 
			runProcessEnd();
			return;
		}
	}
}

function stateChanged2() 
{ 
	if (xmlHttp.readyState==4)
	{ 		
		document.getElementById(fenID).innerHTML=xmlHttp.responseText;
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

function recupereSelection(limSel,Selection){
	listeVal = "";
	for (i=1;i<=limSel;i++) {
		if 	(document.getElementById(Selection+i).checked) {
			if (listeVal=="") {
				listeVal = document.getElementById(Selection+i).value;
			} else {
				listeVal = listeVal+","+document.getElementById(Selection+i).value;
			}
		}
	}
	return listeVal;
}


/**
* Fonction qui permet de gerer des selections complementaires et puis lance l'export des données
*/
function doExportSelect(action,changeSelection) {
	var choixExport="";
	var choixPays="";
	var nbPays = 0;
	var nbSysteme = 0;
	var choixSysteme = "";
	// action : l'action à faire (sequences_ref_param, sequences_donnees, vacuum, reindex)
	var outputDiv=$('exportSelComp');
	// on initialise l'objet AJAX	
	var xhr = getXhr();
	// what to do when the response is received
	xhr.onreadystatechange = function(){
			// while waiting for the response, display the loading animation
		var theLoader='<div align="left"><h5>Operation en cours</h5><br/>&nbsp;<img src="/assets/ajax-loader.gif" alt="Operation en cours..." title="Operation en cours..." valign="center"/></div>';
		if(xhr.readyState < 4) { outputDiv.innerHTML = theLoader;}
		// only do something if the whole response has been received and the server says OK
		if(xhr.readyState == 4 && xhr.status == 200){
			maintenanceResult = xhr.responseText;
			outputDiv.innerHTML=maintenanceResult;
		}  
	} // end xhr.onreadystatechange
	// using GET to send the request
	if (document.getElementById("choixExport1")) {
		choixExport = "&choixExport=" +recupereSelection(3,"choixExport");
	}
	if (document.getElementById("choixPays1")) {
		
		// a choice is done - we get the id of the selected dountry
		if (changeSelection == 'non') {
			choixExport = "&choixExport=" +document.getElementById("choixExportEC").value;
			nbPays = document.getElementById("nbPays").value;
			payschoisi = recupereSelection(nbPays,"choixPays");
			if (payschoisi == "") {
				alert("Merci de choisir un pays");
				return;
			}
			choixPays = "&choixPays=" + payschoisi;
		}
		
	}
	if (document.getElementById("choixPaysEC")) {
		// we finally reached to system selection
		choixExport = "&choixExport=" +document.getElementById("choixExportEC").value;
		if (changeSelection == 'non') {
			choixPays = "&choixPays=" +document.getElementById("choixPaysEC").value;	
			nbSysteme = document.getElementById("nbSysteme").value;
			systemchoisi = recupereSelection(nbSysteme,"choixSysteme");
			if (systemchoisi == "") {
				alert("Merci de choisir au moins un système");
				return;
			}
			choixSysteme = "&choixSysteme=" +systemchoisi;
		}
		
	}
	xhr.open("GET","/export/export_ajax.php?action="+action+choixExport+choixPays+choixSysteme,true);
	xhr.send(null);	
}
/**
* Fonction qui déclenche une opération d'exportation de données et en retourne le résultat
*/
function doExport(action,fenetre,label) {
	// action : l'action à faire (sequences_ref_param, sequences_donnees, vacuum, reindex)
	var outputDiv=$(fenetre);
	// on initialise l'objet AJAX	
	var xhr = getXhr();
	// what to do when the response is received
	xhr.onreadystatechange = function(){
			// while waiting for the response, display the loading animation
		var theLoader='<div align="left"><h5>'+label+'&nbsp;<img src="/assets/ajax-loader.gif" alt="export en cours..." title="export en cours..." valign="center"/></h5></div>';
		if(xhr.readyState < 4) { outputDiv.innerHTML = theLoader;}
		// only do something if the whole response has been received and the server says OK
		if(xhr.readyState == 4 && xhr.status == 200){
			maintenanceResult = xhr.responseText;
			outputDiv.innerHTML=maintenanceResult;
		}  
	} // end xhr.onreadystatechange
	// using GET to send the request
	xhr.open("GET","/export/export_ajax.php?action="+action,true);
	xhr.send(null);	
}
