// Javascript qui gère l'enchainement des scripts php lors du portage
// Permet notamment de gérer les timeout.

var xmlHttp
var fenID
var numProcess = 1
var numProcessPP = 1
var progPhp
var nomFen
var nomURL 
var AddURL
var finTrt = false;
var portageOK = "ok";
// Variable pour lancement recomposition données
var adresse ;
var DBname;
var nbEnreg;
var checkLog;
var relancetrt = false;
// Variables identifiant les différents process a executer ou non
var svgExec = true; 	// sauvegarde
var compExec = true; 	// Comparaison entre devppeao et bdpeche pour param et ref
var compInvExec = true;	// Comparaison entre bdpeche et devppeao  pour param art
var majscExec = true;	// Copie données scientifiques
var majrecExec = true;	// Copie données recomposées
var recExec = true;		// Exécution recompistion données
var statExec = true;	// Exécution calcul stats
var purgeExec = true;	// Exécution purge
// Variables complementaires
var trtok = "ok";		// Etat de l'execution du traitement

function runProcess()
{
	// Premiere fonction initialise la fenetre générale
	// lance la sauvegarde
	// On récupère les valeurs des différents paramètres entrée
	adresse = document.getElementById("adresse").value;
	DBname = document.getElementById("BDName").value;
	nbEnreg = document.getElementById("NBEnr").value;
	checkLog = document.getElementById("logsupp").checked;
	// On identifie les processus à exécuter.
	svgExec = document.getElementById("svgcheck").checked;
	compExec = document.getElementById("compcheck").checked;
	compInvExec = document.getElementById("compinvcheck").checked;
	majscExec = document.getElementById("majsccheck").checked;
	majrecExec = document.getElementById("majreccheck").checked;
	recExec = document.getElementById("reccheck").checked;
	statExec = document.getElementById("statcheck").checked;
	purgeExec = document.getElementById("purgecheck").checked;
	// Initialisation des affichages en début de traitement.
	document.getElementById('titleProcess').innerHTML="Portage automatique en cours";
	document.getElementById("sauvegarde_img").innerHTML="<img src='/assets/ajax-loader_32px.gif' alt=''/>";
	document.getElementById("sauvegarde_txt").innerHTML="Sauvegarde en cours";
	document.getElementById('portageOK_img').innerHTML="<img src='/assets/ajax-loader_32px_gris.gif' alt=''/>";
	document.getElementById('portageOK_txt').innerHTML="Status du portage = en cours de traitement";
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	  {
	  	alert ("Your browser does not support AJAX!");
	  	return;
	  } 
	var url="/process_auto/sauvegarde.php?exec="+svgExec+"&log="+checkLog;
	fenID = "sauvegarde";
	xmlHttp.onreadystatechange=stateChanged1;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
} 

function runProcessNext(phpProcess,locFenID,locURL,locTexte)
{
	// Fonction générique qui permet d'enchainer les executions de scripts php
	// Recupere le nom du script et les paramétres a envoyer dans l'url
	// Commence par afficher l'image gif qui indique le traitement en cours pour cette action
	// Puis execute le script php
	alert("nom fen = "+locFenID);
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
	// Fonction qui lance la derniere action = purge des tables
	
	// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
	if (majrecExec && trtok=="ok") { trtok = document.getElementById("trtok7").value;}
	fenID = "portageOK";
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
	xmlHttp.open("GET","/process_auto/finProcessAuto.php",true);
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
				// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
				if (svgExec && trtok=="ok") { trtok = document.getElementById("trtok1").value;}
				progPhp = "/process_auto/comparaison.php" ;
				nomFen = "comparaison" ;
				nomURL = "action=comp&log="+checkLog+"&numproc="+numProcess+URLSupp+"&exec="+compExec+"&adresse="+adresse;
				texte = "Comparaison du referentiel / parametrage de reference en cours...";
			  break;    
			case 2:
				// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
				if (compExec && trtok=="ok") { trtok = document.getElementById("trtok2").value;}
				progPhp = "/process_auto/comparaison.php" ;
				nomFen = "comparaisonInv" ;
				nomURL = "action=compinv&log="+checkLog+"&numproc="+numProcess+URLSupp+"&exec="+compInvExec+"&adresse="+adresse;
				texte = "Comparaison du parametrage de BDPECHE en cours...";
			  break;
			case 3:
				// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
				if (compInvExec && trtok=="ok") { trtok = document.getElementById("trtok3").value;}
				progPhp = "/process_auto/comparaison.php" ;
				nomFen = "copieScientifique" ;
				nomURL = "action=majsc&log="+checkLog+"&numproc="+numProcess+URLSupp+"&exec="+majscExec+"&adresse="+adresse;
				texte = "Copie des donn&eacute;es scientifiques en cours...";
			  break;
			case 4:
				if (majscExec && trtok=="ok") { trtok = document.getElementById("trtok4").value;}
				progPhp = "/process_auto/processAuto.php" ;
				nomFen = "processAutoRec" ;
				nomURL = "base="+DBname+"&log="+checkLog+"&nb_enr="+nbEnreg+"&adresse="+adresse+"&numproc="+numProcess+"&pg=rec&exec="+recExec;
				texte = "Recomposition automatique des données en cours ...";
			  break;
			case 5:
				// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
				if (recExec && trtok=="ok") { trtok = document.getElementById("trtok5").value;}
				progPhp = "/process_auto/processAuto.php" ;
				nomFen = "processAutoStat" ;
				nomURL = "base="+DBname+"&log="+checkLog+"&nb_enr="+nbEnreg+"&adresse="+adresse+"&numproc="+numProcess+"&pg=stat&exec="+statExec;
				texte = "Calcul statistique automatique des données en cours ...";
			  break;
			case 6:
				// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
				if (statExec && trtok=="ok") { trtok = document.getElementById("trtok6").value;}
				progPhp = "/process_auto/comparaison.php" ;
				nomFen = "copieRecomp" ;
				nomURL = "action=majrec&log="+checkLog+"&numproc="+numProcess+URLSupp+"&exec="+majrecExec+"&adresse="+adresse;
				texte = "Copie des p&ecirc;ches artisanales en cours...";
			  break;
			case 7:
			  finTrt = true;
			  break;
			case 8:
			  return;
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
		runClean("");
	}
}

function runClean(locURL)
{
	
	fenID = "purge";
	fenIDImg = fenID+"_img";
	fenIDText = fenID+"_txt";
	document.getElementById(fenIDImg).innerHTML="<img src='/assets/ajax-loader_32px.gif' alt=''/>";
	document.getElementById(fenIDText).innerHTML="Purge en cours";

	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	  {
	  	alert ("Your browser does not support AJAX!");
	  	return;
	  } 
	xmlHttp.onreadystatechange=stateChanged3;
	if (locURL== "") {
		addURL = "";
	} else {
		addURL = "&"+locURL;
	}
	// Message d'avertissement : si tout s'est bien passe, est-ce que l'utilisateur veut quand meme annuler l'execution
	// de l'action de vider les tables.
	if (trtok =="ok") {
		var msg = "Le traitement s'est bien passé. Voulez-vous vider les tables? (oui = ok non = annuler)";
		if (confirm(msg)) {
			delTable = "yes";	
		} else {
			delTable = "no";
		} 
	} else {
		delTable = "no";	
	}
	xmlHttp.open("GET","/process_auto/purgeTable.php?exec="+purgeExec+"&videT="+delTable+"&log="+checkLog+addURL,true);
	xmlHttp.send(null);
}

function stateChanged3() 
{ 

	if (xmlHttp.readyState==4)
	{ 		
		document.getElementById(fenID).innerHTML=xmlHttp.responseText;
		URLSupp = "";
		if (document.getElementById("nomtable")) {
			// One of the processes needs to be restarted with some parameters because of server TIMEOUT
			numProcess = parseInt(document.getElementById("numproc").value);
			URLSupp = "&table="+document.getElementById("nomtable").value;
			relancetrt = true;
		} else {
			URLSupp ="";
			relancetrt = false;
		}
		if (relancetrt) {
			runClean(URLSupp);
			
		}
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


/**
* Fonction de gestion du portage partiel
*/
function runProcessPartiel(action,fenetre,label) {
	// Premiere fonction initialise la fenetre générale
	// lance la sauvegarde
	// On récupère les valeurs des différents paramètres entrée
	adresse = document.getElementById("adresse").value;
	DBname = document.getElementById("BDName").value;
	nbEnreg = document.getElementById("NBEnr").value;
	checkLog = document.getElementById("logsupp").checked;
	// On identifie les processus à exécuter.
	svgExec = document.getElementById("svgcheck").checked; // Sauvegarde
	selExec = document.getElementById("selcheck").checked; // selection des données a recomposer
	impExec = document.getElementById("impcheck").checked; // import donnees dans BDPECHE
	supExec = document.getElementById("suppcheck").checked; // suppression des données dans BDPPEAO

	recExec = document.getElementById("reccheck").checked; // Lancement recomposition
	statExec = document.getElementById("statcheck").checked; // Lancement calcul stat
	majrecExec = document.getElementById("majreccheck").checked; // Lancement maj donnees peches art
	
	if (document.getElementById("PortagePartiel")) {
		portpart = document.getElementById("PortagePartiel").value;
	} else {
		portpart = "";
	}
	PortPartEncours = "n";
	// Initialisation des affichages en début de traitement.
	document.getElementById('titleProcess').innerHTML="Portage partiel en cours";
	document.getElementById("sauvegarde_img").innerHTML="<img src='/assets/ajax-loader_32px.gif' alt=''/>";
	document.getElementById("sauvegarde_txt").innerHTML="Sauvegarde en cours";
	document.getElementById('portageOK_img').innerHTML="<img src='/assets/ajax-loader_32px_gris.gif' alt=''/>";
	document.getElementById('portageOK_txt').innerHTML="Status du portage = en cours de traitement";
	xmlHttp=GetXmlHttpObject();
	if (xmlHttp==null)
	  {
	  	alert ("Your browser does not support AJAX!");
	  	return;
	  } 
	var url="/process_auto/sauvegarde.php?exec="+svgExec+"&log="+checkLog+"&pp="+portpart;
	fenID = "sauvegarde";
	xmlHttp.onreadystatechange=stateChangedPP;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}
function stateChangedPP() 
{ 
	if (xmlHttp.readyState==4)
	{ 		
		document.getElementById(fenID).innerHTML=xmlHttp.responseText;
		URLSupp = "";
		if (document.getElementById("SelEnCours")) {
			// c'est le processus de selection du pays / ecosysteme
			SelEnCours ="y";
		} else {
			SelEnCours ="n";
		}
		
		if (document.getElementById("nomtable")) {
			// One of the processes needs to be restarted with some parameters because of server TIMEOUT
			numProcessPP = parseInt(document.getElementById("numproc").value);
			URLSupp = "&table="+document.getElementById("nomtable").value+"&numenreg="+document.getElementById("numID").value+"&traitsql="+document.getElementById("execsql").value;
			relancetrt = true;
		} else {
			relancetrt = false;
		}
			
		switch(numProcessPP)
		{
			case 1:
				// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
				if (svgExec && trtok=="ok") { trtok = document.getElementById("trtok1").value;}
				//alert ("1"+trtok);
				progPhp = "/process_auto/portage_ajax.php" ;
				nomFen = "selectionRec" ;
				nomURL = "action=selection&exec="+selExec+"&log="+checkLog;
				texte = "Selection des donnees en cours";
			  break;    
			case 2:
				// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
				if (selExec && trtok=="ok") { trtok = document.getElementById("trtok2").value;}
					if ( SelEnCours =="n") {
						progPhp = "/process_auto/portage_ajax.php" ;
						nomFen = "importBdpeche" ;
						nomURL = "action=importbd&exec="+impExec+"&log="+checkLog;
						texte = "Import des donnees selectionnees dans BDPECHE";
					}
			  break;
			case 3:
				// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
				if (impExec && trtok=="ok") { trtok = document.getElementById("trtok3").value;}
				SelEnCours = "n";
				progPhp = "/process_auto/portage_ajax.php" ;
				nomFen = "SupBdppeao" ;
				nomURL = "action=supbd&exec="+supExec+"&log="+checkLog;
				texte = "Suppression des donnees selectionnees dans BDPPEAO";
			  break;
		  
			case 4:
				if (majscExec && trtok=="ok") { trtok = document.getElementById("trtok4").value;}
				SelEnCours = "n";
				progPhp = "/process_auto/processAuto.php" ;
				nomFen = "processAutoRec" ;
				nomURL = "base="+DBname+"&log="+checkLog+"&nb_enr="+nbEnreg+"&adresse="+adresse+"&numproc="+numProcessPP+"&pg=rec&exec="+recExec;
				texte = "Recomposition automatique des données en cours ...";
			  break;
			case 5:
				// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
				if (recExec && trtok=="ok") { trtok = document.getElementById("trtok5").value;}
				SelEnCours = "n";
				progPhp = "/process_auto/processAuto.php" ;
				nomFen = "processAutoStat" ;
				nomURL = "base="+DBname+"&log="+checkLog+"&nb_enr="+nbEnreg+"&adresse="+adresse+"&numproc="+numProcessPP+"&pg=stat&exec="+statExec;
				texte = "Calcul statistique automatique des données en cours ...";
			  break;
			case 6:
				// Recuperation de l'etat d'execution de la précedente action si elle a ete lancee.
				if (statExec && trtok=="ok") { trtok = document.getElementById("trtok6").value;}
				SelEnCours = "n";
				progPhp = "/process_auto/comparaison.php" ;
				nomFen = "copieRecomp" ;
				nomURL = "action=majrec&log="+checkLog+"&numproc="+numProcessPP+URLSupp+"&exec="+majrecExec+"&adresse="+adresse;
				texte = "Copie des p&ecirc;ches artisanales en cours...";
			  break;			  
			case 7:
			  finTrt = true;
			  break;
			case 8:
			  return;
		}
		if (!finTrt) {
			if (SelEnCours == "n"){// on reste sur le process en cours si la selection des données est en cours
				numProcessPP++; 
				suivant=runProcessPPNext(progPhp,nomFen,nomURL,texte);// On lance les autres étapes du portage partiel (selection, maj BDPECHE)
			}
		} else {
			// fin du traitement 
			runProcessEnd();
			return;
		}
		
	}
}


function runProcessPPNext(phpProcess,locFenID,locURL,locTexte)
{
	// Fonction générique qui permet d'enchainer les executions de scripts php
	// Recupere le nom du script et les paramétres a envoyer dans l'url
	// Commence par afficher l'image gif qui indique le traitement en cours pour cette action
	// Puis execute le script php
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
	xmlHttp.onreadystatechange=stateChangedPP;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
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
	// action : l'action à faire 
	var outputDiv=$('selectionRec');
	// on initialise l'objet AJAX	
	var xhr = getXhr();
	// what to do when the response is received
	xhr.onreadystatechange = function(){
			// while waiting for the response, display the loading animation
		var theLoader='<div align="left"><h2>Operation en cours</h2><br/>&nbsp;<img src="/assets/ajax-loader_32px.gif" alt="Operation en cours..." title="Operation en cours..." valign="center"/></div>';
		if(xhr.readyState < 4) { outputDiv.innerHTML = theLoader;}
		// only do something if the whole response has been received and the server says OK
		if(xhr.readyState == 4 && xhr.status == 200){
			maintenanceResult = xhr.responseText;
			outputDiv.innerHTML=maintenanceResult;
			if (document.getElementById("finSel") ) {
				// on a fini la selection, on lance l'etape suivante qui lance la suppression des données dans BDPPEAO
				numProcessPP ++;
				progPhp = "/process_auto/portage_ajax.php" ;
				nomFen = "importBdpeche" ;
				nomURL = "action=importbd&exec="+impExec+"&log="+checkLog;
				texte = "Import des donnees selectionnees dans BDPECHE";
				suivant=runProcessPPNext(progPhp,nomFen,nomURL,texte);
			}
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
	xhr.open("GET","/process_auto/portage_ajax.php?action="+action+"&exec="+selExec+"&log="+checkLog+choixExport+choixPays+choixSysteme,true);
	xhr.send(null);	
}
