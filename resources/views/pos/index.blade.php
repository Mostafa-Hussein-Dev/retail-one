@extends('layouts.app')

@section('content')

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; height: calc(100vh - 150px);">

        <!-- Left Panel: Product Search & Cart -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">

            <!-- Product Search Section -->
            <div class="card">
                <h3 style="margin-bottom: 1rem;">بحث المنتجات</h3>

                <!-- Barcode Input -->
                <div style="display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1rem;">
                    <input type="text"
                           id="barcode-input"
                           placeholder="ادخل الباركود أو اسم المنتج"
                           style="padding: 12px; border: 2px solid #1abc9c; border-radius: 6px; font-size: 16px;"
                           autocomplete="off">
                    <button onclick="searchProducts()"
                            style="padding: 12px 24px; background: #3498db; color: white; border: none; border-radius: 6px; font-weight: 600;">
                        بحث
                    </button>
                </div>

                <!-- Search Results -->
                <div id="search-results" style="max-height: 200px; overflow-y: auto; display: none;"></div>
            </div>

            <!-- Shopping Cart -->
            <div class="card" style="flex: 1; display: flex; flex-direction: column;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3>سلة التسوق</h3>
                    <button onclick="clearCartWithConfirmation()"
                            style="padding: 6px 12px; background: #e74c3c; color: white; border: none; border-radius: 4px; font-size: 0.9rem;">
                        مسح السلة
                    </button>
                </div>

                <!-- Cart Items -->
                <div id="cart-items" style="flex: 1; overflow-y: auto; margin-bottom: 1rem;">
                    <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
                        <p>السلة فارغة</p>
                        <p style="font-size: 0.9rem;">ابدأ بإضافة منتجات</p>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div id="cart-summary" style="border-top: 2px solid #eee; padding-top: 1rem; display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <strong>عدد الأصناف:</strong> <span id="items-count">0</span>
                        </div>
                        <div>
                            <strong>إجمالي الكمية:</strong> <span id="total-quantity">0</span>
                        </div>
                    </div>

                    <div style="font-size: 1.1rem; margin-bottom: 0.5rem;">
                        <strong>المجموع الفرعي:</strong> $<span id="subtotal">0.00</span>
                    </div>

                    <div style="color: #e74c3c; margin-bottom: 0.5rem;" id="discount-row" class="hidden">
                        <strong>إجمالي الخصم:</strong> $<span id="total-discount">0.00</span>
                    </div>

                    <div style="font-size: 1.3rem; font-weight: bold; color: #2c3e50; border-top: 1px solid #ddd; padding-top: 0.5rem;">
                        <strong>الإجمالي:</strong> $<span id="total">0.00</span>
                    </div>

                    <div style="font-size: 0.9rem; color: #7f8c8d; margin-top: 0.5rem;">
                        <strong>بالليرة:</strong> LL <span id="total-lbp">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Payment & Customer -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">

            <!-- Customer Selection -->
            <div class="card">
                <h3 style="margin-bottom: 1rem;">العميل</h3>

                <div style="margin-bottom: 1rem;">
                    <input type="text"
                           id="customer-search"
                           placeholder="البحث عن عميل"
                           style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                </div>

                <div id="selected-customer" style="display: none; background: #e8f5e8; padding: 1rem; border-radius: 6px; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600;" id="customer-name"></div>
                            <div style="font-size: 0.9rem; color: #7f8c8d;" id="customer-phone"></div>
                        </div>
                        <button onclick="clearCustomer()"
                                style="padding: 4px 8px; background: #e74c3c; color: white; border: none; border-radius: 4px; font-size: 0.8rem;">
                            إلغاء
                        </button>
                    </div>
                    <div style="margin-top: 0.5rem; font-size: 0.9rem;">
                        <span>المديونية:</span> <strong style="color: #e74c3c;">$<span id="customer-debt">0.00</span></strong>
                    </div>
                </div>

                <div id="customer-results" style="max-height: 150px; overflow-y: auto; display: none;"></div>
            </div>

            <!-- Payment Method -->
            <div class="card">
                <h3 style="margin-bottom: 1rem;">طريقة الدفع</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <button id="cash-btn" onclick="setPaymentMethod('cash')"
                            style="padding: 12px; background: #27ae60; color: white; border: none; border-radius: 6px; font-weight: 600;">
                        نقدي
                    </button>
                    <button id="debt-btn" onclick="setPaymentMethod('debt')"
                            style="padding: 12px; background: #95a5a6; color: white; border: none; border-radius: 6px; font-weight: 600;">
                        دين
                    </button>
                </div>

                <!-- Cash Payment Section -->
                <div id="cash-payment" style="display: none;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">المبلغ المدفوع (للحساب فقط)</label>
                    <input type="number"
                           id="paid-amount"
                           placeholder="0.00"
                           step="0.01"
                           min="0"
                           style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 1rem;">
                    <div style="font-size: 0.8rem; color: #7f8c8d; margin-bottom: 1rem;">
                        ملاحظة: المبلغ المدفوع للعرض فقط. النظام يعتبر النقدية مدفوعة كاملة.
                    </div>

                    <div id="change-amount" style="background: #d5dbdb; padding: 0.75rem; border-radius: 6px; text-align: center; display: none;">
                        <strong>الباقي: $<span id="change-value">0.00</span></strong>
                    </div>
                </div>

                <!-- Debt Payment Section -->
                <div id="debt-payment" style="display: none;">
                    <div style="background: #fff3cd; padding: 1rem; border-radius: 6px; border-left: 4px solid #f39c12;">
                        <strong>ملاحظة:</strong> يجب اختيار عميل للدفع بالدين
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">ملاحظات</label>
                <textarea id="sale-notes"
                          rows="3"
                          placeholder="ملاحظات اختيارية..."
                          style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; resize: vertical;"></textarea>
            </div>

            <!-- Process Sale Button -->
            <div class="card" style="text-align: center;">
                <!-- Auto Print Toggle -->
                <div style="margin-bottom: 1rem;">
                    <button id="auto-print-btn" onclick="toggleAutoPrint()"
                            style="width: 100%; padding: 12px; background: #e74c3c; color: white; border: none; border-radius: 6px; font-weight: 600; margin-bottom: 0.5rem;">
                        طباعة تلقائية: متوقفة
                    </button>
                    <div style="font-size: 0.8rem; color: #7f8c8d;">
                        طباعة الإيصال تلقائياً بعد البيع
                    </div>
                </div>

                <button id="process-sale-btn" onclick="processSale()"
                        disabled
                        style="width: 100%; padding: 15px; font-size: 1.1rem; font-weight: 700; border: none; border-radius: 8px; background: #95a5a6; color: white; cursor: not-allowed;">
                    إتمام البيع
                </button>

                <div style="margin-top: 1rem; font-size: 0.9rem; color: #7f8c8d;">
                    الاختصارات: F1 بيع جديد • F2 دفع • F3 مسح السلة
                </div>
            </div>
        </div>
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

            if (autoPrintEnabled) {
                button.style.background = '#27ae60';
                button.textContent = 'طباعة تلقائية: مفعلة';
            } else {
                button.style.background = '#e74c3c';
                button.textContent = 'طباعة تلقائية: متوقفة';
            }
        }

        function displaySearchResults(products) {
            const resultsDiv = document.getElementById('search-results');

            if (products.length === 0) {
                resultsDiv.style.display = 'none';
                return;
            }

            let html = '<div style="border: 1px solid #ddd; border-radius: 6px; background: white; max-height: 200px; overflow-y: auto;">';

            products.forEach(product => {
                html += `
            <div onclick="addToCart(${product.id}, 1)"
                 style="padding: 1rem; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.2s;"
                 onmouseover="this.style.background='#f8f9fa'"
                 onmouseout="this.style.background='white'">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600;">${product.name}</div>
                        <div style="font-size: 0.9rem; color: #7f8c8d;">
                            ${product.barcode ? 'الباركود: ' + product.barcode + ' • ' : ''}
                            المخزون: ${product.stock} ${product.unit}
                        </div>
                    </div>
                    <div style="text-align: left;">
                        <div style="font-weight: 600; color: ${product.stock_color};">$${product.price}</div>
                        <div style="font-size: 0.8rem; background: ${product.stock_color}; color: white; padding: 2px 6px; border-radius: 3px; margin-top: 2px;">
                            ${getStockStatusText(product.stock_status)}
                        </div>
                    </div>
                </div>
            </div>
        `;
            });

            html += '</div>';
            resultsDiv.innerHTML = html;
            resultsDiv.style.display = 'block';
        }

        function clearSearchResults() {
            document.getElementById('search-results').style.display = 'none';
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
        function addToCart(productId, quantity = 1, customPrice = null) {
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
                    price: customPrice
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

        function updateCartItem(index, field, value) {
            const endpoint = field === 'quantity' ? '/pos/update-cart-item' : '/pos/update-cart-price';
            const payload = { index: index };
            payload[field] = value;

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateCartDisplay(data.cart);
                    } else {
                        showMessage(data.message, 'error');
                        loadCart(); // Reload cart to reset values
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    loadCart();
                });
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
            <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🛒</div>
                <p>السلة فارغة</p>
                <p style="font-size: 0.9rem;">ابدأ بإضافة منتجات</p>
            </div>
        `;
                cartSummary.style.display = 'none';
                updateProcessButton();
                return;
            }

            let html = '';
            cart.forEach((item, index) => {
                html += `
            <div class="cart-item" style="border: 1px solid #eee; border-radius: 6px; padding: 1rem; margin-bottom: 0.5rem; background: white;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                    <div style="flex: 1;">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">${item.product_name}</div>
                        ${item.product_barcode ? `<div style="font-size: 0.8rem; color: #7f8c8d;">كود: ${item.product_barcode}</div>` : ''}
                    </div>
                    <button onclick="removeFromCart(${index})"
                            style="background: #e74c3c; color: white; border: none; border-radius: 4px; padding: 4px 8px; font-size: 0.8rem;">
                        حذف
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div>
                        <label style="font-size: 0.8rem; color: #7f8c8d;">الكمية</label>
                        <input type="number"
                               value="${item.quantity}"
                               onchange="updateCartItem(${index}, 'quantity', this.value)"
                               step="0.01"
                               min="0.01"
                               style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; color: #7f8c8d;">السعر</label>
                        <input type="number"
                               value="${item.unit_price}"
                               onchange="updateCartItem(${index}, 'price', this.value)"
                               step="0.01"
                               min="0"
                               style="width: 100%; padding: 4px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;">
                    </div>
                    <div>
                        <label style="font-size: 0.8rem; color: #7f8c8d;">المجموع</label>
                        <div style="padding: 4px; font-weight: 600; font-size: 0.9rem;">$${parseFloat(item.total_price).toFixed(2)}</div>
                    </div>
                </div>

                ${item.price_modified || item.discount_amount > 0 ? `
                    <div style="font-size: 0.8rem; color: #f39c12; background: #fff3cd; padding: 0.25rem 0.5rem; border-radius: 4px;">
                        ${item.price_modified ? 'سعر معدل' : ''}
                        ${item.discount_amount > 0 ? `خصم: $${parseFloat(item.discount_amount).toFixed(2)}` : ''}
                    </div>
                ` : ''}
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

            // Recalculate change when cart updates
            calculateChange();
        }

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

            if (customers.length === 0) {
                resultsDiv.style.display = 'none';
                return;
            }

            let html = '<div style="border: 1px solid #ddd; border-radius: 6px; background: white;">';

            customers.forEach(customer => {
                html += `
            <div onclick="selectCustomer(${customer.id}, '${customer.name}', '${customer.phone || ''}', ${customer.debt})"
                 style="padding: 0.75rem; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.2s;"
                 onmouseover="this.style.background='#f8f9fa'"
                 onmouseout="this.style.background='white'">
                <div style="font-weight: 600;">${customer.name}</div>
                <div style="font-size: 0.9rem; color: #7f8c8d;">
                    ${customer.phone ? 'هاتف: ' + customer.phone + ' • ' : ''}
                    مديونية: $${customer.debt}
                </div>
            </div>
        `;
            });

            html += '</div>';
            resultsDiv.innerHTML = html;
            resultsDiv.style.display = 'block';
        }

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

            // Update button styles
            document.getElementById('cash-btn').style.background = method === 'cash' ? '#27ae60' : '#95a5a6';
            document.getElementById('debt-btn').style.background = method === 'debt' ? '#27ae60' : '#95a5a6';

            // Show/hide payment sections
            document.getElementById('cash-payment').style.display = method === 'cash' ? 'block' : 'none';
            document.getElementById('debt-payment').style.display = method === 'debt' ? 'block' : 'none';

            updateProcessButton();

            if (method === 'cash') {
                calculateChange();
            }
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
            const total = parseFloat(document.getElementById('total').textContent) || 0;

            let canProcess = false;

            if (cart.length > 0) {
                if (paymentMethod === 'cash') {
                    // For cash, we don't need to validate paid amount - it's always considered paid in full
                    canProcess = true;
                } else if (paymentMethod === 'debt') {
                    canProcess = selectedCustomer !== null;
                }
            }

            if (canProcess) {
                button.disabled = false;
                button.style.background = '#1abc9c';
                button.style.cursor = 'pointer';
            } else {
                button.disabled = true;
                button.style.background = '#95a5a6';
                button.style.cursor = 'not-allowed';
            }
        }

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
