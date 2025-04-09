document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('login-form');
    const mensaje = document.getElementById('mensaje');

    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      const email = form.email.value;
      const password = form.password.value;

      try {
        const response = await fetch('http://localhost:8000/api/login', {
          method: 'POST',
          credentials: 'include',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ email, password }),
        });

        const result = await response.json();

        if (response.ok) {
          mensaje.textContent = 'Inicio de sesión exitoso';
          mensaje.style.color = 'green';
          window.location.href = '/mi-perfil';
        } else {
          mensaje.textContent = result.mensaje || 'Error en el login';
        }
      } catch (error) {
        mensaje.textContent = 'Error al conectar con el servidor';
      }
    });
  });