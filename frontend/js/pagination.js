// Pagination.js 
let allAppointments = [];
let currentPage = 1;
let pageSize = 9; // Default page size
let renderFn = () => {}; // Function to render appointments, set by the caller, it will be injected by the main.js

function setRenderFunction(fn) {
    renderFn = fn;
}
function renderPage() {
    const start = (currentPage -1) * pageSize;
    const slice = allAppointments.slice(start, start + pageSize);
    renderFn(slice);
    renderPagination();
}

// Render the pagination controls
function renderPagination() {
  const totalPages = Math.ceil(allAppointments.length / pageSize);
  const pager      = document.getElementById("pagination");
  if (!pager) return; // If there's no pager element, exit early
  pager.innerHTML  = "";

  const addItem = (label, page, disabled = false, active = false) => {
    pager.insertAdjacentHTML("beforeend",
      `<li class="page-item ${disabled ? "disabled":""} ${active?"active":""}">
         <a class="page-link" href="#" data-page="${page}">${label}</a>
       </li>`);
  };

  addItem("Prev", currentPage - 1, currentPage === 1);

  for (let p = 1; p <= totalPages; p++) {
    addItem(p, p, false, p === currentPage);
  }

  addItem("Next", currentPage + 1, currentPage === totalPages);
}

function wirePagerClicks() {
    document.getElementById("pagination").addEventListener("click", e => {
    const a = e.target.closest("a[data-page]");
    if (!a) return;
    e.preventDefault();
    const page = Number(a.dataset.page);
    const totalPages = Math.ceil(allAppointments.length / pageSize);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderPage();
    }
    });
    document.getElementById("pageSizeSelect").addEventListener("change", e => {
    pageSize   = Number(e.target.value);
    currentPage = 1;           // reset to first page
    renderPage();
});
}

// put at bottom of pagination.js
function setAppointments(list) {
  allAppointments = list;
  currentPage     = 1;
  renderPage();            // shows cards + builds pager
}
export { renderPage, renderPagination, wirePagerClicks, setRenderFunction, setAppointments };
