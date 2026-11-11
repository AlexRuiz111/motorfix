// Espera a que todo el documento HTML esté completamente dibujado antes de ejecutar nada.
document.addEventListener("DOMContentLoaded", () => {

    // Busca el formulario de login por su ID en el HTML.
    const loginForm = document.getElementById("loginForm");

    if (loginForm) { // Si el formulario existe en la página actual (es decir, estamos en index.html)...
        
        // Escucha el evento 'submit' (cuando el usuario pulsa el botón de enviar).
        loginForm.addEventListener("submit", async (e) => {
            e.preventDefault(); // Evita que la página se recargue por defecto (el comportamiento habitual de HTML).

            // Captura los valores que el usuario ha escrito usando el ID de los inputs.
            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;

            try {
                // Hace la petición asíncrona 'fetch' a nuestro endpoint PHP.
                const response = await fetch("auth_sw.php", {
                    method: "POST", // Usa el método POST por seguridad para enviar credenciales.
                    headers: {
                        "Content-Type": "application/json" // Avisa a PHP de que le mandamos un paquete JSON puro.
                    },
                    // Convierte el objeto de JavaScript a una cadena de texto JSON para poder viajar por la red.
                    body: JSON.stringify({
                        action: "login", // Le dece al "switch" de PHP qué puerta debe abrir.
                        email: email,
                        password: password
                    })
                });

                // Traduce la respuesta que devuelve PHP (texto JSON) de vuelta a un objeto JavaScript.
                const result = await response.json();

                if (response.ok) { // Si el código HTTP de respuesta es 200 (éxito)...
                    alert(result.message); // Muestra el mensaje (ej: "Inicio de sesión correcto").
                    window.location.href = "panel_cliente.html"; // Redirige automáticamente al panel del cliente.
                } else { // Si PHP devuelve 400, 401, 500, etc (error)...
                    alert("Error: " + result.message); // Muestra el error (ej: "Credenciales incorrectas").
                }
            } catch (error) { // Si el servidor se cae, PHP da un error fatal o no hay internet...
                console.error("Error en la petición de login:", error);
                alert("Error crítico de conexión con el servidor.");
            }
        });
    }

    // Busca el formulario de registro por su ID.
    const registroForm = document.getElementById("registroForm");

    if (registroForm) { // Si esta en la página de registro.html...
        
        registroForm.addEventListener("submit", async (e) => {
            e.preventDefault(); // Bloquea la recarga de la página.

            // FormData es un recolector automático: saca todos los datos del formulario usando los atributos "name" del HTML.
            const formData = new FormData(registroForm);
            
            // Convierte ese recolector en un objeto JavaScript normal y corriente (ej: { dni: "...", nombre: "..." }).
            const dataObj = Object.fromEntries(formData.entries());
            
            // Le inyecta manualmente la propiedad "action" para que el switch de PHP sepa qué hacer.
            dataObj.action = "registro";

            // Comprueba que las dos contraseñas son iguales antes de molestar al servidor.
            if (dataObj.password !== dataObj.confirm_password) {
                alert("Las contraseñas no coinciden.");
                return; // Corta la ejecución.
            }

            try {
                // Envia el paquete de datos al endpoint.
                const response = await fetch("auth_sw.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    // Transforma nuestro objeto con todos los datos del registro en un string JSON.
                    body: JSON.stringify(dataObj)
                });

                const result = await response.json(); // Lee la contestación de PHP.

                if (response.ok) { // Si PHP pudo hacer el INSERT en la base de datos sin problema...
                    alert(result.message); // "Cliente registrado correctamente".
                    window.location.href = "index.html"; // Lo envia a la pantalla de login para que entre con su nueva cuenta.
                } else { // Si faltaba un campo o el DNI ya existía...
                    alert("Error: " + result.message);
                }
            } catch (error) {
                console.error("Error en la petición de registro:", error);
                alert("Error crítico de conexión con el servidor.");
            }
        });
    }
});