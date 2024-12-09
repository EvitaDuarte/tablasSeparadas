//============================================================= 
function Enviar(sitio){
    //console.log('Sitio: '+sitio);
    //$('#main_container').fadeOut(600);
    //document.getElementById("main_container").fadeOut(600);
    setTimeout(function(){
        location.href = sitio;
    }, 600);
}
//=============================================================
function EnviarDropdown(clase, tipo, interes){
    //console.log('Clase: '+clase+'\nTipo: '+tipo+'\nInterés: '+interes);
    /*$('#clases_dropdown').fadeTo("fast" , 0);*/
    var media1200px = window.matchMedia("(min-width: 1200px)");
    if(media1200px.matches == false){
        console.log("Tactil")
    }
}
//=============================================================