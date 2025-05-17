/**
 * Utility functions for string processing, URL handling, and date formatting.
 */

/**
 * Decodes Unicode escape sequences (e.g., "\u0646") in a string to their actual characters.
 * @param str - The input string containing Unicode escape sequences.
 * @returns The decoded string with Unicode characters.
 */
export function decodeUnicode(str: string): string {
  try {
    // Replace Unicode escape sequences like \uXXXX with their actual characters
    return str.replace(/\\u([\dA-Fa-f]{4})/g, (match, grp) =>
      String.fromCharCode(parseInt(grp, 16))
    );
  } catch (error) {
    console.warn(`Error decoding Unicode string: ${str}`, error);
    return str; // Return original string if decoding fails
  }
}

/**
 * Ensures a URL is absolute by prepending a base URL if necessary.
 * @param url - The input URL (relative or absolute).
 * @param baseUrl - The base URL to prepend (e.g., "https://core.journa.ir/storage").
 * @returns The absolute URL.
 */
export function getAbsoluteUrl(path: string, baseUrl?: string): string {
  if (!path || path.startsWith('http')) return path;
  return `${baseUrl}/${path.replace(/^\//, '')}`;
}

/**
 * Formats a date string to a localized format.
 * @param dateStr - The input date string (e.g., "2025-05-15T05:00:00Z").
 * @param locale - The locale for formatting (e.g., "fa", "en").
 * @returns The formatted date string.
 */
export function formatDate(dateStr: string, locale: string): string {
  try {
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) {
      throw new Error('Invalid date');
    }
    return new Intl.DateTimeFormat(locale, {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(date);
  } catch (error) {
    console.warn(`Error formatting date: ${dateStr}`, error);
    return dateStr; // Return original string if formatting fails
  }
}

/**
 * Slugifies a string for use in URLs (e.g., "Sample News" -> "sample-news").
 * @param str - The input string.
 * @returns The slugified string.
 */
export function slugify(str: string): string {
  return str
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '') // Remove special characters
    .replace(/\s+/g, '-') // Replace spaces with hyphens
    .replace(/-+/g, '-'); // Replace multiple hyphens with single hyphen
}

/**
 * Truncates a string to a specified length and adds an ellipsis if needed.
 * @param str - The input string.
 * @param maxLength - The maximum length of the output string.
 * @returns The truncated string.
 */
export function truncateString(str: string, maxLength: number): string {
  if (str.length <= maxLength) return str;
  return `${str.slice(0, maxLength - 3)}...`;
}