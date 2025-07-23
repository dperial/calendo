/*  filter-dashboard.js
    --------------------------------------------------------------
    Requires:
      - pagination.js  (exports setAppointments)
      - page already contains #categoryFilter, #statusFilter, #searchInput
    -------------------------------------------------------------- 
*/

import { setAppointments } from './pagination.js';

/* persistent copy of whatever fetchAppointments() hands us */
let masterAppointments = [];

/* initialise filters once the full list is known */
function initFilters(list) {
  masterAppointments = list;
  populateCategoryOptions(list);
  populateStatusOptions();
  attachFilterListeners();
}

/* ---------------------------- helpers ---------------------------- */

function populateCategoryOptions(list) {
  const select = document.getElementById('categoryFilter');
  const categories = [...new Set(list.map(a => a.category))].sort();

  select.innerHTML =
    '<option value="all" selected>All Categories</option>' +
    categories.map(c => `<option value="${c}">${c}</option>`).join('');
}

function populateStatusOptions() {
  const select = document.getElementById('statusFilter');
  select.innerHTML = `
      <option value="all" selected>All Status</option>
      <option value="scheduled">scheduled</option>
      <option value="ongoing">ongoing</option>
      <option value="completed">completed</option>
      <option value="cancelled">cancelled</option>`;
}

/* ---------- update filter-dashboard.js ---------- */

function attachFilterListeners() {
  ['categoryFilter', 'statusFilter'].forEach(id => {
    document.getElementById(id).addEventListener('change', applyFilters);
  });
  document.getElementById('searchInput').addEventListener('input', applyFilters);

  /* Reset button */
  document.getElementById('resetFiltersBtn').addEventListener('click', () => {
    document.getElementById('categoryFilter').value = 'all';
    document.getElementById('statusFilter').value   = 'all';
    document.getElementById('searchInput').value    = '';
    applyFilters();
  });
}

/* show active chips */
function updateActiveFilterChips(catSel, statSel, query) {
  const box = document.getElementById('activeFilters');
  box.innerHTML = '';                       // clear

  const addChip = (label, idReset) => {
    box.insertAdjacentHTML('beforeend',
      `<span class="badge rounded-pill bg-secondary d-flex align-items-center gap-1">
         ${label}
         <i class="bi bi-x-circle-fill cursor-pointer" data-clear="${idReset}"></i>
       </span>`);
  };

  if (catSel !== 'all')  addChip(catSel, 'categoryFilter');
  if (statSel!== 'all')  addChip(statSel, 'statusFilter');
  if (query)            addChip(`"${query}"`, 'searchInput');

  /* chip X click → clear that filter */
  box.querySelectorAll('[data-clear]').forEach(x =>
    x.addEventListener('click', () => {
      const id = x.dataset.clear;
      if (id === 'searchInput') document.getElementById(id).value = '';
      else                      document.getElementById(id).value = 'all';
      applyFilters();
    }));
}

/* -------------- applyFilters tweaks -------------- */
function applyFilters() {
  const catSel  = document.getElementById('categoryFilter').value.toLowerCase();
  const statSel = document.getElementById('statusFilter').value.toLowerCase();
  const query   = document.getElementById('searchInput').value.trim().toLowerCase();

  updateActiveFilterChips(catSel, statSel, query);   // 🆕 show chips

  const filtered = masterAppointments.filter(app => {
    const catOK  = catSel === 'all' || app.category.toLowerCase() === catSel;
    const statOK = statSel=== 'all' || app.status.toLowerCase()   === statSel;
    const textOK = query === '' || [
      app.title, app.description, app.category,
      app.status, app.type, app.creator_name
    ].join(' ').toLowerCase().includes(query);

    return catOK && statOK && textOK;
  });

  setAppointments(filtered);        // pagination refresh
}

/* ------------------- export for main.js ------------------------- */
export { initFilters };
