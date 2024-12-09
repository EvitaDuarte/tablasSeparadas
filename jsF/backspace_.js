function deshabilitarRetroceso(event) {
    const esInput = event.target.tagName.toLowerCase() === 'input';
    const esSoloLectura = event.target.readOnly;
    
    // Deshabilitar el comportamiento del retroceso solo si no se está editando un campo de entrada de texto
    if (!esInput || esSoloLectura) {
        const teclaBackspace = 8; // Código de la tecla Backspace
        if (event.keyCode === teclaBackspace) {
            //console.log("detecto backspace")
            event.preventDefault();
        }
    }
}

// Agregar un event listener para el evento keydown
document.addEventListener('keydown', deshabilitarRetroceso);