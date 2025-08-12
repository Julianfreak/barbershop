// js/usuarios.js (versión debug y funcional)
document.addEventListener("DOMContentLoaded", () => {
  console.log("usuarios.js cargado");

  const userForm = document.getElementById("userForm");
  const userTable = document.getElementById("userTable");

  console.log("Elementos:", { userForm, userTable });

  // Si no existe el form, salimos y mostramos error
  if (!userForm) {
    console.error("No se encontró el formulario #userForm. Revisa el HTML.");
    return;
  }

  // fallback: detectar el botón submit dentro del form y vigilar clicks
  const submitBtn =
    userForm.querySelector('button[type="submit"]') ||
    userForm.querySelector("button.btn-submit");
  console.log("submitBtn:", submitBtn);

  if (submitBtn) {
    submitBtn.addEventListener("click", (ev) => {
      console.log("submit button clicked (evento click)", ev);
      // no hacemos preventDefault aquí; solo debug
    });
  } else {
    console.warn(
      "No se encontró botón type='submit' dentro del formulario (comprobar HTML)."
    );
  }

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

    console.log("Valores del formulario:", {
      user,
      username,
      phone,
      email,
      role,
      passwordPresent: !!password1,
      confirmPresent: !!password,
    });

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
      // intentar parsear JSON
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

      // Estructura esperada: { success: true, usuarios: [...] }
      const usuarios =
        data && data.success && Array.isArray(data.usuarios)
          ? data.usuarios
          : [];
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
    } catch (err) {
      console.error("Error al obtener usuarios:", err);
    }
  }

  // pequeño helper
  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  // inicial
  cargarUsuarios();
});
