
(function () {
    "use strict";

    var table = document.getElementById("cart-table");
    if (!table) {
        return; // empty cart — nothing to enhance
    }

    // The cart controller derives BASE_URL from the current path: everything
    // lives under ".../?page=cart", so we can post back to the same script.
    var CART_URL = window.location.pathname;

    var MIN_QTY = 0;   // 0 means "remove this line"
    var MAX_QTY = 99;

    var totalEl = document.getElementById("cart-total");
    var updateBtn = document.getElementById("update-cart-btn");

    // With JS on, per-line AJAX replaces the bulk "Update Cart" button.
    if (updateBtn) {
        updateBtn.style.display = "none";
    }

    function formatMoney(value) {
        return "$" + Number(value).toFixed(2);
    }

    function clamp(n) {
        n = parseInt(n, 10);
        if (isNaN(n)) { n = MIN_QTY; }
        return Math.max(MIN_QTY, Math.min(MAX_QTY, n));
    }

    // POST helper. Flags the request as AJAX so the controller returns JSON.
    function postAjax(params) {
        params.ajax = "1";
        var body = Object.keys(params)
            .map(function (k) {
                return encodeURIComponent(k) + "=" + encodeURIComponent(params[k]);
            })
            .join("&");

        return fetch(CART_URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: body,
            credentials: "same-origin"
        }).then(function (res) {
            if (!res.ok) { throw new Error("Request failed: " + res.status); }
            return res.json();
        });
    }

    function rowFor(el) {
        return el.closest("tr");
    }

    function updateCartTotal(value) {
        if (totalEl) {
            totalEl.textContent = formatMoney(value);
        }
    }

    function removeRow(row) {
        row.parentNode.removeChild(row);
        // If that was the last line, reload so the "empty cart" view shows.
        if (table.querySelectorAll("tbody tr").length === 0) {
            window.location.reload();
        }
    }

    // Send a new quantity for one line to the server.
    function commitQuantity(row, qty) {
        var id = row.getAttribute("data-id");

        postAjax({ action: "update", id: id, qty: qty })
            .then(function (data) {
                if (data.removed) {
                    removeRow(row);
                } else {
                    var subtotalCell = row.querySelector(".cart-subtotal");
                    if (subtotalCell) {
                        subtotalCell.textContent = formatMoney(data.line_subtotal);
                    }
                }
                updateCartTotal(data.cart_total);
            })
            .catch(function () {
                // If the AJAX call fails, fall back to a full form submit so
                // the user is never stuck with a stale cart.
                var form = document.getElementById("cart-form");
                if (form) { form.submit(); }
            });
    }

    // Recalculate one line's subtotal locally for instant feedback.
    function previewSubtotal(row, qty) {
        var price = parseFloat(row.getAttribute("data-price")) || 0;
        var cell = row.querySelector(".cart-subtotal");
        if (cell) {
            cell.textContent = formatMoney(price * qty);
        }
    }

    // Debounce so holding the stepper / typing doesn't spam the server.
    var timers = {};
    function scheduleCommit(row, qty) {
        var id = row.getAttribute("data-id");
        previewSubtotal(row, qty);
        clearTimeout(timers[id]);
        timers[id] = setTimeout(function () {
            commitQuantity(row, qty);
        }, 350);
    }

    // --- Stepper buttons (+ / -) --------------------------------------------
    table.addEventListener("click", function (e) {
        var step = e.target.closest(".qty-step");
        if (!step) { return; }

        var row = rowFor(step);
        var input = row.querySelector(".qty-input");
        var next = clamp(parseInt(input.value, 10) + parseInt(step.getAttribute("data-dir"), 10));
        input.value = next;
        scheduleCommit(row, next);
    });

    // --- Typing directly in the quantity box --------------------------------
    table.addEventListener("input", function (e) {
        var input = e.target.closest(".qty-input");
        if (!input) { return; }
        var row = rowFor(input);
        scheduleCommit(row, clamp(input.value));
    });

    // Normalise the field when the user leaves it (e.g. blank -> 0).
    table.addEventListener("change", function (e) {
        var input = e.target.closest(".qty-input");
        if (!input) { return; }
        input.value = clamp(input.value);
    });

    // --- Remove links -------------------------------------------------------
    table.addEventListener("click", function (e) {
        var link = e.target.closest(".remove-item");
        if (!link) { return; }

        e.preventDefault();
        if (!window.confirm("Remove this item from your cart?")) {
            return;
        }

        var row = rowFor(link);
        var id = row.getAttribute("data-id");

        postAjax({ action: "remove", id: id })
            .then(function (data) {
                removeRow(row);
                updateCartTotal(data.cart_total);
            })
            .catch(function () {
                // Fallback: follow the plain link the server still understands.
                window.location.href = link.getAttribute("href");
            });
    });
})();
