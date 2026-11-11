// js/login.js
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();

  fetch("../backend/login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "include", // envía la cookie de sesión PHP
    body: JSON.stringify({ username, password }),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Respuesta del servidor:", data);
      if (data.success === true) {
        // Guardar en sessionStorage para usar en el frontend (UI solamente)
        sessionStorage.setItem("usuarioActivo", data.username);
        sessionStorage.setItem("rolUsuario", data.rol_id);
        sessionStorage.setItem("idUsuario", data.id);

        // Redirigir al menú
        window.location.href = "html/menu.html";
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error al conectar con el servidor:", error);
      alert("Error de conexión al servidor.");
    });
});
