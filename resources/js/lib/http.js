import axios from 'axios';
import NProgress from 'nprogress';
import { notify } from '@/lib/notify';

// Same-origin SPAs on Sanctum's session cookie: axios sends X-XSRF-TOKEN from
// the XSRF-TOKEN cookie by itself.
const http = axios.create({
  headers: { 'X-Requested-With': 'XMLHttpRequest' },
});

/**
 * Admin-wide handling of failed requests. Callers still get the rejection
 * (the user forms read 422 errors themselves) but need not notify.
 */
export function handleErrors(router) {
  http.interceptors.response.use(response => response, error => {
    const response = error.response;
    NProgress.done();

    // The user forms pass { handleErrors: false } and show the messages themselves
    if (error.config?.handleErrors === false && response?.status === 422) {
      return Promise.reject(error);
    }

    switch (response?.status) {
      // Session gone; 419 when the CSRF token expired with it. The login is a Blade page.
      case 401:
      case 419:
        window.location.href = '/login';
        break;
      case 403:
        notify({ type: 'error', text: '403 Zugriff verweigert' });
        router.push({ name: 'forbidden' });
        break;
      case 404:
        notify({ type: 'error', text: '404 Nicht gefunden' });
        router.push({ name: 'not-found' });
        break;
      case 422:
        notify({ type: 'error', text: 'Bitte alle mit * markierten Felder prüfen!' });
        break;
      default:
        notify({ type: 'error', text: `${response?.status ?? 'Netzwerkfehler'} ${response?.data?.message ?? ''}`.trim() });
    }

    return Promise.reject(error);
  });
}

export default http;

/**
 * Log out by POST (a form, so the session redirect works as a page load)
 */
export function logout() {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/logout';
  const token = document.createElement('input');
  token.type = 'hidden';
  token.name = '_token';
  token.value = document.querySelector('meta[name="csrf-token"]').content;
  form.appendChild(token);
  document.body.appendChild(form);
  form.submit();
}
