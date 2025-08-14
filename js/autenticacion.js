// js/auth.js
document.addEventListener("DOMContentLoaded", () => {
  const usuario = sessionStorage.getItem("usuarioActivo");
  if (!usuario) {
    // Sin sesión -> al login
    window.location.href = "../index.html";
    return;
  }

  const usuarioInput = document.getElementById("usuario");
  if (usuarioInput) {
    usuarioInput.value = usuario;
    usuarioInput.readOnly = true;
  }
});
