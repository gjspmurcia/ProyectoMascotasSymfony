document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("formRecuperarPassword");
    const mensaje = document.getElementById("mensaje");

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      const email = form.email.value;
      
      try {
        const response = await fetch("http://localhost:8000/api/recuperar-password", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({ email }),
        });
  
        const result = await response.json();
  
        if (response.ok) {
          mensaje.textContent = "Se ha enviado un enlace de recuperación a tu correo electrónico.";
          mensaje.classList.remove("text-red-600");
          mensaje.classList.add("text-green-600");
        } else {
          mensaje.textContent = result.mensaje || "Error al enviar el correo.";
          mensaje.classList.remove("text-green-600");
          mensaje.classList.add("text-red-600");
        }
      } catch (error) {
        mensaje.textContent = "Error al conectar con el servidor.";
        mensaje.classList.remove("text-green-600");
        mensaje.classList.add("text-red-600");
      }
    });
  });