/* ==========================================================================
   InvoiceFlow — Create Invoice Page Logic
   --------------------------------------------------------------------------
   Responsibilities:
   1. LIVE PREVIEW  → reflect every input change in the right-hand preview.
   2. MATH         → line totals, subtotal, tax, discount, grand total
                     (uses integer-cents internally to avoid float drift).
   3. FORM HELPERS → Add/remove item rows (keep at least 1), logo upload,
                     currency-symbol selection, date formatting,
                     SERVICE DROPDOWN → fill description + price from saved svc.
   4. SUBMIT       → ensure the form POSTs correctly; any server-side
                     validation errors are shown by Blade on re-render.
   ========================================================================== */

(function () {
    'use strict';

    // ---------------------------------------------------------------------
    // Currency symbol map (mirrors the PHP currency array)
    // ---------------------------------------------------------------------
    var CURRENCY_SYMBOLS = {
        USD: '$',
        PKR: 'Rs',
        EUR: '\u20AC',
        GBP: '\u00A3'
    };

    // ---------------------------------------------------------------------
    // Math helpers (work in minor units = cents)
    // ---------------------------------------------------------------------

    /**
     * Parse a string as a non-negative float. Treat '' / NaN / negative as 0.
     * Returns a float.
     */
    function parsePositiveNumber(value) {
        if (value === null || value === undefined) return 0;
        var num = parseFloat(String(value).replace(/,/g, ''));
        if (isNaN(num) || !isFinite(num) || num < 0) return 0;
        return num;
    }

    /**
     * Parse a string as an integer >= 1 for quantity.
     */
    function parseQuantity(value) {
        var n = parseInt(value, 10);
        if (isNaN(n) || !isFinite(n) || n < 1) return 1;
        return n;
    }

    /**
     * Format a float amount with the right currency symbol and 2 decimals.
     * Symbol appears LEFT of the number for $ / € / £, RIGHT for Rs (PKR).
     */
    function formatMoney(amount, currencyCode) {
        var symbol = CURRENCY_SYMBOLS[currencyCode] || '';
        var fixed  = Number(amount).toFixed(2);
        // PKR:  Rs 1,234.56   (symbol on left, with space — matches common usage)
        if (currencyCode === 'PKR') {
            return symbol + ' ' + fixed;
        }
        return symbol + fixed;
    }

    /**
     * Format a date input value YYYY-MM-DD to locale friendly MMM D, YYYY.
     */
    function formatDate(str) {
        if (!str) return '\u2013'; // en dash
        var d = new Date(str + 'T00:00:00');
        if (isNaN(d.getTime())) return str;
        var opts = { year: 'numeric', month: 'short', day: 'numeric' };
        return d.toLocaleDateString(undefined, opts);
    }

    // ---------------------------------------------------------------------
    // DOM references
    // ---------------------------------------------------------------------
    var $ = function (id) { return document.getElementById(id); };

    document.addEventListener('DOMContentLoaded', function () {
        // -- Form elements ---------------------------------------------------
        var form           = $('invoiceForm');
        var addItemBtn     = $('addItemBtn');
        var itemsBody      = $('itemsBody');
        var currencySel    = $('currency');
        var logoFileInput  = $('logoFile');
        var logoDataInput  = $('logoData');

        // -- Form totals display (left column) ------------------------------
        var subtotalDisplay = $('subtotal_display');
        var taxDisplay      = $('tax_display');
        var taxPctLabel     = $('tax_pct_label');
        var discountDisplay = $('discount_display');
        var totalDisplay    = $('total_display');

        // -- Preview elements (right column) --------------------------------
        var pBusinessName    = $('p_business_name');
        var pBusinessEmail   = $('p_business_email');
        var pBusinessPhone   = $('p_business_phone');
        var pBusinessAddress = $('p_business_address');
        var pLogoWrap        = $('p_business_logo');
        var pLogoImg         = $('p_logo_img');

        var pClientName    = $('p_client_name');
        var pClientEmail   = $('p_client_email');
        var pClientPhone   = $('p_client_phone');
        var pClientAddress = $('p_client_address');

        var pInvoiceNumber = $('p_invoice_number');
        var pInvoiceDate   = $('p_invoice_date');
        var pDueDate       = $('p_due_date');

        var pItemsBody   = $('p_items_body');
        var pSubtotal    = $('p_subtotal');
        var pTax         = $('p_tax');
        var pTaxLabel    = $('p_tax_label');
        var pDiscount    = $('p_discount');
        var pTotal       = $('p_total');
        var pNotes       = $('p_notes');
        var pNotesPH     = $('p_notes_placeholder');

        // -- Services: load saved products from the JSON <script> tag --------
        var SERVICE_OPTIONS = [];
        try {
            var tag = document.getElementById('servicesData');
            if (tag && tag.textContent) {
                SERVICE_OPTIONS = JSON.parse(tag.textContent) || [];
            }
        } catch (e) {
            SERVICE_OPTIONS = [];
        }

        // -- Empty-field placeholders (matches the design request) ----------
        var PLACEHOLDER = {
            businessName: 'Your business name',
            clientName:   'Client name'
        };

        // =====================================================================
        // RECALCULATE EVERYTHING
        // Called on every `input` event.
        // =====================================================================
        function recalculate() {
            var currency = currencySel.value || 'USD';

            // -- (A) Line totals for each item row in the form ----------------
            var rows = itemsBody.querySelectorAll('.item-row');
            var subtotalCents = 0;
            var itemSummaries = []; // {description, qty, price, lineTotalDecimal}

            rows.forEach(function (row) {
                var qtyInput   = row.querySelector('.item-quantity');
                var priceInput = row.querySelector('.item-price');
                var descInput  = row.querySelector('.item-description');
                var lineEl     = row.querySelector('.item-line-total');

                var qty   = parseQuantity(qtyInput && qtyInput.value);
                var price = parsePositiveNumber(priceInput && priceInput.value);

                // Convert to cents to avoid float rounding
                var priceCents    = Math.round(price * 100);
                var lineCents     = qty * priceCents;
                var lineDecimal   = lineCents / 100;

                subtotalCents += lineCents;

                // Update inline line-total cell (right of each form row)
                if (lineEl) {
                    lineEl.textContent = formatMoney(lineDecimal, currency);
                }

                // Keep data for the preview items table
                itemSummaries.push({
                    description: (descInput && descInput.value) ? String(descInput.value).trim() : '',
                    qty:         qty,
                    price:       price,
                    lineTotal:   lineDecimal
                });
            });

            // -- (B) Tax -------------------------------------------------------
            var taxInput = $('tax');
            var taxPct = parsePositiveNumber(taxInput ? taxInput.value : 0);
            if (taxPct > 100) taxPct = 100; // safety: tax % max 100
            var taxCents = Math.round((subtotalCents * taxPct) / 100);

            // -- (C) Discount (fixed amount) ----------------------------------
            var discInput = $('discount');
            var discDecimal = parsePositiveNumber(discInput ? discInput.value : 0);
            var discCents   = Math.round(discDecimal * 100);

            // -- (D) Grand total ----------------------------------------------
            var beforeDiscCents = subtotalCents + taxCents;
            var totalCents      = Math.max(0, beforeDiscCents - discCents);

            var subtotalDecimal = subtotalCents / 100;
            var taxDecimal      = taxCents      / 100;
            var totalDecimal    = totalCents    / 100;

            // =================================================================
            // UPDATE FORM-SIDE DISPLAY
            // =================================================================
            if (subtotalDisplay) subtotalDisplay.textContent = formatMoney(subtotalDecimal, currency);
            if (taxDisplay)      taxDisplay.textContent      = formatMoney(taxDecimal, currency);
            if (taxPctLabel)     taxPctLabel.textContent     = '(' + Number(taxPct).toFixed(Number.isInteger(taxPct) ? 0 : 2) + '%)';
            if (discountDisplay) discountDisplay.textContent = formatMoney(discDecimal, currency);
            if (totalDisplay)    totalDisplay.textContent    = formatMoney(totalDecimal, currency);

            // =================================================================
            // UPDATE PREVIEW-SIDE TOTALS
            // =================================================================
            if (pSubtotal) pSubtotal.textContent = formatMoney(subtotalDecimal, currency);
            if (pTax)      pTax.textContent      = formatMoney(taxDecimal, currency);
            if (pTaxLabel) pTaxLabel.textContent = 'Tax (' + Number(taxPct).toFixed(Number.isInteger(taxPct) ? 0 : 2) + '%)';
            if (pDiscount) pDiscount.textContent = formatMoney(discDecimal, currency);
            if (pTotal)    pTotal.textContent    = formatMoney(totalDecimal, currency);

            // =================================================================
            // UPDATE PREVIEW: ITEMS TABLE
            // =================================================================
            renderPreviewItems(itemSummaries, currency);
        }

        // =====================================================================
        // Render preview items <tbody> from itemSummaries array
        // =====================================================================
        function renderPreviewItems(itemSummaries, currency) {
            if (!pItemsBody) return;

            var html = '';
            itemSummaries.forEach(function (item) {
                var desc = item.description || '<span style="color:rgba(74,88,102,0.4);font-style:italic;">Item description</span>';
                html +=
                    '<tr style="border-bottom:1px solid rgba(18,32,46,0.06);">' +
                        '<td style="text-align:left;  padding:0.5rem 0.25rem;">' + escapeHtml(desc, true) + '</td>' +
                        '<td style="text-align:right; padding:0.5rem 0.25rem; font-variant-numeric:tabular-nums;">' + item.qty + '</td>' +
                        '<td style="text-align:right; padding:0.5rem 0.25rem; font-variant-numeric:tabular-nums;">' + formatMoney(item.price, currency) + '</td>' +
                        '<td style="text-align:right; padding:0.5rem 0.25rem; font-variant-numeric:tabular-nums; font-weight:600;">' + formatMoney(item.lineTotal, currency) + '</td>' +
                    '</tr>';
            });

            // If there are somehow zero rows (shouldn't happen), show a hint.
            if (!itemSummaries.length) {
                html =
                    '<tr><td colspan="4" style="text-align:center; padding:1rem; color:rgba(74,88,102,0.4); font-style:italic;">' +
                        'No items yet.' +
                    '</td></tr>';
            }

            pItemsBody.innerHTML = html;
        }

        // =====================================================================
        // Update PREVIEW textual fields (business / client / invoice meta / notes)
        // =====================================================================
        function updateTextPreview() {
            // Business
            var bName = ($('business_name').value || '').trim();
            pBusinessName.textContent    = bName || PLACEHOLDER.businessName;
            pBusinessName.style.color    = bName ? '' : 'rgba(74,88,102,0.4)';

            setOptionalText(pBusinessEmail,   ($('business_email').value || '').trim());
            setOptionalText(pBusinessPhone,   ($('business_phone').value || '').trim());
            setOptionalText(pBusinessAddress, ($('business_address').value || '').trim());

            // Client
            var cName = ($('client_name').value || '').trim();
            pClientName.textContent    = cName || PLACEHOLDER.clientName;
            pClientName.style.color    = cName ? '' : 'rgba(74,88,102,0.4)';

            setOptionalText(pClientEmail,   ($('client_email').value || '').trim());
            setOptionalText(pClientPhone,   ($('client_phone').value || '').trim());
            setOptionalText(pClientAddress, ($('client_address').value || '').trim());

            // Invoice meta
            pInvoiceNumber.textContent = ($('invoice_number').value || 'INV-001').trim() || 'INV-001';
            pInvoiceDate.textContent   = formatDate($('invoice_date').value);
            pDueDate.textContent       = formatDate($('due_date').value);

            // Notes
            var notes = ($('notes').value || '').trim();
            if (notes) {
                pNotes.textContent = notes;
                pNotes.style.display = 'block';
                if (pNotesPH) pNotesPH.style.display = 'none';
            } else {
                pNotes.textContent = '';
                pNotes.style.display = 'none';
                if (pNotesPH) pNotesPH.style.display = 'block';
            }
        }

        /**
         * For optional fields: show the value, or hide the element entirely.
         * (This avoids the design having ugly "empty" lines.)
         */
        function setOptionalText(el, val) {
            if (!el) return;
            if (val) {
                el.textContent = val;
                el.style.display = '';
            } else {
                el.textContent = '';
                el.style.display = 'none';
            }
        }

        // =====================================================================
        // Basic HTML escape (prevents XSS / broken layout from user text)
        // =====================================================================
        function escapeHtml(str, allowMarkupForPlaceholder) {
            if (str === null || str === undefined) return '';
            var s = String(str);
            // If it's our placeholder markup (<span style="...">...</span>) let it through.
            if (allowMarkupForPlaceholder && /<span/.test(s) && /Item description/.test(s)) return s;
            // Otherwise escape.
            return s
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // =====================================================================
        // ADD / REMOVE item rows
        // Includes service dropdown when saved services are available.
        // =====================================================================
        function getNextIndex() {
            var rows = itemsBody.querySelectorAll('.item-row');
            if (!rows.length) return 0;
            var max = -1;
            rows.forEach(function (r) {
                var idx = parseInt(r.getAttribute('data-index'), 10);
                if (idx > max) max = idx;
            });
            return max + 1;
        }

        function buildServiceDropdownMarkup(idx) {
            if (!SERVICE_OPTIONS.length) return '';
            var opts = '';
            SERVICE_OPTIONS.forEach(function (svc) {
                var safeName = escapeHtml(svc.name + ' — $' + svc.price);
                var safeDesc = escapeHtml(svc.description || '');
                var safePrice = escapeHtml(String(svc.price));
                opts += '<option value="' + svc.id + '"' +
                            ' data-description="' + safeDesc + '"' +
                            ' data-price="' + safePrice + '">' +
                            safeName +
                        '</option>';
            });
            return (
                '<div class="mb-2">' +
                    '<select class="form-select form-select-sm item-service" ' +
                            'data-row-index="' + idx + '" ' +
                            'aria-label="Pick a saved service for this row">' +
                        '<option value="">— Pick a saved service (optional) —</option>' +
                        opts +
                    '</select>' +
                '</div>'
            );
        }

        function addItemRow() {
            var idx = getNextIndex();
            var tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.setAttribute('data-index', idx);
            tr.innerHTML =
                '<td>' +
                    buildServiceDropdownMarkup(idx) +
                    '<input type="text" name="items[' + idx + '][description]" ' +
                           'class="form-control item-description" ' +
                           'placeholder="Website design">' +
                '</td>' +
                '<td>' +
                    '<input type="number" name="items[' + idx + '][quantity]" ' +
                           'class="form-control item-quantity" min="1" step="1" value="1">' +
                '</td>' +
                '<td>' +
                    '<input type="number" name="items[' + idx + '][price]" ' +
                           'class="form-control item-price" min="0" step="0.01" placeholder="0.00">' +
                '</td>' +
                '<td class="item-line-total text-end pe-3" ' +
                    'style="font-variant-numeric:tabular-nums; font-weight:600;">0.00</td>' +
                '<td class="text-center">' +
                    '<button type="button" class="btn btn-sm btn-link remove-item-btn" ' +
                            'style="color:var(--overdue);" aria-label="Remove this item">' +
                        '<i class="bi bi-trash3"></i>' +
                    '</button>' +
                '</td>';
            itemsBody.appendChild(tr);
            // Focus the description for quick typing.
            var desc = tr.querySelector('.item-description');
            if (desc) desc.focus();
        }

        function removeItemRow(btn) {
            var rows = itemsBody.querySelectorAll('.item-row');
            if (rows.length <= 1) {
                // Can't go below one row.
                var warning = btn.closest('tr') ? btn.closest('tr').querySelector('.item-description') : null;
                if (warning) {
                    warning.focus();
                }
                return;
            }
            var row = btn.closest('.item-row');
            if (row) row.remove();
        }

        // Delegate remove-item clicks.
        if (itemsBody) {
            itemsBody.addEventListener('click', function (e) {
                var btn = e.target.closest('.remove-item-btn');
                if (btn) {
                    e.preventDefault();
                    removeItemRow(btn);
                    recalculate();
                    updateTextPreview();
                }
            });

            // -----------------------------------------------------------------
            // SERVICE DROPDOWN HANDLER (event delegation → any row)
            // On change → fill description + price, reset the dropdown to
            // "Pick a service…" so user can pick another next time if needed.
            // -----------------------------------------------------------------
            itemsBody.addEventListener('change', function (e) {
                var sel = e.target.closest('.item-service');
                if (!sel) return;

                var val = sel.value;
                if (!val) return;

                var opt = sel.options[sel.selectedIndex];
                if (!opt) return;

                var row = sel.closest('.item-row');
                if (!row) return;

                var description = opt.getAttribute('data-description') || '';
                var price       = opt.getAttribute('data-price') || '';

                // Fill description if empty. If user already typed something,
                // concatenate (or replace if only whitespace).
                var descInput = row.querySelector('.item-description');
                if (descInput) {
                    var current = (descInput.value || '').trim();
                    if (current === '') {
                        // Prefer name + description, fallback to option text.
                        var fallback = opt.textContent.split(' — ')[0] || '';
                        descInput.value = description ? description : fallback;
                    }
                }

                // Fill price if empty.
                var priceInput = row.querySelector('.item-price');
                if (priceInput) {
                    var currPrice = (priceInput.value || '').trim();
                    if (currPrice === '' || parsePositiveNumber(currPrice) === 0) {
                        priceInput.value = price;
                    }
                }

                // Reset to "— Pick a saved service —" so row looks clean.
                // User can pick again if they want; dropdown doesn't need to
                // remember selection because fields were filled.
                sel.value = '';

                recalculate();
                updateTextPreview();
            });
        }

        if (addItemBtn) {
            addItemBtn.addEventListener('click', function (e) {
                e.preventDefault();
                addItemRow();
                // Recalc will make sure the new row's initial line-total displays correctly.
                recalculate();
            });
        }

        // =====================================================================
        // LOGO UPLOAD (optional)
        // Reads the chosen file as a data URL and:
        //   a) stores it in the hidden logoData input for the form POST,
        //   b) updates the live-preview logo <img> element.
        // If the page already has a default logo (from BusinessProfile), the
        // user can still upload a per-invoice override (or clear to default).
        // =====================================================================
        if (logoFileInput && logoDataInput && pLogoImg) {
            logoFileInput.addEventListener('change', function () {
                var file = logoFileInput.files && logoFileInput.files[0];
                if (!file) {
                    // No file chosen → keep whatever logo is currently stored
                    // (BusinessProfile default set via logoDataInput on page load)
                    return;
                }
                if (!/^image\//.test(file.type)) {
                    logoFileInput.value = '';
                    return;
                }
                var reader = new FileReader();
                reader.onload = function () {
                    var dataUrl = reader.result;
                    logoDataInput.value = dataUrl;
                    pLogoImg.src = dataUrl;
                    pLogoWrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        }

        // =====================================================================
        // MASTER LISTENER — any `input` anywhere in the form triggers update.
        // =====================================================================
        if (form) {
            form.addEventListener('input', function () {
                recalculate();
                updateTextPreview();
            });

            // Currency change also triggers (it's a `change` event not `input`
            // on some browsers for <select>).
            if (currencySel) {
                currencySel.addEventListener('change', function () {
                    recalculate();
                    updateTextPreview();
                });
            }

            // Client dropdown (when authed) → populate preview "Bill to" block
            var clientSel = $('client_id');
            if (clientSel) {
                clientSel.addEventListener('change', function () {
                    var opt = clientSel.options[clientSel.selectedIndex];
                    if (opt) {
                        var name  = opt.getAttribute('data-name')  || opt.textContent.split('<')[0].trim();
                        var email = opt.getAttribute('data-email') || '';
                        if (pClientName) pClientName.textContent = name || PLACEHOLDER.clientName;
                        if (pClientEmail) setOptionalText(pClientEmail, email);
                    }
                });
            }
        }

        // =====================================================================
        // INITIAL RUN — after DOMContentLoaded, paint the preview from
        // whatever defaults/old input the page rendered with.
        // =====================================================================
        recalculate();
        updateTextPreview();
    });
})();
