export function formatTanggalIndo(dateStr) {
  if (!dateStr) return '-';
  const clean = String(dateStr).split('T')[0];
  const [y, m, d] = clean.split('-');
  if (!y || !m || !d) return dateStr;
  const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  return `${parseInt(d, 10)} ${bulan[parseInt(m, 10) - 1] || m} ${y}`;
}

export function formatLastInfak(lastInfak) {
  if (!lastInfak) return 'Belum pernah bayar';
  return `${lastInfak.bulan_indo} ${lastInfak.kalender_tahun} (bayar ${formatTanggalIndo(lastInfak.tanggal_bayar)})`;
}

export function isMonthAfterExit(student, mIndex, currentAcadStart) {
  if (!student || student.status !== 'keluar') return false;
  if (!student.tanggal_keluar) return true;
  const parts = String(student.tanggal_keluar).split('-').map(Number);
  if (parts.length < 2) return true;
  const [y, m] = parts;
  const exitAcadStart = m >= 7 ? y : y - 1;
  const exitIdx = m >= 7 ? m - 7 : m + 5;
  if (exitAcadStart < currentAcadStart) return true;
  if (exitAcadStart === currentAcadStart) return mIndex >= exitIdx;
  return false;
}

