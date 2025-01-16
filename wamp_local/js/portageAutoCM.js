//**************************************************
// PortageAutpCM.js
//**************************************************
// Creation le 21-10-2008 par Yann Laurent
// Gestion de l'affichage des CR dans le portage auto
// CM = cache misere car le code ci-dessous est miserable.... J'ai honte...
//**************************************************

window.addEvent('domready', function() {
var mySlide = new Fx.Slide('vertical_slide');
mySlide.hide();
$('v_slidein').addEvent('click', function(e){
	e = new Event(e);
	mySlide.slideIn();
	e.stop();
});
 
$('v_slideout').addEvent('click', function(e){
	e = new Event(e);
	mySlide.slideOut();
	e.stop();
});
 

});

// A mettre a jour pour gérer les affichages des comptes rendus

