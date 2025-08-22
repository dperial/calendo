import { fetchAppointments } from '../appointments.ui.js';
import * as api from '../appointments.api.js';
import { setAppointments, setRenderFunction  } from '../pagination.js';

jest.mock('../appointments.api.js');
jest.mock('../pagination.js', () => ({
  setAppointments: jest.fn(),
  setRenderFunction: jest.fn()
}));

jest.mock('../appointments.api.js', () => ({
  list: jest.fn(() => Promise.resolve([])),
}));

jest.mock('../filter-dashboard.js', () => ({
  initFilters: jest.fn(),
}));

const mockSetAppointments = setAppointments;
beforeEach(() => {
    document.body.innerHTML = '<div id="appointmentsContainer"></div>';
    mockSetAppointments.mockClear();
})
describe('fetchAppointments', () => {
/*   it('calls setAppointments with fetched list', async () => {
    const mockData = [{ id: 1 }];
    const { list } = await import('../appointments.api.js');
    list.mockResolvedValue(mockData);

    await fetchAppointments();

    const { setAppointments: mockSetAppointments } = await import('../pagination.js');
    expect(mockSetAppointments).toHaveBeenCalledWith(mockData);
  }); */
  test('fetchAppointments sets appointments from API', async () => {
  const data = [{ id: 1, title: 'a' }];
  api.list.mockResolvedValue(data);
  await fetchAppointments();
  expect(api.list).toHaveBeenCalled();
  expect(mockSetAppointments).toHaveBeenCalledWith(data);
});

test('fetchAppointments isolates setAppointments call counts', async () => {
  api.list.mockResolvedValue([]);
  await fetchAppointments();
  expect(mockSetAppointments).toHaveBeenCalledTimes(1);
});
});