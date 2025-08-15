# Calendo
Google Calendar Clone with WebServices REST API – PHP, MySQL, JavaScript, HTML, and BOOTSTRAP 5
## Testing Endpoints

Some backend endpoints can connect to a dedicated test database. To force a request
into the test environment, either:

* send the HTTP header `X-Test-Env: 1`, or
* append the query string parameter `?env=test`.

This is useful for automated tests that need to isolate data from production.