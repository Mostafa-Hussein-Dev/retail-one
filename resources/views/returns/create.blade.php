@extends('layouts.app')

@section('title', 'إرجاع جديد')

@section('content')
<h1 style="margin-bottom: 2rem;">إرجاع جديد</h1>

<!-- Step Indicator -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 2rem;">
    <div id="step-1-indicator"
         style="flex: 1; padding: 1rem; background: #f39c12; color: white; border-radius: 6px; text-align: center; font-weight: 600;">
        1. البحث عن البيع
    </div>
    <div id="step-2-indicator"
         style="flex: 1; padding: 1rem; background: #ecf0f1; color: #7f8c8d; border-radius: 6px; text-align: center; font-weight: 600;">
        2. اختيار العناصر
    </div>
    <div id="step-3-indicator"
         style="flex: 1; padding: 1rem; background: #ecf0f1; color: #7f8c8d; border-radius: 6px; text-align: center; font-weight: 600;">
        3. التأكيد
    </div>
</div>

<!-- Step 1: Search Sale -->
<div id="step-1" class="step-content">
    <div class="card">
        <h4 style="margin-bottom: 1.5rem;">أدخل رقم الإيصال</h4>
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
            <input type="text"
                   id="receipt-search"
                   class="form-control"
                   placeholder="20260113-0001"
                   style="flex: 1; padding: 13px; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem;"
                   autofocus>
            <button onclick="searchSale()"
                    style="padding: 13px 30px; background-color: #f39c12; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                بحث
            </button>
        </div>
        <div id="search-result"></div>
    </div>
</div>

<!-- Step 2: Select Items (Initially Hidden) -->
<div id="step-2" class="step-content" style="display: none;">
    <div class="card" style="margin-bottom: 2rem;">
        <h4 style="margin-bottom: 1rem;">معلومات البيع</h4>
        <div id="sale-info" style="line-height: 2;"></div>
    </div>

    <div class="card">
        <h4 style="margin-bottom: 1.5rem;">اختر العناصر للإرجاع</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>المباع</th>
                    <th>المرتجع</th>
                    <th>المتاح</th>
                    <th>كمية الإرجاع</th>
                    <th>السعر</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody id="items-tbody"></tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: 700;">الإجمالي:</td>
                    <td id="grand-total" style="text-align: right; font-weight: 700; color: #e74c3c; font-size: 1.2rem;">$0.00</td>
                </tr>
            </tfoot>
        </table>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem;">
            <div style="display: flex; gap: 1rem;">
                <button onclick="backToStep1()"
                        style="padding: 13px 30px; background-color: transparent; color: #1abc9c; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                    رجوع
                </button>
                <button onclick="returnAllItems()"
                        style="padding: 13px 30px; background-color: #e74c3c; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                    إرجاع كل العناصر
                </button>
            </div>
            <button onclick="goToStep3()"
                    style="padding: 13px 30px; background-color: #f39c12; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                التالي
            </button>
        </div>
    </div>
</div>

<!-- Step 3: Confirm (Initially Hidden) -->
<div id="step-3" class="step-content" style="display: none;">
    <div class="card">
        <h4 style="margin-bottom: 1.5rem;">تأكيد الإرجاع</h4>

        <div id="return-summary" style="margin-bottom: 2rem;"></div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">سبب الإرجاع *</label>
            <select id="reason-select" class="form-control"
                    style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px; margin-bottom: 0.5rem;">
                <option value="">اختر السبب</option>
                <option value="منتج تالف">منتج تالف</option>
                <option value="منتج خاطئ">منتج خاطئ</option>
                <option value="تغيير رأي العميل">تغيير رأي العميل</option>
                <option value="منتج معيب">منتج معيب</option>
                <option value="منتج منتهي الصلاحية">منتج منتهي الصلاحية</option>
                <option value="أخرى">أخرى</option>
            </select>
            <textarea id="reason-text"
                      class="form-control"
                      placeholder="تفاصيل إضافية (اختياري)"
                      rows="3"
                      style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;"></textarea>
        </div>

        <div id="refund-warning" style="padding: 1rem; border-radius: 6px; margin-bottom: 2rem;"></div>

        <div style="display: flex; justify-content: space-between;">
            <button onclick="backToStep2()"
                    style="padding: 13px 30px; background-color: transparent; color: #1abc9c; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                رجوع
            </button>
            <button onclick="processReturn()"
                    style="padding: 13px 30px; background-color: #27ae60; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                معالجة الإرجاع
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentSale = null;
let selectedItems = [];

// Search for sale by receipt number
async function searchSale() {
    const receiptNumber = document.getElementById('receipt-search').value.trim();
    const searchButton = document.querySelector('button[onclick="searchSale()"]');
    const resultDiv = document.getElementById('search-result');

    console.log('Searching for receipt:', receiptNumber);

    if (!receiptNumber) {
        showError('الرجاء إدخال رقم الإيصال');
        return;
    }

    // Disable button and show loading
    searchButton.disabled = true;
    searchButton.textContent = 'جاري البحث...';
    resultDiv.innerHTML = '<div style="padding: 1rem; color: #7f8c8d;">جاري البحث...</div>';

    try {
        console.log('Sending request to:', '{{ route("returns.search-sale") }}');

        const response = await fetch('{{ route("returns.search-sale") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ receipt_number: receiptNumber })
        });

        console.log('Response status:', response.status);

        const data = await response.json();
        console.log('Response data:', data);

        if (!data.success) {
            showError(data.message);
            return;
        }

        currentSale = data.sale;
        displaySaleItems(data.items);
        goToStep2();

    } catch (error) {
        console.error('Search error:', error);
        showError('حدث خطأ أثناء البحث: ' + error.message);
    } finally {
        // Re-enable button
        searchButton.disabled = false;
        searchButton.textContent = 'بحث';
    }
}

// Show error message
function showError(message) {
    const errorDiv = document.getElementById('search-result');
    errorDiv.innerHTML = `
        <div style="padding: 1rem; background-color: #f8d7da; color: #721c24; border-radius: 6px; margin-top: 1rem; border-left: 4px solid #f5c6cb;">
            <strong>خطأ:</strong> ${message}
        </div>
    `;

    // Auto-hide error after 5 seconds
    setTimeout(() => {
        errorDiv.innerHTML = '';
    }, 5000);
}

// Display sale items for selection
function displaySaleItems(items) {
    const tbody = document.getElementById('items-tbody');
    tbody.innerHTML = '';

    items.forEach(item => {
        const row = `
            <tr>
                <td>${item.product_name}</td>
                <td>${item.quantity_sold}</td>
                <td>${item.quantity_returned}</td>
                <td style="font-weight: 600; color: #27ae60;">${item.quantity_available}</td>
                <td>
                    <input type="number"
                           class="form-control return-qty"
                           data-item='${JSON.stringify(item)}'
                           data-max="${item.quantity_available}"
                           data-sold="${item.quantity_sold}"
                           data-product="${item.product_name}"
                           min="0.01"
                           max="${item.quantity_available}"
                           step="0.01"
                           value="0"
                           style="width: 100px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"
                           onchange="validateQuantity(this)"
                           oninput="calculateTotals()">
                </td>
                <td>${parseFloat(item.unit_price).toFixed(2)}</td>
                <td class="item-total" style="font-weight: 600;">$0.00</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });

    displaySaleInfo();
    calculateTotals();
}

// Display sale information
function displaySaleInfo() {
    const info = `
        <p><strong>رقم البيع:</strong> ${currentSale.receipt_number}</p>
        <p><strong>التاريخ:</strong> ${currentSale.sale_date}</p>
        <p><strong>العميل:</strong> ${currentSale.customer_name}</p>
        <p><strong>الإجمالي:</strong> ${parseFloat(currentSale.total_amount).toFixed(2)}</p>
        <p><strong>المدفوع:</strong> ${parseFloat(currentSale.paid_amount).toFixed(2)}</p>
        <p><strong>الدين المتبقي:</strong> <span style="color: #e74c3c; font-weight: 600;">${parseFloat(currentSale.debt_amount).toFixed(2)}</span></p>
    `;
    document.getElementById('sale-info').innerHTML = info;
}

// Validate return quantity
function validateQuantity(input) {
    const maxQty = parseFloat(input.getAttribute('data-max'));
    const soldQty = parseFloat(input.getAttribute('data-sold'));
    const productName = input.getAttribute('data-product');
    const qty = parseFloat(input.value) || 0;

    // Check if quantity is negative or zero
    if (input.value !== '' && qty <= 0) {
        showAlertDialog({
            type: 'warning',
            title: 'تحذير',
            message: 'يجب أن تكون كمية الإرجاع أكبر من صفر'
        }).then(() => {
            input.value = maxQty > 0 ? 0.01 : 0;
            calculateTotals();
        });
        return;
    }

    // Check if quantity exceeds available
    if (qty > maxQty) {
        const alreadyReturned = soldQty - maxQty;
        showAlertDialog({
            type: 'warning',
            title: 'تحذير',
            message: `لا يمكن إرجاع ${qty} من ${productName}\nالحد الأقصى المتاح: ${maxQty}`
        }).then(() => {
            input.value = maxQty > 0 ? maxQty : 0;
            calculateTotals();
        });
        return;
    }

    // Still calculate totals after validation
    calculateTotals();
}

// Return all items with confirmation
async function returnAllItems() {
    const rows = document.querySelectorAll('.return-qty');
    let totalItems = 0;
    let totalAmount = 0;

    rows.forEach(input => {
        const item = JSON.parse(input.dataset.item);
        const maxQty = parseFloat(input.getAttribute('data-max'));
        totalItems += maxQty;
        totalAmount += maxQty * parseFloat(item.unit_price);
    });

    // Show detailed confirmation dialog
    const confirmed = await showConfirmDialog({
        type: 'warning',
        title: 'تأكيد إرجاع كامل المنتجات',
        message: `عدد الأصناف: ${rows.length}\nالكمية الإجمالية: ${totalItems}\nمبلغ الإرجاع: $${totalAmount.toFixed(2)}\n\nهل أنت متأكد من إرجاع جميع المنتجات؟\nلا يمكن التراجع عن هذا الإجراء.`,
        confirmText: 'تأكيد الإرجاع',
        cancelText: 'إلغاء'
    });

    if (!confirmed) {
        return;
    }

    // Fill all quantities to their maximum
    rows.forEach(input => {
        const maxQty = parseFloat(input.getAttribute('data-max'));
        input.value = maxQty > 0 ? maxQty : 0;
    });

    // Recalculate totals
    calculateTotals();

    // Show success message
    await showAlertDialog({
        type: 'success',
        title: 'نجاح',
        message: `تم تعبئة جميع الكميات المتاحة للإرجاع\n\nالإجمالي: $${totalAmount.toFixed(2)}\n\nاضغط "التالي" للمتابعة.`
    });
}

// Calculate totals in real-time
function calculateTotals() {
    let total = 0;
    const rows = document.querySelectorAll('.return-qty');

    rows.forEach(input => {
        const item = JSON.parse(input.dataset.item);
        const qty = parseFloat(input.value) || 0;
        const itemTotal = qty * parseFloat(item.unit_price);

        // Update row total
        const totalCell = input.closest('tr').querySelector('.item-total');
        totalCell.textContent = '$' + itemTotal.toFixed(2);

        total += itemTotal;
    });

    document.getElementById('grand-total').textContent = '$' + total.toFixed(2);
    return total;
}

// Navigate to step 2
function goToStep2() {
    document.getElementById('step-1').style.display = 'none';
    document.getElementById('step-2').style.display = 'block';
    updateStepIndicator(2);
}

// Navigate to step 3
async function goToStep3() {
    selectedItems = [];
    let total = 0;

    document.querySelectorAll('.return-qty').forEach(input => {
        const qty = parseFloat(input.value) || 0;
        if (qty > 0) {
            const item = JSON.parse(input.dataset.item);
            selectedItems.push({
                sale_item_id: item.sale_item_id,
                quantity: qty,
                product_name: item.product_name,
                unit_price: item.unit_price,
                total: qty * item.unit_price
            });
            total += qty * item.unit_price;
        }
    });

    if (selectedItems.length === 0) {
        await showAlertDialog({
            type: 'warning',
            title: 'تحذير',
            message: 'الرجاء اختيار عنصر واحد على الأقل'
        });
        return;
    }

    displayReturnSummary(total);

    document.getElementById('step-2').style.display = 'none';
    document.getElementById('step-3').style.display = 'block';
    updateStepIndicator(3);
}

// Display return summary
function displayReturnSummary(totalRefund) {
    const debtAmount = parseFloat(currentSale.debt_amount);

    let cashRefund, debtReduction, paymentMethod, warningBg, warningColor;

    if (currentSale.payment_method === 'cash') {
        cashRefund = totalRefund;
        debtReduction = 0;
        paymentMethod = 'استرداد نقدي';
        warningBg = '#d4edda';
        warningColor = '#155724';
    } else {
        if (totalRefund <= debtAmount) {
            cashRefund = 0;
            debtReduction = totalRefund;
            paymentMethod = 'تخفيض دين';
            warningBg = '#d1ecf1';
            warningColor = '#0c5460';
        } else {
            debtReduction = debtAmount;
            cashRefund = totalRefund - debtAmount;
            paymentMethod = 'مختلط (تخفيض دين + استرداد نقدي)';
            warningBg = '#fff3cd';
            warningColor = '#856404';
        }
    }

    const summary = `
        <h5 style="margin-bottom: 1rem;">العناصر المرتجعة:</h5>
        <ul style="line-height: 2; margin-bottom: 1.5rem;">
            ${selectedItems.map(item => `
                <li>${item.product_name} - الكمية: ${item.quantity} - المبلغ: $${item.total.toFixed(2)}</li>
            `).join('')}
        </ul>
        <hr style="border: none; border-top: 1px solid #dee2e6; margin: 1.5rem 0;">
        <p style="font-size: 1.1rem; margin-bottom: 0.5rem;"><strong>إجمالي الإرجاع:</strong> <span style="color: #e74c3c; font-size: 1.3rem; font-weight: 700;">$${totalRefund.toFixed(2)}</span></p>
        <p style="font-size: 1.1rem; margin-bottom: 0.5rem;"><strong>طريقة الاسترداد:</strong> ${paymentMethod}</p>
        <p style="font-size: 1.1rem; margin-bottom: 0.5rem;"><strong>الاسترداد النقدي:</strong> <span style="color: #27ae60; font-weight: 600;">$${cashRefund.toFixed(2)}</span></p>
        <p style="font-size: 1.1rem; margin-bottom: 0.5rem;"><strong>تخفيض الدين:</strong> <span style="color: #3498db; font-weight: 600;">$${debtReduction.toFixed(2)}</span></p>
    `;

    document.getElementById('return-summary').innerHTML = summary;

    // Set warning message
    let warning = '';
    if (cashRefund > 0) {
        warning = `<strong>⚠️ يجب استرداد مبلغ $${cashRefund.toFixed(2)} نقداً للعميل</strong>`;
    } else if (debtReduction > 0) {
        warning = `<strong>✓ سيتم تخفيض دين العميل بمبلغ $${debtReduction.toFixed(2)}</strong>`;
    }

    document.getElementById('refund-warning').innerHTML = `
        <div style="background-color: ${warningBg}; color: ${warningColor}; padding: 1rem; border-radius: 6px;">
            ${warning}
        </div>
    `;
}

// Process return
async function processReturn() {
    const reasonSelect = document.getElementById('reason-select').value;
    const reasonText = document.getElementById('reason-text').value;
    const reason = reasonSelect + (reasonText ? ' - ' + reasonText : '');

    if (!reasonSelect) {
        await showAlertDialog({
            type: 'warning',
            title: 'تحذير',
            message: 'الرجاء اختيار سبب الإرجاع'
        });
        return;
    }

    const data = {
        sale_id: currentSale.id,
        reason: reason,
        items: selectedItems.map(item => ({
            sale_item_id: item.sale_item_id,
            quantity: item.quantity
        }))
    };

    try {
        const response = await fetch('{{ route("returns.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        // Let browser follow redirects automatically
        if (response.redirected || response.status === 302 || response.status === 301) {
            window.location.href = response.url;
            return;
        }

        if (response.ok) {
            // Success - check if there's a redirect URL in the response
            try {
                const result = await response.json();
                if (result.redirect_url) {
                    window.location.href = result.redirect_url;
                } else {
                    // No redirect URL, go to returns index
                    window.location.href = '{{ route("returns.index") }}';
                }
            } catch (e) {
                // Not JSON response, redirect to returns index
                window.location.href = '{{ route("returns.index") }}';
            }
        } else {
            // Error response
            let errorMessage = 'حدث خطأ أثناء معالجة الإرجاع';
            try {
                const errorData = await response.json();
                if (errorData.message) {
                    errorMessage = errorData.message;
                } else if (errorData.error) {
                    errorMessage = errorData.error;
                }
            } catch (e) {
                console.error('Could not parse error response');
            }
            await showAlertDialog({
                type: 'error',
                title: 'خطأ',
                message: errorMessage
            });
        }

    } catch (error) {
        await showAlertDialog({
            type: 'error',
            title: 'خطأ',
            message: 'حدث خطأ أثناء معالجة الإرجاع: ' + error.message
        });
        console.error('Return processing error:', error);
    }
}

// Navigation functions
function backToStep1() {
    document.getElementById('step-2').style.display = 'none';
    document.getElementById('step-1').style.display = 'block';
    updateStepIndicator(1);
}

function backToStep2() {
    document.getElementById('step-3').style.display = 'none';
    document.getElementById('step-2').style.display = 'block';
    updateStepIndicator(2);
}

// Update step indicator
function updateStepIndicator(step) {
    for (let i = 1; i <= 3; i++) {
        const indicator = document.getElementById(`step-${i}-indicator`);
        if (i === step) {
            indicator.style.background = '#f39c12';
            indicator.style.color = 'white';
        } else if (i < step) {
            indicator.style.background = '#27ae60';
            indicator.style.color = 'white';
        } else {
            indicator.style.background = '#ecf0f1';
            indicator.style.color = '#7f8c8d';
        }
    }
}

// Enter key triggers search
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('receipt-search').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchSale();
        }
    });
});
</script>
@endpush
