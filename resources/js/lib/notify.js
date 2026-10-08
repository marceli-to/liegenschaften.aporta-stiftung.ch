import { reactive } from 'vue';

/**
 * Toast messages, in place of vue-notification.
 * notify({ type: 'success' | 'error', text }); shown for 3 s.
 */
export const notifications = reactive([]);

let id = 0;

export function notify({ type = 'success', text, duration = 3000 }) {
  const notification = { id: ++id, type, text };
  notifications.push(notification);
  setTimeout(() => dismiss(notification), duration);
}

export function dismiss(notification) {
  const index = notifications.indexOf(notification);
  if (index !== -1) {
    notifications.splice(index, 1);
  }
}
