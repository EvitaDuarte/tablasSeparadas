$(document).ready(function(e){

    const hamburger   = document.querySelector('.Menu .hamburger');
    const mobile_menu = document.querySelector('.Menu .nav-list ul');
    const header      = document.querySelector('.nav-bar');
    const menuLinks   = document.querySelectorAll("#menu_list .li-dr");
    const menuAdLinks   = document.querySelectorAll("#menu_list .li-dr li");
    const Side_Bar    = document.querySelector('.side-bar');

    hamburger.addEventListener('click', ()=>{
        hamburger.classList.toggle('active');
        mobile_menu.classList.toggle('active');
        Side_Bar.classList.toggle('active');

        // Animación
        menuLinks.forEach((link, index) => {
            if(link.style.animation){    
                $(link).removeAttr("style");
                $(link).fadeTo("slow", 0.1);
            }else{
                link.style.animation = `menuFade .5s ease forwards ${index / 7 + 0.3}s`;
            }
        });

        // Animación
        menuAdLinks.forEach((link, index) => {
            if(link.style.animation){    
                $(link).removeAttr("style");
                $(link).fadeTo("slow", 0);
            }else{
                link.style.animation = `menuFade 0s ease forwards 0s`;
            }
        });
    });

    $(window).click(function() {
        closeDefMenu();
    });
    
    $('.side-bar').click(function(event){
        event.stopPropagation();
    });

    $('.hamburger').click(function(event){
        event.stopPropagation();
    });

    $('.nav-list ul').click(function(event){
        event.stopPropagation();
    });

    $('#close_side').dblclick(function(){
        closeMenu();
    });

    function closeMenu(){
        hamburger.classList.toggle('active');
        mobile_menu.classList.toggle('active');
        Side_Bar.classList.toggle('active');
        
       // Animación
        menuLinks.forEach((link, index) => {
            if(link.style.animation){    
                $(link).removeAttr("style");
                $(link).fadeTo("slow", 0.1);
            }else{
                link.style.animation = `menuFade .5s ease forwards ${index / 7 + 0.3}s`;
            }
        });

        // Animación
        menuAdLinks.forEach((link, index) => {
            if(link.style.animation){    
                $(link).removeAttr("style");
                $(link).fadeTo("slow", 0);
            }else{
                link.style.animation = `menuFade 0s ease forwards 0s`;
            }
        });

    }

    function closeDefMenu(){
        hamburger.classList.remove('active');
        mobile_menu.classList.remove('active');
        Side_Bar.classList.remove('active');
        
       // Animación
        menuLinks.forEach((link, index) => {
            if(link.style.animation){    
                $(link).removeAttr("style");
                $(link).fadeTo("slow", 0.1);
            }
        });

        // Animación
        menuAdLinks.forEach((link, index) => {
            if(link.style.animation){    
                $(link).removeAttr("style");
                $(link).fadeTo("slow", 0);
            }
        });

    }

});