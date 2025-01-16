// fonctions javascript utilisées par le module de sélection des données à extraire

function goToNextStep(current_step,url) {
	// current_step: le numero de l'etape ACTUELLE
	// url: l'url qui doit servir de base pour l'etape suivante
	url=replaceQueryString(url,'step',current_step+1);
	var theStepForm=document.getElementById("step_"+current_step+"_form");
	//var formValues=theStepForm.toQueryString();
	//stupid stupid IE... this does not work but the line below does... go figure!!!
	var formValues=$("step_"+current_step+"_form").toQueryString();
	if (formValues=='') {var separator='';} else {var separator='&';}
	url=url+separator+formValues;	
	document.location=url;
}

// fonction qui effectue une requete AJAX pour mettre a jour le selecteur de systemes quand on change le pays selectionne
function refreshSystemes(liste_campagnes, liste_enquetes) {

// liste_campagnes: un tableau contenant la liste des id des campagnes deja filtrees
// liste_enquetes: un tableau contenant la liste des id des enquetes deja filtrees
	var paysSelect=$("pays");
	var systemesDiv=$("systemes_div");
	// si une valeur est sélectionnée
	if ((paysSelect.selectedIndex!=-1)) {
		var xhr = getXhr();
		// what to do when the response is received
		xhr.onreadystatechange = function(){
			// only do something if the whole response has been received and the server says OK
			if(xhr.readyState == 4 && xhr.status == 200){
				//on récupère la réponse du serveur (les <options> du select)
				var theNewOptions = xhr.responseText;
				// on remplace le contenu du <select id=systemes>  :
				//systemesSelect.innerHTML=theNewOptions; stupid stupid IE, it does not like this,
				//donc on doit generer le <select> et son contenu et le renvoyer via xhr:
				// on le peuple avec les <option>
				systemesDiv.innerHTML=theNewOptions;

				
			;} // end if xhr.readyState == 4
		} // end xhr.onreadystatechange
	
	// on passe les valeurs sélectionnées des SELECT dans l'URL
	var theString="&"+$('step_3_form').toQueryString();

	// using GET to send the request
	xhr.open("GET","/extraction/selection/refresh_systemes_ajax.php?&campagnes="+liste_campagnes+"&enquetes="+liste_enquetes+theString,true);
	xhr.send(null);

	}// end if ((currentSelect.selectedIndex!=-1)
	
	// else if no value is selected, we remove the next criteria select
	else {
		systemesDiv.innerHTML='';
		
		;}
	// on affiche le lien pour passer a l'etape suivante si au moins une valeur est selectionnee dans l'un des deux <select>
	//toggleNextStepLink('pays','systemes','step_3_link');
}

// fonction qui effectue une requete AJAX pour mettre a jour le selecteur de secteurs quand on change le systeme selectionne
function refreshSecteurs(liste_enquetes) {
// liste_enquetes: un tableau contenant la liste des id des enquetes deja filtrees
	var systemes2Select=$("systemes2");
	var secteursSelect=$("secteurs");
	// si une valeur est sélectionnée
	if ((systemes2Select.selectedIndex!=-1)) {
		var xhr = getXhr();
		// what to do when the response is received
		xhr.onreadystatechange = function(){
			// only do something if the whole response has been received and the server says OK
			if(xhr.readyState == 4 && xhr.status == 200){
				//on récupère la réponse du serveur (les <options> du select)
				theNewOptions = xhr.responseText;
				// debug 	alert(theNewOptions);
				// on remplace le contenu du <select id=systemes>  :
				secteursSelect.innerHTML=theNewOptions;
			;} // end if xhr.readyState == 4
		} // end xhr.onreadystatechange
	
	// on passe les valeurs sélectionnées des SELECT dans l'URL
	var theString="&"+$('step_7_form').toQueryString();

	// using GET to send the request
	xhr.open("GET","/extraction/selection/refresh_secteurs_ajax.php?&enquetes="+liste_enquetes+theString,true);
	xhr.send(null);

	}// end if ((currentSelect.selectedIndex!=-1)
	
	// else if no value is selected, we remove the next criteria select
	else {
		secteursSelect.innerHTML='';
		
		;}
		// on affiche le lien pour passer a l'etape suivante si au moins une valeur est selectionnee dans l'un des deux <select>
	toggleNextStepLink('systemes2','secteurs','step_7_link');
}

// fonction qui effectue une requete AJAX pour mettre a jour le selecteur de periode quand on change une des selections
function refreshPeriode(selection,debut_annee,debut_mois,fin_annee,fin_mois) {

// element : quelle selection a change
// valeurs possibles : d_a pour annee de debut, d_m pour mois de debut, f_a pour annee de fin
// debut_annee : annee de debut de la periode complete 
// debut_mois : mois de debut de la periode complete
//fin_annee : annee de fin de la periode complete
// fin_mois : mois de fin de la periode complete
	// quel est le <select>  concerne?
	var theSelect=$(selection);
	// quelle est la valeur selectionnee?
	var theValue=theSelect.value;
	var theResponseText="";
	
	// si la valeur selectionnee est -1 on ne fait rien
	if (theValue!=-1) {
	// on declare les variables
	var d_a=""; var d_m=""; var f_a=""; var f_m="";
	
	if (selection=='d_a') {d_a=theValue;}
	if (selection=='d_m') {d_a=$("d_a").value;d_m=theValue}
	if (selection=='f_a') {d_a=$("d_a").value; d_m=$("d_m").value;f_a=theValue;}
	if (selection=='f_m') {d_a=$("d_a").value; d_m=$("d_m").value;f_a=$("f_a").value;f_m=theValue;}


		var xhr = getXhr();
		// what to do when the response is received
		xhr.onreadystatechange = function(){
			// only do something if the whole response has been received and the server says OK
			if(xhr.readyState == 4 && xhr.status == 200){
				//on récupère la réponse du serveur (les <options> du select)
				theResponseText = xhr.responseText;
				// debug 	alert(theResponseText);
				// on remplace le <select> concerne et on efface les suivants  :
				if (selection=='d_a') 
					{$("div_d_m").innerHTML=theResponseText; $("div_f_a").innerHTML=""; $("div_f_m").innerHTML="";}
				if (selection=='d_m') 
					{$("div_f_a").innerHTML=theResponseText;  $("div_f_m").innerHTML="";}
				if (selection=='f_a') 
					{$("div_f_m").innerHTML=theResponseText;}
				// cas particulier: si on vient de choisir un mois de fin, on affiche le lien pour passer a la suite
				if (selection=='f_m') {
					$("step_4_link").innerHTML=theResponseText;
				}
			;} // end if xhr.readyState == 4
		} // end xhr.onreadystatechange
	
	// on passe les valeurs sélectionnées des SELECT dans l'URL
	var theString="&"+$('step_4_form').toQueryString();
	
	// using GET to send the request
	xhr.open("GET","/extraction/selection/refresh_periode_ajax.php?selection="+selection+"&d_a="+d_a+"&d_m="+d_m+"&f_a="+f_a+"&debut_annee="+debut_annee+"&debut_mois="+debut_mois+"&fin_annee="+fin_annee+"&fin_mois="+fin_mois,true);
	xhr.send(null);

	}
}


// fonction qui permet de passer de la derniere etape de la selection au choix des filieres d'extraction
function goToChoixFilieres(url) {
	// url: l'url qui doit servir de base pour l'etape suivante
	if (document.getElementById("metadataForm")) {
	// stupid IE, this does not work
	//theForm=document.getElementById("metadataForm")
	//var formValues=theForm.toQueryString();
	//but this does:
	var formValues=$("metadataForm").toQueryString();
	}
	if (formValues=='') {var separator='';} else {var separator='&';}
	url=url+separator+formValues;
	
	document.location=url;
}

// fonction qui permet d'aficher et masque les textes d'aide
function toggleAide(topic) {
	if ($(topic).getStyle('display')=='none') {$(topic).setStyle('display','block');} else {$(topic).setStyle('display','none');}
}


// fonction qui permet d'afficher ou de masquer les liens permettant de passer à l'etape suivante de la selection
function toggleNextStepLink(inputId1,inputId2,linkId) {
// inputId1, inputId2 : les id uniques des SELECT a tester (si un seul SELECT, indiquer deux fois son id, ***DIRTY HACK***)
	// si une valeur au moins est sélectionnée dans l'une des deux, on affiche le lien, sinon on le masque
	if ($(inputId1).selectedIndex!=-1 || $(inputId2).selectedIndex!=-1) {$(linkId).setStyle('display','block');} 
		else {$(linkId).setStyle('display','none');}
}


/**
* Fonction générique qui permet de sélectionner ou désélectionner toutes les valeurs d'un SELECT
*/
function toggleSelectSelection(select,what,next_step_link) {
	// select : l'id du SELECT à utiliser
	// what : que sélectionner ('all' ou 'none')
	// next_step_link : l'id du lien permettant de passer a l'etape suivante (a afficher ou masquer)
	
	// on pointe le SELECT à utiliser
	var theSelect=$(select);
	// on récupère les OPTIONS de ce SELECT
	var theOptions=theSelect.getElements('option');
	
	// on boucle sur les options
	for (i=0;i<theOptions.length;i++) {
		// si on veut tout sélectionner
		if (what=='all') {theOptions[i].setProperty('selected','selected');
		$(next_step_link).setStyle('display','block');}
		// si on veut tout désélectionner
		if (what=='none') {theOptions[i].removeProperty('selected');
		$(next_step_link).setStyle('display','none');}
	} // end for
	
}
