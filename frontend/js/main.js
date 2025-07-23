
document.addEventListener("DOMContentLoaded", () => {
  fetch("../backend/appointments/appointments.php")
    .then(response => response.json())
    .then(data => {
      console.log("Fetched appointments:", data);
      renderAppointments(data);
    })
    .catch(error => {
      console.error("Error fetching appointments:", error);
    });

  // Optional: attach filter/search logic here
});

function renderAppointments(appointments) {
  const container = document.getElementById("appointmentsContainer");
  container.innerHTML = "";

  appointments.forEach(app => {
    container.innerHTML += `
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card appointment-card">
          <div class="card-body position-relative">
            <!-- Top-right delete icon -->
            <div class="position-absolute top-0 end-0 p-2">
              <i class="bi bi-three-dots-vertical text-muted" role="button" onclick="deleteAppointment(${app.id})"></i>
            </div>

            <div class="category-icon icon-${app.category.toLowerCase()}">
              <i class="bi ${app.icon_class}"></i>
            </div>
            <h5 class="card-title">${app.title}</h5>
            <!-- Subtle date badge -->
            <div class="mb-2">
              <span class="badge text-primary-emphasis bg-primary-subtle d-inline-block mb-2 text-start rounded-pill">
                <i class="bi bi-calendar-event me-1"></i>
                ${formatDateRange(app.start_date, app.start_time, app.end_date, app.end_time)}
              </span>
            </div>
            <span class="badge bg-primary me-1">
              ${app.category}
            </span>
            <span class="badge bg-${getStatusColor(app.status)} me-1">${app.status}</span>
            <span class="badge bg-secondary">${app.type}</span>
            <p class="card-text mt-2 description-text text-truncate d-flex justify-content-between align-items-center">
              <span class="text-truncate">
                ${app.description || "No description provided."}
              </span>
                <button class="btn rounded-5 text-primary-emphasis bg-secondary-subtle btn-more">
                  <i class="bi bi-caret-right text-muted toggle-desc" role="button" aria-expanded="false"></i>
                </button>
            </p>

            <!-- Separator -->
            <hr class="my-3">

            <!-- Action buttons -->
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-primary-emphasis px-2 bg-secondary-subtle rounded-pill"> Created by: ${app.creator_name}</span>
              <div class="d-flex justify-content-end gap-2">
                <button class="btn border border-2 rounded-4 text-primary-emphasis bg-secondary-subtle btn-share"><i class="bi bi-share me-1"></i></button>
                <button class="btn border border-2 rounded-4 text-primary-emphasis bg-secondary-subtle btn-edit"><i class="bi bi-pencil me-1"></i></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  });
}

function getStatusColor(status) {
  switch (status.toLowerCase()) {
    case "scheduled": return "warning";
    case "ongoing": return "success";
    case "completed": return "secondary";
    case "cancelled": return "danger";
    default: return "light";
  }
}
function formatDateRange(startDate, startTime, endDate, endTime) {
  const start = new Date(`${startDate}T${startTime}`);
  const end = new Date(`${endDate}T${endTime}`);

  const options = { weekday: 'short', month: 'short', day: 'numeric' };
  const timeOptions = { hour: 'numeric', minute: '2-digit' };

  return `${start.toLocaleDateString(undefined, options)} · ${start.toLocaleTimeString(undefined, timeOptions)} → ${end.toLocaleTimeString(undefined, timeOptions)}`;
}

