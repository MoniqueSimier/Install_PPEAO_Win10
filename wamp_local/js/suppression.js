/*
* Fonctions utilisées par le module de suppression des campagnes et des periodes d'enquetes
*/

/*
* Fonction utilisée pour insérer le <div><select> correspondant à la table systeme
*/
function showNextSelect(domaine,newLevel,last) {
	// domaine : le "domaine" dans lequel on travaille : "exp" (campagnes) ou "art" (periodes d'enquete)
	// newLevel: the level of the new div to create (used in <div id="level_n">)
	// currentSelect : le "nom" du select precedent, par exemple "pays" si on est sur le selecteur "systemes"
	// last : "last" si on est au DERNIER <select> de la liste

	var level=parseInt(newLevel)-1; // les niveaux sont 1,2,3 etc alors que les tableaux sont indexés à partir de 0
	var theLevel='level_'+level;
	var theSelect='select_'+level;
	var theValues=$(theLevel);
	
	// si on est au dernier <select> on n'essaie pas d'en ajouter un autre
	if (last!='last') {
	// si une valeur est sélectionnée
	var currentSelect=$(theSelect).firstChild;
	if ((currentSelect.selectedIndex!=-1)) {
		var xhr = getXhr();
		// what to do when the response is received
		xhr.onreadystatechange = function(){
			// only do something if the whole response has been received and the server says OK
			if(xhr.readyState == 4 && xhr.status == 200){
				//on récupère la réponse du serveur (le SELECT)
				theNewDivCode = xhr.responseText;
				// debug	alert(theNewDivCode);
				// on sélectionne le DIV  :
				var theForm=$('selector_form');
				//si le select existe  deja, on met a jour son contenu, sinon on l'ajoute
				if ($('level_'+newLevel)) {$('level_'+newLevel).innerHTML=theNewDivCode;} else {
				theForm.innerHTML+=theNewDivCode;}
			;} // end if xhr.readyState == 4
		} // end xhr.onreadystatechange
	
	// on appelle cette fonction pour supprimer d'éventuels SELECTs suivants	
	removedependentSelects(parseInt(newLevel)-1);
	// on passe les valeurs sélectionnées des SELECT dans l'URL
	var theString="&"+$('selector_form').toQueryString();
		
	// using GET to send the request
	// on récupère les valeurs des paramètres de l'URL (fonction gup() définie dans basic.js)
	xhr.open("GET","suppression/addNextSelect_ajax.php?&level="+newLevel+theString+"&domaine="+domaine,true);
	xhr.send(null);

	}// end if ((currentSelect.selectedIndex!=-1)
	
	// else if no value is selected, we remove the next criteria select and update the edit link
	else {
		removedependentSelects(parseInt(newLevel)-1);
		;}
	} // end if (last!='last') 
	
	// maintenant, on met a jour le lien permettant d'afficher les resultats
	updateSubmitLink(domaine);

}


/*
* Fonction utilisée pour mettre a jour le lien permettant d'afficher les resultats, chaque fois que la selection change
*/
function updateSubmitLink(domaine) {

		var xhr = getXhr();
		// what to do when the response is received
		xhr.onreadystatechange = function() {
			// only do something if the whole response has been received and the server says OK
			if(xhr.readyState == 4 && xhr.status == 200){
				//on récupère la réponse du serveur (le SELECT)
				theNewLinkText = xhr.responseText;
				// debug	alert(theNewDivCode);
				// on sélectionne le DIV  :
				var theLinkDiv=$('affiche_unites');
				//on met a jour son contenu
				theLinkDiv.innerHTML=theNewLinkText;
			;} // end if xhr.readyState == 4
		} // end xhr.onreadystatechange

	// on passe les valeurs sélectionnées des SELECT dans l'URL
	var theString="&"+$('selector_form').toQueryString();
		
	// using GET to send the request
	// on récupère les valeurs des paramètres de l'URL (fonction gup() définie dans basic.js)
	xhr.open("GET","/suppression/refreshSubmitLink_ajax.php?&domaine="+domaine+theString,true);
	xhr.send(null);
}

/**
* Fonction qui affiche la fenetre de dialogue modale pour supprimer une campagne ou une enquête
* domaine : exp ou art selon que l'on veut supprimer une campagne ou une periode d'enquete
* unite : l'identifiant unique de la campagne ou de l'enquête à supprimer
* theLevel  : le niveau de l'overlay (utilise si on veut afficher plusieurs overlays les uns devant les autres)
*/
function modalDialogDeleteUnite(theLevel,domaine,unite) {
// on crée le nouvel élément
	
	var theOverlay=new Element ('div', {
		'id': "overlay_"+theLevel,
		'class': "overlay",
		'style': "z-index:"+theLevel*1000
	}
	);
	
	var theOverlayWindow= new  Element ('div', {
		'id': "overlay_"+theLevel+"_window",
		'class': "overlay_window"
	}
	);
	
	var theOverlayContent= new  Element ('div', {
		'id': "overlay_"+theLevel+"_content",
		'class': "overlay_content"
	}
	);
	
	if (domaine=='exp') {var domaine_string='une campagne';} else {var domaine_string='une p&eacute;riode d\'enqu&ecirc;te';}
	
	theOverlayContent.innerHTML='<div align="center"><h1>supprimer '+domaine_string+'&quot;</h1></div>';
		
	var theOverlayButtons= new  Element ('div', {
		'id': "overlay_"+theLevel+"_buttons",
		'class': "overlay_buttons"
	}
	);
	
	var theOverlayLoaderDiv =new  Element ('div', {
		'id': "overlay_"+theLevel+"_loader",
		'class': "overlay_loader"
	}
	); 
	
	theOverlayButtons.innerHTML='<a id="overlay_'+theLevel+'_close" href="#" onclick="javascript:modalDialogClose(\'overlay_'+theLevel+'\',\'\');return false;" class="small link_button">annuler</a>';
	
	
	theOverlay.injectInside($E('body'));
	theOverlayWindow.injectInside(theOverlay);
	theOverlayLoaderDiv.injectInside(theOverlayWindow);
	theOverlayContent.injectInside(theOverlayWindow);
	theOverlayButtons.injectInside(theOverlayWindow);
	
	
	// on initialise l'objet AJAX	
	var xhr = getXhr();
	// what to do when the response is received
	xhr.onreadystatechange = function(){
			// en attendant la réponse, on remplace les boutons d'enregistrement/annulation par un loader
		var theLoader='<div align="center" id="the_loader_'+theLevel+'"><img src="/assets/ajax-loader.gif" alt="chargement en cours..." title="chargement en cours..." valign="center"/></div>';
		if(xhr.readyState < 4) { theOverlayLoaderDiv.innerHTML = theLoader;}
		// only do something if the whole response has been received and the server says OK
		if(xhr.readyState == 4 && xhr.status == 200){
			var theResponseText = xhr.responseText;
			
			// on affiche le message de confirmation de la suppression de l' enregistrement
			theOverlayLoaderDiv.innerHTML='';
			theOverlayContent.innerHTML=theResponseText;
			
			// on affiche le bouton "supprimer"			
			var theDeleteButton=new Element('a', {
			    'class': 'small link_button',
			    'href': '#',
				'id': "overlay_"+theLevel+"_delete",
				'onclick': 'sendUnitToDelete('+theLevel+',\''+domaine+'\',\''+unite+'\')'
			});
			theDeleteButton.innerHTML="supprimer";
			theDeleteButton.injectBefore("overlay_"+theLevel+"_close");
			
			// on met à jour la hauteur de l'overlay au cas où le dialogue soit plus haut que l'écran
			// si on ne fait pas ça, l'overlay ne couvre qu'une partie de la page
			window.addEvent('domready', function(){
			// il faudrait trouver comment declarer correctement la hauteur de l'overlay
			//	var theHeight=$E('body').height;
			//	alert(theHeight);
				$('overlay'+"_"+theLevel).setStyle('height','100%');
				});
			
			}  
	} // end xhr.onreadystatechange

	// using GET to send the request	
	xhr.open("GET","/suppression/suppression_unite_ajax.php?&domaine="+domaine+"&level="+theLevel+"&unite="+unite+"&action=ask",true);
	xhr.send(null);	
	
}


/**
* Fonction pour supprimer l'enregistrement choisi
*/
function sendUnitToDelete(theLevel,domaine,unite) {

var theDeleteButton=$('overlay_'+theLevel+'_delete');
var theLoader=$("overlay_"+theLevel+"_loader");
var theOverlayContent=$("overlay_"+theLevel+"_content");
var theCloseButton=$('overlay_'+theLevel+'_close');
var theTitle=$('delete_title').innerHTML;
// on initialise l'objet AJAX	
var xhr = getXhr();
// what to do when the response is received
xhr.onreadystatechange = function(){
		
	if(xhr.readyState < 4) {
		theDeleteButton.setStyle("visibility","hidden");
		theCloseButton.setStyle("visibility","hidden");
		// en attendant la réponse, on remplace les boutons d'enregistrement/annulation par un loader
			if (domaine=='exp') {var domaine_string='la campagne';} else {var domaine_string='la p&eacute;riode d\'enqu&ecirc;te';}
		theLoader.innerHTML='<h1>'+theTitle+'</h1><h2>suppression de '+domaine_string+' en cours</h2><img src="/assets/ajax-loader.gif" alt="suppression en cours..." title="suppression en cours..." valign="center"/>';
		theOverlayContent.innerHTML='';
	}
	// only do something if the whole response has been received and the server says OK
	if(xhr.readyState == 4 && xhr.status == 200){
		theLoader.innerHTML='';
		var theResponseText = xhr.responseText;
		// on affiche le résultat de la suppression
		theOverlayContent.innerHTML=theResponseText;
				
			// maintenant, on change le comportement du bouton "fermer" : on raffraichit l'affichage
			var over='overlay_'+theLevel;
			theCloseButton.setStyle("visibility","visible");
			theCloseButton.innerHTML='fermer';
			theCloseButton.setProperty("onclick","javascript:modalDialogClose(\'"+over+"\',\'refresh\')");
		} // end if xhr.readyState == 4 && xhr.status == 200
	
} // end xhr.onreadystatechange


// using GET to send the request
xhr.open("GET","/suppression/suppression_unite_ajax.php?&domaine="+domaine+"&unite="+unite+"&action=delete",true);
xhr.send(null);

}

