/**
 * Duration & Time tracking helper in Khmer
 */

export const toKhmerNum = (num) => {
  if (num === null || num === undefined) return '';
  const digits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
  return String(num).split('').map(d => digits[d] ?? d).join('');
};

export const formatDurationKhmer = (startIso, endIso = null) => {
  if (!startIso) return '---';
  const start = new Date(startIso).getTime();
  const end = endIso ? new Date(endIso).getTime() : Date.now();
  if (isNaN(start) || isNaN(end) || end < start) return 'តិចជាង ១ នាទី';

  const diffMs = end - start;
  const diffSec = Math.floor(diffMs / 1000);
  const diffMin = Math.floor(diffSec / 60);
  const diffHours = Math.floor(diffMin / 60);
  const diffDays = Math.floor(diffHours / 24);

  if (diffDays > 0) {
    const remHours = diffHours % 24;
    if (remHours > 0) {
      return `${toKhmerNum(diffDays)} ថ្ងៃ ${toKhmerNum(remHours)} ម៉ោង`;
    }
    return `${toKhmerNum(diffDays)} ថ្ងៃ`;
  }
  if (diffHours > 0) {
    const remMin = diffMin % 60;
    if (remMin > 0) {
      return `${toKhmerNum(diffHours)} ម៉ោង ${toKhmerNum(remMin)} នាទី`;
    }
    return `${toKhmerNum(diffHours)} ម៉ោង`;
  }
  if (diffMin > 0) {
    return `${toKhmerNum(diffMin)} នាទី`;
  }
  return 'តិចជាង ១ នាទី';
};

export const getMovementDuration = (m, index, movements, doc) => {
  if (!movements || !Array.isArray(movements) || movements.length === 0) return '';
  const isLast = (index === movements.length - 1);
  if (!isLast) {
    const nextMovement = movements[index + 1];
    return formatDurationKhmer(m.created_at, nextMovement.created_at);
  }
  if (doc?.status === 'COMPLETED') {
    return 'បានបញ្ចប់';
  }
  return `កំពុងកាន់កាប់ (${formatDurationKhmer(m.created_at, null)})`;
};

export const getApprovalDuration = (app, aIdx, approvals, resp) => {
  if (!approvals || !Array.isArray(approvals) || approvals.length === 0) return '';
  const isLast = (aIdx === approvals.length - 1);
  if (!isLast) {
    const nextApp = approvals[aIdx + 1];
    return formatDurationKhmer(app.created_at, nextApp.created_at);
  }
  if (resp?.status === 'APPROVED_BY_DG') {
    return 'បានឯកភាពជាស្ថាពរ';
  }
  if (resp?.status === 'UNDER_REVIEW') {
    return `កំពុងពិនិត្យ (${formatDurationKhmer(app.created_at, null)})`;
  }
  if (resp?.status === 'RETURNED') {
    return `បញ្ជូនត្រឡប់ (${formatDurationKhmer(app.created_at, null)})`;
  }
  return formatDurationKhmer(app.created_at, null);
};
