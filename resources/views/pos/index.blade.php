@extends('layouts.app')

@section('content')


    <div class="pos-shell">
        <!-- LEFT: Products -->
        <section class="pos-left">
            <div class="pos-card pos-searchCard">
                <div class="pos-cardHead">
                    <h3 class="pos-title">بحث المنتجات</h3>
                    <div class="pos-subtitle">ادخل باركود (أرقام) أو اسم المنتج (3 أحرف+)</div>
                </div>

                <div class="pos-searchRow">
                    <input type="text"
                           id="barcode-input"
                           placeholder="ادخل الباركود أو اسم المنتج"
                           class="pos-input pos-input--lg"
                           autocomplete="off">
                    <button onclick="searchProducts()" class="pos-btn pos-btn--primary pos-btn--lg">بحث</button>
                </div>

                <!-- Search Results -->
                <div id="search-results" class="pos-results" style="display: none;"></div>

                <div class="pos-hints">
                    <span>ESC لإغلاق النتائج</span>
                    <span>Enter للبحث بالباركود</span>
                </div>
            </div>
        </section>

        <!-- RIGHT: Checkout -->
        <aside class="pos-right">
            <!-- Cart -->
            <div class="pos-card pos-cartCard">
                <div class="pos-cardHead pos-cardHead--row">
                    <h3 class="pos-title">سلة التسوق</h3>
                    <button onclick="clearCartWithConfirmation()" class="pos-btn pos-btn--danger pos-btn--sm">مسح السلة</button>
                </div>

                <div id="cart-items" class="pos-cartItems">
                    <div class="pos-empty">
                        <div class="pos-emptyIcon">🛒</div>
                        <div class="pos-emptyTitle">السلة فارغة</div>
                        <div class="pos-emptyText">ابدأ بإضافة منتجات من البحث</div>
                    </div>
                </div>

                <div id="cart-summary" class="pos-summary" style="display: none;">
                    <div class="pos-summaryGrid">
                        <div class="pos-summaryStat">
                            <div class="pos-muted">عدد الأصناف</div>
                            <div class="pos-stat" id="items-count">0</div>
                        </div>
                        <div class="pos-summaryStat">
                            <div class="pos-muted">إجمالي الكمية</div>
                            <div class="pos-stat"><span id="total-quantity">0</span></div>
                        </div>
                    </div>

                    <div class="pos-divider"></div>

                    <div class="pos-summaryRow">
                        <span class="pos-muted">المجموع الفرعي</span>
                        <span class="pos-money">$<span id="subtotal">0.00</span></span>
                    </div>

                    <div id="discount-row" class="pos-summaryRow pos-summaryRow--danger hidden">
                        <span class="pos-muted">إجمالي الخصم</span>
                        <span class="pos-money">-$<span id="total-discount">0.00</span></span>
                    </div>

                    <div class="pos-summaryTotal">
                        <span>الإجمالي</span>
                        <span class="pos-money">$<span id="total">0.00</span></span>
                    </div>

                    <div class="pos-lbp">
                        <span class="pos-muted">بالليرة</span>
                        <span><span id="total-lbp">0</span> ل.ل.</span>
                    </div>
                </div>
            </div>

            <!-- Customer -->
            <div class="pos-card">
                <div class="pos-cardHead">
                    <h3 class="pos-title">العميل</h3>
                </div>

                <input type="text"
                       id="customer-search"
                       placeholder="البحث عن عميل"
                       class="pos-input">

                <div id="selected-customer" class="pos-customer" style="display: none;">
                    <div class="pos-customerTop">
                        <div>
                            <div class="pos-customerName" id="customer-name"></div>
                            <div class="pos-muted" id="customer-phone"></div>
                        </div>
                        <button onclick="clearCustomer()" class="pos-btn pos-btn--danger pos-btn--xs">إلغاء</button>
                    </div>
                    <div class="pos-customerDebt">
                        <span class="pos-muted">المديونية</span>
                        <strong class="pos-danger">$<span id="customer-debt">0.00</span></strong>
                    </div>
                </div>

                <div id="customer-results" class="pos-customerResults" style="display: none;"></div>
            </div>

            <!-- Payment -->
            <div class="pos-card">
                <div class="pos-cardHead">
                    <h3 class="pos-title">طريقة الدفع</h3>
                </div>

                <div class="pos-segment" role="tablist" aria-label="Payment method">
                    <button id="cash-btn" onclick="setPaymentMethod('cash')" class="pos-segmentBtn is-active" role="tab">نقدي</button>
                    <button id="debt-btn" onclick="setPaymentMethod('debt')" class="pos-segmentBtn" role="tab">دين</button>
                </div>

                <div id="cash-payment" class="pos-payBox" style="display: none;">
                    <label class="pos-label">المبلغ المدفوع (للحساب فقط)</label>
                    <input type="number"
                           id="paid-amount"
                           placeholder="0.00"
                           step="0.01"
                           min="0"
                           class="pos-input">
                    <div class="pos-hint">
                        ملاحظة: المبلغ المدفوع للعرض فقط. النظام يعتبر النقدية مدفوعة كاملة.
                    </div>

                    <div id="change-amount" class="pos-change" style="display: none;">
                        <strong>الباقي: $<span id="change-value">0.00</span></strong>
                    </div>
                </div>

                <div id="debt-payment" class="pos-payBox" style="display: none;">
                    <div class="pos-warning">
                        <strong>ملاحظة:</strong> يجب اختيار عميل للدفع بالدين
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="pos-card">
                <label class="pos-label">ملاحظات</label>
                <textarea id="sale-notes"
                          rows="3"
                          placeholder="ملاحظات اختيارية..."
                          class="pos-input pos-textarea"></textarea>
            </div>

            <!-- Bottom actions (sticky) -->
            <div class="pos-actions">
                <button id="auto-print-btn" onclick="toggleAutoPrint()" class="pos-btn pos-btn--ghost">
                    طباعة تلقائية: متوقفة
                </button>

                <button id="process-sale-btn" onclick="processSale()" disabled class="pos-btn pos-btn--cta is-disabled">
                    إتمام البيع
                </button>

                <div class="pos-shortcuts">
                    الاختصارات: F1 بيع جديد • F2 دفع • F3 مسح السلة
                </div>
            </div>
        </aside>
    </div>


    <!-- Loading Overlay -->
    <div id="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 2rem; margin-bottom: 1rem;">⏳</div>
            <div style="font-weight: 600;">جار معالجة البيع...</div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let cart = [];
        let selectedCustomer = null;
        let paymentMethod = 'cash';

        // Initialize POS
        document.addEventListener('DOMContentLoaded', function() {
            loadCart();

            // Barcode input auto-search
            const barcodeInput = document.getElementById('barcode-input');
            barcodeInput.addEventListener('input', debounce(handleBarcodeInput, 300));
            barcodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchByBarcode();
                }
            });

            // Customer search
            const customerSearch = document.getElementById('customer-search');
            customerSearch.addEventListener('input', debounce(searchCustomers, 300));

            // Paid amount calculation
            const paidAmount = document.getElementById('paid-amount');
            paidAmount.addEventListener('input', calculateChange);

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F1') {
                    e.preventDefault();
                    newSale();
                } else if (e.key === 'F2') {
                    e.preventDefault();
                    document.getElementById('process-sale-btn').click();
                } else if (e.key === 'F3') {
                    e.preventDefault();
                    clearCart();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    clearSearchResults();
                }
            });
        });

        // Barcode/Product Search Functions
        function handleBarcodeInput() {
            const input = document.getElementById('barcode-input');
            const value = input.value.trim();

            if (value.length >= 3) {
                if (isBarcode(value)) {
                    searchByBarcode();
                } else {
                    searchProducts();
                }
            } else {
                clearSearchResults();
            }
        }

        function isBarcode(value) {
            return /^\d{8,}$/.test(value);
        }

        function searchByBarcode() {
            const barcode = document.getElementById('barcode-input').value.trim();

            if (!barcode) return;

            fetch('/pos/search-barcode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ barcode: barcode })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        addToCart(data.product.id, 1);
                        document.getElementById('barcode-input').value = '';
                        clearSearchResults();
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('حدث خطأ في البحث', 'error');
                });
        }

        function searchProducts() {
            const search = document.getElementById('barcode-input').value.trim();

            if (!search || search.length < 3) {
                clearSearchResults();
                return;
            }

            fetch('/pos/search-products', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ search: search })
            })
                .then(response => response.json())
                .then(data => {
                    displaySearchResults(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        let autoPrintEnabled = false;

        function toggleAutoPrint() {
            autoPrintEnabled = !autoPrintEnabled;
            const button = document.getElementById('auto-print-btn');

            button.classList.toggle('is-on', autoPrintEnabled);

            if (autoPrintEnabled) {
                button.textContent = 'طباعة تلقائية: مفعلة';
            } else {
                button.textContent = 'طباعة تلقائية: متوقفة';
            }



            function displaySearchResults(products) {
                const resultsDiv = document.getElementById('search-results');

                if (!products || products.length === 0) {
                    resultsDiv.style.display = 'none';
                    resultsDiv.innerHTML = '';
                    return;
                }

                let html = '';

                products.forEach(product => {
                    html += `
                    <div class="pos-productCard" onclick="addToCart(${product.id}, 1)">
                        <div class="pos-productCard__top">
                            <div class="pos-productCard__name">${product.name}</div>
                            <div class="pos-productCard__price" style="color:${product.stock_color};">$${product.price}</div>
                        </div>

                        <div class="pos-productCard__meta">
                            ${product.barcode ? `<span>باركود: ${product.barcode}</span>` : ``}
                            <span>المخزون: ${product.stock} ${product.unit}</span>
                        </div>

                        <div class="pos-productCard__badge" style="background:${product.stock_color};">
                            ${getStockStatusText(product.stock_status)}
                        </div>
                    </div>
                `;
                });

                resultsDiv.innerHTML = html;
                resultsDiv.style.display = 'grid';
            }


            function clearSearchResults() {
                const resultsDiv = document.getElementById('search-results');
                resultsDiv.style.display = 'none';
                resultsDiv.innerHTML = '';
            }

            function getStockStatusText(status) {
                switch(status) {
                    case 'in_stock': return 'متوفر';
                    case 'low_stock': return 'منخفض';
                    case 'out_of_stock': return 'نفد';
                    default: return '';
                }
            }

            // Cart Management Functions
            function addToCart(productId, quantity = 1) {
                showLoading();

                fetch('/pos/add-to-cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity,
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();
                        if (data.success) {
                            updateCartDisplay(data.cart);
                            showMessage(data.message, 'success');
                            clearSearchResults();
                        } else {
                            showMessage(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        console.error('Error:', error);
                        showMessage('حدث خطأ في إضافة المنتج', 'error');
                    });
            }

            function updateQuantity(index, quantity) {
                fetch('/pos/update-cart-item', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        index: index,
                        quantity: parseFloat(quantity)
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateCartDisplay(data.cart);
                        } else {
                            showMessage(data.message, 'error');
                            loadCart();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        loadCart();
                    });
            }

            function applyDiscount(index) {
                const discountType = document.getElementById(`discount-type-${index}`).value;
                const discountValue = parseFloat(document.getElementById(`discount-value-${index}`).value) || 0;

                if (discountValue < 0) {
                    showMessage('قيمة الخصم يجب أن تكون موجبة', 'error');
                    return;
                }

                fetch('/pos/apply-discount', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        index: index,
                        discount_type: discountType,
                        discount_value: discountValue
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateCartDisplay(data.cart);
                            showMessage('تم تطبيق الخصم', 'success');
                        } else {
                            showMessage(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('حدث خطأ في تطبيق الخصم', 'error');
                    });
            }

            function removeDiscount(index) {
                applyDiscount(index); // Apply with value 0
                document.getElementById(`discount-value-${index}`).value = 0;
            }

            function removeFromCart(index) {
                if (confirm('هل تريد حذف هذا المنتج من السلة؟')) {
                    fetch('/pos/remove-from-cart', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ index: index })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                updateCartDisplay(data.cart);
                                showMessage(data.message, 'success');
                            } else {
                                showMessage(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }
            }

            function clearCart() {
                if (cart.length === 0) return;

                fetch('/pos/clear-cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateCartDisplay(data.cart);
                            showMessage(data.message, 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }

            function clearCartWithConfirmation() {
                if (cart.length === 0) return;

                if (confirm('هل تريد مسح جميع المنتجات من السلة؟')) {
                    clearCart();
                }
            }

            function loadCart() {
                fetch('/pos/get-cart')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateCartDisplay(data.cart);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading cart:', error);
                    });
            }

            function updateCartDisplay(cartData) {
                cart = cartData.items || [];
                const cartItemsDiv = document.getElementById('cart-items');
                const cartSummary = document.getElementById('cart-summary');

                if (cart.length === 0) {
                    cartItemsDiv.innerHTML = `
                    <div class="pos-empty">
                        <div class="pos-emptyIcon">🛒</div>
                        <div class="pos-emptyTitle">السلة فارغة</div>
                        <div class="pos-emptyText">ابدأ بإضافة منتجات من البحث</div>
                    </div>
                `;
                    cartSummary.style.display = 'none';
                    updateProcessButton();
                    return;
                }

                let html = '';

                cart.forEach((item, index) => {
                    html += `
                    <div class="pos-cartItem">
                        <div class="pos-cartItemTop">
                            <div class="pos-cartItemInfo">
                                <div class="pos-cartItemName">${item.name}</div>
                                <div class="pos-cartItemMeta">
                                    <span class="pos-muted">السعر:</span>
                                    <span class="pos-money">$${parseFloat(item.price).toFixed(2)}</span>
                                    ${item.unit ? `<span class="pos-muted">•</span><span class="pos-muted">${item.unit}</span>` : ``}
                                </div>
                            </div>

                            <button onclick="removeFromCart(${index})" class="pos-iconBtn pos-iconBtn--danger" title="حذف">✕</button>
                        </div>

                        <div class="pos-cartItemControls">
                            <div class="pos-qty">
                                <button onclick="updateQuantity(${index}, -1)" class="pos-qtyBtn" title="نقص">−</button>
                                <input type="number"
                                       value="${item.quantity}"
                                       step="0.01"
                                       min="0.01"
                                       onchange="setQuantity(${index}, this.value)"
                                       class="pos-qtyInput">
                                <button onclick="updateQuantity(${index}, 1)" class="pos-qtyBtn" title="زيادة">+</button>
                            </div>

                            <div class="pos-lineTotal">
                                <div class="pos-muted">الإجمالي</div>
                                <div class="pos-money">$${parseFloat(item.total || 0).toFixed(2)}</div>
                            </div>
                        </div>

                        <!-- Discount (keep IDs for existing logic) -->
                        <div class="pos-discount">
                            <div class="pos-discountGrid">
                                <div>
                                    <label class="pos-miniLabel">نوع الخصم</label>
                                    <select id="discount-type-${index}" class="pos-miniSelect">
                                        <option value="percentage">%</option>
                                        <option value="amount">$</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="pos-miniLabel">قيمة الخصم</label>
                                    <input type="number"
                                           id="discount-value-${index}"
                                           value="${item.discount_percentage > 0 ? item.discount_percentage : (item.discount_amount > 0 ? item.discount_amount : 0)}"
                                           step="0.01"
                                           min="0"
                                           placeholder="0"
                                           class="pos-miniInput">
                                </div>
                                <button onclick="applyDiscount(${index})" class="pos-btn pos-btn--primary pos-btn--sm">تطبيق</button>
                            </div>

                            ${item.discount_amount > 0 ? `
                                <div class="pos-discountActive">
                                    <span>خصم نشط: ${item.discount_percentage > 0 ? item.discount_percentage.toFixed(1) + '%' : '$' + parseFloat(item.discount_amount).toFixed(2)}</span>
                                    <button onclick="removeDiscount(${index})" class="pos-linkDanger">إزالة</button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                });

                cartItemsDiv.innerHTML = html;

                // Update summary
                document.getElementById('items-count').textContent = cartData.items_count || 0;
                document.getElementById('total-quantity').textContent = parseFloat(cartData.total_quantity || 0).toFixed(2);
                document.getElementById('subtotal').textContent = parseFloat(cartData.subtotal || 0).toFixed(2);
                document.getElementById('total-discount').textContent = parseFloat(cartData.total_discount || 0).toFixed(2);
                document.getElementById('total').textContent = parseFloat(cartData.total || 0).toFixed(2);
                document.getElementById('total-lbp').textContent = (parseFloat(cartData.total || 0) * 89500).toLocaleString();

                // Show/hide discount row
                const discountRow = document.getElementById('discount-row');
                if (cartData.total_discount > 0) {
                    discountRow.classList.remove('hidden');
                } else {
                    discountRow.classList.add('hidden');
                }

                cartSummary.style.display = 'block';
                updateProcessButton();


                // Customer Management Functions
                function searchCustomers() {
                    const search = document.getElementById('customer-search').value.trim();

                    if (!search || search.length < 2) {
                        document.getElementById('customer-results').style.display = 'none';
                        return;
                    }

                    fetch('/pos/search-customers', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ search: search })
                    })
                        .then(response => response.json())
                        .then(data => {
                            displayCustomerResults(data);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }

                function displayCustomerResults(customers) {
                    const resultsDiv = document.getElementById('customer-results');

                    if (!customers || customers.length === 0) {
                        resultsDiv.style.display = 'none';
                        resultsDiv.innerHTML = '';
                        return;
                    }

                    let html = '';

                    customers.forEach(customer => {
                        const safeName = String(customer.name || '').replace(/'/g, "\'");
                        const safePhone = String(customer.phone || '').replace(/'/g, "\'");
                        html += `
                    <div class="pos-customerItem"
                         onclick="selectCustomer(${customer.id}, '${safeName}', '${safePhone}', ${customer.debt})">
                        <div>
                            <div style="font-weight:900;color:var(--text);">${customer.name}</div>
                            <div class="pos-muted">${customer.phone || ''}</div>
                        </div>
                        <div style="text-align:left;">
                            <div class="pos-muted">دين</div>
                            <div style="font-weight:900;color:var(--danger);">$${parseFloat(customer.debt || 0).toFixed(2)}</div>
                        </div>
                    </div>
                `;
                    });

                    resultsDiv.innerHTML = html;
                    resultsDiv.style.display = 'block';


                    function selectCustomer(id, name, phone, debt) {
                        selectedCustomer = { id, name, phone, debt };

                        document.getElementById('customer-name').textContent = name;
                        document.getElementById('customer-phone').textContent = phone || '';
                        document.getElementById('customer-debt').textContent = parseFloat(debt).toFixed(2);
                        document.getElementById('selected-customer').style.display = 'block';
                        document.getElementById('customer-results').style.display = 'none';
                        document.getElementById('customer-search').value = '';

                        updateProcessButton();
                    }

                    function clearCustomer() {
                        selectedCustomer = null;
                        document.getElementById('selected-customer').style.display = 'none';
                        document.getElementById('customer-search').value = '';
                        updateProcessButton();
                    }

                    // Payment Functions
                    function setPaymentMethod(method) {
                        paymentMethod = method;

                        // Toggle segment active state (no inline styles)
                        document.getElementById('cash-btn').classList.toggle('is-active', method === 'cash');
                        document.getElementById('debt-btn').classList.toggle('is-active', method === 'debt');

                        // Show/hide payment sections
                        document.getElementById('cash-payment').style.display = method === 'cash' ? 'block' : 'none';
                        document.getElementById('debt-payment').style.display = method === 'debt' ? 'block' : 'none';

                        updateProcessButton();

                        if (method === 'cash') {
                            calculateChange();
                        }


                        // UPDATED: Frontend-only change calculation
                        function calculateChange() {
                            if (paymentMethod !== 'cash') return;

                            const total = parseFloat(document.getElementById('total').textContent) || 0;
                            const paid = parseFloat(document.getElementById('paid-amount').value) || 0;
                            const change = Math.max(0, paid - total);

                            document.getElementById('change-value').textContent = change.toFixed(2);
                            document.getElementById('change-amount').style.display = paid > 0 ? 'block' : 'none';

                            updateProcessButton();
                        }

                        function updateProcessButton() {
                            const button = document.getElementById('process-sale-btn');

                            let canProcess = false;

                            if (cart.length > 0) {
                                if (paymentMethod === 'cash') {
                                    const total = parseFloat(document.getElementById('total').textContent) || 0;
                                    const paid = parseFloat(document.getElementById('paid-amount').value) || 0;
                                    canProcess = paid >= total;
                                } else if (paymentMethod === 'debt') {
                                    canProcess = selectedCustomer !== null;
                                }
                            }

                            button.disabled = !canProcess;
                            button.classList.toggle('is-disabled', !canProcess);


                            // UPDATED: Sale Processing - No paid_amount sent to server
                            function processSale() {
                                if (cart.length === 0) {
                                    showMessage('السلة فارغة', 'error');
                                    return;
                                }

                                if (paymentMethod === 'debt' && !selectedCustomer) {
                                    showMessage('يجب اختيار عميل للدفع بالدين', 'error');
                                    return;
                                }

                                if (paymentMethod === 'cash') {
                                    const total = parseFloat(document.getElementById('total').textContent) || 0;
                                    const paid = parseFloat(document.getElementById('paid-amount').value) || 0;

                                    if (paid < total) {
                                        showMessage(`المبلغ المدفوع ($${paid.toFixed(2)}) أقل من الإجمالي ($${total.toFixed(2)})`, 'error');
                                        return;
                                    }
                                }


                                showLoading();

                                const data = {
                                    payment_method: paymentMethod,
                                    customer_id: selectedCustomer ? selectedCustomer.id : null,
                                    notes: document.getElementById('sale-notes').value.trim()
                                    // REMOVED: paid_amount - no longer sent to server
                                };

                                fetch('/pos/process-sale', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify(data)
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        hideLoading();
                                        if (data.success) {
                                            showMessage(data.message, 'success');

                                            // Calculate change on frontend for display
                                            const frontendChangeAmount = calculateFrontendChange();

                                            // Show success modal with receipt option
                                            showSaleSuccessModal(data, frontendChangeAmount);

                                        } else {
                                            showMessage(data.message, 'error');
                                        }
                                    })
                                    .catch(error => {
                                        hideLoading();
                                        console.error('Error:', error);
                                        showMessage('حدث خطأ في معالجة البيع', 'error');
                                    });
                            }

                            // UPDATED: Calculate change on frontend for display purposes only
                            function calculateFrontendChange() {
                                if (paymentMethod !== 'cash') return 0;

                                const total = parseFloat(document.getElementById('total').textContent) || 0;
                                const paid = parseFloat(document.getElementById('paid-amount').value) || 0;

                                return Math.max(0, paid - total);
                            }

                            // UPDATED: Success modal with frontend-calculated change
                            function showSaleSuccessModal(saleData, changeAmount = 0) {
                                const changeText = (paymentMethod === 'cash' && changeAmount > 0) ? `<div style="color: #27ae60; font-weight: bold; margin-top: 1rem;">الباقي: $${changeAmount.toFixed(2)}</div>` : '';

                                const modal = document.createElement('div');
                                modal.id = 'sale-success-modal';
                                modal.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.5); z-index: 2000; display: flex;
                align-items: center; justify-content: center;
            `;

                                modal.innerHTML = `
                <div style="background: white; padding: 2rem; border-radius: 8px; text-align: center; max-width: 400px;">
                    <div style="font-size: 3rem; color: #27ae60; margin-bottom: 1rem;">✓</div>
                    <h3 style="margin-bottom: 1rem;">تم البيع بنجاح!</h3>
                    <div style="margin-bottom: 1rem;">رقم الإيصال: <strong>${saleData.receipt_number}</strong></div>
                    ${changeText}
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button onclick="window.open('/sales/${saleData.sale_id}/receipt', '_blank')"
                                style="flex: 1; padding: 10px; background: #3498db; color: white; border: none; border-radius: 4px;">
                            طباعة الإيصال
                        </button>
                        <button onclick="closeSaleModal(this)"
                                style="flex: 1; padding: 10px; background: #27ae60; color: white; border: none; border-radius: 4px;">
                            إغلاق
                        </button>
                    </div>
                </div>
            `;

                                document.body.appendChild(modal);
                            }

                            function closeSaleModal() {
                                // Remove modal by ID
                                const modal = document.getElementById('sale-success-modal');
                                if (modal) {
                                    modal.remove();
                                }

                                // Clear POS interface
                                newSale();
                            }

                            // Utility Functions
                            function newSale() {
                                clearCart();
                                clearCustomer();
                                setPaymentMethod('cash');
                                document.getElementById('paid-amount').value = '';
                                document.getElementById('sale-notes').value = '';
                                document.getElementById('barcode-input').value = '';
                                clearSearchResults();
                                document.getElementById('barcode-input').focus();
                            }

                            function showLoading() {
                                document.getElementById('loading-overlay').style.display = 'block';
                            }

                            function hideLoading() {
                                document.getElementById('loading-overlay').style.display = 'none';
                            }

                            function showMessage(message, type) {
                                const messageDiv = document.createElement('div');
                                messageDiv.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 1000;
        padding: 1rem 1.5rem; border-radius: 6px; font-weight: 600;
        background: ${type === 'success' ? '#d4edda' : '#f8d7da'};
        color: ${type === 'success' ? '#155724' : '#721c24'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
    `;
                                messageDiv.textContent = message;

                                document.body.appendChild(messageDiv);

                                setTimeout(() => {
                                    if (document.body.contains(messageDiv)) {
                                        messageDiv.remove();
                                    }
                                }, 3000);
                            }

                            function debounce(func, wait) {
                                let timeout;
                                return function executedFunction(...args) {
                                    const later = () => {
                                        clearTimeout(timeout);
                                        func(...args);
                                    };
                                    clearTimeout(timeout);
                                    timeout = setTimeout(later, wait);
                                };
                            }

                            // Initialize
                            document.addEventListener('DOMContentLoaded', function() {
                                setPaymentMethod('cash');
                                document.getElementById('barcode-input').focus();
                            });
    </script>

    <style>
        .hidden {
            display: none !important;
        }

        .cart-item input:focus {
            border-color: #1abc9c !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
@endpush
