/**
 * Two digits: 3 → '03' (pagination)
 */
export function padStart(value) {
  return String(value).padStart(2, '0');
}

/**
 * Cache buster for the export links
 */
export function randomString() {
  return Math.random().toString(36).slice(2);
}

export function validateRequired(str) {
  return str != null && str.length > 0;
}

export function validateEmail(email) {
  const filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return email != null && email.length > 0 && filter.test(email);
}

/**
 * Value at a dotted path ('building.street')
 */
function get(object, path) {
  return path.split('.').reduce((value, key) => value?.[key], object);
}

/**
 * Stable sort by a dotted path, as lodash's orderBy did: plain < and >,
 * empty values last (first when descending). No key: original order.
 */
export function orderBy(data, key, direction = 'asc') {
  if (!key) {
    return [...data];
  }
  const sign = direction === 'desc' ? -1 : 1;
  const rank = value => value === undefined ? 2 : (value === null ? 1 : 0);
  return [...data].sort((a, b) => {
    const x = get(a, key), y = get(b, key);
    const empty = rank(x) - rank(y);
    if (empty || rank(x)) {
      return empty * sign;
    }
    return (x > y ? 1 : x < y ? -1 : 0) * sign;
  });
}
