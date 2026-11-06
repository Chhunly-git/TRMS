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
  width = 240,
}) => {
  const safeComment = escapeHtml(comment);
  const safeReviewerName = escapeHtml(reviewerName);
  const safeDateStr = escapeHtml(shortDateKh || getShortKhmerDateString(new Date()));
  const safeOwnerId = escapeHtml(ownerId);
  const safeSigUrl = sanitizeUrl(signatureUrl);
  const safeWidth = Number(width) > 0 ? Number(width) : 240;
  const filePath = extractFilePathFromUrl(signatureUrl);
  const dataPathAttr = filePath ? ` data-file-path="${filePath}"` : '';

  const sigHtml = safeSigUrl
    ? `<img src="${safeSigUrl}"${dataPathAttr} alt="ហត្ថលេខា" style="height: 48px; max-width: 170px; object-fit: contain; display: inline-block; vertical-align: middle;" />`
    : `<span style="font-family: 'Brush Script MT', 'Segoe Script', cursive, serif; font-size: 18pt; color: #002060; font-weight: bold; border-bottom: 1.5px dashed #002060; padding: 2px 14px; letter-spacing: 1px; display: inline-block;">${safeReviewerName}</span>`;

  return `
    <div class="draggable-opinion-box"
         data-draggable="true"
         data-owner-id="${safeOwnerId}"
         data-signer-name="${safeReviewerName}"
         style="position: relative; border: 1px dashed transparent; padding: 6px 12px; text-align: center; width: ${safeWidth}px; background: transparent; cursor: move; user-select: none; display: inline-block; vertical-align: top; box-sizing: border-box; line-height: 1.45; margin: 0;">
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
      <div class="opinion-box-signer" style="font-family: 'Khmer OS Muol Light', serif; font-size: 10.5pt; color: #000; margin-top: 3px;">
        ${safeReviewerName}
      </div>
    </div>
  `;
};

/**
 * Embed opinion box into document HTML based on placement preference
 */
export const embedOpinionBoxIntoHtml = (htmlContent, boxHtml, placement = 'SIDE_BY_SIDE_LEFT') => {
  if (!htmlContent) return boxHtml;

  // Clean up any stray (ហត្ថលេខា) in existing content if present
  let content = htmlContent.replace(/<[^>]*>\s*\(ហត្ថលេខា\)\s*<\/[^>]*>/gi, '');
  content = content.replace(/\(ហត្ថលេខា\)/g, '');

  // Enforcement: 1 Reviewer can only have ONE opinion box in the report!
  // If an opinion box for this reviewer already exists, update/replace it in-place instead of duplicating
  const ownerMatch = boxHtml.match(/data-owner-id=["'](\d+)["']/);
  if (ownerMatch && ownerMatch[1]) {
    const ownerId = ownerMatch[1];
    if (typeof document !== 'undefined') {
      const tempDiv = document.createElement('div');
      tempDiv.innerHTML = content;
      const existing = tempDiv.querySelector(`.draggable-opinion-box[data-owner-id="${ownerId}"]`);
      if (existing) {
        existing.outerHTML = boxHtml;
        return tempDiv.innerHTML;
      }
    }
  }

  if (placement === 'SIDE_BY_SIDE_LEFT') {
    // 1. Try to find opinion-left-col inside drafter table
    const leftColMatch = content.match(/<td[^>]*class=["'][^"']*opinion-left-col[^"']*["'][^>]*>/i);
    if (leftColMatch) {
      const insertIdx = leftColMatch.index + leftColMatch[0].length;
      return content.slice(0, insertIdx) + boxHtml + content.slice(insertIdx);
    }

    // 2. Try first <td> in drafter-signature-table
    const drafterTableIdx = content.indexOf('drafter-signature-table');
    if (drafterTableIdx !== -1) {
      const firstTdIdx = content.indexOf('<td', drafterTableIdx);
      if (firstTdIdx !== -1) {
        const closeTagIdx = content.indexOf('>', firstTdIdx);
        if (closeTagIdx !== -1) {
          return content.slice(0, closeTagIdx + 1) + boxHtml + content.slice(closeTagIdx + 1);
        }
      }
      const tableStart = content.lastIndexOf('<table', drafterTableIdx);
      if (tableStart !== -1) {
        return content.slice(0, tableStart) + boxHtml + content.slice(tableStart);
      }
    }
  } else if (placement === 'ABOVE_DRAFTER') {
    const drafterIdx = content.indexOf('drafter-signature-table');
    if (drafterIdx !== -1) {
      const tableStart = content.lastIndexOf('<table', drafterIdx);
      if (tableStart !== -1) {
        return content.slice(0, tableStart) + boxHtml + content.slice(tableStart);
      }
    }
  } else if (placement === 'BELOW_DRAFTER') {
    const drafterIdx = content.indexOf('drafter-signature-table');
    if (drafterIdx !== -1) {
      const tableEndTag = '</table>';
      const afterDrafterIdx = content.indexOf(tableEndTag, drafterIdx);
      if (afterDrafterIdx !== -1) {
        const insertPoint = afterDrafterIdx + tableEndTag.length;
        return content.slice(0, insertPoint) + boxHtml + content.slice(insertPoint);
      }
    }
  }

  // Fallback: append before the closing div of document-page or at the end
  const lastClosingDiv = content.lastIndexOf('</div>');
  if (lastClosingDiv !== -1 && content.includes('document-page')) {
    return content.slice(0, lastClosingDiv) + boxHtml + content.slice(lastClosingDiv);
  }

  return content + boxHtml;
};

/**
 * Setup interactive mouse drag & resize & inline editing for .draggable-opinion-box elements
 */
export const setupDraggableOpinionBoxes = (containerEl, onDragEnd, options = {}) => {
  if (!containerEl) return () => {};

  const { currentUserId, isAdmin = false, canMove: canMoveOpt = true, canEdit = true } = options;

  // Ensure table columns always align to top
  containerEl.querySelectorAll('.drafter-signature-table td').forEach(td => {
    td.style.verticalAlign = 'top';
  });

  // Initialize visual attributes & editability based on ownership & doc in hand
  const updateBoxPermissions = () => {
    containerEl.querySelectorAll('.draggable-opinion-box').forEach(box => {
      // 1. Remove border, set transparent and vertical-align: top
      box.style.border = '1px dashed transparent';
      box.style.background = 'transparent';
      box.style.verticalAlign = 'top';
      box.style.margin = '0';

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

      // 3. Ensure comment font is Siemreap small
      const commentEl = box.querySelector('.opinion-box-comment');
      if (commentEl) {
        commentEl.style.fontFamily = "'Khmer OS Siemreap', 'Siemreap', sans-serif";
        commentEl.style.fontSize = "9.5pt";
        commentEl.style.lineHeight = "1.45";
      }

      // 4. Ensure handles have data-handle attribute
      const handleClasses = ['tl', 'tc', 'tr', 'ml', 'mr', 'bl', 'bc', 'br'];
      handleClasses.forEach(h => {
        const el = box.querySelector(`.handle-${h}`);
        if (el && !el.getAttribute('data-handle')) {
          el.setAttribute('data-handle', h);
        }
      });

      const ownerId = box.getAttribute('data-owner-id');
      const signerName = box.getAttribute('data-signer-name') || 'ថ្នាក់ដឹកនាំ';
      const isOwner = !ownerId || (currentUserId && Number(ownerId) === Number(currentUserId));
      
      // CAN MOVE ONLY IF:
      // - user is owner or admin
      // - AND document is currently in hand (canMoveOpt !== false && canEdit !== false)
      const canMove = (isOwner || isAdmin) && (canMoveOpt !== false) && (canEdit !== false);
      const canEditContent = isOwner && (canEdit !== false) && (canMoveOpt !== false);

      const dateEl = box.querySelector('.opinion-box-date');

      if (!canMove) {
        box.classList.add('is-locked-box');
        box.classList.remove('is-editable-box', 'is-selected');
        box.style.cursor = 'default';
        const tooltip = !isOwner
          ? `ប្រអប់មតិយោបល់របស់ ${signerName} (អាចរំកិលបានតែដោយសាមីខ្លួន)`
          : 'ឯកសារបានបញ្ជូនចេញរួចរាល់ (មិនអាចរំកិលបានឡើយ)';
        box.setAttribute('title', tooltip);
        if (commentEl) commentEl.removeAttribute('contenteditable');
        if (dateEl) dateEl.removeAttribute('contenteditable');
      } else {
        box.classList.remove('is-locked-box');
        box.style.cursor = 'move';
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
  let isDragging = false;
  let isResizing = false;
  let resizeHandle = '';

  const onMouseDown = (e) => {
    const box = e.target.closest('.draggable-opinion-box');
    if (!box || !containerEl.contains(box)) {
      containerEl.querySelectorAll('.draggable-opinion-box').forEach(b => b.classList.remove('is-selected'));
      return;
    }

    const ownerId = box.getAttribute('data-owner-id');
    const isOwner = !ownerId || (currentUserId && Number(ownerId) === Number(currentUserId));
    const canMove = (isOwner || isAdmin) && (canMoveOpt !== false) && (canEdit !== false);

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
    isDragging = false;
    isResizing = false;

    e.preventDefault();

    window.addEventListener('mousemove', onMouseMove);
    window.addEventListener('mouseup', onMouseUp);
  };

  const onMouseMove = (e) => {
    if (!activeEl) return;
    const dx = e.clientX - startX;
    const dy = e.clientY - startY;

    if (isResizing) {
      // Horizontal resizing (width)
      if (resizeHandle.includes('r')) {
        const newWidth = Math.max(160, Math.min(650, origWidth + dx));
        activeEl.style.width = `${newWidth}px`;
        activeEl.style.minWidth = `${newWidth}px`;
      } else if (resizeHandle.includes('l')) {
        const newWidth = Math.max(160, Math.min(650, origWidth - dx));
        activeEl.style.width = `${newWidth}px`;
        activeEl.style.minWidth = `${newWidth}px`;
        activeEl.style.left = `${origLeft + dx}px`;
      }

      // Vertical resizing (height)
      if (resizeHandle.includes('b')) {
        const newHeight = Math.max(70, origHeight + dy);
        activeEl.style.minHeight = `${newHeight}px`;
      } else if (resizeHandle.includes('t')) {
        const newHeight = Math.max(70, origHeight - dy);
        activeEl.style.minHeight = `${newHeight}px`;
        activeEl.style.top = `${origTop + dy}px`;
      }
      return;
    }

    // Normal dragging
    if (!isDragging && (Math.abs(dx) > 3 || Math.abs(dy) > 3)) {
      isDragging = true;
      activeEl.classList.add('is-dragging');
    }

    if (isDragging) {
      activeEl.style.position = 'relative';
      activeEl.style.left = `${origLeft + dx}px`;
      activeEl.style.top = `${origTop + dy}px`;
    }
  };

  const onMouseUp = () => {
    window.removeEventListener('mousemove', onMouseMove);
    window.removeEventListener('mouseup', onMouseUp);

    if (activeEl) {
      const wasResizing = isResizing;
      const wasDragging = isDragging;
      activeEl.classList.remove('is-dragging');
      activeEl.classList.remove('is-resizing');

      if ((wasDragging || wasResizing) && typeof onDragEnd === 'function') {
        onDragEnd(activeEl, wasResizing ? 'resize' : 'drag');
      }
      activeEl = null;
    }
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
