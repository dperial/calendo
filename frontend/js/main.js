import { setRenderFunction, wirePagerClicks } from "./pagination.js";
import {
  renderAppointments,
  populateCategoryOptions,
  showAppointmentModal,
  setupAppointmentFormHandler,
  initDeleteModal,
  wireDescriptionToggles,
  wireContainerClicks,
  fetchAppointments
} from "./appointments.ui.js";

/* Render pagination */
setRenderFunction(renderAppointments);

document.addEventListener("DOMContentLoaded", () => {
  initDeleteModal();                           // delete modal
  let addApp = document.getElementById("addAppointmentBtn") // + button
  if (addApp) addApp.addEventListener("click", showAppointmentModal);

  setupAppointmentFormHandler();               // create / update
  wireContainerClicks();                     // container clicks
  wirePagerClicks();                           // pager buttons
  wireDescriptionToggles();                    // trunc/expand
  // wireEditButtons();                        // pencil buttons

  populateCategoryOptions().then(fetchAppointments); // first load
});
