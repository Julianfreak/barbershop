// js/ventas.js
document.addEventListener("DOMContentLoaded", () => {
  const usuarioActivo = sessionStorage.getItem("usuarioActivo");
  const ventaForm = document.getElementById("ventaForm");
  const ventasTable = document.getElementById("ventasTable");
  const inputUsuario = document.getElementById("usuario");
  const selectServicio = document.getElementById("servicio");
  const divProductos = document.getElementById("productos");
  const inputTotal = document.getElementById("total");

  // Si no hay sesión en frontend, manda al login (defensa básica de UI)
  if (!usuarioActivo) {
    window.location.href = "../index.html";
    return;
  }

  // Solo para mostrar el usuario en la UI (no se envía al backend)
  if (inputUsuario) {
    inputUsuario.value = usuarioActivo;
    inputUsuario.readOnly = true;
  }

  if (!ventaForm || !ventasTable) return;

  // Precios base (referenciales para UI — el backend debe recalcular)
  const PRECIOS_SERVICIOS = {
    corte_basico: 10000,
    corte_avanzado: 15000,
    corte_barba: 18000,
    barba: 8000,
  };

  const PRECIOS_PRODUCTOS = {
    shampoo: 55000,
    cera: 25000,
    gel: 15000,
    maquina: 180000,
    tratamiento: 75000,
  };

  function calcularTotal() {
    const serv = selectServicio ? selectServicio.value : "";
    let total = PRECIOS_SERVICIOS[serv] || 0;

    const checks = divProductos
      ? divProductos.querySelectorAll('input[type="checkbox"]:checked')
      : [];

    checks.forEach((c) => {
      const key = c.value;
      total += PRECIOS_PRODUCTOS[key] || 0;
    });

    if (inputTotal) inputTotal.value = total;
    return total;
  }

  // Recalcular total cuando cambie el servicio o productos
  if (selectServicio) selectServicio.addEventListener("change", calcularTotal);
  if (divProductos) divProductos.addEventListener("change", calcularTotal);

  // (Opcional) cargar ventas con sesión
  async function cargarVentas() {
    try {
      const res = await fetch("../backend/obtener_ventas.php", {
        method: "GET",
        credentials: "include", // importante para enviar cookie de sesión
      });
      const text = await res.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch {
        console.error("Respuesta no JSON:", text);
        return;
      }

      const ventas =
        data && data.success && Array.isArray(data.ventas) ? data.ventas : [];
      ventasTable.innerHTML = "";

      ventas.forEach((v) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${escapeHtml(v.empleado || "")}</td>
          <td>${escapeHtml(v.servicio || "")}</td>
          <td>${escapeHtml(v.productos || "")}</td>
          <td>${escapeHtml(String(v.total || ""))}</td>
        `;
        ventasTable.appendChild(tr);
      });
    } catch (e) {
      console.error(e);
    }
  }

  function getProductosSeleccionados() {
    const checks = divProductos
      ? divProductos.querySelectorAll('input[type="checkbox"]:checked')
      : [];
    // Enviamos los nombres de producto; el backend mapeará a IDs y precios
    return Array.from(checks).map((c) => c.value);
  }

  if (ventaForm) {
    ventaForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const servicio = selectServicio ? selectServicio.value.trim() : "";
      const productos = getProductosSeleccionados();
      let total = Number(inputTotal && inputTotal.value ? inputTotal.value : 0);

      // Forzar recálculo por si el campo quedó desactualizado
      if (!total || total < 0) total = calcularTotal();

      // Validaciones mínimas en el frontend
      if (!servicio && productos.length === 0) {
        alert("Debes seleccionar un servicio y/o al menos un producto.");
        return;
      }
      if (!total || total <= 0) {
        alert("El total debe ser mayor a 0.");
        return;
      }

      // Preparamos los datos que enviamos al backend
      const payload = {
        servicio: servicio,
        productos: productos,
        total: total,
      };

      try {
        const res = await fetch("../backend/crear_venta.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include", // envía cookie de sesión (PHPSESSID)
          body: JSON.stringify(payload),
        });

        const text = await res.text();
        let data;
        try {
          data = JSON.parse(text);
        } catch {
          console.error("Respuesta no JSON:", text);
          alert("Respuesta inesperada del servidor.");
          return;
        }

        if (data.success) {
          alert(data.message || "Venta registrada.");
          ventaForm.reset();
          calcularTotal(); // recalcula y limpia total
          // cargarVentas(); // si quieres refrescar la lista
        } else {
          alert(
            "No se pudo registrar la venta: " +
              (data.message || "Error desconocido")
          );
        }
      } catch (err) {
        console.error(err);
        alert("Error de red al registrar la venta.");
      }
    });
  }

  function escapeHtml(str) {
    return String(str || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;");
  }

  // Calcular total inicial
  calcularTotal();
  // cargarVentas(); // opcional
});
