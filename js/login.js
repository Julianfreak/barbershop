
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();

  fetch("../backend/login.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ username, password }),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Respuesta del servidor:", data);
      if (data.success) {
        localStorage.setItem("role", data.role);
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



function inicializarRegistroVentas() {
  const precios = {
    servicios: {
      corte_basico: 15000,
      corte_avanzado: 25000,
      corte_barba: 30000,
      barba: 10000,
    },
    productos: {
      shampoo: 12000,
      cera: 8000,
      gel: 7000,
      maquina: 50000,
      tratamiento: 20000,
    },
  };
  const elementos = {
    servicioSelect: document.getElementById("servicio"),
    productosCheckboxes: document.querySelectorAll(
      "#productos input[type='checkbox']"
    ),
    totalInput: document.getElementById("total"),
    ventaForm: document.getElementById("ventaForm"),
    ventasTable: document.getElementById("ventasTable"),
  };

  function calcularTotal() {
    let total = 0;
    const servicioSeleccionado = elementos.servicioSelect.value;
    if (servicioSeleccionado && precios.servicios[servicioSeleccionado]) {
      total += precios.servicios[servicioSeleccionado];
    }
    elementos.productosCheckboxes.forEach((checkbox) => {
      if (checkbox.checked) {
        total += precios.productos[checkbox.value];
      }
    });
    elementos.totalInput.value = total;
  }

  function registrarVenta(e) {
    e.preventDefault();
    const empleado = document.getElementById("usuario").value.trim();
    const servicio =
      elementos.servicioSelect.options[elementos.servicioSelect.selectedIndex]
        ?.text || "N/A";
    const productos = Array.from(elementos.productosCheckboxes)
      .filter((checkbox) => checkbox.checked)
      .map((checkbox) => checkbox.nextSibling.textContent.trim())
      .join(", ");
    const total = elementos.totalInput.value;
    if (!empleado || total === "0") {
      alert("Por favor, complete todos los campos correctamente.");
      return;
    }
    const nuevaFila = document.createElement("tr");
    nuevaFila.innerHTML = `
            <td>${empleado}</td>
            <td>${servicio}</td>
            <td>${productos || "Sin productos"}</td>
            <td>$${parseInt(total).toLocaleString("es-CO")}</td>
        `;
    elementos.ventasTable.appendChild(nuevaFila);
    elementos.ventaForm.reset();
    elementos.totalInput.value = 0;
  }
  elementos.servicioSelect.addEventListener("change", calcularTotal);
  elementos.productosCheckboxes.forEach((checkbox) =>
    checkbox.addEventListener("change", calcularTotal)
  );
  elementos.ventaForm.addEventListener("submit", registrarVenta);
}

document.addEventListener("DOMContentLoaded", inicializarRegistroVentas);

/* Validación del formulario de inicio de sesión
document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault(); // Evita el envío por defecto

    // Obtener valores ingresados por el usuario
    const username = document.getElementById("username").value.trim();
    const password = document.getElementById("password").value.trim();

    // Validar campos vacíos
    if (!username || !password) {
        alert("Por favor, complete todos los campos.");
        return; 
    }

    // Validar credenciales
    const userFound = validUsers.find(user => user.username === username && user.password === password);

    if (userFound) {
        window.location.href = "menu.html";
    } else {
        alert("Credenciales incorrectas. Intenta nuevamente.");
    }
});

// usuarios.js

/* Espera a que el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function () {
    // Verifica si el formulario y la tabla existen
    const userForm = document.getElementById("userForm");
    const userTable = document.getElementById("userTable");

    if (!userForm) {
        console.error("Formulario de usuario no encontrado.");
        return;
    }

    if (!userTable) {
        console.error("Tabla de usuarios no encontrada.");
        return;
    }

    // Lista de usuarios inicial (simulada)
    let users = [];

    // Función para actualizar la tabla de usuarios
    function updateUserTable() {
        console.log("Actualizando tabla de usuarios...");
        // Limpiar la tabla
        userTable.innerHTML = "";

        // Renderizar cada usuario
        users.forEach((user, index) => {
            const row = document.createElement("tr");

            row.innerHTML = `
                <td>${user.username}</td>
                <td>${user.role}</td>
                <td>${user.phone}</td>
                <td>${user.email}</td>
                <td>
                    <button class="btn btn-edit" onclick="editUser(${index})">Editar</button>
                    <button class="btn btn-delete" onclick="deleteUser(${index})">Eliminar</button>
                </td>
            `;

            userTable.appendChild(row);
        });

        console.log("Tabla actualizada:", users);
    }

    // Función para manejar el envío del formulario
    userForm.addEventListener("submit", function (e) {
        e.preventDefault(); // Evitar el comportamiento por defecto
        console.log("Formulario enviado");

        // Obtener los valores del formulario
        const username = document.getElementById("username").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const email = document.getElementById("email").value.trim();
        const role = document.getElementById("role").value;

        // Validar campos
        if (!username || !phone || !email || !role) {
            alert("Por favor, complete todos los campos.");
            return;
        }

        // Añadir el nuevo usuario a la lista
        users.push({ username, phone, email, role });

        // Actualizar la tabla
        updateUserTable();

        // Resetear el formulario
        userForm.reset();

        alert("Usuario añadido correctamente.");
    });

    // Función para editar un usuario
    function editUser(index) {
        console.log(`Editando usuario en posición ${index}`);
        // Obtener el usuario a editar
        const user = users[index];

        // Pedir nuevos datos al usuario
        const newUsername = prompt("Editar nombre de usuario:", user.username);
        const newPhone = prompt("Editar teléfono:", user.phone);
        const newEmail = prompt("Editar correo:", user.email);
        const newRole = prompt("Editar rol (admin/empleado):", user.role);

        // Validar los nuevos datos
        if (newUsername && newPhone && newEmail && newRole) {
            users[index] = {
                username: newUsername,
                phone: newPhone,
                email: newEmail,
                role: newRole
            }; // Actualizar datos

            updateUserTable(); // Actualizar la tabla
            alert("Usuario editado correctamente.");
        } else {
            alert("Los datos no pueden estar vacíos.");
        }
    }

    // Función para eliminar un usuario
    function deleteUser(index) {
        console.log(`Eliminando usuario en posición ${index}`);
        if (confirm("¿Seguro que desea eliminar este usuario?")) {
            users.splice(index, 1); // Eliminar usuario de la lista
            updateUserTable(); // Actualizar la tabla
            alert("Usuario eliminado correctamente.");
        }
    }

    // Inicializar la tabla (vacía al cargar la página)
    updateUserTable();
}); */
