// Fonctions javascript pour la gestion de l'extraction, phase II (lecture des données apres la selection)
// creation : Y. Laurent le 30/06/2009
// 
// Definition des variables globales
var xmlHttp;
var fenID;
var numProcess = 1;
var progPhp;
var nomFen;
var nomURL ;
var AddURL;
var finTrt = false;
var portageOK = "ok";
// Variable pour lancement recomposition données
var checkLog;
var globaltypepeche = "";
var globalTableEnCours = "" ;
var globaltypestat = "";

var globalAction = "";
var numElementExplode = 0;
// Pour les tabulations, numtab =
// 1 = critères généraux
// 2 = catégories écologiques
// 3 = catégories trophiques
// 4 = Colonnes

function explode( delimiter, string, limit ) {
	// http://kevin.vanzonneveld.net
    // +     original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     improved by: kenneth
    // +     improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     improved by: d3x
    // +     bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: explode(' ', 'Kevin van Zonneveld');
    // *     returns 1: {0: 'Kevin', 1: 'van', 2: 'Zonneveld'}
    // *     example 2: explode('=', 'a=bc=d', 2);
    // *     returns 2: ['a', 'bc=d']
 
    var emptyArray = { 0: '' };
    
    // third argument is not required
    if ( arguments.length < 2 ||
        typeof arguments[0] == 'undefined' ||
        typeof arguments[1] == 'undefined' )
    {
        return null;
    }
 
    if ( delimiter === '' ||
        delimiter === false ||
        delimiter === null )
    {
        return false;
    }
 
    if ( typeof delimiter == 'function' ||
        typeof delimiter == 'object' ||
        typeof string == 'function' ||
        typeof string == 'object' )
    {
        return emptyArray;
    }
 
    if ( delimiter === true ) {
        delimiter = '1';
    }
    
    if (!limit) {
        return string.toString().split(delimiter.toString());
    } else {
        // support for limit argument
        var splitted = string.toString().split(delimiter.toString());
        var partA = splitted.splice(0, limit - 1);
        var partB = splitted.join(delimiter.toString());
        partA.push(partB);
        return partA;
    }
}

// To be able to run javascript withinajax HTML
function parseScript(_source) {
	var source = _source;
	var scripts = new Array();
	
	// Strip out tags
	while(source.indexOf("<script") > -1 || source.indexOf("</script") > -1) {
		var s = source.indexOf("<script");
		var s_e = source.indexOf(">", s);
		var e = source.indexOf("</script", s);
		var e_e = source.indexOf(">", e);
		
		// Add to scripts array
		scripts.push(source.substring(s_e+1, e));
		// Strip from source
		source = source.substring(0, s) + source.substring(e_e+1);
	}
	
	// Loop through every script collected and eval it
	for(var i=0; i<scripts.length; i++) {
		//alert(scripts[i]);
		// Analyse if theren is a function
		// to be done
		try {
			eval(scripts[i]);
		}
		catch(ex) {
			
			// do what you want here when a script fails
		}
	}
	
	// Return the cleaned source
	return source;
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


function runFilieresExp(typePeche,typeAction,numtab,tableEnCours,validation,selToutesCol,selToutescat,selToutEsp,urlComp) {
// fonction runFilieresExp : permet de gerer l'affichage des différents tabs des differentes filieres pour les peches exp
// Y compris de recuperer toutes les valeurs saisies

	// On récupère les valeurs des différents paramètres entrée
	checkLog = document.getElementById("logsupp").checked;
	globaltypepeche = typePeche;
	qual = "";
	rest = "";
	poiss = "";
	listCE = "";
	listCT = "";
	listCol = "";
	ListEsp = "";
	EvalCE = false;
	EvalCT = false;
	if  (globalAction == typeAction) {
		changementAction = '';
		// On charge les données modifiées
		// Recuperation des selections sur qualite coup de peche
		qual = recupereSelection(5,"qualiteCP");
		// Recuperation des selections sur restriction aux coups du protocole
		rest  = recupereSelection(2,"restreindre");
		// Recuperation des selections sur choix des poissons
		poiss  = recupereSelection(4,"poisson");
		if (poiss=="pp,np") {
			alert("Vous devez au moins inclure soit les poissons, soit les non-poissons, mais pas exclure les deux");
			poiss="0,np";
		}
		// Recuperation des categories ecologiques / trophiques
		switch (selToutescat) {
			case "tout-CEco" :
				listCE = "XtoutX";
				EvalCE = true;
				break;
			case "aucun-CEco" :
				listCE = "XpasttX";
				EvalCE = true;
				break;
			case "tout-CTro" :
				listCT = "XtoutX";
				EvalCT = true;
				break;
			case "aucun-CTro" :
				listCT = "XpasttX";
				EvalCT = true;
				break;
		}
		if (!EvalCE) {
			limCE = document.getElementById("numCEco").value;
			listCE  = recupereSelection(limCE,"CEco");
		}
		if (!EvalCT) {
			limCT = document.getElementById("numCTro").value;
			listCT  = recupereSelection(limCT,"CTro");
		}
		// Recuperation des especes
		limEsp = document.getElementById("numEsp").value;
		if (!(selToutEsp == '')) {
			if 	(selToutEsp == 'tout') {
				ListEsp = 'XtoutX';
			} 	else {
				ListEsp = 'XpasttX';
			}
		} else {
			ListEsp  = recupereSelection(limEsp,"Esp");
		}
		if (ListEsp == '' && (!(globalAction=="peuplement"))) {
			if (listCE == '' || listCT == '') {
				alert("Merci de modifier cette catégorie pour qu'une espece au moins soit selectionnée  !");
				return;
			}
		}
		// Recuperation des colonnes en plus selectionnees
		TEC = "&TEC="+tableEnCours;
		TPEC = document.getElementById("tableEC").value;
		// Recuperation des colonnes
		limFac = document.getElementById("numFac").value;
		DeselCol = false;
		if (!(selToutesCol == '')) {
			if 	(selToutesCol == 'tout') {
				listCol = 'XtoutX';
			} 	else {
				listCol = 'XpasttX';
			}
		} else {
			for (i=1;i<=limFac;i++) {
				IDEnCours = TPEC+"fac"+i;
				if 	(document.getElementById(IDEnCours).checked) {
					if (listCol=="") {
						listCol = document.getElementById(IDEnCours).value+"-X";
					} else {
						listCol = listCol+","+document.getElementById(IDEnCours).value+"-X";
					}
				} 	else {
					if (listCol=="") {
						listCol = document.getElementById(IDEnCours).value+"-N";
					} else {
						listCol = listCol+","+document.getElementById(IDEnCours).value+"-N";
					}
					// On déselectionne la case a cocher tout
					document.getElementById('facTout').checked = false;
				}
			}
		}
		if (listCE == "") {
			addCE = "" ;
		} else {
			addCE = "&CE="+listCE;
		}
		if (listCT == "") {
			addCT = "" ;
		} else {
			addCT = "&CT="+listCT;
		}
		if (listCol == "") {
			addCol = "" ;
		} else {
			addCol = "&Col="+listCol;
		}
		if (ListEsp == "") {
			addEsp = "" ;
		} else {
			addEsp = "&Esp="+ListEsp;
		}
		
		addURL = "&qual="+qual+"&rest="+rest+"&pois="+poiss+addCE+addCT+TEC+addCol+addEsp;
	} else {
		globalAction = typeAction;
		changementAction = '&chgA=y';
		addURL = "";
	}
	ExpFic = '';
	if (validation == 'y') {
		if 	(document.getElementById("ExpFic").checked) {
			addExpFic = '&exf=y' ;
		} else {
			addExpFic = '';
		}
		var url="/extraction/extraction/extraction_resultat_exp.php?log="+checkLog+"&action="+typeAction+"&tab="+numtab+"&tp="+typePeche+addExpFic+addURL;
		window.location.replace(url);
	} else {
		//if (typeAction =='peuplement') {
		// Demander si on veut exporter sous forme de fichier.
		//	var url="/extraction/extraction/extraction_resultat_exp.php?log="+checkLog+"&action="+typeAction+"&tp="+typePeche
		//	window.location.replace(url)
		//} else {
		xmlHttp=GetXmlHttpObject();
		if (xmlHttp==null){
			alert ("votre navigateur n'est pas compatible avec AJAX !!");
			return;
		} 
		fenID = "resultfiliere";
		var url="/extraction/extraction/extraction_gestion_filieres_exp.php?log="+checkLog+"&action="+typeAction+"&tp="+typePeche+"&tab="+numtab+changementAction+addURL;
		xmlHttp.onreadystatechange=stateChanged2;
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
		//}
	}
} 


function stateChanged2() { 
// Fonction qui gere l'apres selection dans le tableau des filieres pour les peches experimentales
	if (xmlHttp.readyState==4)	{ 		
		peuActif = "";
		envActif = "";
		ntActif = "";
		bioActif = "";
		trophActif = "";
		
		if (globalAction=="peuplement") {peuActif=" active";}
		if (globalAction=="environnement") {envActif=" active";}
		if (globalAction=="NtPt") {ntActif=" active";}
		if (globalAction=="biologie") {bioActif=" active";}
		if (globalAction=="trophique") {trophActif=" active";}
		
		document.getElementById(fenID).innerHTML=xmlHttp.responseText;
		document.getElementById("runProcess").innerHTML="<b>Choix de la fili&egrave;re :</b>&nbsp;<a href=\"#\" onClick=\"runFilieresExp('"+globaltypepeche+"','peuplement','1','"+globalTableEnCours+"','n','','','','')\" class=\"peuplement"+peuActif+"\">Peuplement</a>&nbsp;-&nbsp;<a href=\"#\" onClick=\"runFilieresExp('"+globaltypepeche+"','environnement','1','"+globalTableEnCours+"','n','','','','')\" class=\"environnement"+envActif+"\">Environnement</a>&nbsp;-&nbsp;<a href=\"#\" onClick=\"runFilieresExp('"+globaltypepeche+"','NtPt','1','"+globalTableEnCours+"','n','','','','')\" class=\"NtPt"+ntActif+"\">Nt/Pt</a>&nbsp;-&nbsp;<a href=\"#\" onClick=\"runFilieresExp('"+globaltypepeche+"','biologie','1','"+globalTableEnCours+"','n','','','','')\" class=\"biologie"+bioActif+"\">Biologie</a>&nbsp;-&nbsp;<a href=\"#\" onClick=\"runFilieresExp('"+globaltypepeche+"','trophique','1','"+globalTableEnCours+"','n','','','','')\" class=\"trophique"+trophActif+"\">Trophique</a>";
		document.getElementById("exportFic").innerHTML= "<input type=\"button\" id=\"validation\" onClick=\"runFilieresExp('"+globaltypepeche+"','"+globalAction+"','1','','y','','','','')\" value=\"Afficher le resultat\"/><br/><input type=\"checkbox\" id=\"ExpFic\" checked=\"checked\"/>exporter en fichier";
	}
}


function runFilieresArt(typePeche,typeAction,numtab,tableEnCours,validation,selToutesCol,selToutescat,selToutEsp,urlComp) {
// fonction runFilieresArt : permet de gerer l'affichage des différents tabs des differentes filieres pour les peches art
// Y compris de recuperer toutes les valeurs saisies

	// On récupère les valeurs des différents paramètres entrée
	//document.getElementById("resultfiliere").innerHTML="<img src='/assets/ajax-loader_32px.gif' alt=''/>";
	checkLog = document.getElementById("logsupp").checked;
	globaltypepeche = typePeche;
	qual = "";
	rest = "";
	poiss = "";
	listCE = "";
	listCT = "";
	listCol = "";
	ListEsp = "";
	EvalCE = false;
	EvalCT = false;
	if  (globalAction == typeAction) {
		changementAction = '';
		// On charge les données modifiées
		// Recuperation des selections sur choix des poissons
		poiss  = recupereSelection(4,"poisson");
		if (poiss=="pp,np") {
			alert("Vous devez au moins inclure soit les poissons, soit les non-poissons, mais pas exclure les deux");
			poiss="0,np";
		}
		// Recuperation des categories ecologiques
		if ( typeAction == 'NtPart' || typeAction == 'taillart' ) {
			// Recuperation des categories ecologiques / trophiques
			switch (selToutescat) {
				case "tout-CEco" :
					listCE = "XtoutX";
					EvalCE = true;
					break;
				case "aucun-CEco" :
					listCE = "XpasttX";
					EvalCE = true;
					break;
				case "tout-CTro" :
					listCT = "XtoutX";
					EvalCT = true;
					break;
				case "aucun-CTro" :
					listCT = "XpasttX";
					EvalCT = true;
					break;
			}
			if (!EvalCE) {
				limCE = document.getElementById("numCEco").value;
				listCE  = recupereSelection(limCE,"CEco");
			}
			if (!EvalCT) {
				limCT = document.getElementById("numCTro").value;
				listCT  = recupereSelection(limCT,"CTro");
			}
			// Recuperation des especes
			limEsp = document.getElementById("numEsp").value;
			if (!(selToutEsp == '')) {
				if 	(selToutEsp == 'tout') {
					ListEsp = 'XtoutX';
				} 	else {
					ListEsp = 'XpasttX';
				}
			} else {
				ListEsp  = recupereSelection(limEsp,"Esp");
			}
		}
		if (ListEsp == ''  && (globalAction=="taillart" || globalAction=="NtPart")) {
			if (listCE == '' || listCT == '') {
				alert("Merci de modifier cette catégorie pour qu'une espece au moins soit selectionnée !");
				return;
			}
		}
		// Recuperation des colonnes en plus selectionnees
		TEC = "&TEC="+tableEnCours;
		TPEC = document.getElementById("tableEC").value;
		// Recuperation des colonnes
		limFac = document.getElementById("numFac").value;
		DeselCol = false;
		if (!(selToutesCol == '')) {
			if 	(selToutesCol == 'tout') {
					listCol = 'XtoutX';
			} 	else {
					listCol = 'XpasttX'
			}
		} else {
			for (i=1;i<=limFac;i++) {
				IDEnCours = TPEC+"fac"+i
				if 	(document.getElementById(IDEnCours).checked) {
					if (listCol=="") {
						listCol = document.getElementById(IDEnCours).value+"-X"
					} else {
						listCol = listCol+","+document.getElementById(IDEnCours).value+"-X"
					}
				} 	else {
					if (listCol=="") {
						listCol = document.getElementById(IDEnCours).value+"-N"
					} else {
						listCol = listCol+","+document.getElementById(IDEnCours).value+"-N"
					}
					// On déselectionne la case a cocher tout
					document.getElementById('facTout').checked = false
					DeselCol = true
				}
			}
		}
		if (listCE == "") {
			addCE = "" ;
		} else {
			addCE = "&CE="+listCE;
		}
		if (listCT == "") {
			addCT = "" ;
		} else {
			addCT = "&CT="+listCT;
		}
		if (listCol == "") {
			addCol = "" ;
		} else {
			addCol = "&Col="+listCol;
		}
		if (ListEsp == "") {
			addEsp = "" ;
		} else {
			addEsp = "&Esp="+ListEsp;
		}
		// Gestion des regroupements
		if (urlComp=="&nvReg=f" || urlComp=="&nvReg=f&gard=y") {
			ValNomReg = document.getElementById("nomReg").value;
			ValCodeReg = document.getElementById("codeReg").value;
			if (ValCodeReg=="") {
				alert('Merci de saisir le code du regroupement');
					urlComp = "&nvReg=y&gard=n&nomReg="+ValNomReg;
			} else {
				if (ValNomReg=="") {
					alert('Merci de saisir le libellé du regroupement');
					urlComp = "&nvReg=y&codeEC="+ValCodeReg;
				} else {
					urlComp =urlComp+"&nomReg="+ValNomReg+"&codeReg="+ValCodeReg;
				}
			}
		}
		if (urlComp=="&nvReg=y&gard=n") {
			// On ne garde pas le code en cours, on va proposer de garder le meme libellé
			ValNomReg = document.getElementById("nomReg").value;
			if (!(ValNomReg=="")) {
				urlComp =urlComp+"&nomReg="+ValNomReg;
			}
		}
		if (urlComp=="&nvReg=y&gard=n") {
			// On ne garde pas le code en cours, on va proposer de garder le meme libellé
			ValNomReg = document.getElementById("nomReg").value;
			if (!(ValNomReg=="")) {
				urlComp =urlComp+"&nomReg="+ValNomReg;
			}
		}
		EspSelec = "";
		if (urlComp=="&affEsp=y") {
			// On recupere le numero du regroupement selectionné + la liste des especes selectionnées
			var EspDispo = document.getElementById("especesDispo");
			for (i=0;i<EspDispo.options.length;i++) {
				if (EspDispo.options[i].selected) {
					if (EspSelec == "") {
						EspSelec = EspDispo.options[i].value;
					} else {
						EspSelec = EspSelec+","+EspDispo.options[i].value;
					}
				}
			}
			if (EspSelec=="") {
				alert('Merci de sélectionner au moins une espèce');
			} else {
				urlComp = urlComp + "&espAff="+	EspSelec;
			}
		}
		// On doit garder les espces sélectionnées comme groupe à part entière
		if (urlComp=="&affEsp=y&garder=y") {
			// On recupere le numero du regroupement selectionné + la liste des especes selectionnées
			var EspDispo = document.getElementById("especesDispo");
			for (i=0;i<EspDispo.options.length;i++) {
				if (EspDispo.options[i].selected) {
					if (EspSelec == "") {
						EspSelec = EspDispo.options[i].value;
					} else {
						EspSelec = EspSelec+","+EspDispo.options[i].value;
					}
				}
			}
			if (EspSelec=="") {
				alert('Merci de sélectionner au moins une espèce');
			} else {
				urlComp = urlComp + "&garder=y&espAff="+	EspSelec;
			}
		}
		EspaSupp= "";
		if (urlComp=="&suppEsp=EC") {
			// On recupere le numero du regroupement selectionné + la liste des especes selectionnées
			var EspSelReg = document.getElementById("Regroupcontenu");
			for (i=0;i<EspSelReg.options.length;i++) {
				if (EspSelReg.options[i].selected) { // Probleme sous IE8, ca plante a ce niveau
					if (EspaSupp == "") {
						EspaSupp = EspSelReg.options[i].value;
					} else {
						EspaSupp = EspaSupp+","+EspSelReg.options[i].value;
					}
				}
			}
			if (EspaSupp=="") {
				alert('Merci de sélectionner au moins une espèce');
			} else {
				urlComp = urlComp + "&espasup="+EspaSupp;
			}				
		}
		regEC="";
		if (!(urlComp=="")) { // Ca veut dire qu'on est en gestion des regroupements
			try{
				regEC = "&RegEC="+document.getElementById("Regroupement").selectedIndex;
			}
			catch (e) { }
		  
		}
		addURL = "&pois="+poiss+addCE+addCT+TEC+addCol+addEsp+urlComp+regEC;
	} else {
		globalAction = typeAction;
		changementAction = '&chgA=y';
		addURL = "";
	}
	if (validation == 'y') {
		if 	(document.getElementById("ExpFic").checked) {
			addExpFic = '&exf=y' ;
		} else {
			addExpFic ='';
		}
		var url="/extraction/extraction/extraction_resultat_art.php?log="+checkLog+"&action="+typeAction+"&tab="+numtab+"&tp="+typePeche+addExpFic+addURL;
		window.location.replace(url);
	} else {
		xmlHttp=GetXmlHttpObject();
		if (xmlHttp==null){
			alert ("votre navigateur n'est pas compatible avec AJAX !!");
			return;
		} 
		fenID = "resultfiliere";
		var url="/extraction/extraction/extraction_gestion_filieres_art.php?log="+checkLog+"&action="+typeAction+"&tp="+typePeche+"&tab="+numtab+changementAction+addURL;
		xmlHttp.onreadystatechange=stateChanged3;
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	}
} 

function runFilieresStat(typeStat,typeAction,numtab,tableEnCours,validation,selToutesCol,selToutescat,selToutEsp,urlComp) {
// fonction runFilieresStat : permet de gerer l'affichage des différents tabs des differentes filieres pour les statistiques
// C'est moins complique pour les peches, on ne gere que 2 tabs.
// Y compris de recuperer toutes les valeurs saisies

	// On récupère les valeurs des différents paramètres entrée

	//document.getElementById("resultfiliere").innerHTML="<img src='/assets/ajax-loader_32px.gif' alt=''/>";
	checkLog = document.getElementById("logsupp").checked;
	globaltypestat = typeStat;
	listCol = "";
	if  (globalAction == typeAction) {
		changementAction = '';
		// On charge les données modifiées
		// Recuperation de la table a extraire 
		// Recuperation des selections sur choix des especes
		// Recuperation des especes
		limEsp = document.getElementById("numEsp").value;
		if (!(selToutEsp == '')) {
			if 	(selToutEsp == 'tout') {
				ListEsp = 'XtoutX';
			} 	else {
				ListEsp = 'XpasttX';
			}
		} else {
			ListEsp  = recupereSelection(limEsp,"Esp");
		}
		// Pour les colonnes on ne recupere que l'info si on est exporte ou non les colonnes supplémentaires
		if 	(document.getElementById("facTout").checked) {
				listCol = 'XtoutX';
		} 	else {
				listCol = 'XpasttX'
		}
		addCol = "&Col="+listCol;

		if (ListEsp == "") {
			addEsp = "" ;
		} else {
			addEsp = "&Esp="+ListEsp;
		}
		if (ListEsp == '' ) {
			alert("Merci de selectionner au moins une espece !");
			return;
		}
		// Gestion des regroupements
		if (urlComp=="&nvReg=f" || urlComp=="&nvReg=f&gard=y") {
			ValNomReg = document.getElementById("nomReg").value;
			ValCodeReg = document.getElementById("codeReg").value;
			if (ValCodeReg=="") {
				alert('Merci de saisir le code du regroupement');
					urlComp = "&nvReg=y&gard=n&nomReg="+ValNomReg;
			} else {
				if (ValNomReg=="") {
					alert('Merci de saisir le libellé du regroupement');
					urlComp = "&nvReg=y&codeEC="+ValCodeReg;
				} else {
					urlComp =urlComp+"&nomReg="+ValNomReg+"&codeReg="+ValCodeReg;
				}
			}
		}
		if (urlComp=="&nvReg=y&gard=n") {
			// On ne garde pas le code en cours, on va proposer de garder le meme libellé
			ValNomReg = document.getElementById("nomReg").value;
			if (!(ValNomReg=="")) {
				urlComp =urlComp+"&nomReg="+ValNomReg;
			}
		}
		EspSelec = "";
		if (urlComp=="&affEsp=y") {
			// On recupere le numero du regroupement selectionné + la liste des especes selectionnées
			var EspDispo = document.getElementById("especesDispo");
			for (i=0;i<EspDispo.options.length;i++) {
				if (EspDispo.options[i].selected) {
					if (EspSelec == "") {
						EspSelec = EspDispo.options[i].value;
					} else {
						EspSelec = EspSelec+","+EspDispo.options[i].value;
					}
				}
			}
			if (EspSelec=="") {
				alert('Merci de sélectionner au moins une espèce');
			} else {
				urlComp = urlComp + "&espAff="+	EspSelec;
			}
		}
		// On doit garder les espces sélectionnées comme groupe à part entière
		if (urlComp=="&affEsp=y&garder=y") {
			// On recupere le numero du regroupement selectionné + la liste des especes selectionnées
			var EspDispo = document.getElementById("especesDispo");
			for (i=0;i<EspDispo.options.length;i++) {
				if (EspDispo.options[i].selected) {
					if (EspSelec == "") {
						EspSelec = EspDispo.options[i].value;
					} else {
						EspSelec = EspSelec+","+EspDispo.options[i].value;
					}
				}
			}
			if (EspSelec=="") {
				alert('Merci de sélectionner au moins une espèce');
			} else {
				urlComp = urlComp + "&garder=y&espAff="+	EspSelec;
			}
		}	
		EspaSupp= "";
		if (urlComp=="&suppEsp=EC") {
			// On recupere le numero du regroupement selectionné + la liste des especes selectionnées
			var EspSelReg = document.getElementById("Regroupcontenu");
			for (i=0;i<EspSelReg.options.length;i++) {
				if (EspSelReg.options[i].selected) {
					if (EspaSupp == "") {
						EspaSupp = EspSelReg.options[i].value;
					} else {
						EspaSupp = EspaSupp+","+EspSelReg.options[i].value;
					}
				}
			}
			if (EspaSupp=="") {
				alert('Merci de sélectionner au moins une espèce');
			} else {
				urlComp = urlComp + "&espasup="+EspaSupp;
			}				
		}
		regEC="";
		if (!(urlComp=="")) { // Ca veut dire qu'on est en gestion des regroupements
			try{
				regEC = "&RegEC="+document.getElementById("Regroupement").selectedIndex;
			}
			catch (e) { }
		  
		}
		addURL = addCol+addEsp+urlComp+regEC;;
	} else {
		globalAction = typeAction;
		changementAction = '&chgA=y';
		addURL = "";
	}
	if (validation == 'y') {
		if 	(document.getElementById("ExpFic").checked) {
			addExpFic ="&exf=y" ;

		} else {
			addExpFic ="";
		}
		var url="/extraction/extraction/extraction_resultat_stat.php?log="+checkLog+"&action="+typeAction+"&tab="+numtab+"&ts="+typeStat+addExpFic+addURL;
		window.location.replace(url);
	} else {
		xmlHttp=GetXmlHttpObject();
		if (xmlHttp==null){
			alert ("votre navigateur n'est pas compatible avec AJAX !!");
			return;
		} 	
		fenID = "resultfiliere";
		var url="/extraction/extraction/extraction_gestion_filieres_stat.php?log="+checkLog+"&action="+typeAction+"&ts="+typeStat+"&tab="+numtab+changementAction+addURL;
		xmlHttp.onreadystatechange=stateChanged4;
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	}
} 

function stateChanged1() {
	if (xmlHttp.readyState==4) {
		document.getElementById(fenID).innerHTML=xmlHttp.responseText;
	}
}


function stateChanged3() {
// Fonction qui gere l'apres selection dans le tableau des filieres pour les peches artisanales
	if (xmlHttp.readyState==4)	{ 		
		actActif = "";
		capActif = "";
		ntActif = "";
		strActif = "";
		engActif = "";
		
		if (globalAction=="activite") {actActif=" active";}
		if (globalAction=="capture") {capActif=" active";}
		if (globalAction=="NtPart") {ntActif=" active";}
		if (globalAction=="taillart") {strActif=" active";}
		if (globalAction=="engin") {engActif=" active";}
		response=xmlHttp.responseText;

		document.getElementById(fenID).innerHTML = parseScript(response);

		document.getElementById("runProcess").innerHTML="<b>Choix de la fili&egrave;re :</b>&nbsp;<a href=\"#\" onClick=\"runFilieresArt('"+globaltypepeche+"','activite','1','"+globalTableEnCours+"','n','','','','')\" class=\"activite"+actActif+"\">Activit&eacute</a>&nbsp;-&nbsp;<a href=\"#\" onClick=\"runFilieresArt('"+globaltypepeche+"','capture','1','"+globalTableEnCours+"','n','','','','')\" class=\"capture"+capActif+"\">Captures totales</a>&nbsp;-&nbsp;<a href=\"#\" onClick=\"runFilieresArt('"+globaltypepeche+"','NtPart','1','"+globalTableEnCours+"','n','','','','')\" class=\"NtPt"+ntActif+"\">Nt/Pt</a>&nbsp;-&nbsp;<a href=\"#\" onClick=\"runFilieresArt('"+globaltypepeche+"','taillart','1','"+globalTableEnCours+"','n','','','','')\" class=\"structure"+strActif+"\">Structure de taille</a>&nbsp;-&nbsp;<a href=\"#\" onClick=\"runFilieresArt('"+globaltypepeche+"','engin','1','"+globalTableEnCours+"','n','','','','')\" class=\"engin"+engActif+"\">Engin de p&ecirc;che</a>";
		document.getElementById("exportFic").innerHTML= "<input type=\"button\" id=\"validation\" onClick=\"runFilieresArt('"+globaltypepeche+"','"+globalAction+"','1','','y','','','','')\" value=\"afficher le resultat\"/><input type=\"checkbox\" id=\"ExpFic\" checked=\"checked\"/>exporter en fichier";
		
	}
}


function stateChanged4() {
// Fonction qui gere l'apres selection dans le tableau des filieres pour les statistiques
	if (xmlHttp.readyState==4)	{ 		
		gloActif = "";
		gtActif = "";
		
		if (globalAction=="globale") {gloActif=" active";}
		if (globalAction=="GT") {gtActif=" active";}
		
		document.getElementById(fenID).innerHTML=xmlHttp.responseText;
		//document.getElementById("runProcess").innerHTML="<a href=\"#\" onClick=\"runFilieresStat('"+globaltypestat+"','globale;','1','"+globalTableEnCours+"','n','','','','')\" class=\"globale"+gloActif+"\">statistiques</a>";
		document.getElementById("runProcess").innerHTML="";//on ne met rien pour l'instant dedans
		document.getElementById("exportFic").innerHTML= "<input type=\"button\" id=\"validation\" onClick=\"runFilieresStat('"+globaltypepeche+"','"+globalAction+"','1','','y','','','','')\" value=\"afficher le resultat\"/><input type=\"checkbox\" id=\"ExpFic\" checked=\"checked\"/>Exporter en fichier";
		
	}
}


function GetXmlHttpObject(){
	var xmlHttp=null;
	try{
	  // Firefox, Opera 8.0+, Safari
	  xmlHttp=new XMLHttpRequest();
	  }
	catch (e) {
	  // Internet Explorer
	  try{
		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
	  catch (e)	{
		xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	  }
	return xmlHttp;
}