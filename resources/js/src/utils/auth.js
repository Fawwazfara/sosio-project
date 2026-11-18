export function getStoredUser() {
  try {
    return JSON.parse(localStorage.getItem('user') || 'null');
  } catch (error) {
    return null;
  }
}

export function isReadOnlyUser() {
  return getStoredUser()?.role === 'viewer';
}

export function getRoleHome(role) {
  return new Map([
    ['admin', '/admin/dashboard'],
    ['guru', '/guru/dashboard'],
    ['wali', '/wali/dashboard'],
  ]).get(role) || null;
}
