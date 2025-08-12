// js/usuarios.js (versión debug y funcional)
document.addEventListener("DOMContentLoaded", () => {
  console.log("usuarios.js cargado");

  const userForm = document.getElementById("userForm");
  const userTable = document.getElementById("userTable");

  console.log("Elementos:", { userForm, userTable });

  // fallback: detectar el botón submit dentro del form y vigilar clicks
  const submitBtn =
    userForm.querySelector('button[type="submit"]') ||
    userForm.querySelector("button.btn-submit");
  console.log("submitBtn:", submitBtn);

  // attach del evento submit
  userForm.addEventListener("submit", async (e) => {
    console.log("Evento submit disparado");
    e.preventDefault();

    // capturar valores (IDs según tu HTML)
    const user = document.getElementById("user").value.trim() || "";
    const username = document.getElementById("username")?.value.trim() || "";
    const phone = document.getElementById("phone")?.value.trim() || "";
    const email = document.getElementById("email")?.value.trim() || "";
    const role = document.getElementById("role")?.value || "";
    const password1 = document.getElementById("password1")?.value || "";
    const password = document.getElementById("confirmPassword")?.value || "";


    // validaciones
    if (!user || !username || !email || !role || !password1 || !password) {
      alert("Por favor completa todos los campos.");
      return;
    }
    if (password1 !== password) {
      alert("Las contraseñas no coinciden.");
      return;
    }

    const payload = { user,username, phone, email, role, password };

    try {
      console.log("Enviando payload a backend/crear_usuario.php ...", payload);
      const res = await fetch("../backend/crear_usuario.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      // Debug: status + raw text si no es JSON
      console.log("Response status:", res.status, res.statusText);
      const text = await res.text();
      try {
        const data = JSON.parse(text);
        console.log("Respuesta JSON del backend:", data);
        if (data.success) {
          alert(data.message || "Usuario creado.");
          userForm.reset();
          cargarUsuarios();
        } else {
          alert("Error: " + (data.message || "No se pudo crear el usuario."));
        }
      } catch (parseErr) {
        console.error("No se pudo parsear JSON; respuesta cruda:", text);
        alert(
          "El servidor devolvió una respuesta inesperada. Revisa la consola (Network/Response)."
        );
      }
    } catch (err) {
      console.error("Error de fetch:", err);
      alert("Ocurrió un error de red al intentar crear el usuario.");
    }
  });

  // ========== cargarUsuarios ==========
async function cargarUsuarios() {
  try {
    console.log("Solicitando lista a backend/obtener_usuarios.php");
    const res = await fetch("../backend/obtener_usuarios.php");
    console.log("Status:", res.status);

    const text = await res.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      console.error(
        "Respuesta de obtener_usuarios.php no es JSON válido:",
        text
      );
      alert(
        "No se pudo cargar la lista de usuarios (respuesta inválida del servidor)."
      );
      return;
    }

    const usuarios =
    data && data.success && Array.isArray(data.usuarios) ? data.usuarios : [];
    console.log("Usuarios recibidos:", usuarios);

    userTable.innerHTML = "";

    usuarios.forEach((user) => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${escapeHtml(user.nombre_usuario || "")}</td>
        <td>${escapeHtml(user.rol || "")}</td>
        <td>${escapeHtml(user.telefono || "")}</td>
        <td>${escapeHtml(user.correo || "")}</td>
        <td>
          <button class="edit-btn" data-id="${user.id}">Editar</button>
          <button class="delete-btn" data-id="${user.id}">Eliminar</button>
        </td>
      `;
      userTable.appendChild(row);
    });

    // Asignar eventos después de renderizar
    asignarEventosEliminar();
  } catch (err) {
    console.error("Error al obtener usuarios:", err);
  }
}

function asignarEventosEliminar() {
  document.querySelectorAll(".delete-btn").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const userId = btn.getAttribute("data-id");

      if (confirm("¿Seguro que deseas eliminar este usuario?")) {
        try {
          const res = await fetch("../backend/eliminar_usuarios.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(userId)}`,
          });

          const data = await res.json();

          if (data.success) {
            alert("Usuario eliminado correctamente.");
            setTimeout(() => {
            cargarUsuarios();
            }, 0);
          } else {
            alert(
              "Error al eliminar usuario: " + (data.message || "Desconocido")
            );
          }
        } catch (error) {
          console.error("Error en la solicitud:", error);
          alert("No se pudo conectar con el servidor.");
        }
      }
    });
  });
}

// Helper
function escapeHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

// Inicial
cargarUsuarios();

});
