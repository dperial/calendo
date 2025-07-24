/* appointments.api.js */
const BASE = "../backend/appointments/";

export const list   = ()        => fetchJson("appointments.php");
export const create = (d)      => postJson ("create_appointment.php",  d);
export const update = (d)      => postJson ("update_appointment.php",  d);
export const remove = (id)     => postJson ("delete_appointment.php", { id });

/* ---------- helpers ------------------------------ */
async function fetchJson(url) {
  const r   = await fetch(BASE + url);
  const txt = await r.text();
  return safeParse(txt, r.status);
}

async function postJson(url, body) {
  const r   = await fetch(BASE + url, {
               method : "POST",
               headers: { "Content-Type": "application/json" },
               body   : JSON.stringify(body)
             });
  const txt = await r.text();      // always read text first
  return safeParse(txt, r.status);
}

function safeParse(txt, status) {
  try {
    const json = JSON.parse(txt);
    return json;
  } catch (e) {
    /* return a synthetic error object the UI can display */
    return {
      success : false,
      error   : `Server returned status ${status} and non-JSON body`,
      raw     : txt.trim()          // helpful while debugging
    };
  }
}
