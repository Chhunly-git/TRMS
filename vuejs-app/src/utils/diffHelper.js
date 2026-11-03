/**
 * Helper to compute diff between two texts or HTML contents
 * Supports Khmer and Latin scripts with word/token granularity
 */

// Tokenizes text into words, whitespace, HTML tags, and punctuation
export function tokenize(str) {
  if (!str) return [];
  // Match HTML tags OR sequences of Khmer characters OR English words OR numbers OR spaces/punctuation
  const regex = /(<[^>]+>)|([\u1780-\u17FF]+)|([a-zA-Z0-9_]+)|(\s+)|([^\s\w\u1780-\u17FF<]+)/gu;
  const tokens = [];
  let match;
  while ((match = regex.exec(str)) !== null) {
    tokens.push(match[0]);
  }
  return tokens;
}

/**
 * Longest Common Subsequence (LCS) Diff
 * Returns an array of diff operations:
 * { type: 'equal' | 'add' | 'delete', text: string }
 */
export function computeTokenDiff(oldStr, newStr) {
  const oldTokens = tokenize(oldStr);
  const newTokens = tokenize(newStr);

  const m = oldTokens.length;
  const n = newTokens.length;

  // For very long documents, compare paragraph by paragraph
  if (m * n > 250000) {
    return computeParagraphDiff(oldStr, newStr);
  }

  // Dynamic programming matrix for LCS
  // Optimize space by using two rows
  const dp = Array.from({ length: m + 1 }, () => new Uint16Array(n + 1));

  for (let i = 0; i < m; i++) {
    for (let j = 0; j < n; j++) {
      if (oldTokens[i] === newTokens[j]) {
        dp[i + 1][j + 1] = dp[i][j] + 1;
      } else {
        dp[i + 1][j + 1] = Math.max(dp[i + 1][j], dp[i][j + 1]);
      }
    }
  }

  // Backtrack to assemble diff operations
  let i = m;
  let j = n;
  const rawOps = [];

  while (i > 0 || j > 0) {
    if (i > 0 && j > 0 && oldTokens[i - 1] === newTokens[j - 1]) {
      rawOps.push({ type: 'equal', text: oldTokens[i - 1] });
      i--;
      j--;
    } else if (j > 0 && (i === 0 || dp[i][j - 1] >= dp[i - 1][j])) {
      rawOps.push({ type: 'add', text: newTokens[j - 1] });
      j--;
    } else if (i > 0 && (j === 0 || dp[i][j - 1] < dp[i - 1][j])) {
      rawOps.push({ type: 'delete', text: oldTokens[i - 1] });
      i--;
    }
  }

  rawOps.reverse();

  // Merge consecutive operations of same type for cleaner rendering
  const mergedOps = [];
  for (const op of rawOps) {
    if (mergedOps.length > 0 && mergedOps[mergedOps.length - 1].type === op.type) {
      mergedOps[mergedOps.length - 1].text += op.text;
    } else {
      mergedOps.push({ ...op });
    }
  }

  return mergedOps;
}

/**
 * Paragraph-level diff fallback for very large texts
 */
function computeParagraphDiff(oldStr, newStr) {
  const oldParas = (oldStr || '').split(/(<\/p>|<br\s*\/?>|\n\n)/i);
  const newParas = (newStr || '').split(/(<\/p>|<br\s*\/?>|\n\n)/i);
  const ops = [];

  const maxLen = Math.max(oldParas.length, newParas.length);
  for (let i = 0; i < maxLen; i++) {
    const o = oldParas[i] || '';
    const n = newParas[i] || '';
    if (o === n) {
      if (o) ops.push({ type: 'equal', text: o });
    } else {
      if (o) ops.push({ type: 'delete', text: o });
      if (n) ops.push({ type: 'add', text: n });
    }
  }
  return ops;
}

/**
 * Render diff operations as HTML with highlighted additions and deletions
 */
export function renderDiffHtml(oldStr, newStr) {
  if (!oldStr && !newStr) return '<em class="text-muted">ពុំមានខ្លឹមសារ</em>';
  if (!oldStr) return `<ins class="diff-add-block" style="background:#d4edda; color:#155724; padding:2px 4px; border-radius:4px;">${newStr}</ins>`;
  if (!newStr) return `<del class="diff-del-block" style="background:#f8d7da; color:#721c24; text-decoration:line-through; padding:2px 4px; border-radius:4px;">${oldStr}</del>`;

  const ops = computeTokenDiff(oldStr, newStr);

  let html = '';
  for (const op of ops) {
    if (op.type === 'equal') {
      html += op.text;
    } else if (op.type === 'add') {
      // If the text contains HTML tags, we avoid wrapping tags themselves into <ins> if possible
      if (op.text.startsWith('<') && op.text.endsWith('>')) {
        html += op.text;
      } else {
        html += `<ins class="diff-added" style="background-color: #d1e7dd; color: #0f5132; text-decoration: none; border-bottom: 2px solid #198754; font-weight: bold; padding: 1px 3px; border-radius: 3px;" title="បន្ថែម">${escapeHtmlPreserveTags(op.text)}</ins>`;
      }
    } else if (op.type === 'delete') {
      if (op.text.startsWith('<') && op.text.endsWith('>')) {
        // Skip rendering hidden tag deletions
      } else {
        html += `<del class="diff-deleted" style="background-color: #f8d7da; color: #842029; text-decoration: line-through; padding: 1px 3px; border-radius: 3px; margin: 0 1px; opacity: 0.85;" title="លុបចេញ">${escapeHtmlPreserveTags(op.text)}</del>`;
      }
    }
  }

  return html;
}

function escapeHtmlPreserveTags(text) {
  // If it's pure text, don't double escape
  return text;
}
