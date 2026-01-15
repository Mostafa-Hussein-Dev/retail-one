@extends('layouts.app')

@section('title', 'شراء جديد')

@section('content')
    @csrf
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إنشاء شراء جديد</h1>
        <a href="{{ route('purchases.index') }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للقائمة
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Left Column: Purchase Form -->
        <div>
            <div class="card" style="margin-bottom: 2rem;">
                <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
                    <h3 style="margin: 0; color: #1abc9c;">معلومات الشراء</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">المورد <span style="color: #e74c3c;">*</span></label>
                        <select id="supplier-select" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                            <option value="">اختر المورد</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">طريقة الدفع <span style="color: #e74c3c;">*</span></label>
                        <select id="payment-method" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                            <option value="cash">نقدي (مدفوع بالكامل)</option>
                            <option value="debt">دين (غير مدفوع)</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">ملاحظات</label>
                        <textarea id="notes" rows="3" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;" placeholder="ملاحظات إضافية..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="card">
                <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
                    <h3 style="margin: 0; color: #1abc9c;">ملخص الشراء</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>عدد المنتجات:</span>
                        <strong id="items-count">0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>الإجمالي:</span>
                        <strong id="total-amount" style="color: #1abc9c; font-size: 1.3rem;">$0.00</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>طريقة الدفع:</span>
                        <strong id="payment-method-display">نقدي</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Add Products -->
        <div>
            <div class="card" style="margin-bottom: 2rem;">
                <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
                    <h3 style="margin: 0; color: #1abc9c;">إضافة منتجات</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">المنتج</label>
                        <select id="product-select" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                            <option value="">اختر المنتج</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        data-cost="{{ $product->cost_price }}"
                                        data-name="{{ $product->name }}">
                                    {{ $product->name }} ({{$product->cost_price}})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الكمية <span style="color: #e74c3c;">*</span></label>
                            <input type="number"
                                   id="quantity-input"
                                   min="0.01"
                                   step="0.01"
                                   value="1"
                                   style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">سعر الوحدة ($)</label>
                            <div id="unit-cost-display"
                                 style="width: 100%; padding: 13px; border: 1px solid #ddd; border-radius: 6px; background: #f8f9fa; color: #7f8c8d; font-weight: 600;">
                                $0.00
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الإجمالي:</label>
                            <div id="item-total" style="font-size: 1.2rem; font-weight: 600; color: #1abc9c;">$0.00</div>
                        </div>
                        <button type="button"
                                onclick="addItem()"
                                style="background-color: transparent; color: #27ae60; padding: 12px 50px; border: 2px solid #27ae60; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                                onmouseover="this.style.backgroundColor='rgba(39, 174, 96, 0.1)'"
                                onmouseout="this.style.backgroundColor='transparent'">
                            إضافة
                        </button>
                    </div>
                </div>
            </div>

            <!-- Items List -->
            <div class="card">
                <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
                    <h3 style="margin: 0; color: #1abc9c;">المنتجات المضافة ({{ count($products) }})</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <table class="table" style="margin-bottom: 0;">
                        <thead>
                        <tr>
                            <th>المنتج</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                            <th>الإجمالي</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="items-table">
                        </tbody>
                    </table>
                    <div id="no-items" style="text-align: center; padding: 2rem; color: #7f8c8d;">
                        لا توجد منتجات مضافة
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="button"
                    onclick="submitPurchase()"
                    style="width: 100%; background-color: transparent; color: #1abc9c; padding: 15px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1.1rem; transition: all 0.3s ease; margin-top: 2rem;"
                    onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                حفظ الشراء
            </button>
        </div>
    </div>

    <script>
        let items = [];
        let selectedProductCost = 0;

        // Update item total when quantity changes
        document.getElementById('quantity-input').addEventListener('input', updateItemTotal);

        // Update unit cost display when product is selected
        document.getElementById('product-select').addEventListener('change', function() {
            const select = this;
            const selectedOption = select.options[select.selectedIndex];
            const cost = parseFloat(selectedOption.dataset.cost) || 0;
            selectedProductCost = cost;
            document.getElementById('unit-cost-display').textContent = '$' + cost.toFixed(2);
            updateItemTotal();
        });

        // Update payment method display
        document.getElementById('payment-method').addEventListener('change', function() {
            const text = this.options[this.selectedIndex].text;
            document.getElementById('payment-method-display').textContent = text.split('(')[0].trim();
        });

        function updateItemTotal() {
            const quantity = parseFloat(document.getElementById('quantity-input').value) || 0;
            const total = quantity * selectedProductCost;
            document.getElementById('item-total').textContent = '$' + total.toFixed(2);
        }

        function addItem() {
            const productSelect = document.getElementById('product-select');
            const productId = productSelect.value;
            const productName = productSelect.options[productSelect.selectedIndex].dataset.name;
            const quantity = parseFloat(document.getElementById('quantity-input').value);

            if (!productId || !quantity || !selectedProductCost) {
                showAlertDialog({
                    type: 'warning',
                    title: 'تحذير',
                    message: 'يرجى ملء جميع الحقول المطلوبة'
                });
                return;
            }

            const totalCost = quantity * selectedProductCost;
            items.push({ product_id: parseInt(productId), product_name: productName, quantity, unit_cost: selectedProductCost, total_cost: totalCost });

            renderItems();
            updateSummary();

            // Reset form
            productSelect.value = '';
            document.getElementById('quantity-input').value = 1;
            document.getElementById('unit-cost-display').textContent = '$0.00';
            document.getElementById('item-total').textContent = '$0.00';
            selectedProductCost = 0;
        }

        function removeItem(index) {
            items.splice(index, 1);
            renderItems();
            updateSummary();
        }

        function renderItems() {
            const tbody = document.getElementById('items-table');
            const noItems = document.getElementById('no-items');

            if (items.length === 0) {
                tbody.innerHTML = '';
                noItems.style.display = 'block';
                return;
            }

            noItems.style.display = 'none';
            tbody.innerHTML = items.map((item, index) => `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${item.quantity}</td>
                    <td>$${item.unit_cost.toFixed(2)}</td>
                    <td>$${item.total_cost.toFixed(2)}</td>
                    <td>
                        <button type="button" onclick="removeItem(${index})" style="background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 1.2rem;">×</button>
                    </td>
                </tr>
            `).join('');
        }

        function updateSummary() {
            const total = items.reduce((sum, item) => sum + item.total_cost, 0);
            document.getElementById('items-count').textContent = items.length;
            document.getElementById('total-amount').textContent = '$' + total.toFixed(2);
        }

        async function submitPurchase() {
            const supplierId = document.getElementById('supplier-select').value;
            const paymentMethod = document.getElementById('payment-method').value;
            const notes = document.getElementById('notes').value;

            if (!supplierId) {
                await showAlertDialog({
                    type: 'warning',
                    title: 'تحذير',
                    message: 'يرجى اختيار المورد'
                });
                return;
            }

            if (items.length === 0) {
                await showAlertDialog({
                    type: 'warning',
                    title: 'تحذير',
                    message: 'يرجى إضافة منتج واحد على الأقل'
                });
                return;
            }

            const data = {
                supplier_id: parseInt(supplierId),
                payment_method: paymentMethod,
                notes: notes,
                items: items
            };

            fetch('/purchases', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(async response => {
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);

                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);

                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Expected JSON, got:', text);
                    await showAlertDialog({
                        type: 'error',
                        title: 'خطأ',
                        message: 'حدث خطأ: استجابة غير صالحة من الخادم'
                    });
                    return;
                }

                const data = await response.json();
                console.log('Response data:', data);

                if (!response.ok || data.error) {
                    await showAlertDialog({
                        type: 'error',
                        title: 'خطأ',
                        message: 'حدث خطأ: ' + (data.message || data.error || 'يرجى المحاولة مرة أخرى')
                    });
                    return;
                }

                // Success - redirect to purchase page
                console.log('Redirecting to:', '/purchases/' + data.id);
                window.location.href = '/purchases/' + data.id;
            })
            .catch(async error => {
                console.error('Error:', error);
                await showAlertDialog({
                    type: 'error',
                    title: 'خطأ',
                    message: 'حدث خطأ أثناء حفظ الشراء: ' + error.message
                });
            });
        }
    </script>

@endsection
