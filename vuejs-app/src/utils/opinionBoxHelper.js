export const KHMER_DIGITS = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];

/**
 * Escape HTML special characters to prevent XSS.
 */
export const escapeHtml = (str) => {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
};

/**
 * Validate and sanitize URL (blocks javascript:, data:, vbscript: schemes).
 */
export const sanitizeUrl = (url) => {
  if (!url) return '';
  const s = String(url).trim();
  if (/^(?:javascript|data|vbscript):/i.test(s)) {
    return '';
  }
  return escapeHtml(s);
};

/**
 * Extract relative file path from signed URL or legacy bucket URL if it points to users/signatures/.
 */
export const extractFilePathFromUrl = (url) => {
  if (!url) return '';
  const match = String(url).match(/(?:[?&]path=|\/trms-bucket\/)([^&"'>\s]+)/i);
  if (match) {
    try {
      const decoded = decodeURIComponent(match[1]);
      if (decoded.startsWith('users/signatures/')) {
        return escapeHtml(decoded);
      }
    } catch {
      return '';
    }
  }
  return '';
};

export const toKhmerNum = (num) => {
  return String(num).split('').map(c => KHMER_DIGITS[c] || c).join('');
};

/**
 * Format date to short Khmer representation: D/M/YY (e.g., ៤/១១/២៦)
 */
export const getShortKhmerDateString = (input = new Date()) => {
  let d = input instanceof Date ? input : new Date(input);
  if (isNaN(d.getTime())) {
    d = new Date();
  }
  const day = toKhmerNum(d.getDate());
  const month = toKhmerNum(d.getMonth() + 1);
  const year2 = toKhmerNum(String(d.getFullYear()).slice(-2));
  return `${day}/${month}/${year2}`;
};

/**
 * Generate official administrative draggable opinion & signature box
 * - Font Siemreap for comment (small size)
 * - Date editable
 * - No "(ហត្ថលេខា)" text
 * - 8 Resizing & Drag handles
 */
export const generateDraggableOpinionBoxHtml = ({
  comment = '',
  signatureUrl = '',
  reviewerName = '',
  shortDateKh = '',
  ownerId = '',
  width = 205,
}) => {
  const safeComment = escapeHtml(comment);
  const safeReviewerName = escapeHtml(reviewerName);
  const safeDateStr = escapeHtml(shortDateKh || getShortKhmerDateString(new Date()));
  const safeOwnerId = escapeHtml(ownerId);
  const safeSigUrl = sanitizeUrl(signatureUrl);
  const safeWidth = Number(width) > 0 ? Number(width) : 205;
  const filePath = extractFilePathFromUrl(signatureUrl);
  const dataPathAttr = filePath ? ` data-file-path="${filePath}"` : '';

  const sigHtml = safeSigUrl
    ? `<img src="${safeSigUrl}"${dataPathAttr} alt="ហត្ថលេខា" style="height: 46px; max-width: 150px; object-fit: contain; display: inline-block; vertical-align: middle;" />`
    : `<span style="font-family: 'Brush Script MT', 'Segoe Script', cursive, serif; font-size: 18pt; color: #002060; font-weight: bold; border-bottom: 1.5px dashed #002060; padding: 2px 14px; letter-spacing: 1px; display: inline-block;">${safeReviewerName}</span>`;

  return `
    <div class="draggable-opinion-box"
         data-draggable="true"
         data-owner-id="${safeOwnerId}"
         data-signer-name="${safeReviewerName}"
         style="position: relative; border: 1px dashed transparent; padding: 5px 8px; text-align: center; width: ${safeWidth}px; background: transparent; cursor: move; user-select: none; display: inline-block; vertical-align: top; box-sizing: border-box; line-height: 1.45; margin: 0;">
      <!-- 8 Selection & Resize Handles -->
      <span class="box-handle handle-tl" data-handle="tl"></span>
      <span class="box-handle handle-tc" data-handle="tc"></span>
      <span class="box-handle handle-tr" data-handle="tr"></span>
      <span class="box-handle handle-ml" data-handle="ml"></span>
      <span class="box-handle handle-mr" data-handle="mr"></span>
      <span class="box-handle handle-bl" data-handle="bl"></span>
      <span class="box-handle handle-bc" data-handle="bc"></span>
      <span class="box-handle handle-br" data-handle="br"></span>

      <!-- 1. Opinion Comment: Khmer OS Siemreap, small size -->
      <div class="opinion-box-comment" style="font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; font-size: 9.5pt; color: #000; line-height: 1.45; margin-bottom: 6px; white-space: pre-line; outline: none; word-break: break-word;">${safeComment}</div>

      <!-- 2. Signature image or styled text (no "(ហត្ថលេខា)" text) -->
      <div style="text-align: center; margin: 4px 0; min-height: 46px; display: flex; align-items: center; justify-content: center;">
        ${sigHtml}
      </div>

      <!-- 3. Date in Siemreap -->
      <div class="opinion-box-date" style="font-size: 9pt; color: #111; margin: 2px 0; font-family: 'Khmer OS Siemreap', 'Siemreap', sans-serif; outline: none;">${safeDateStr}</div>

      <!-- 4. Reviewer Name -->
      <div class="opinion-box-signer" style="font-family: 'Khmer OS Muol Light', 'Moul', serif; font-size: 10.5pt; color: #000; margin-top: 3px;">
        ${safeReviewerName}
      </div>
    </div>
  `;
};

const createOpinionFlowRow = () => {
  const row = document.createElement('div');
  row.className = 'opinion-boxes-row';
  row.setAttribute('data-region', 'opinion-flow');
  row.style.cssText = 'width: 100%; text-align: center; margin-top: 8px; clear: both; display: flex; flex-wrap: wrap; justify-content: center; align-items: flex-start; gap: 8px 14px; box-sizing: border-box;';
  return row;
};

const ensureOpinionFlowRow = (rootEl, drafterTable, position = 'afterend') => {
  if (drafterTable && position === 'afterend') {
    const next = drafterTable.nextElementSibling;
    if (next && next.classList.contains('opinion-boxes-row')) return next;
    const row = createOpinionFlowRow();
    drafterTable.insertAdjacentElement('afterend', row);
    return row;
  }
  if (drafterTable && position === 'beforebegin') {
    const prev = drafterTable.previousElementSibling;
    if (prev && prev.classList.contains('opinion-boxes-row')) return prev;
    const row = createOpinionFlowRow();
    drafterTable.insertAdjacentElement('beforebegin', row);
    return row;
  }
  const existingRows = rootEl.querySelectorAll('.opinion-boxes-row');
  if (existingRows.length > 0) return existingRows[existingRows.length - 1];
  const row = createOpinionFlowRow();
  const target = rootEl.querySelector('.document-page') || rootEl;
  target.appendChild(row);
  return row;
};

/**
 * Place or update an opinion box inside an existing DOM container using spacious multi-column auto-placement.
 */
export const embedOpinionBoxIntoDom = (rootEl, boxHtml, placement = 'AUTO') => {
  if (!rootEl || !boxHtml) return;

  // Enforce 1 box per reviewer: replace in-place if owner already exists
  const ownerMatch = boxHtml.match(/data-owner-id=["'](\d+)["']/);
  if (ownerMatch && ownerMatch[1]) {
    const existing = rootEl.querySelector(`.draggable-opinion-box[data-owner-id="${ownerMatch[1]}"]`);
    if (existing) {
      existing.outerHTML = boxHtml;
      return;
    }
  }

  const mode = placement || 'AUTO';
  const drafterTable = rootEl.querySelector('.drafter-signature-table');
  const leftCol = drafterTable?.querySelector('.opinion-left-col, td:first-child') || null;
  const leftBoxesCount = leftCol ? leftCol.querySelectorAll('.draggable-opinion-box').length : 0;

  // AUTO (or SIDE_BY_SIDE_LEFT): use leftCol beside drafter for box #1, then compact multi-column row below drafter
  if (mode === 'AUTO' || mode === 'SIDE_BY_SIDE_LEFT') {
    if (leftCol && leftBoxesCount === 0) {
      leftCol.insertAdjacentHTML('beforeend', boxHtml);
      return;
    }
    const flowRow = drafterTable
      ? ensureOpinionFlowRow(rootEl, drafterTable, 'afterend')
      : ensureOpinionFlowRow(rootEl, null);
    flowRow.insertAdjacentHTML('beforeend', boxHtml);
    return;
  }

  if (mode === 'ABOVE_DRAFTER' && drafterTable) {
    const flowRow = ensureOpinionFlowRow(rootEl, drafterTable, 'beforebegin');
    flowRow.insertAdjacentHTML('beforeend', boxHtml);
    return;
  }

  if (mode === 'BELOW_DRAFTER' && drafterTable) {
    const flowRow = ensureOpinionFlowRow(rootEl, drafterTable, 'afterend');
    flowRow.insertAdjacentHTML('beforeend', boxHtml);
    return;
  }

  const flowRow = ensureOpinionFlowRow(rootEl, null);
  flowRow.insertAdjacentHTML('beforeend', boxHtml);
};

/**
 * Embed opinion box into document HTML based on placement preference (defaults to AUTO spacious layout)
 */
export const embedOpinionBoxIntoHtml = (htmlContent, boxHtml, placement = 'AUTO') => {
  if (!htmlContent) return boxHtml;

  // Clean up any stray (ហត្ថលេខា) in existing content if present
  let content = htmlContent.replace(/<[^>]*>\s*\(ហត្ថលេខា\)\s*<\/[^>]*>/gi, '');
  content = content.replace(/\(ហត្ថលេខា\)/g, '');

  if (typeof document !== 'undefined') {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = content;
    embedOpinionBoxIntoDom(tempDiv, boxHtml, placement);
    return tempDiv.innerHTML;
  }

  // String fallback (non-DOM environments)
  const ownerMatch = boxHtml.match(/data-owner-id=["'](\d+)["']/);
  const mode = placement || 'AUTO';
  const flowOpen = '<div class="opinion-boxes-row" data-region="opinion-flow" style="width: 100%; text-align: center; margin-top: 8px; clear: both;">';

  if (mode === 'AUTO' || mode === 'SIDE_BY_SIDE_LEFT') {
    const leftColMatch = content.match(/<td[^>]*class=["'][^"']*opinion-left-col[^"']*["'][^>]*>/i);
    if (leftColMatch) {
      const insertIdx = leftColMatch.index + leftColMatch[0].length;
      const tdClose = content.indexOf('</td>', insertIdx);
      const colInner = tdClose !== -1 ? content.slice(insertIdx, tdClose) : '';
      if (!colInner.includes('draggable-opinion-box')) {
        return content.slice(0, insertIdx) + boxHtml + content.slice(insertIdx);
      }
    }
    const drafterIdx = content.indexOf('drafter-signature-table');
    if (drafterIdx !== -1) {
      const afterDrafterIdx = content.indexOf('</table>', drafterIdx);
      if (afterDrafterIdx !== -1) {
        const insertPoint = afterDrafterIdx + '</table>'.length;
        return content.slice(0, insertPoint) + flowOpen + boxHtml + '</div>' + content.slice(insertPoint);
      }
    }
  } else if (mode === 'ABOVE_DRAFTER') {
    const drafterIdx = content.indexOf('drafter-signature-table');
    if (drafterIdx !== -1) {
      const tableStart = content.lastIndexOf('<table', drafterIdx);
      if (tableStart !== -1) {
        return content.slice(0, tableStart) + flowOpen + boxHtml + '</div>' + content.slice(tableStart);
      }
    }
  } else if (mode === 'BELOW_DRAFTER') {
    const drafterIdx = content.indexOf('drafter-signature-table');
    if (drafterIdx !== -1) {
      const afterDrafterIdx = content.indexOf('</table>', drafterIdx);
      if (afterDrafterIdx !== -1) {
        const insertPoint = afterDrafterIdx + '</table>'.length;
        return content.slice(0, insertPoint) + flowOpen + boxHtml + '</div>' + content.slice(insertPoint);
      }
    }
  }

  const wrappedBox = ownerMatch ? `${flowOpen}${boxHtml}</div>` : boxHtml;
  const lastClosingDiv = content.lastIndexOf('</div>');
  if (lastClosingDiv !== -1 && content.includes('document-page')) {
    return content.slice(0, lastClosingDiv) + wrappedBox + content.slice(lastClosingDiv);
  }

  return content + wrappedBox;
};

const BOX_GAP = 6;

const isBoxOwnedByUser = (box, currentUserId) => {
  const ownerId = box.getAttribute('data-owner-id');
  if (!ownerId) return true;
  return Boolean(currentUserId) && Number(ownerId) === Number(currentUserId);
};

const rectsOverlap = (a, b, gap = BOX_GAP) => !(
  a.right + gap <= b.left ||
  a.left - gap >= b.right ||
  a.bottom + gap <= b.top ||
  a.top - gap >= b.bottom
);

const getObstacleRects = (containerEl, excludeBox) => {
  const rects = [];
  containerEl.querySelectorAll('.draggable-opinion-box').forEach((other) => {
    if (other !== excludeBox) {
      rects.push({ kind: 'box', rect: other.getBoundingClientRect() });
    }
  });
  containerEl.querySelectorAll('.drafter-signature-table').forEach((tbl) => {
    const cells = tbl.querySelectorAll('tr > td');
    const sigCell = cells.length > 1 ? cells[cells.length - 1] : null;
    if (sigCell && !sigCell.contains(excludeBox)) {
      rects.push({ kind: 'sig', rect: sigCell.getBoundingClientRect() });
    }
  });
  return rects;
};

const isInsideContainer = (boxRect, containerEl, pad = 6) => {
  const cr = containerEl.getBoundingClientRect();
  return (
    boxRect.left >= cr.left + pad &&
    boxRect.right <= cr.right - pad &&
    boxRect.top >= cr.top + pad
  );
};

const hasBoxCollision = (box, containerEl, { checkContainer = true } = {}) => {
  const r = box.getBoundingClientRect();
  if (checkContainer && !isInsideContainer(r, containerEl)) return true;
  const obstacles = getObstacleRects(containerEl, box);
  return obstacles.some((o) => rectsOverlap(r, o.rect, BOX_GAP));
};

const applyBoxOffset = (box, x, y) => {
  box.style.position = 'relative';
  box.style.left = `${Math.round(x)}px`;
  box.style.top = `${Math.round(y)}px`;
  box.setAttribute('data-offset-x', String(Math.round(x)));
  box.setAttribute('data-offset-y', String(Math.round(y)));
};

const resolveInitialOverlap = (box, containerEl) => {
  if (!hasBoxCollision(box, containerEl)) return;

  const baseX = parseFloat(box.getAttribute('data-offset-x') ?? box.style.left) || 0;
  const baseY = parseFloat(box.getAttribute('data-offset-y') ?? box.style.top) || 0;
  const cr = containerEl.getBoundingClientRect();
  const r0 = box.getBoundingClientRect();
  if (r0.width <= 0 || r0.height <= 0) return;

  const scaleX = (containerEl.offsetWidth ? cr.width / containerEl.offsetWidth : 1) || 1;
  const scaleY = (containerEl.offsetHeight ? cr.height / containerEl.offsetHeight : 1) || 1;
  const pad = 6;

  // Minimum and maximum horizontal dx keeping box inside containerEl
  const minDx = (cr.left + pad - r0.left) / scaleX;
  const maxDx = (cr.right - pad - r0.right) / scaleX;
  const obstacles = getObstacleRects(containerEl, box);

  const buildRowXOffsets = () => {
    const cands = [0];
    obstacles.forEach((o) => {
      cands.push((o.rect.right + BOX_GAP + 4 - r0.left) / scaleX);
      cands.push((o.rect.left - BOX_GAP - 4 - r0.right) / scaleX);
    });
    for (let x = minDx; x <= maxDx; x += 28) cands.push(x);
    return Array.from(new Set(cands.map((v) => Math.round(v))))
      .filter((v) => v >= minDx - 1 && v <= maxDx + 1)
      .sort((a, b) => Math.abs(a) - Math.abs(b));
  };

  const rowYOffsets = [0];
  obstacles.forEach((o) => {
    const belowDy = Math.round((o.rect.bottom + BOX_GAP + 4 - r0.top) / scaleY);
    if (belowDy > 0) rowYOffsets.push(belowDy);
  });
  for (let dy = 36; dy <= 520; dy += 36) rowYOffsets.push(dy);
  const sortedY = Array.from(new Set(rowYOffsets)).sort((a, b) => a - b);
  const xOffsets = buildRowXOffsets();

  // Search horizontal open spots on the current row before stepping downward to avoid blank white rows
  for (const dy of sortedY) {
    for (const dx of xOffsets) {
      applyBoxOffset(box, baseX + dx, baseY + dy);
      if (!hasBoxCollision(box, containerEl)) {
        return;
      }
    }
  }

  // Fallback restore if no free spot found
  applyBoxOffset(box, baseX, baseY);
};

/**
 * Clone a container and strip transient drag/edit UI attributes before saving HTML.
 */
export const serializeCleanOpinionHtml = (containerEl) => {
  if (!containerEl) return '';
  const clone = containerEl.cloneNode(true);
  clone.querySelectorAll('.draggable-opinion-box').forEach((b) => {
    b.classList.remove('is-selected', 'is-dragging', 'is-resizing', 'is-locked-box', 'is-editable-box', 'is-blocked');
    b.removeAttribute('title');
    b.querySelectorAll('div').forEach((d) => {
      if (d.textContent.trim() === '(ហត្ថលេខា)') {
        d.remove();
      }
    });
    b.querySelectorAll('[contenteditable]').forEach((el) => {
      el.removeAttribute('contenteditable');
      el.removeAttribute('title');
    });
  });
  return clone.innerHTML;
};

/**
 * Setup interactive mouse drag & resize & inline editing for .draggable-opinion-box elements
 */
export const setupDraggableOpinionBoxes = (containerEl, onDragEnd, options = {}) => {
  if (!containerEl) return () => {};

  const { currentUserId, canMove: canMoveOpt = true, canEdit = true } = options;

  // Ensure table columns align to top and multi-column opinion flow rows stay flex-wrapped
  containerEl.querySelectorAll('.drafter-signature-table td').forEach(td => {
    td.style.verticalAlign = 'top';
  });
  containerEl.querySelectorAll('.opinion-boxes-row').forEach(row => {
    row.style.display = 'flex';
    row.style.flexWrap = 'wrap';
    row.style.justifyContent = 'center';
    row.style.alignItems = 'flex-start';
    row.style.gap = '8px 14px';
    row.style.width = '100%';
    row.style.marginTop = '8px';
    row.style.clear = 'both';
    row.style.boxSizing = 'border-box';
  });

  // Initialize visual attributes & editability strictly based on box ownership
  const updateBoxPermissions = () => {
    containerEl.querySelectorAll('.draggable-opinion-box').forEach(box => {
      // 1. Remove border, set transparent, inline-block, and vertical-align: top
      box.style.display = 'inline-block';
      box.style.boxSizing = 'border-box';
      box.style.border = '1px dashed transparent';
      box.style.background = 'transparent';
      box.style.verticalAlign = 'top';
      box.style.margin = '0';

      // Restore position from data-offset-x and data-offset-y
      const attrX = box.getAttribute('data-offset-x');
      const attrY = box.getAttribute('data-offset-y');
      if (attrX !== null || attrY !== null) {
        const posX = parseFloat(attrX) || 0;
        const posY = parseFloat(attrY) || 0;
        box.style.position = 'relative';
        box.style.left = `${posX}px`;
        box.style.top = `${posY}px`;
      } else if (box.style.left || box.style.top) {
        box.setAttribute('data-offset-x', String(parseFloat(box.style.left) || 0));
        box.setAttribute('data-offset-y', String(parseFloat(box.style.top) || 0));
      }

      // 2. Remove any old (ហត្ថលេខា)
      box.querySelectorAll('*').forEach(el => {
        if (el.textContent.trim() === '(ហត្ថលេខា)') {
          el.remove();
        }
      });
      Array.from(box.childNodes).forEach(node => {
        if (node.nodeType === Node.TEXT_NODE && node.textContent.includes('(ហត្ថលេខា)')) {
          node.textContent = node.textContent.replace(/\(ហត្ថលេខា\)/g, '').trim();
        }
      });

      // 3. Enforce uniform opinion-box fonts (matching View / Print)
      const commentEl = box.querySelector('.opinion-box-comment');
      if (commentEl) {
        commentEl.style.fontFamily = "'Khmer OS Siemreap', 'Siemreap', sans-serif";
        commentEl.style.fontSize = "9.5pt";
        commentEl.style.lineHeight = "1.45";
      }
      const dateEl = box.querySelector('.opinion-box-date');
      if (dateEl) {
        dateEl.style.fontFamily = "'Khmer OS Siemreap', 'Siemreap', sans-serif";
        dateEl.style.fontSize = "9pt";
      }
      const signerEl = box.querySelector('.opinion-box-signer');
      if (signerEl) {
        signerEl.style.fontFamily = "'Khmer OS Muol Light', 'Moul', serif";
        signerEl.style.fontSize = "10.5pt";
        signerEl.style.fontWeight = "normal";
      }

      // 4. Ensure handles have data-handle attribute
      const handleClasses = ['tl', 'tc', 'tr', 'ml', 'mr', 'bl', 'bc', 'br'];
      handleClasses.forEach(h => {
        const el = box.querySelector(`.handle-${h}`);
        if (el && !el.getAttribute('data-handle')) {
          el.setAttribute('data-handle', h);
        }
      });

      const signerName = box.getAttribute('data-signer-name') || 'ថ្នាក់ដឹកនាំ';
      const isOwner = isBoxOwnedByUser(box, currentUserId);

      // Strict ownership rule: even DG/admin cannot move or edit another user's opinion box
      const canMove = isOwner && (canMoveOpt !== false) && (canEdit !== false);
      const canEditContent = isOwner && (canEdit !== false) && (canMoveOpt !== false);

      if (!canMove) {
        box.classList.add('is-locked-box');
        box.classList.remove('is-editable-box', 'is-selected');
        box.style.cursor = 'default';
        const tooltip = !isOwner
          ? `ប្រអប់មតិយោបល់របស់ ${signerName} (អាចកែ និងរំកិលបានតែដោយសាមីខ្លួន)`
          : 'ឯកសារបានបញ្ជូនចេញរួចរាល់ (មិនអាចរំកិលបានឡើយ)';
        box.setAttribute('title', tooltip);
        if (commentEl) commentEl.removeAttribute('contenteditable');
        if (dateEl) dateEl.removeAttribute('contenteditable');
      } else {
        box.classList.remove('is-locked-box');
        box.style.cursor = 'move';
        resolveInitialOverlap(box, containerEl);
        if (canEditContent) {
          box.classList.add('is-editable-box');
          box.setAttribute('title', 'ចុចហើយអូសដើម្បីផ្លាស់ប្តូរទីតាំង, អូសចំណុចដើម្បីពង្រីក-បង្រួម, ឬចុចលើអត្ថបទដើម្បីកែ');
          if (commentEl) {
            commentEl.setAttribute('contenteditable', 'true');
            commentEl.setAttribute('title', 'ចុចទីនេះដើម្បីកែសម្រួលមតិយោបល់');
          }
          if (dateEl) {
            dateEl.setAttribute('contenteditable', 'true');
            dateEl.setAttribute('title', 'ចុចទីនេះដើម្បីកែសម្រួលកាលបរិច្ឆេទ');
          }
        } else {
          box.classList.remove('is-editable-box');
          box.setAttribute('title', 'ចុចហើយអូសដើម្បីផ្លាស់ប្តូរទីតាំង');
          if (commentEl) commentEl.removeAttribute('contenteditable');
          if (dateEl) dateEl.removeAttribute('contenteditable');
        }
      }
    });
  };

  updateBoxPermissions();

  let activeEl = null;
  let startX = 0;
  let startY = 0;
  let origLeft = 0;
  let origTop = 0;
  let origWidth = 0;
  let origHeight = 0;
  let lastValidLeft = 0;
  let lastValidTop = 0;
  let lastValidResize = null;
  let isDragging = false;
  let isResizing = false;
  let resizeHandle = '';

  const getScale = () => {
    const cr = containerEl.getBoundingClientRect();
    return {
      x: containerEl.offsetWidth ? cr.width / containerEl.offsetWidth : 1,
      y: containerEl.offsetHeight ? cr.height / containerEl.offsetHeight : 1,
    };
  };

  const onMouseDown = (e) => {
    const box = e.target.closest('.draggable-opinion-box');
    if (!box || !containerEl.contains(box)) {
      containerEl.querySelectorAll('.draggable-opinion-box').forEach(b => b.classList.remove('is-selected'));
      return;
    }

    const isOwner = isBoxOwnedByUser(box, currentUserId);
    const canMove = isOwner && (canMoveOpt !== false) && (canEdit !== false);

    if (!canMove) {
      containerEl.querySelectorAll('.draggable-opinion-box').forEach(b => b.classList.remove('is-selected'));
      return;
    }

    // If clicking inside an editable element, do NOT drag or resize - allow typing!
    if (e.target.isContentEditable || e.target.closest('[contenteditable="true"]')) {
      containerEl.querySelectorAll('.draggable-opinion-box').forEach(b => b.classList.remove('is-selected'));
      box.classList.add('is-selected');
      return;
    }

    const handle = e.target.closest('.box-handle');
    if (handle) {
      // RESIZE MODE
      containerEl.querySelectorAll('.draggable-opinion-box').forEach(b => b.classList.remove('is-selected'));
      box.classList.add('is-selected');
      activeEl = box;
      isResizing = true;
      isDragging = false;
      resizeHandle = handle.getAttribute('data-handle') || handle.className.match(/handle-([a-z]+)/)?.[1] || 'br';
      startX = e.clientX;
      startY = e.clientY;
      origWidth = box.offsetWidth;
      origHeight = box.offsetHeight;
      origLeft = parseFloat(box.style.left) || 0;
      origTop = parseFloat(box.style.top) || 0;
      lastValidResize = {
        width: box.style.width,
        minWidth: box.style.minWidth,
        minHeight: box.style.minHeight,
        left: box.style.left,
        top: box.style.top,
        ox: box.getAttribute('data-offset-x'),
        oy: box.getAttribute('data-offset-y'),
      };
      activeEl.classList.add('is-resizing');
      e.preventDefault();
      e.stopPropagation();

      window.addEventListener('mousemove', onMouseMove);
      window.addEventListener('mouseup', onMouseUp);
      return;
    }

    // DRAG MODE
    containerEl.querySelectorAll('.draggable-opinion-box').forEach(b => b.classList.remove('is-selected'));
    box.classList.add('is-selected');

    activeEl = box;
    startX = e.clientX;
    startY = e.clientY;
    origLeft = parseFloat(box.style.left) || 0;
    origTop = parseFloat(box.style.top) || 0;
    lastValidLeft = origLeft;
    lastValidTop = origTop;
    isDragging = false;
    isResizing = false;

    e.preventDefault();

    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', onMouseUp);
  };

  const onMouseMove = (e) => {
    if (!activeEl) return;
    const sc = getScale();
    const dx = (e.clientX - startX) / (sc.x || 1);
    const dy = (e.clientY - startY) / (sc.y || 1);

    if (isResizing) {
      if (resizeHandle.includes('r')) {
        const newWidth = Math.max(160, Math.min(650, origWidth + dx));
        activeEl.style.width = `${newWidth}px`;
        activeEl.style.minWidth = `${newWidth}px`;
      } else if (resizeHandle.includes('l')) {
        const newWidth = Math.max(160, Math.min(650, origWidth - dx));
        const actualDx = origWidth - newWidth;
        activeEl.style.width = `${newWidth}px`;
        activeEl.style.minWidth = `${newWidth}px`;
        applyBoxOffset(activeEl, origLeft + actualDx, origTop);
      }

      if (resizeHandle.includes('b')) {
        const newHeight = Math.max(70, origHeight + dy);
        activeEl.style.minHeight = `${newHeight}px`;
      } else if (resizeHandle.includes('t')) {
        const newHeight = Math.max(70, origHeight - dy);
        const actualDy = origHeight - newHeight;
        activeEl.style.minHeight = `${newHeight}px`;
        applyBoxOffset(activeEl, parseFloat(activeEl.style.left) || origLeft, origTop + actualDy);
      }

      if (hasBoxCollision(activeEl, containerEl) && lastValidResize) {
        activeEl.style.width = lastValidResize.width;
        activeEl.style.minWidth = lastValidResize.minWidth;
        activeEl.style.minHeight = lastValidResize.minHeight;
        activeEl.style.left = lastValidResize.left;
        activeEl.style.top = lastValidResize.top;
        if (lastValidResize.ox !== null) activeEl.setAttribute('data-offset-x', lastValidResize.ox);
        if (lastValidResize.oy !== null) activeEl.setAttribute('data-offset-y', lastValidResize.oy);
        activeEl.classList.add('is-blocked');
      } else {
        activeEl.classList.remove('is-blocked');
        lastValidResize = {
          width: activeEl.style.width,
          minWidth: activeEl.style.minWidth,
          minHeight: activeEl.style.minHeight,
          left: activeEl.style.left,
          top: activeEl.style.top,
          ox: activeEl.getAttribute('data-offset-x'),
          oy: activeEl.getAttribute('data-offset-y'),
        };
      }
      return;
    }

    // Normal dragging
    if (!isDragging && (Math.abs(dx) > 3 || Math.abs(dy) > 3)) {
      isDragging = true;
      activeEl.classList.add('is-dragging');
    }

    if (isDragging) {
      const candLeft = Math.round(origLeft + dx);
      const candTop = Math.round(origTop + dy);

      applyBoxOffset(activeEl, candLeft, candTop);
      if (!hasBoxCollision(activeEl, containerEl)) {
        lastValidLeft = candLeft;
        lastValidTop = candTop;
        activeEl.classList.remove('is-blocked');
        return;
      }

      // Slide cleanly along X or Y axis if one direction is clear
      applyBoxOffset(activeEl, candLeft, lastValidTop);
      if (!hasBoxCollision(activeEl, containerEl)) {
        lastValidLeft = candLeft;
        activeEl.classList.add('is-blocked');
        return;
      }

      applyBoxOffset(activeEl, lastValidLeft, candTop);
      if (!hasBoxCollision(activeEl, containerEl)) {
        lastValidTop = candTop;
        activeEl.classList.add('is-blocked');
        return;
      }

      // Blocked in both directions: hold last non-overlapping position
      applyBoxOffset(activeEl, lastValidLeft, lastValidTop);
      activeEl.classList.add('is-blocked');
    }
  };

  const onMouseUp = () => {
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup', onMouseUp);

    if (activeEl) {
      const wasResizing = isResizing;
      const wasDragging = isDragging;
      activeEl.classList.remove('is-dragging', 'is-resizing', 'is-blocked');

      if ((wasDragging || wasResizing) && typeof onDragEnd === 'function') {
        onDragEnd(activeEl, wasResizing ? 'resize' : 'drag');
      }
      activeEl = null;
    }
    lastValidResize = null;
    isDragging = false;
    isResizing = false;
  };

  const onInput = (e) => {
    const box = e.target.closest('.draggable-opinion-box');
    if (box && typeof options.onContentChange === 'function') {
      options.onContentChange(box);
    }
  };

  containerEl.addEventListener('mousedown', onMouseDown);
  containerEl.addEventListener('input', onInput);

  return () => {
    containerEl.removeEventListener('mousedown', onMouseDown);
    containerEl.removeEventListener('input', onInput);
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup', onMouseUp);
  };
};
