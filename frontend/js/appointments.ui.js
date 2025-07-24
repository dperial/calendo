/* appointments.ui.js – keeps ALL function names from your old main.js */
import * as api                       from "./appointments.api.js";
import { setAppointments, setRenderFunction } from "./pagination.js";
import { initFilters }                from "./filter-dashboard.js";

/* ───────────────── helpers ─────────────────────────── */
export function formatDateRange(sd, st, ed, et) {
  const s=new Date(`${sd}T${st}`), e=new Date(`${ed}T${et}`);
  const dOpt={weekday:"short",month:"short",day:"numeric"};
  const tOpt={hour:"numeric",minute:"2-digit"};
  return `${s.toLocaleDateString(undefined,dOpt)} ·
          ${s.toLocaleTimeString(undefined,tOpt)} → ${e.toLocaleTimeString(undefined,tOpt)}`;
}
export function getStatusColor(s){
  switch(s.toLowerCase()){case"scheduled":return"warning";
    case"ongoing":return"info";case"completed":return"success";
    case"cancelled":return"danger";default:return"light";}
}
export function showToast(msg,variant="success"){
  const el=document.getElementById("saveToast");
  el.className=`toast text-white bg-${variant}`;
  el.querySelector(".toast-body").textContent=msg;
  bootstrap.Toast.getOrCreateInstance(el,{delay:2500}).show();
}

/* ───────────────── rendering ───────────────────────── */
export function renderAppointments(list){
  const box=document.getElementById("appointmentsContainer"); box.innerHTML="";
  list.forEach(a=>box.insertAdjacentHTML("beforeend",cardHtml(a)));
  wireDescriptionToggles();  // add expand/collapse buttons
  wireContainerClicks();     // add click handlers for container
}
setRenderFunction(renderAppointments);

function cardHtml(a){return`<div class="col-md-6 col-lg-4 mb-4">
<div class="card appointment-card"><div class="card-body position-relative">
 <div class="position-absolute top-0 end-0 p-2">
   <i class="bi bi-three-dots-vertical text-muted btn-delete" data-id="${a.id}"></i></div>
 <div class="category-icon icon-${a.category.toLowerCase()}"><i class="bi ${a.icon_class || 'bi-tag'}"></i></div>
 <h5 class="card-title truncate-1">${a.title}</h5>
 <span class="badge text-primary-emphasis bg-primary-subtle d-inline-block mb-2 text-start rounded-pill"><i class="bi bi-calendar-event me-1"></i>${formatDateRange(a.start_date,a.start_time,a.end_date,a.end_time)}</span><br>
 <span class="badge bg-primary me-1">${a.category}</span>
 <span class="badge bg-${getStatusColor(a.status)} me-1">${a.status}</span>
 <span class="badge bg-secondary">${a.type}</span>
 <p class="card-text mt-2 d-flex justify-content-between">
   <span id="desc-${a.id}" class="truncate-1 flex-grow-1">${a.description||"No description provided."}</span>
   <button class="btn btn-sm btn-more bg-secondary-subtle rounded-pill" data-target="#desc-${a.id}" aria-expanded="false"><i class="bi bi-caret-right"></i></button>
 </p><hr>
 <div class="d-flex justify-content-between">
   <span class="badge text-primary-emphasis px-2 bg-secondary-subtle rounded-pill">Author: ${a.creator_name}</span>
   <button class="btn btn-edit text-primary-emphasis bg-secondary-subtle rounded-pill" data-app='${JSON.stringify(a).replace(/"/g,"&quot;")}'><i class="bi bi-pencil"></i></button>

   </div></div></div></div>`;}

/* ───────────────── description toggle ──────────────── */
export function wireDescriptionToggles(){
  document.querySelectorAll(".btn-more").forEach(b=>{
    b.onclick=()=>{const s=document.querySelector(b.dataset.target);
      const ex=b.getAttribute("aria-expanded")==="true";
      s.classList.toggle("truncate-1",ex);
      b.innerHTML=`<i class="bi bi-caret-${ex?'right':'down'}"></i>`;
      b.setAttribute("aria-expanded",(!ex).toString());
    };
  });
}

/* ───────────────── modal helpers ────────────────────── */
export async function populateCategoryOptions() {
  const r    = await fetch("../backend/categories/categories.php");
  const cats = await r.json();

  // try the original selector *or* an explicit id if you added one
  const sel = document.querySelector(
    "#appointmentForm select[name='category_id'], #categorySelect"
  );

  sel.innerHTML = cats
    .map(c => `<option value="${c.id}">${c.name}</option>`)
    .join("");
}

export function showAppointmentModal(){openModalCreate();}   // keeps old name

function openModalCreate(){
  const f=document.getElementById("appointmentForm"); f.reset(); f.id.value="";
  document.getElementById("appointmentModalLabel").textContent="Create a New Appointment";
  f.querySelector("button[type='submit']").textContent="Create Appointment";
  populateCategoryOptions();
  bootstrap.Modal.getOrCreateInstance('#appointmentModal').show();
}

function openModalEdit(app) {
  const f = document.getElementById("appointmentForm");

  populateCategoryOptions().then(() => {
    /* hidden id field */
    f.id.value           = app.id;

    /* plain inputs / textarea */
    f.title.value        = app.title;
    f.description.value  = app.description || "";
    f.start_date.value   = app.start_date;
    f.start_time.value   = app.start_time;
    f.end_date.value     = app.end_date;
    f.end_time.value     = app.end_time;

    /* selects */
    f.category_id.value  = app.category_id;
    f.status.value       = app.status;
    f.type.value         = app.type;

    /* modal header & button */
    document.getElementById("appointmentModalLabel").textContent = "Edit Appointment";
    f.querySelector("button[type='submit']").textContent = "Update";

    /* show modal */
    bootstrap.Modal.getOrCreateInstance("#appointmentModal").show();
  });
}

/* -----------------------------------------------
   One delegated listener for both edit & delete
------------------------------------------------*/
export function wireContainerClicks() {
  const box = document.getElementById("appointmentsContainer");

  box.addEventListener("click", e => {
    /* Edit (pencil) */
    const editBtn = e.target.closest(".btn-edit");
    if (editBtn) {
      openModalEdit(JSON.parse(editBtn.dataset.app));   // existing helper
      return;                                           // stop; no delete check
    }

    /* Delete (three-dots) */
    const delIcon = e.target.closest(".btn-delete");
    if (delIcon) {
      confirmDeleteBtn.dataset.appId = delIcon.dataset.id;
      deleteModal.show();
    }
  });
}

/* ───────────────── delete modal ─────────────────────── */
let deleteModal, confirmDeleteBtn;
export function initDeleteModal(){
  deleteModal   = new bootstrap.Modal('#deleteConfirmModal');
  confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
  confirmDeleteBtn.onclick = handleDeleteConfirm;
}

async function handleDeleteConfirm() {
  const id = confirmDeleteBtn.dataset.appId;
  if (!id) return;                        // safety guard

  try {
    /* api.remove() already parses JSON and returns the object */
    const res = await api.remove(id);

    if (res.success) {
      showToast(res.message || "Appointment deleted", "warning");
      fetchAppointments(); // refresh list
    } else {
      // show specific SQL error if backend provided one
      showToast(res.error || res.sql_error || "Delete failed", "danger");
    }
  } catch (err) {
    /* network error or non-JSON response */
    showToast("Server error while deleting", "danger");
  } finally {
    deleteModal.hide(); // hide modal in all cases
  }
}


/* ───────────────── suggest status ───────────────────── */
export function suggestStatus(){
  const f=document.getElementById("appointmentForm");
  const s=new Date(`${f.start_date.value}T${f.start_time.value}`);
  const e=new Date(`${f.end_date.value}T${f.end_time.value}`);
  const now=new Date();
  f.status.value=e<now?"completed": (s<=now&&now<=e?"ongoing":"scheduled");
}
["start_date","start_time","end_date","end_time"].forEach(n=>
  document.querySelector(`[name='${n}']`).addEventListener("change",suggestStatus));

/* ───────────────── FORM SUBMIT ───────────────────────── */
export function setupAppointmentFormHandler(){
  document.getElementById("appointmentForm").addEventListener("submit",async e=>{
    e.preventDefault(); const d=Object.fromEntries(new FormData(e.target).entries());
    const r=d.id?await api.update(d):await api.create(d);
    if(r.success){ showToast(r.message,"success");
      bootstrap.Modal.getInstance('#appointmentModal').hide();
      fetchAppointments();
    }else showToast(r.error||"Failed","danger");
  });
}

/* ───────────────── data fetch  ───────────────────────── */
export async function fetchAppointments(){
  const list=await api.list();
  setAppointments(list);
  initFilters(list);

}

/* ───────────────── export description toggle again so main.js can call ─ */

/* keep getFormJSON utility for compatibility */
export function getFormJSON(form){
  const d=Object.fromEntries(new FormData(form).entries()); d.user_id=1; return d;
}
