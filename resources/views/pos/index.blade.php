@extends('layouts.app')

@section('title', 'نقطة البيع - POS')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
    <style>
        /* Ensure full width for POS page */
        body { margin: 0; padding: 0; overflow: hidden; }
        .container, .container-fluid { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        main { padding: 0 !important; }
    </style>
@endpush

@section('content')
    <div class="pos-container">
        <!-- LEFT COLUMN: Categories + Products -->
        <div class="pos-left">
            <!-- Categories Section -->
            <div class="categories-section">
                <h3 class="section-title">الفئات</h3>
                <div class="categories-grid" id="categories-grid">
                    <div class="category-card active" onclick="filterByCategory('all')">
                        <div class="category-icon">🛒</div>
                        <div class="category-name">الكل</div>
                    </div>
                    <!-- Categories will be loaded dynamically -->
                </div>
            </div>

            <!-- Products Section -->
            <div class="products-section">
                <div class="products-header">
                    <h3 class="section-title">المنتجات</h3>
                    <input type="text"
                           id="product-search"
                           class="search-input"
                           placeholder="🔍 بحث عن منتج...">
                </div>
                <div class="products-grid" id="products-grid">
                    <!-- Products will be loaded here -->
                    <div class="no-products">
                        <p>ابحث عن منتج أو اختر فئة</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- MIDDLE COLUMN: Payment Calculator -->
        <div class="pos-middle">
            <!-- Barcode Scanner -->
            <div class="barcode-section">
                <input type="text"
                       id="barcode-input"
                       class="barcode-input"
                       placeholder="📷 مسح الباركود..."
                       autocomplete="off">
            </div>

            <!-- Quick Amount Display -->
            <div class="amount-display">
                <div class="amount-label">المبلغ المطلوب</div>
                <div class="amount-value" id="cart-total-display">0.00 $</div>
                <div class="amount-lbp" id="cart-total-lbp">0 ل.ل.</div>
            </div>

            <!-- Payment Method Selection -->
            <div class="payment-method-section">
                <button class="payment-btn active" id="cash-method-btn" onclick="setPaymentMethod('cash')">
                    💵 نقدي
                </button>
                <button class="payment-btn" id="debt-method-btn" onclick="setPaymentMethod('debt')">
                    📋 دين
                </button>
            </div>

            <!-- Cash Payment Section -->
            <div id="cash-payment-section">
                <div class="paid-amount-section">
                    <label class="input-label">المبلغ المدفوع</label>
                    <input type="number"
                           id="paid-amount-input"
                           class="amount-input"
                           placeholder="0.00"
                           step="0.01"
                           min="0">
                </div>
                <div class="change-display" id="change-display" style="display: none;">
                    <div class="change-label">الباقي</div>
                    <div class="change-value" id="change-value">0.00 $</div>
                </div>
            </div>

            <!-- Debt Payment Section -->
            <div id="debt-payment-section" style="display: none;">
                <div class="customer-search-section">
                    <input type="text"
                           id="customer-search"
                           class="search-input"
                           placeholder="🔍 بحث عن عميل...">
                    <div id="customer-results" class="customer-results"></div>
                </div>
                <div id="selected-customer-info" style="display: none;">
                    <div class="customer-info-card">
                        <div class="customer-name" id="customer-name-display"></div>
                        <div class="customer-debt">
                            مديونية: <span id="customer-debt-display">0.00 $</span>
                        </div>
                        <button onclick="clearCustomer()" class="btn-clear">✕ إلغاء</button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn-action btn-process" id="process-sale-btn" onclick="processSale()" disabled>
                    ✓ إتمام البيع
                </button>
                <button class="btn-action btn-clear" onclick="newSale()">
                    🔄 بيع جديد
                </button>
            </div>
        </div>

        <!-- RIGHT COLUMN: Cart/Order -->
        <div class="pos-right">
            <!-- Header -->
            <div class="cart-header">
                <h3 class="section-title">السلة</h3>
                <button class="btn-clear-cart" onclick="clearCart()">
                    🗑️ مسح الكل
                </button>
            </div>

            <!-- Cart Items -->
            <div class="cart-items" id="cart-items">
                <div class="empty-cart">
                    <div class="empty-icon">🛒</div>
                    <p>السلة فارغة</p>
                    <p class="empty-subtitle">ابدأ بإضافة منتجات</p>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary" id="cart-summary" style="display: none;">
                <div class="summary-row">
                    <span>عدد الأصناف:</span>
                    <span id="items-count">0</span>
                </div>
                <div class="summary-row">
                    <span>المجموع الفرعي:</span>
                    <span id="subtotal">0.00 $</span>
                </div>
                <div class="summary-row discount-row" id="discount-row" style="display: none;">
                    <span>الخصم:</span>
                    <span id="total-discount" style="color: #e74c3c;">0.00 $</span>
                </div>
                <div class="summary-total">
                    <span>الإجمالي:</span>
                    <span id="total">0.00 $</span>
                </div>
                <div class="summary-lbp">
                    <span id="total-lbp">0</span> ل.ل.
                </div>
            </div>

            <!-- Notes Section -->
            <div class="notes-section">
            <textarea id="sale-notes"
                      class="notes-input"
                      placeholder="ملاحظات اختيارية..."
                      rows="2"></textarea>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">جار معالجة البيع...</div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="success-icon">✓</div>
            <h3>تم البيع بنجاح!</h3>
            <p class="receipt-number">رقم الإيصال: <span id="modal-receipt-number"></span></p>
            <div id="modal-change" style="display: none;">
                الباقي: <strong id="modal-change-amount">0.00 $</strong>
            </div>
            <div class="modal-actions">
                <button onclick="printReceipt()" class="btn-print">🖨️ طباعة</button>
                <button onclick="closeSuccessModal()" class="btn-close">إغلاق</button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}">
    <style>
        /* Ensure full width for POS page */
        body { margin: 0; padding: 0; overflow: hidden; }
        .container, .container-fluid { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        main { padding: 0 !important; }
    </style>
@endpush

@push('scripts')
    <script>
        let cart = [];
        let selectedCustomer = null;
        let paymentMethod = 'cash';
        let categories = [];
        let currentCategory = 'all';

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadCategories();
            loadCart();
            initializeEventListeners();
        });

        function initializeEventListeners() {
            // Barcode input
            const barcodeInput = document.getElementById('barcode-input');
            barcodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchByBarcode();
                }
            });

            // Product search
            const productSearch = document.getElementById('product-search');
            productSearch.addEventListener('input', debounce(searchProducts, 300));

            // Customer search
            const customerSearch = document.getElementById('customer-search');
            customerSearch.addEventListener('input', debounce(searchCustomers, 300));

            // Paid amount
            const paidAmount = document.getElementById('paid-amount-input');
            paidAmount.addEventListener('input', calculateChange);

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.key === 'F1') {
                    e.preventDefault();
                    newSale();
                } else if (e.key === 'F2') {
                    e.preventDefault();
                    document.getElementById('process-sale-btn').click();
                }
            });
        }

        // Load Categories
        function loadCategories() {
            fetch('/api/categories')
                .then(response => response.json())
                .then(data => {
                    categories = data;
                    displayCategories();
                });
        }

        function displayCategories() {
            const grid = document.getElementById('categories-grid');
            let html = `
            <div class="category-card active" onclick="filterByCategory('all')">
                <div class="category-icon">🛒</div>
                <div class="category-name">الكل</div>
            </div>
        `;

            const icons = ['🍎', '🥬', '🥛', '🍞', '🥩', '🐟', '🧃', '🍬'];

            categories.forEach((cat, index) => {
                html += `
                <div class="category-card" onclick="filterByCategory(${cat.id})">
                    <div class="category-icon">${icons[index % icons.length]}</div>
                    <div class="category-name">${cat.display_name}</div>
                </div>
            `;
            });

            grid.innerHTML = html;
        }

        function filterByCategory(categoryId) {
            currentCategory = categoryId;

            // Update active state
            document.querySelectorAll('.category-card').forEach(card => {
                card.classList.remove('active');
            });
            event.target.closest('.category-card').classList.add('active');

            // Load products for this category
            searchProducts();
        }

        // Search Products
        function searchProducts() {
            const search = document.getElementById('product-search').value;

            fetch('/pos/search-products', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    search: search || 'a',
                    category_id: currentCategory !== 'all' ? currentCategory : null
                })
            })
                .then(response => response.json())
                .then(products => {
                    displayProducts(products);
                });
        }

        function displayProducts(products) {
            const grid = document.getElementById('products-grid');

            if (products.length === 0) {
                grid.innerHTML = '<div class="no-products"><p>لا توجد منتجات</p></div>';
                return;
            }

            let html = '';
            products.forEach(product => {
                const stockClass = product.stock_status === 'out_of_stock' ? 'out-of-stock' :
                    product.stock_status === 'low_stock' ? 'low-stock' : '';

                html += `
                <div class="product-card ${stockClass}" onclick="addToCart(${product.id})">
                    ${product.image ?
                    `<img src="${product.image}" class="product-image" alt="${product.name}">` :
                    `<div class="product-image-placeholder">📦</div>`
                }
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="product-stock">${product.stock} ${product.unit}</div>
                    </div>
                    <div class="product-price">${product.price} $</div>
                </div>
            `;
            });

            grid.innerHTML = html;
        }

        // Search by Barcode
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
                        addToCart(data.product.id);
                        document.getElementById('barcode-input').value = '';
                    } else {
                        showMessage(data.message, 'error');
                    }
                });
        }

        // Cart Functions
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
                    quantity: quantity
                })
            })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        updateCartDisplay(data.cart);
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message, 'error');
                    }
                });
        }

        function updateCartDisplay(cartData) {
            cart = cartData.items;
            const cartItemsDiv = document.getElementById('cart-items');
            const cartSummary = document.getElementById('cart-summary');

            if (cart.length === 0) {
                cartItemsDiv.innerHTML = `
                <div class="empty-cart">
                    <div class="empty-icon">🛒</div>
                    <p>السلة فارغة</p>
                    <p class="empty-subtitle">ابدأ بإضافة منتجات</p>
                </div>
            `;
                cartSummary.style.display = 'none';
                updateProcessButton();
                return;
            }

            let html = '';
            cart.forEach((item, index) => {
                html += `
                <div class="cart-item">
                    <div class="cart-item-header">
                        <div class="cart-item-name">${item.product_name}</div>
                        <button onclick="removeFromCart(${index})" class="btn-remove">✕</button>
                    </div>

                    <div class="cart-item-details">
                        <div class="quantity-control">
                            <label>الكمية</label>
                            <input type="number"
                                   value="${item.quantity}"
                                   onchange="updateQuantity(${index}, this.value)"
                                   class="quantity-input"
                                   step="0.01"
                                   min="0.01">
                        </div>
                        <div class="item-price">
                            <label>السعر</label>
                            <div class="price-value">${parseFloat(item.unit_price).toFixed(2)} $</div>
                        </div>
                        <div class="item-total">
                            <label>المجموع</label>
                            <div class="total-value">${parseFloat(item.total_price).toFixed(2)} $</div>
                        </div>
                    </div>

                    <div class="discount-section">
                        <select class="discount-type" id="discount-type-${index}">
                            <option value="percentage">%</option>
                            <option value="amount">$</option>
                        </select>
                        <input type="number"
                               id="discount-value-${index}"
                               class="discount-input"
                               value="${item.discount_percentage > 0 ? item.discount_percentage : item.discount_amount}"
                               placeholder="خصم"
                               step="0.01"
                               min="0">
                        <button onclick="applyDiscount(${index})" class="btn-apply-discount">تطبيق</button>
                    </div>

                    ${item.discount_amount > 0 ? `
                        <div class="active-discount">
                            خصم: ${item.discount_percentage > 0 ? item.discount_percentage.toFixed(1) + '%' : item.discount_amount.toFixed(2) + ' $'}
                            <button onclick="removeDiscount(${index})">✕</button>
                        </div>
                    ` : ''}
                </div>
            `;
            });

            cartItemsDiv.innerHTML = html;

            // Update summary
            document.getElementById('items-count').textContent = cartData.items_count || 0;
            document.getElementById('subtotal').textContent = parseFloat(cartData.subtotal || 0).toFixed(2) + ' $';
            document.getElementById('total-discount').textContent = parseFloat(cartData.total_discount || 0).toFixed(2) + ' $';
            document.getElementById('total').textContent = parseFloat(cartData.total || 0).toFixed(2) + ' $';
            document.getElementById('total-lbp').textContent = (parseFloat(cartData.total || 0) * 89500).toLocaleString();

            // Update main display
            document.getElementById('cart-total-display').textContent = parseFloat(cartData.total || 0).toFixed(2) + ' $';
            document.getElementById('cart-total-lbp').textContent = (parseFloat(cartData.total || 0) * 89500).toLocaleString() + ' ل.ل.';

            // Show/hide discount row
            const discountRow = document.getElementById('discount-row');
            if (cartData.total_discount > 0) {
                discountRow.style.display = 'flex';
            } else {
                discountRow.style.display = 'none';
            }

            cartSummary.style.display = 'block';
            updateProcessButton();
            calculateChange();
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
                });
        }

        function applyDiscount(index) {
            const discountType = document.getElementById(`discount-type-${index}`).value;
            const discountValue = parseFloat(document.getElementById(`discount-value-${index}`).value) || 0;

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
                });
        }

        function removeDiscount(index) {
            document.getElementById(`discount-value-${index}`).value = 0;
            applyDiscount(index);
        }

        function removeFromCart(index) {
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
                    }
                });
        }

        async function clearCart() {
            const confirmed = await showConfirmDialog({
                type: 'warning',
                title: 'تأكيد المسح',
                message: 'هل تريد مسح جميع المنتجات من السلة؟',
                confirmText: 'نعم، امسح',
                cancelText: 'إلغاء'
            });

            if (!confirmed) return;

            fetch('/pos/clear-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    updateCartDisplay(data.cart);
                });
        }

        function loadCart() {
            fetch('/pos/get-cart')
                .then(response => response.json())
                .then(data => {
                    updateCartDisplay(data.cart);
                });
        }

        // Payment Methods
        function setPaymentMethod(method) {
            paymentMethod = method;

            document.getElementById('cash-method-btn').classList.toggle('active', method === 'cash');
            document.getElementById('debt-method-btn').classList.toggle('active', method === 'debt');

            document.getElementById('cash-payment-section').style.display = method === 'cash' ? 'block' : 'none';
            document.getElementById('debt-payment-section').style.display = method === 'debt' ? 'block' : 'none';

            updateProcessButton();
            if (method === 'cash') calculateChange();
        }

        function calculateChange() {
            if (paymentMethod !== 'cash') return;

            const total = parseFloat(document.getElementById('total').textContent) || 0;
            const paid = parseFloat(document.getElementById('paid-amount-input').value) || 0;
            const change = Math.max(0, paid - total);

            document.getElementById('change-value').textContent = change.toFixed(2) + ' $';
            document.getElementById('change-display').style.display = paid > 0 ? 'block' : 'none';

            updateProcessButton();
        }

        function updateProcessButton() {
            const button = document.getElementById('process-sale-btn');
            const total = parseFloat(document.getElementById('total').textContent) || 0;

            let canProcess = false;

            if (cart.length > 0) {
                if (paymentMethod === 'cash') {
                    const paid = parseFloat(document.getElementById('paid-amount-input').value) || 0;
                    canProcess = paid >= total;
                } else if (paymentMethod === 'debt') {
                    canProcess = selectedCustomer !== null;
                }
            }

            button.disabled = !canProcess;
            button.classList.toggle('disabled', !canProcess);
        }

        // Customer Search
        function searchCustomers() {
            const search = document.getElementById('customer-search').value.trim();
            if (search.length < 2) {
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
                .then(customers => {
                    displayCustomerResults(customers);
                });
        }

        function displayCustomerResults(customers) {
            const resultsDiv = document.getElementById('customer-results');

            if (customers.length === 0) {
                resultsDiv.style.display = 'none';
                return;
            }

            let html = '';
            customers.forEach(customer => {
                html += `
                <div class="customer-result" onclick='selectCustomer(${JSON.stringify(customer)})'>
                    <div class="customer-result-name">${customer.name}</div>
                    <div class="customer-result-info">
                        ${customer.phone ? customer.phone + ' • ' : ''}مديونية: ${customer.debt} $
                    </div>
                </div>
            `;
            });

            resultsDiv.innerHTML = html;
            resultsDiv.style.display = 'block';
        }

        function selectCustomer(customer) {
            selectedCustomer = customer;
            document.getElementById('customer-name-display').textContent = customer.name;
            document.getElementById('customer-debt-display').textContent = customer.debt + ' $';
            document.getElementById('selected-customer-info').style.display = 'block';
            document.getElementById('customer-results').style.display = 'none';
            document.getElementById('customer-search').value = '';
            updateProcessButton();
        }

        function clearCustomer() {
            selectedCustomer = null;
            document.getElementById('selected-customer-info').style.display = 'none';
            updateProcessButton();
        }

        // Process Sale
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
                const paid = parseFloat(document.getElementById('paid-amount-input').value) || 0;

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
                        showSuccessModal(data);
                    } else {
                        showMessage(data.message, 'error');
                    }
                });
        }

        function showSuccessModal(saleData) {
            const changeAmount = paymentMethod === 'cash' ?
                Math.max(0, parseFloat(document.getElementById('paid-amount-input').value) - parseFloat(document.getElementById('total').textContent)) : 0;

            document.getElementById('modal-receipt-number').textContent = saleData.receipt_number;

            if (changeAmount > 0) {
                document.getElementById('modal-change-amount').textContent = changeAmount.toFixed(2) + ' $';
                document.getElementById('modal-change').style.display = 'block';
            } else {
                document.getElementById('modal-change').style.display = 'none';
            }

            document.getElementById('success-modal').style.display = 'flex';

            window.currentSaleId = saleData.sale_id;
        }

        function closeSuccessModal() {
            document.getElementById('success-modal').style.display = 'none';
            newSale();
        }

        function printReceipt() {
            window.open(`/sales/${window.currentSaleId}/receipt`, '_blank');
        }

        function newSale() {
            clearCart();
            clearCustomer();
            setPaymentMethod('cash');
            document.getElementById('paid-amount-input').value = '';
            document.getElementById('sale-notes').value = '';
            document.getElementById('barcode-input').value = '';
            document.getElementById('barcode-input').focus();
        }

        // Utility Functions
        function showLoading() {
            document.getElementById('loading-overlay').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loading-overlay').style.display = 'none';
        }

        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `toast toast-${type}`;
            messageDiv.textContent = message;
            document.body.appendChild(messageDiv);

            setTimeout(() => {
                messageDiv.remove();
            }, 3000);
        }

        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    </script>
@endpush
