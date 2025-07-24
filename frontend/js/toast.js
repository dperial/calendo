// Utility: Show a toast message
function showToast(message, variant = "success") {
  const toastEl = document.getElementById("saveToast");

  // change color class dynamically
  toastEl.classList.remove("bg-success", "bg-danger", "bg-warning", "bg-info");
  toastEl.classList.add(`bg-${variant}`);

  toastEl.querySelector(".toast-body").textContent = message;

  const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3000 });
  toast.show();
}