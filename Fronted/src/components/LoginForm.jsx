import React, { useState } from 'react';

export default function LoginForm() {
  const [mensaje, setMensaje] = useState(null);

  const handleLogin = async (event) => {
    event.preventDefault();
    const form = event.target;
    const email = form.email.value;
    const password = form.password.value;

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
      setMensaje('Inicio de sesión exitoso');
      window.location.href = '/mi-perfil';
    } else {
      setMensaje(result.mensaje || 'Error en el login');
    }
  };

  return (
    <form onSubmit={handleLogin} className="flex flex-col gap-2 p-4">
      <label>Email:</label>
      <input type="email" name="email" required className="border p-1" />

      <label>Contraseña:</label>
      <input type="password" name="password" required className="border p-1" />

      <button type="submit" className="bg-teal-600 text-white p-2 mt-2">Iniciar sesión</button>

      {mensaje && <p className="text-red-600">{mensaje}</p>}
    </form>
  );
}
