import { initFilters } from '../filter-dashboard.js';
import { setAppointments as mockSetAppointments } from '../pagination.js';

jest.mock('../pagination.js', () => ({
  setAppointments: jest.fn(),
}));
  beforeEach(() => {
    document.body.innerHTML = `
      <select id="categoryFilter"></select>
      <select id="statusFilter"></select>
      <input id="searchInput" />
      <div id="activeFilters"></div>
      <button id="resetFiltersBtn"></button>
    `;
  });
describe('filter-dashboard, initFilters with Categories and Status', () => {
  const appointments = [
    { title: 'Checkup', description: 'General checkup', category: 'consultation', status: 'scheduled', type: 'in-person', creator_name: 'Dr. Smith' },
    { title: 'Surgery', description: 'Heart surgery', category: 'surgery', status: 'completed', type: 'in-person', creator_name: 'Dr. Doe' },
    { title: 'Therapy', description: 'Physical therapy', category: 'therapy', status: 'ongoing', type: 'virtual', creator_name: 'Dr. Jane' },
  ];

  test('initFilters populates category and status options', () => {
    const categorySelect = document.getElementById('categoryFilter');
    const statusSelect = document.getElementById('statusFilter');
    
    initFilters(appointments);

    expect([...categorySelect.options].map(o => o.value)).toEqual(['all','consultation','surgery','therapy']);
    expect([...statusSelect.options].map(o => o.value)).toEqual(['all','scheduled','ongoing','completed','cancelled']);
  });
});
describe('Filter-Dashboard, Reset after applying filters', () => {
  test('resetFilters clears filters and calls setAppointments', () => {
    const resetBtn = document.getElementById('resetFiltersBtn');
    const activeFilters = document.getElementById('activeFilters');

    const appointments = [
        { title: 'Checkup', description: 'General checkup', category: 'consultation', status: 'scheduled', type: 'in-person', creator_name: 'Dr. Smith' },
        { title: 'Surgery', description: 'Heart surgery', category: 'surgery', status: 'completed', type: 'in-person', creator_name: 'Dr. Doe' },
        { title: 'Therapy', description: 'Physical therapy', category: 'therapy', status: 'ongoing', type: 'virtual', creator_name: 'Dr. Jane' },
    ];
    initFilters(appointments);
    resetBtn.click();

    expect(document.getElementById('categoryFilter').value).toBe('all');
    expect(document.getElementById('statusFilter').value).toBe('all');
    expect(document.getElementById('searchInput').value).toBe('');
    expect(activeFilters.innerHTML).toBe('');
    expect(mockSetAppointments).toHaveBeenCalledWith(appointments); // enable if your reset calls it
  });
});
describe('filter-dashboard, applyFilters on Status option', () => {
  test('calls setAppointments with filtered appointments on status change', () => {

    const appointments = [
      {
        category: 'work',
        status: 'scheduled',
        title: 'Meeting',
        description: 'Project meeting',
        type: 'event',
        creator_name: 'Alice',
      },
      {
        category: 'personal',
        status: 'completed',
        title: 'Dentist',
        description: 'Dental checkup',
        type: 'appointment',
        creator_name: 'Bob',
      },
    ];

    initFilters(appointments);

    const statusSelect = document.getElementById('statusFilter');
    statusSelect.value = 'completed';
    statusSelect.dispatchEvent(new Event('change'));

    expect(mockSetAppointments).toHaveBeenCalledWith([appointments[1]]);
  });
});
describe('filter-dashboard, applyFilters on Category option', () => {
  test('calls setAppointments with filtered appointments on category change', () => {

    const appointments = [
      {
        category: 'work',
        status: 'scheduled',
        title: 'Meeting',
        description: 'Project meeting',
        type: 'event',
        creator_name: 'Alice',
      },
      {
        category: 'personal',
        status: 'completed',
        title: 'Dentist',
        description: 'Dental checkup',
        type: 'appointment',
        creator_name: 'Bob',
      },
    ];

    initFilters(appointments);

    const categorySelect = document.getElementById('categoryFilter');
    categorySelect.value = 'work';
    categorySelect.dispatchEvent(new Event('change'));

    expect(mockSetAppointments).toHaveBeenCalledWith([appointments[0]]);
  });
});