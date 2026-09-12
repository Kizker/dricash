export function formatRupiah(amount, withPrefix = true) {
  if (amount === null || amount === undefined || isNaN(amount)) {
    return withPrefix ? 'Rp 0' : '0';
  }
  
  const num = Math.round(Number(amount));
  const formatted = new Intl.NumberFormat('id-ID').format(num);
  return withPrefix ? `Rp ${formatted}` : formatted;
}

export function formatCompactRupiah(amount) {
  if (amount === null || amount === undefined || isNaN(amount)) return 'Rp 0';
  const num = Math.abs(Number(amount));
  
  if (num >= 1_000_000_000) {
    return `Rp ${(num / 1_000_000_000).toFixed(1)} M`;
  }
  if (num >= 1_000_000) {
    return `Rp ${(num / 1_000_000).toFixed(1)} Jt`;
  }
  if (num >= 1_000) {
    return `Rp ${(num / 1_000).toFixed(0)} Rb`;
  }
  return formatRupiah(amount);
}

export function formatPercent(val, decimals = 1) {
  if (val === null || val === undefined || isNaN(val)) return '0%';
  return `${Number(val).toFixed(decimals)}%`;
}

/**
 * Format raw number/string to Indonesian dot-separated thousands (e.g. 35000000 -> "35.000.000")
 */
export function formatThousands(val) {
  if (val === null || val === undefined || val === '') return '';
  const clean = String(val).replace(/\D/g, '');
  if (!clean) return '';
  const num = parseInt(clean, 10);
  return new Intl.NumberFormat('id-ID').format(num);
}

/**
 * Parse dot-separated string into integer (e.g. "35.000.000" -> 35000000)
 */
export function parseThousands(val) {
  if (!val) return 0;
  const clean = String(val).replace(/\D/g, '');
  return parseInt(clean, 10) || 0;
}

