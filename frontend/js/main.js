import { setAppointments, wirePagerClicks, setRenderFunction } from './pagination.js';

// Utility: Format the date range
function formatDateRange(startDate, startTime, endDate, endTime) {
  const start = new Date(`${startDate}T${startTime}`);
  const end = new Date(`${endDate}T${endTime}`);
  const options = { weekday: 'short', month: 'short', day: 'numeric' };
  const timeOptions = { hour: 'numeric', minute: '2-digit' };

  return `${start.toLocaleDateString(undefined, options)} · ${start.toLocaleTimeString(undefined, timeOptions)} → ${end.toLocaleTimeString(undefined, timeOptions)}`;
}

// Utility: Get color class for status
function getStatusColor(status) {
  switch (status.toLowerCase()) {
    case "scheduled": return "warning";
    case "ongoing": return "success";
    case "completed": return "secondary";
    case "cancelled": return "danger";
    default: return "light";
  }
}

// Main: Render appointment cards
function renderAppointments(appointments) {
  const container = document.getElementById("appointmentsContainer");
  container.innerHTML = "";

  appointments.forEach(app => {
    container.innerHTML += `
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card appointment-card">
          <div class="card-body position-relative">
            <!-- Delete icon top-right -->
            <div class="position-absolute top-0 end-0 p-2">
              <i class="bi bi-three-dots-vertical text-muted btn-delete" data-id="${app.id}"></i>
            </div>
            <!-- Icon & title -->
            <div class="category-icon icon-${app.category.toLowerCase()}">
              <i class="bi ${app.icon_class}"></i>
            </div>
            <h5 class="card-title">${app.title}</h5>
            <!-- Date -->
            <div class="mb-2">
              <span class="badge text-primary-emphasis bg-primary-subtle d-inline-block mb-2 text-start rounded-pill">
                <i class="bi bi-calendar-event me-1"></i>
                ${formatDateRange(app.start_date, app.start_time, app.end_date, app.end_time)}
              </span>
            </div>
            <!-- Labels -->
            <span class="badge bg-primary me-1">${app.category}</span>
            <span class="badge bg-${getStatusColor(app.status)} me-1">${app.status}</span>
            <span class="badge bg-secondary">${app.type}</span>
            <!-- Description -->
            <p class="card-text mt-2 description-text d-flex justify-content-between align-items-center">
              <span id="desc-${app.id}" class="truncate-1 flex-grow-1">${app.description || "No description provided."}</span>
              <button class="btn btn-sm btn-more rounded-5 text-primary-emphasis bg-secondary-subtle btn-more"  aria-expanded="false" data-target="#desc-${app.id}">
                <i class="bi bi-caret-right"></i>
              </button>
            </p>
            <hr class="my-3">
            <!-- Footer: Creator & Actions -->
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-primary-emphasis px-2 bg-secondary-subtle rounded-pill">Author: ${app.creator_name}</span>
              <div class="d-flex justify-content-end gap-2">
                <button class="btn border border-2 rounded-4 text-primary-emphasis bg-secondary-subtle btn-share"><i class="bi bi-share me-1"></i></button>
                <button class="btn border border-2 rounded-4 text-primary-emphasis bg-secondary-subtle btn-edit" data-app='${JSON.stringify(app).replace(/"/g, "&quot;")}'>
                  <i class="bi bi-pencil me-1"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  // Wire up the description toggles
  wireDescriptionToggles();
  // Wire up the edit buttons
  wireEditButtons();
}
// Set the render function to be used by pagination.js
setRenderFunction(renderAppointments);

/**
 * Get every appointment from the backend,
 * store them in the global array,
 * then render only the current page.
 */
async function fetchAppointments() {
  try {
    const res = await fetch("../backend/appointments/appointments.php");
    const data = await res.json();     // save full list once
    setAppointments(data);   // setAppointments() slices & calls renderAppointments()
  } catch (err) {
    showToast("Unable to load appointments", "danger");
  }
}

// Show the modal and load categories
function showAppointmentModal() {
  const modal = new bootstrap.Modal(document.getElementById("appointmentModal"));
  modal.show();
  populateCategoryOptions();
}

// Call once in DOMContentLoaded to ensure categories are loaded
populateCategoryOptions().then(() =>fetchAppointments());
// Fetch and populate category dropdown
async function populateCategoryOptions() {
  const res = await fetch("../backend/categories/categories.php");
  const cats = await res.json();
  const select = document.querySelector("#appointmentForm select[name='category_id']");
  select.innerHTML = "";
  cats.forEach(c => {
    const opt = document.createElement("option");
    opt.value = c.id;
    opt.textContent = c.name;
    select.appendChild(opt);
  });
}


// === helper ===
function getFormJSON(form) {
  const data = Object.fromEntries(new FormData(form).entries());
  data.user_id = 1;          // <-- TEMP: until you inject the real user ID
  return data;
}

// === main handler ===
function setupAppointmentFormHandler() {
  const form = document.getElementById("appointmentForm");
  if (!form) return;

  form.addEventListener("submit", async e => {
    e.preventDefault();

    const payload = Object.fromEntries(new FormData(form).entries()); // ← keep the object
    const isEdit = !!payload.id; // check if we have an ID to determine if it's edit or create
    const url = isEdit 
      ? "../backend/appointments/update_appointment.php" 
      : "../backend/appointments/create_appointment.php";
    try {
      const res = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      const txt = await res.text();
      if (!txt.trim()) {
        showToast("Empty response from server", "danger");
        return;
      }
      const result = JSON.parse(txt);

      // const result = await res.json();

      if (result.success) {
        // ✅ different message / colour
        showToast(
          isEdit ? "Appointment updated successfully!" : "Appointment created successfully!",
          isEdit ? "info" : "success"
        );
        // Hide the modal and reset the form
        bootstrap.Modal.getInstance(
          document.getElementById("appointmentModal")
        ).hide();
        form.reset();
        form.id.value = ""; // clear id
        document.getElementById("appointmentModalLabel").textContent = "Create a New Appointment";
        form.querySelector("button[type='submit']").textContent = "Create Appointment";
        fetchAppointments();
      } else {
        showToast(result.error || result.sql_error || "Insert failed", "danger");
      }
    } catch (err) {
      showToast("Network or server error.", "danger");
    }
  });
}
// Utility: Edit an appointment
function wireEditButtons() {
  document
    .getElementById("appointmentsContainer")
    .addEventListener("click", e => {
      const btn = e.target.closest(".btn-edit");
      if (!btn) return;

      const app = JSON.parse(btn.dataset.app);

      // put values into the form
      const form   = document.getElementById("appointmentForm");
      form.id.value          = app.id;
      form.title.value       = app.title;
      form.description.value = app.description || "";
      form.category_id.value = app.category_id;
      form.start_date.value  = app.start_date;
      form.start_time.value  = app.start_time;
      form.end_date.value    = app.end_date;
      form.end_time.value    = app.end_time;
      form.type.value        = app.type;

      // change modal header / button label
      document.getElementById("appointmentModalLabel").textContent = "Edit Appointment";
      form.querySelector("button[type='submit']").textContent = "Update";

      // show modal
      bootstrap.Modal.getOrCreateInstance(
        document.getElementById("appointmentModal")
      ).show();
    });
}
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

// Utility: Delete an appointment
let deleteModal; // Keep a reference so we can hide it later
let confirmDeleteBtn; // reference to the red Delete button
function initDeleteModal () {
  deleteModal      = new bootstrap.Modal(document.getElementById("deleteConfirmModal"));
  confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

  // run only once
  confirmDeleteBtn.addEventListener("click", handleDeleteConfirm);
}

async function handleDeleteConfirm () {
  const id = confirmDeleteBtn.dataset.appId;          // set just before modal opens
  if (!id) return;

  try {
    const res    = await fetch("../backend/appointments/delete_appointment.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    });
    const result = await res.json();

    if (result.success) {
      showToast("Appointment deleted.", "warning");
      fetchAppointments();
    } else {
      showToast(result.error || "Delete failed.", "danger");
    }
  } catch (err) {
    showToast("Network or server error. ", "danger");
  } finally {
    deleteModal.hide();
  }
}
/* function openDeleteModal (id) {
  confirmDeleteBtn.dataset.appId = id;   // stash for handleDeleteConfirm()
  deleteModal.show();
}
// Make sure the global function is available
window.openDeleteModal = openDeleteModal; */

// Utility: Show  more or less Appointment description
function wireDescriptionToggles() {
  document.querySelectorAll(".btn-more").forEach(btn => {
    btn.addEventListener("click", () => {
      const targetSel = btn.getAttribute("data-target");
      const descSpan  = document.querySelector(targetSel);

      const expanded  = btn.getAttribute("aria-expanded") === "true";

      if (expanded) {
        // collapse
        descSpan.classList.add("truncate-1");
        btn.innerHTML = '<i class="bi bi-caret-right"></i>';
        btn.setAttribute("aria-expanded", "false");
      } else {
        // expand
        descSpan.classList.remove("truncate-1");
        btn.innerHTML = '<i class="bi bi-caret-down"></i>';
        btn.setAttribute("aria-expanded", "true");
      }
    });
  });
}

// ✅ Page Init
document.addEventListener("DOMContentLoaded", () => {
  // 0. wire up the delete modal
  initDeleteModal();
  /* 0-b. delegate clicks on all ⋮ delete icons */
  document.getElementById("appointmentsContainer")
    .addEventListener("click", e => {
      const icon = e.target.closest(".btn-delete");   // <i class="… btn-delete" data-id="…">
      if (!icon) return;
      confirmDeleteBtn.dataset.appId = icon.dataset.id; // stash id on red button
      deleteModal.show();                               // open Bootstrap modal
    });

  // 1. open modal when + button clicked
  const addBtn = document.getElementById("addAppointmentBtn");
  if (addBtn) addBtn.addEventListener("click", showAppointmentModal);
  // 2. attach submit-handler for the modal form
  setupAppointmentFormHandler();
  // 3. wire up the pagination controls
  wirePagerClicks();
  // 4. load existing appointments onto the dashboard
  fetchAppointments();
});

