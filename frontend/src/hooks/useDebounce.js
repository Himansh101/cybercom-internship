import { useState, useEffect } from 'react';

/**
 * Returns a debounced copy of `value` that only updates
 * after `delay` milliseconds of inactivity (default 400ms).
 *
 * @param {*}      value  The value to debounce.
 * @param {number} delay  Milliseconds to wait (default 400).
 * @returns {*}           The debounced value.
 */
export function useDebounce(value, delay = 400) {
  const [debounced, setDebounced] = useState(value);

  useEffect(() => {
    const timer = setTimeout(() => setDebounced(value), delay);
    return () => clearTimeout(timer);
  }, [value, delay]);

  return debounced;
}
