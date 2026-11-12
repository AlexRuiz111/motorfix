document.addEventListener("DOMContentLoaded", async () => {

    // COMPROBAR SEGURIDAD NADA MÁS ENTRAR
    try {
        // Hace una petición GET  a la nueva puerta 'session'
        const response = await fetch("auth_sw.php?action=session");

        if (!response.ok) { 
            // Si PHP devuelve un error (ej. 401 No autorizado), vuelve al index.
            window.location.href = "index.html";
            return; // Corta la ejecución para que no cargue nada más.
        }

        const result = await response.json();

    } catch (error) {
        // Si el servidor falla, se expulsa.
        console.error("Error de seguridad:", error);
        window.location.href = "index.html";
    }

    // LÓGICA DEL BOTÓN "CERRAR SESIÓN"
    const btnLogout = document.getElementById("btnLogout");

    if (btnLogout) {
        btnLogout.addEventListener("click", async () => {
            try {
                // Avisa a PHP de que destruya la sesión
                await fetch("auth_sw.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ action: "logout" })
                });

                // Se redirige a la pantalla de login
                window.location.href = "index.html";
            } catch (error) {
                console.error("Error al cerrar sesión:", error);
            }
        });
    }
});