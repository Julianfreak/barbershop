// js/login.js
/*
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();

  fetch("../backend/login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ username, password }),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Respuesta del servidor:", data);
      if (data.success == true) {
        sessionStorage.setItem("usuarioActivo", data.username);
        sessionStorage.setItem("rolUsuario", data.rol);
        sessionStorage.setItem("idUsuario", data.id);
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
*/
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();

  fetch("../backend/login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ username, password }),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Respuesta del servidor:", data);
      if (data.success === true) {
        // Guardar en sessionStorage para usar en el frontend
        sessionStorage.setItem("usuarioActivo", data.username);
        sessionStorage.setItem("rolUsuario", data.rol_id); // 🔹 corregido
        sessionStorage.setItem("idUsuario", data.id);

        // Redirigir
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
