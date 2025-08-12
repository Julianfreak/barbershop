
document.addEventListener("DOMContentLoaded", () => {
  console.log("usuarios.js cargado");

  const userForm = document.getElementById("userForm");
  const userTable = document.getElementById("userTable");

  if (!userForm || !userTable) {
    console.error("Faltan elementos en el DOM: #userForm o #userTable");
    return;
  }

  // botón submit del formulario
  const submitBtn =
    userForm.querySelector('button[type="submit"]') ||
    userForm.querySelector("button.btn-submit");

  // inputs relevantes
  const inputUser = document.getElementById("user");
  const inputUsername = document.getElementById("username");
  const inputPhone = document.getElementById("phone");
  const inputEmail = document.getElementById("email");
  const inputRole = document.getElementById("role");
  const pwd1 = document.getElementById("password1");
  const pwd2 = document.getElementById("confirmPassword");

  // cache de usuarios y estado de edición
  let usuariosCache = [];
  let editingId = null; // null => creación, >0 => edición

  // helper: escape
  function escapeHtml(str) {
    return String(str || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  // === validación en tiempo real de contraseñas ===
  function checkPasswords() {
    // si no hay inputs, salir
    if (!pwd1 || !pwd2 || !submitBtn) return;

    // En modo edición: si ambos vacíos -> permitir (no se actualiza contraseña)
    if (editingId && pwd1.value === "" && pwd2.value === "") {
      pwd2.setCustomValidity("");
      submitBtn.disabled = false;
      return;
    }

    if (pwd1.value !== pwd2.value) {
      pwd2.setCustomValidity("Las contraseñas no coinciden");
      submitBtn.disabled = true;
    } else {
      pwd2.setCustomValidity("");
      submitBtn.disabled = false;
    }
  }

  if (pwd1) pwd1.addEventListener("input", checkPasswords);
  if (pwd2) pwd2.addEventListener("input", checkPasswords);

  // === enviar formulario (crear / actualizar) ===
  userForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    console.log("Evento submit disparado");

    const user = inputUser.value.trim();
    const username = inputUsername.value.trim();
    const phone = inputPhone.value.trim();
    const email = inputEmail.value.trim();
    const role = inputRole.value;
    const password1 = pwd1 ? pwd1.value : "";
    const password = pwd2 ? pwd2.value : "";

    // validaciones básicas (nombre, user, rol)
    if (!user || !username || !role) {
      alert("Por favor completa los campos obligatorios: Nombre, Usuario, Rol.");
      return;
    }

    // Si no estamos en edición y contraseñas vacías -> error
    if (!editingId && (!password1 || !password)) {
      alert("Por favor ingresa y confirma la contraseña.");
      return;
    }

    // comprobación extra (debería ya manejarlo checkPasswords)
    if (password1 !== password) {
      alert("Las contraseñas no coinciden.");
      return;
    }

    // payload común
    const payload = {
      user,
      username,
      phone,
      email,
      role
    };

    // si hay contraseña y no está vacía -> incluir en payload
    if (password && password.length > 0) {
      payload.password = password;
    }

    // decidir endpoint
    const url = editingId ? "../backend/actualizar_usuarios.php" : "../backend/crear_usuarios.php";
    if (editingId) payload.id = editingId;

    try {
      console.log(`Enviando payload a ${url} ...`, payload);
      const res = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const text = await res.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (err) {
        console.error("Respuesta no JSON:", text);
        alert("Respuesta inesperada del servidor. Revisa la consola.");
        return;
      }

      if (data.success) {
        alert(data.message || (editingId ? "Usuario actualizado." : "Usuario creado."));
        // reset estado
        userForm.reset();
        editingId = null;
        if (submitBtn) submitBtn.textContent = "Añadir Usuario";
        // quitar boton cancelar si existe
        const cancelBtn = document.getElementById("cancelEditBtn");
        if (cancelBtn) cancelBtn.remove();
        // recargar tabla
        cargarUsuarios();
      } else {
        alert("Error: " + (data.message || "No se pudo guardar el usuario."));
      }
    } catch (err) {
      console.error("Error en fetch:", err);
      alert("Ocurrió un error de red al intentar guardar el usuario.");
    }
  });

  // === cargarUsuarios ===
  async function cargarUsuarios() {
    try {
      console.log("Solicitando lista a backend/obtener_usuarios.php");
      const res = await fetch("../backend/obtener_usuarios.php");
      const text = await res.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        console.error("Respuesta de obtener_usuarios.php no es JSON válido:", text);
        alert("No se pudo cargar la lista de usuarios (respuesta inválida del servidor).");
        return;
      }

      const usuarios = data && data.success && Array.isArray(data.usuarios) ? data.usuarios : [];
      usuariosCache = usuarios; // actualizar cache
      console.log("Usuarios recibidos:", usuarios);

      userTable.innerHTML = "";
      usuarios.forEach((u) => {
        const row = document.createElement("tr");
        row.innerHTML = `
          <td>${escapeHtml(u.nombre || "")}</td>
          <td>${escapeHtml(u.nombre_usuario || "")}</td>
          <td>${escapeHtml(u.rol || "")}</td>
          <td>${escapeHtml(u.telefono || "")}</td>
          <td>${escapeHtml(u.correo || "")}</td>
          <td>
            <button class="edit-btn" data-id="${u.id}">Editar</button>
            <button class="delete-btn" data-id="${u.id}">Eliminar</button>
          </td>
        `;
        userTable.appendChild(row);
      });

      // asignar eventos (eliminar y editar)
      asignarEventosEliminar();
      asignarEventosEditar();
    } catch (err) {
      console.error("Error al obtener usuarios:", err);
    }
  }

  // === eventos eliminar ===
  function asignarEventosEliminar() {
    document.querySelectorAll(".delete-btn").forEach((btn) => {
      btn.removeEventListener("click", null); // asegurar no duplicados (no perfecto, pero práctico)
      btn.addEventListener("click", async () => {
        const userId = btn.getAttribute("data-id");
        if (!userId) return;
        if (!confirm("¿Seguro que deseas eliminar este usuario?")) return;

        try {
          const res = await fetch("../backend/eliminar_usuarios.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(userId)}`,
          });
          const data = await res.json();
          if (data.success) {
            alert("Usuario eliminado correctamente.");
            // recargar tabla
            cargarUsuarios();
          } else {
            alert("Error al eliminar usuario: " + (data.message || "Desconocido"));
          }
        } catch (err) {
          console.error("Error en la solicitud:", err);
          alert("No se pudo conectar con el servidor.");
        }
      });
    });
  }

  // === eventos editar ===
  function asignarEventosEditar() {
    document.querySelectorAll(".edit-btn").forEach((btn) => {
      btn.removeEventListener("click", null);
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-id");
        if (!id) return;
        const usuario = usuariosCache.find((x) => String(x.id) === String(id));
        if (!usuario) {
          alert("Usuario no encontrado en cache.");
          return;
        }
        populateFormForEdit(usuario);
      });
    });
  }

  // rellenar formulario con datos del usuario para editar
  function populateFormForEdit(u) {
    editingId = u.id;
    inputUser.value = u.nombre || "";
    inputUsername.value = u.nombre_usuario || "";
    inputPhone.value = u.telefono || "";
    inputEmail.value = u.correo || "";
    inputRole.value = u.rol || "";

    // No mostramos contraseña: dejamos inputs vacíos y si el admin no las rellena, no se actualiza la contraseña.
    if (pwd1) pwd1.value = "";
    if (pwd2) pwd2.value = "";

    if (submitBtn) submitBtn.textContent = "Actualizar Usuario";

    // crear boton cancelar si no existe
    if (!document.getElementById("cancelEditBtn")) {
      const cancelBtn = document.createElement("button");
      cancelBtn.type = "button";
      cancelBtn.id = "cancelEditBtn";
      cancelBtn.textContent = "Cancelar";
      cancelBtn.style.marginLeft = "8px";
      submitBtn.insertAdjacentElement("afterend", cancelBtn);
      cancelBtn.addEventListener("click", () => {
        editingId = null;
        userForm.reset();
        if (submitBtn) submitBtn.textContent = "Añadir Usuario";
        cancelBtn.remove();
      });
    }

    // foco en el primer campo
    inputUser.focus();
    // actualizar validación contraseñas
    checkPasswords();
  }

  // inicial
  cargarUsuarios();
});
