// import React, { useEffect, useState } from 'react';

// export default function MiPerfil() {
//   const [usuario, setUsuario] = useState(null);
//   const [error, setError] = useState(null);

//   useEffect(() => {
//     const obtenerPerfil = async () => {
//       try {
//         const response = await fetch('http://localhost:8000/api/mi_perfil', {
//           method: 'GET',
//           credentials: 'include',
//         });

//         const data = await response.json();

//         if (response.ok && data.status === 'success') {
//           setUsuario(data.usuario);
//         } else {
//           setError(data.mensaje || 'Debes iniciar sesión para ver tu perfil.');
//         }
//       } catch (err) {
//         setError('Error al conectar con el servidor');
//       }
//     };

//     obtenerPerfil();
//   }, []);

//   if (error) {
//     return <p className="text-red-600">{error}</p>;
//   }

//   if (!usuario) {
//     return <p>Cargando perfil...</p>;
//   }

//   return (
//     <div className="p-4">
//       <h1 className="text-xl font-bold">Bienvenido, {usuario.nombre}</h1>
//       <p>Email: {usuario.email}</p>
      
//     </div>
//   );
// }
