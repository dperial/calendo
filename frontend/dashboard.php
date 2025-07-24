<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <?php include __DIR__ . '/partials/head.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>
  <!-- Main Content -->
  <div class="container">
    <!-- Filter Section -->
    <div class="row mt-3 justify-content-center">
      <div class="col-md-3">
        <select class="form-select" id="categoryFilter">
          <option selected value="all">All Categories</option>
          <!-- Add options dynamically -->
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select" id="statusFilter">
          <option selected value="all">All Status</option>
          <!-- Add options dynamically -->
        </select>
      </div>
      <div class="col-md-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search appointments...">
      </div>
      <div class="col-md-3">
        <div class="row">
            <div class="col-auto ms-auto">
                <label class="me-2">Show</label>
                <select id="pageSizeSelect" class="form-select d-inline-block w-auto">
                <option value="9" selected>9</option>
                <option value="18">18</option>
                <option value="27">27</option>
                </select>
                <label class="ms-2">Appointments / Page</label>
            </div>
        </div>
      </div>
    </div>
    <!-- Filter chips + reset (single row) -->
    <div class="row mt-2">
        <div class="col">
            <div class="d-flex align-items-center gap-3 flex-wrap"
                id="filterRow">
            <button id="resetFiltersBtn"
                    class="btn btn-outline-secondary btn-sm flex-shrink-0">
                <i class="bi bi-x-circle me-1"></i> Reset filters
            </button>

            <!-- chips will be injected here -->
            <div id="activeFilters" class="d-flex flex-wrap gap-2"></div>
            </div>
        </div>
    </div>

    <!-- Add Appointment Button -->
    <div class="row mt-4">
        <div class="col d-flex justify-content-end">
            <button class="btn btn-primary px-4 py-2" id="addAppointmentBtn">
            <i class="bi bi-plus-circle me-1"></i>
            Add Appointment
            </button>
        </div>
    </div>
    <!-- Appointment Modal -->
     <!-- Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="appointmentForm" class="modal-content">
            <input type="hidden" name="id" value="">
            <div class="modal-header">
                <h5 class="modal-title" id="appointmentModalLabel">Create a New Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Fields -->
                <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                <label class="form-label">Category</label>
                <select id="categorySelect" name="category_id" class="form-select" required>
                    <!-- Populate via JS -->
                </select>
                </div>
                <!-- Status -->
                 <div class="mt-3">
                    <label class="form-label">Status</label>
                    <select name="status" id="statusSelect" class="form-select">
                        <option value="scheduled">Scheduled</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
                </div>
                <div class="mt-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Create Appointment</button>
            </div>
            </form>
        </div>
    </div>
    <!-- End Appointment Modal -->
    <!-- Appointment Cards Grid -->
    <div class="row mt-4" id="appointmentsContainer">
      <!-- Cards will be injected here -->
    </div>

    <!-- Pagination (Optional) -->
    <nav class="mt-4 paginate" aria-label="Page navigation example">
      <ul id="pagination" class="pagination justify-content-center mb-0"></ul>
    </nav>
  </div>
  <!-- Toast container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
    <div id="saveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
        <div class="toast-body">
            <!-- message will be injected here -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    </div>
    <!-- Delete-confirm modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1"
        aria-labelledby="deleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="deleteConfirmLabel">Delete Appointment?</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
        </div>

        <div class="modal-body">
            Are you sure you want to delete this appointment?  
            This action can’t be undone.
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="confirmDeleteBtn"
                    class="btn btn-danger">Delete</button>
        </div>
        </div>
    </div>
    </div>

  <script>
    // Example function to render one appointment card
    function renderAppointmentCard(app) {
      return `
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">${app.title}</h5>
              <span class="badge bg-primary card-badge">${app.category}</span>
              <span class="badge bg-${app.status === 'cancelled' ? 'danger' : app.status === 'ongoing' ? 'success' : 'warning'} card-badge">${app.status}</span>
              <span class="badge bg-secondary card-badge">${app.type}</span>
              <p class="card-text mt-2">
                ${app.start_date} ${app.start_time} → ${app.end_date} ${app.end_time}
              </p>
            </div>
          </div>
        </div>
      `;
    }
  </script>
  <script type="module" src="js/main.js"></script>
</body>
</html>
