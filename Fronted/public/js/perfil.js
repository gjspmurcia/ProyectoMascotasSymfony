document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('perfil-container');

    try {
      const response = await fetch('http://localhost:8000/api/mi_perfil', {
        method: 'GET',
        credentials: 'include',
      });

      const data = await response.json();

      if (response.ok && data.status === 'success') 
    {
        const usuario = data.usuario;
        container.innerHTML = `
          <h1 class="text-3xl font-bold text-center mt-10">Bienvenido, ${usuario.nombre}</h1>
          <br/>
          <p><strong>Email:</strong> ${usuario.email}</p>
          <p><strong>DNI:</strong> ${usuario.dni}</p>
          <p><strong>Ciudad:</strong> ${usuario.cuidad}</p>
          <p><strong>Telefono:</strong> ${usuario.telefono}</p>
          <p><strong>Dirección:</strong> ${usuario.calle}, ${usuario.num_calle}, ${usuario.cod_postal}, ${usuario.cuidad}</p>
        `;
      } else {
        container.innerHTML = '<p class="text-red-600">Usuario no autenticado</p>';
      }
    } catch (err) {
      container.innerHTML = '<p class="text-red-600">Error al conectar con el servidor.</p>';
    }
  });