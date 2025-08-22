import { fireEvent } from '@testing-library/dom';
import '@testing-library/jest-dom';
import { setRenderFunction, setAppointments, wirePagerClicks } from '../pagination';

describe('pagination', () => {
  test('renderPage slices data correctly when setAppointments supplies a list', () => {
    document.body.innerHTML = '<ul id="pagination"></ul><select id="pageSizeSelect"></select>';
    const data = Array.from({ length: 12 }, (_, i) => i);
    const renderMock = jest.fn();
    setRenderFunction(renderMock);
    setAppointments(data);
    expect(renderMock).toHaveBeenCalledWith(data.slice(0, 9));
  });

  test('renderPagination builds Prev/Next links and page numbers', () => {
    document.body.innerHTML = '<ul id="pagination"></ul><select id="pageSizeSelect"></select>';
    const data = Array.from({ length: 20 }, (_, i) => i);
    setRenderFunction(() => {});
    setAppointments(data);
    const pager = document.getElementById('pagination');
    const items = Array.from(pager.querySelectorAll('li'));
    expect(items[0].textContent).toContain('Prev');
    expect(items[items.length - 1].textContent).toContain('Next');
    const pages = items.slice(1, -1).map(li => li.textContent.trim());
    expect(pages).toEqual(['1', '2', '3']);
  });

  test('wirePagerClicks responds to navigation and size changes', () => {
    document.body.innerHTML = `
      <ul id="pagination"></ul>
      <select id="pageSizeSelect">
        <option value="2">2</option>
        <option value="5">5</option>
      </select>`;
    const data = [1,2,3,4,5];
    const renderMock = jest.fn();
    setRenderFunction(renderMock);
    setAppointments(data);
    wirePagerClicks();

    renderMock.mockClear();
    const select = document.getElementById('pageSizeSelect');
    select.value = '2';
    fireEvent.change(select);
    expect(renderMock).toHaveBeenLastCalledWith(data.slice(0,2));

    renderMock.mockClear();
    const page2 = document.querySelector('a[data-page="2"]');
    fireEvent.click(page2);
    expect(renderMock).toHaveBeenLastCalledWith(data.slice(2,4));

    renderMock.mockClear();
    select.value = '5';
    fireEvent.change(select);
    expect(renderMock).toHaveBeenLastCalledWith(data.slice(0,5));
    const active = document.querySelector('#pagination li.active a');
    expect(active).toHaveTextContent('1');
  });
});