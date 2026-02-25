/**
 * PharmaPilot POS System - Working Version
 */

// Global variables
(function(){ window.startCamera = function() { console.log('overridden startCamera'); }; window.stopCamera = function() { console.log('overridden stopCamera'); }; window.startBarcodeDetection = function() {}; window.processBarcode = function() {}; window.showError = function(message) { console.warn('overridden showError:', message); }; window.resetModal = function() {}; })();
let cart = [];
let selectedCustomer = null;
let currentPromotion = null;

// Helper functions for cart and UI
function showMessage(message, type = 'info') {
    // Basic alert-based messaging
    alert(message);
}
function showLoading(buttonId) {
    const btn = document.getElementById(buttonId);
    if (btn) btn.disabled = true;
}
function hideLoading(buttonId) {
    const btn = document.getElementById(buttonId);
    if (btn) btn.disabled = false;
}
function displayScanResult(product, success, suggestions = []) {
    const resultEl = document.getElementById('scanResult');
    if (!resultEl) return;
    resultEl.style.display = 'block';
    if (success && product) {
        resultEl.innerHTML = `
            <div class="alert alert-success">
                <strong>${product.name}</strong><br>
                Price: $${product.price.toFixed(2)}<br>
                Stock: ${product.quantity}
            </div>`;
    } else {
        if (suggestions.length) {
            resultEl.innerHTML = `<div class="alert alert-warning">Suggestions:<br>${suggestions.map(s => '- ' + s).join('<br>')}</div>`;
        } else {
            resultEl.innerHTML = `<div class="alert alert-danger">Product not found</div>`;
        }
    }
}
function addToCart(product) {
    // Enforce stock limits
    const existing = cart.find(item => item.id === product.id);
    if (existing) {
        if (existing.quantity < product.quantity) {
            existing.quantity++;
            showMessage(`Added another ${product.name} to cart`, 'success');
        } else {
            showMessage(`Cannot add more. Only ${product.quantity} in stock`, 'warning');
            return;
        }
    } else {
        // Add new item with quantity 1 and track stock
        cart.push({ id: product.id, name: product.name, price: product.price, quantity: 1, stock: product.quantity });
        showMessage(`Added ${product.name} to cart`, 'success');
    }
    updateCartDisplay();
}
function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartDisplay();
}
function updateCartDisplay() {
    const cartItemsEl = document.getElementById('cartItems');
    const countEl = document.getElementById('cartItemCount');
    if (countEl) countEl.textContent = cart.length;
    if (!cartItemsEl) return;
    if (cart.length === 0) {
        cartItemsEl.innerHTML = `
            <div class="empty-cart text-center text-muted py-4">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <p>Cart is empty</p>
            </div>`;
    } else {
        let html = '<ul class="list-group">';
        cart.forEach((item, idx) => {
            html += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small>${item.quantity} x $${item.price.toFixed(2)}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${idx})">
                        <i class="fas fa-trash"></i>
                    </button>
                </li>`;
        });
        html += '</ul>';
        cartItemsEl.innerHTML = html;
    }
    calculateSummary();
}
function calculateSummary() {
    const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const discount = 0;
    const tax = subtotal * 0.1;
    const total = subtotal - discount + tax;
    document.getElementById('subtotalAmount').textContent = subtotal.toFixed(2);
    document.getElementById('discountAmount').textContent = `-${discount.toFixed(2)}`;
    document.getElementById('taxAmount').textContent = tax.toFixed(2);
    document.getElementById('totalAmount').textContent = total.toFixed(2);
}
function initializeCart() {
    // Initialize cart display on load
    updateCartDisplay();
}

// Calculate totals via server
async function calculateTotals(csrfToken) {
    try {
        const items = cart.map(item => ({ product_id: item.id || item.product_id, quantity: item.quantity || 1 }));
        const response = await fetch('/pos/calculate-total', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                items,
                customer_id: selectedCustomer ? selectedCustomer.id : null,
                promotion_code: document.getElementById('promotionCode')?.value || null,
                loyalty_points_used: parseInt(document.getElementById('loyaltyPointsUse')?.value) || 0,
                quick_discount_type: document.getElementById('discountType')?.value,
                quick_discount_value: parseFloat(document.getElementById('discountValue')?.value) || 0
            })
        });
        const data = await response.json();
        if (data.success && data.calculation) {
            const c = data.calculation;
            document.getElementById('subtotalAmount').textContent = c.subtotal.toFixed(2);
            document.getElementById('discountAmount').textContent = `-${c.discount_amount.toFixed(2)}`;
            document.getElementById('loyaltyDiscountAmount').textContent = `-${c.loyalty_discount.toFixed(2)}`;
            document.getElementById('taxAmount').textContent = c.tax_amount.toFixed(2);
            document.getElementById('totalAmount').textContent = c.final_amount.toFixed(2);
            const pointsEl = document.getElementById('pointsToEarn');
            if (pointsEl) pointsEl.textContent = c.loyalty_points_earned;
        } else {
            showMessage(data.message || 'Failed to calculate totals', 'error');
        }
    } catch (error) {
        console.error('Calculate total error:', error);
        showMessage('Error calculating totals', 'error');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('POS System initializing...');
    initializePOS();
});

function initializePOS() {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        console.error('CSRF token not found');
        return;
    }
    
    // Initialize barcode scanner
    initializeBarcodeScanner(csrfToken);
    
    // Initialize product search
    initializeProductSearch(csrfToken);
    
    // Initialize cart functionality
    initializeCart();
    
    // Initialize payment processing
    initializePayment(csrfToken);
    
    // Initialize other buttons
    initializeButtons(csrfToken);
    initializeCustomerSelection();
    
    console.log('POS System initialized successfully');
}

function initializeBarcodeScanner(csrfToken) {
    const barcodeInput = document.getElementById('barcodeInput');
    const scanBtn = document.getElementById('scanBtn');
    
    if (!barcodeInput || !scanBtn) {
        console.log('Barcode scanner elements not found');
        return;
    }
    
    // Scan button click
    scanBtn.addEventListener('click', function() {
        const barcode = barcodeInput.value.trim();
        if (!barcode) {
            showMessage('Please enter a barcode', 'warning');
            return;
        }
        scanBarcode(barcode, csrfToken);
    });
    
    // Enter key on barcode input
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const barcode = barcodeInput.value.trim();
            if (barcode) {
                scanBarcode(barcode, csrfToken);
            }
        }
    });
}

async function scanBarcode(barcode, csrfToken) {
    let data = null;
    
    try {
        showLoading('scanBtn');
        
        const scanUrl = window.posScanUrl || '/pos/scan-barcode';
        const response = await fetch(scanUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ barcode: barcode })
        });
        
        data = await response.json();
        
        if (data.success && data.product) {
            displayScanResult(data.product, true);
            addToCart(data.product);
            document.getElementById('barcodeInput').value = '';
        } else {
            displayScanResult(null, false, data.suggestions || []);
        }
    } catch (error) {
        console.error('Barcode scan error:', error);
        showMessage('Error scanning barcode', 'error');
    } finally {
        hideLoading('scanBtn');
    }
    return data;
}

function initializeProductSearch(csrfToken) {
    const productSearch = document.getElementById('productSearch');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    
    if (!productSearch) {
        console.log('Product search elements not found');
        return;
    }
    
    let searchTimeout;
    
    // Search input with debounce
    productSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                searchProducts(query, csrfToken);
            }, 300);
        } else {
            clearSearchResults();
        }
    });
    
    // Clear search button
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            productSearch.value = '';
            clearSearchResults();
        });
    }
}

async function searchProducts(query, csrfToken) {
    try {
        const response = await fetch('/pos/search-products', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ query: query })
        });
        
        const data = await response.json();
        
        if (data.success) {
            displaySearchResults(data.products || []);
        } else {
            clearSearchResults();
        }
    } catch (error) {
        console.error('Product search error:', error);
        clearSearchResults();
    }
}

function displaySearchResults(products) {
    const searchResults = document.getElementById('searchResults');
    if (!searchResults) return;
    
    if (products.length === 0) {
        searchResults.innerHTML = '<div class="text-muted">No products found</div>';
        return;
    }
    
    let html = '<div class="list-group">';
    products.forEach(product => {
        html += `
            <div class="list-group-item list-group-item-action" onclick="addToCart(${JSON.stringify(product).replace(/"/g, '&quot;')})">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${product.name}</h6>
                        <small class="text-muted">${product.barcode || 'No barcode'}</small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary">$${parseFloat(product.price || 0).toFixed(2)}</span>
                        <br>
                        <small class="text-muted">Stock: ${product.quantity || 0}</small>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    searchResults.innerHTML = html;
}

function clearSearchResults() {
    const searchResults = document.getElementById('searchResults');
    if (searchResults) {
        searchResults.innerHTML = '';
    }
}

function displayScanResult(product, found, suggestions = []) {
    const scanResult = document.getElementById('scanResult');
    if (!scanResult) return;
    
    if (found && product) {
        scanResult.innerHTML = `
            <div class="alert alert-success">
                <strong>Product Found:</strong> ${product.name}
                <br>Price: $${parseFloat(product.price || 0).toFixed(2)}
                <br>Stock: ${product.quantity || 0}
            </div>
        `;
    } else {
        let html = '<div class="alert alert-warning">Product not found</div>';
        if (suggestions.length > 0) {
            html += '<div class="mt-2"><strong>Suggestions:</strong><ul>';
            suggestions.forEach(suggestion => {
                html += `<li>${suggestion.name} - $${parseFloat(suggestion.price || 0).toFixed(2)}</li>`;
            });
            html += '</ul></div>';
        }
        scanResult.innerHTML = html;
    }
    
    scanResult.style.display = 'block';
    setTimeout(() => {
        scanResult.style.display = 'none';
    }, 5000);
}

function initializeCart() {
    const clearCartBtn = document.getElementById('clearCartBtn');
    
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function() {
            if (confirm('Clear all items from cart?')) {
                clearCart();
            }
        });
    }
    
    updateCartDisplay();
}

function addToCart(product, quantity = 1) {
    if (!product || !product.id) {
        showMessage('Invalid product', 'error');
        return;
    }
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.product_id === product.id);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            product_id: product.id,
            name: product.name,
            price: parseFloat(product.price || 0),
            quantity: quantity,
            barcode: product.barcode || '',
            stock: product.quantity || 0
        });
    }
    
    updateCartDisplay();
    showMessage(`${product.name} added to cart`, 'success');
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.product_id !== productId);
    updateCartDisplay();
}

function updateCartQuantity(productId, quantity) {
    const item = cart.find(item => item.product_id === productId);
    if (item) {
        if (quantity <= 0) {
            removeFromCart(productId);
        } else {
            item.quantity = quantity;
            updateCartDisplay();
        }
    }
}

function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    const cartItemCount = document.getElementById('cartItemCount');
    
    if (!cartItems) return;
    
    if (cart.length === 0) {
        cartItems.innerHTML = `
            <div class="empty-cart text-center text-muted py-4">
                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                <p>Cart is empty</p>
            </div>
        `;
        if (cartItemCount) cartItemCount.textContent = '0';
    } else {
        let html = '';
        cart.forEach(item => {
            const total = item.price * item.quantity;
            html += `
                <div class="cart-item border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${item.name}</h6>
                            <small class="text-muted">$${item.price.toFixed(2)} each</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateCartQuantity(${item.product_id}, ${item.quantity - 1})">-</button>
                            <span class="mx-2">${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary" onclick="updateCartQuantity(${item.product_id}, ${item.quantity + 1})">+</button>
                            <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeFromCart(${item.product_id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-end">
                        <strong>$${total.toFixed(2)}</strong>
                    </div>
                </div>
            `;
        });
        
        cartItems.innerHTML = html;
        if (cartItemCount) cartItemCount.textContent = cart.length.toString();
    }
    
    updateOrderSummary();
}

async function updateOrderSummary() {
    const csrfToken = document.querySelector("meta[name='csrf-token']").getAttribute('content');
    await calculateTotals(csrfToken);
    const processPaymentBtn = document.getElementById('processPaymentBtn');
    if (processPaymentBtn) processPaymentBtn.disabled = cart.length === 0;
}

function clearCart() {
    cart = [];
    updateCartDisplay();
    showMessage('Cart cleared', 'info');
}

function initializePayment(csrfToken) {
    const processPaymentBtn = document.getElementById('processPaymentBtn');
    
    if (processPaymentBtn) {
        processPaymentBtn.addEventListener('click', function() {
            processPayment(csrfToken);
        });
    }
}

async function processPayment(csrfToken) {
    if (cart.length === 0) {
        showMessage('Cart is empty', 'warning');
        return;
    }
    
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
    
    try {
        showLoading('processPaymentBtn');
        
        const response = await fetch('/pos/process-sale', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                items: cart.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity
                })),
                payment_method: paymentMethod,
                customer_id: selectedCustomer?.id || null,
                sale_date: document.getElementById('saleDate')?.value || null
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Payment processed successfully!', 'success');
            clearCart();
            
            // Show receipt options if available
            if (data.receipt_url) {
                showReceiptOptions(data.receipt_url, data.receipt_number);
            }
        } else {
            showMessage(data.message || 'Payment failed', 'error');
        }
    } catch (error) {
        console.error('Payment error:', error);
        showMessage('Payment processing failed', 'error');
    } finally {
        hideLoading('processPaymentBtn');
    }
}

function initializeButtons(csrfToken) {
    // Clear cart button
    const clearCartBtn = document.getElementById('clearCartBtn');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', clearCart);
    }
    // Promotion code apply
    const applyPromoBtn = document.getElementById('applyPromoBtn');
    if (applyPromoBtn) {
        applyPromoBtn.addEventListener('click', () => calculateTotals(csrfToken));
    }
    // Quick discount apply
    const applyQuickDiscountBtn = document.getElementById('applyQuickDiscountBtn');
    if (applyQuickDiscountBtn) {
        applyQuickDiscountBtn.addEventListener('click', () => calculateTotals(csrfToken));
    }

    // Low stock alerts button
    const lowStockBtn = document.getElementById('lowStockBtn');
    if (lowStockBtn) {
        lowStockBtn.addEventListener('click', function() {
            showLowStockAlerts(csrfToken);
        });
    }
    
    // Sync offline button
    const syncOfflineBtn = document.getElementById('syncOfflineBtn');
    if (syncOfflineBtn) {
        syncOfflineBtn.addEventListener('click', function() {
            syncOfflineSales(csrfToken);
        });
    }
    
    
}

async function showLowStockAlerts(csrfToken) {
    try {
        const response = await fetch('/pos/low-stock-alerts', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayLowStockModal(data.alerts || []);
        } else {
            showMessage('Failed to load stock alerts', 'error');
        }
    } catch (error) {
        console.error('Low stock alerts error:', error);
        showMessage('Error loading stock alerts', 'error');
    }
}

function displayLowStockModal(alerts) {
    const contentEl = document.getElementById('lowStockContent');
    if (!contentEl) return;
    if (alerts.length === 0) {
        contentEl.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                <h5>No low stock items</h5>
                <p>All products are well stocked</p>
            </div>`;
    } else {
        let html = '<ul class="list-group">';
        alerts.forEach(item => {
            html += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>${item.name}</span>
                    <span><span class="badge bg-warning">${item.quantity}</span></span>
                </li>`;
        });
        html += '</ul>';
        contentEl.innerHTML = html;
    }
    const modal = new bootstrap.Modal(document.getElementById('lowStockModal'));
    modal.show();
    // This would open a modal with low stock alerts
    // For now, just show an alert
    if (alerts.length === 0) {
        showMessage('No low stock alerts', 'info');
    } else {
        let message = `Low Stock Alerts (${alerts.length} items):\n`;
        alerts.forEach(alert => {
            message += `- ${alert.name}: ${alert.quantity} remaining\n`;
        });
        alert(message);
    }
}

async function syncOfflineSales(csrfToken) {
    try {
        showLoading('syncOfflineBtn');
        
        const response = await fetch('/pos/sync-offline', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(`Synced ${data.synced_count || 0} offline sales`, 'success');
        } else {
            showMessage('Sync failed', 'error');
        }
    } catch (error) {
        console.error('Sync error:', error);
        showMessage('Sync failed', 'error');
    } finally {
        hideLoading('syncOfflineBtn');
    }
}

function openCameraScanner() {
    // This would open camera scanner modal
    showMessage('Camera scanner feature coming soon', 'info');
}

function showReceiptOptions(receiptUrl, receiptNumber) {
    const message = `Receipt ${receiptNumber} generated successfully!\n\nOptions:\n- Print receipt\n- Email receipt\n- View receipt`;
    alert(message);
    
    // Open receipt in new window
    if (receiptUrl) {
        window.open(receiptUrl, '_blank');
    }
}

// Utility functions
function showMessage(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}

function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.disabled = true;
        element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    }
}

function hideLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.disabled = false;
        // Restore original text based on element ID
        const originalTexts = {
            'scanBtn': '<i class="fas fa-search"></i> Scan',
            'processPaymentBtn': '<i class="fas fa-check me-2"></i> Process Payment',
            'syncOfflineBtn': '<i class="fas fa-sync"></i> Sync Offline'
        };
        element.innerHTML = originalTexts[elementId] || 'Submit';
    }
}

console.log('POS System script loaded successfully');

// Camera Scanner Integration
let zxingReader = null;

function ensureZXingReady(callback) {
    if (window.ZXing && window.ZXing.BrowserBarcodeReader) {
        if (!zxingReader) zxingReader = new ZXing.BrowserBarcodeReader();
        callback();
    } else {
        console.warn('ZXing not ready yet, retrying...');
        setTimeout(() => ensureZXingReady(callback), 300);
    }
}
function stopScanCamera() {
    // Stop Quagga if running
    if (window.Quagga) {
        try { Quagga.stop(); } catch (e) { console.warn('Quagga stop error:', e); }
    }
    // Stop all video streams on the page
    document.querySelectorAll('video').forEach(video => {
        if (video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
        try { video.pause(); video.load(); } catch (e) {}
    });
}
/* ===== CUSTOM CAMERA SCAN ===== */
let scanning = false;
let scanLoopId = null;
let barcodeDetector = null;

// Initialize native BarcodeDetector if available
if ('BarcodeDetector' in window) {
  try {
    barcodeDetector = new BarcodeDetector({ formats: ['qr_code','ean_13','ean_8','upc_e','upc_a','code_128','code_39','code_93'] });
  } catch (e) {
    console.warn('BarcodeDetector unsupported formats, will fallback to ZXing');
  }
} else {
  console.warn('BarcodeDetector API not available, will fallback to ZXing');
}

// Helper to hide the camera modal
function hideModal() {
  const modalEl = document.getElementById('cameraScannerModal');
  if (modalEl) modalEl.style.display = 'none';
  // Stop scanning loop
  scanning = false;
  if (scanLoopId) { cancelAnimationFrame(scanLoopId); scanLoopId = null; }
  // Stop camera stream
  stopScanCamera();
  // Restore scrollability
  document.body.style.overflow = '';
  document.documentElement.style.overflow = '';
  document.body.classList.remove('modal-open');
}

// Unified startScanCamera using QuaggaJS in cameraPreview container
async function startScanCamera() {
    const previewEl = document.getElementById('cameraPreview');
    if (!previewEl) return;
        // Use native BarcodeDetector if available
        if (barcodeDetector) {
            const videoEl = document.getElementById('cameraVideo');
            if (!videoEl) return;
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                videoEl.srcObject = stream;
                await videoEl.play();
            } catch (err) {
                console.error('Error accessing camera:', err);
                showMessage('Error accessing camera', 'error');
                return;
            }
            scanning = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            async function scanLoop() {
                if (!scanning) return;
                try {
                    const barcodes = await barcodeDetector.detect(videoEl);
                    if (barcodes.length > 0) {
                        const code = barcodes[0].rawValue;
                        if (!code || code.length < 6) {
                            console.log('Ignored short or invalid code:', code);
                            scanLoopId = requestAnimationFrame(scanLoop);
                            return;
                        }
                        const data = await scanBarcode(code, csrfToken);
                        if (data && data.success) {
                            scanning = false;
                            stopScanCamera();
                            hideModal();
                            return;
                        } else {
                            console.log('No product found:', data);
                            scanLoopId = requestAnimationFrame(scanLoop);
                            return;
                        }
                    }
                } catch (err) {
                    console.error('BarcodeDetector detection error:', err);
                }
                scanLoopId = requestAnimationFrame(scanLoop);
            }
            scanLoop();
            return;
        }
    // Remove existing video element to prevent duplicates
    const oldVideo = previewEl.querySelector('video');
    if (oldVideo) oldVideo.remove();
    // Ensure Quagga is available
    if (!window.Quagga) {
        console.error('QuaggaJS not loaded');
        showMessage('Barcode scanning not available', 'error');
        return;
    }
    // Update scanning status
    const cameraStatusEl = document.getElementById('cameraStatus');
    if (cameraStatusEl) {
        cameraStatusEl.innerHTML = '<i class="fas fa-search me-2"></i> Scanning for barcode...';
        cameraStatusEl.className = 'alert alert-success';
        console.log('startScanCamera: initializing Quagga');
    }
    Quagga.init({
        inputStream: {
            name: 'LiveStream',
            type: 'LiveStream',
            target: previewEl,
            constraints: {
                facingMode: 'environment',
                width: { ideal: 640 },
                height: { ideal: 480 }
            }
        },
        decoder: {
            readers: ['ean_reader', 'ean_8_reader', 'upc_reader', 'upc_e_reader', 'code_128_reader', 'code_39_reader', 'code_93_reader']
        },
        locate: true
    }, function(err) {
        console.log('Quagga init callback err:', err);
        if (err) {
            console.error('Quagga init error:', err);
            showMessage('Error initializing scanner', 'error');
            return;
        }
        Quagga.start();
        console.log('Quagga started');
        scanning = true;
        Quagga.onDetected(onQuaggaDetected);
    });
}

// Callback for QuaggaJS detection
// Callback for QuaggaJS detection
async function onQuaggaDetected(result) {
    // Unregister handler to prevent duplicate calls
    Quagga.offDetected(onQuaggaDetected);
    scanning = false;
    // Extract and trim code
    const code = result.codeResult && result.codeResult.code ? result.codeResult.code.trim() : '';
    console.log('onQuaggaDetected code:', code);
    // Ignore invalid or too-short codes
    if (!code || code.length < 6) {
        console.log('Ignored short or invalid code:', code);
        // Re-register for next detection
        Quagga.onDetected(onQuaggaDetected);
        scanning = true;
        return;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    try {
        const data = await scanBarcode(code, csrfToken);
        if (data && data.success && data.product) {
            stopScanCamera();
            hideModal();
        } else {
            console.log('No product found, continuing scan', data);
            Quagga.onDetected(onQuaggaDetected);
            scanning = true;
        }
    } catch (error) {
        console.error('Error in onQuaggaDetected:', error);
        Quagga.onDetected(onQuaggaDetected);
        scanning = true;
    }
}


/* ===== SIMPLE CAMERA MODAL HANDLER ===== */

/* ===== SIMPLE CAMERA MODAL HANDLER ===== */
document.addEventListener('DOMContentLoaded', ()=>{
    console.log('Simple camera modal handler loaded');
  const modalEl  = document.getElementById('cameraScannerModal');
  const preview  = document.getElementById('cameraPreview');
  const useBtn   = document.getElementById('useCameraBtn');
  const startBtn = document.getElementById('startCameraBtn');
  const stopBtn  = document.getElementById('stopCameraBtn');
  const closeBtn = document.getElementById('closeCameraBtn');

  function resetUI(){
    if (preview) preview.style.display = 'none';
    if (startBtn) startBtn.style.display = 'inline-block';
    if (stopBtn) stopBtn.style.display = 'none';
  }


  useBtn && useBtn.addEventListener('click', ()=>{
        console.log('useCameraBtn clicked (simple handler)');
    resetUI();

    modalEl.style.display = 'flex';
    // Prevent background scroll when modal open
    document.body.style.overflow = 'hidden';
    document.documentElement.style.overflow = 'hidden';
  });

  startBtn && startBtn.addEventListener('click', ()=>{
        console.log('startCameraBtn clicked (simple handler)');
    resetUI();
    preview && (preview.style.display = 'block');
    startBtn.style.display = 'none';
    stopBtn.style.display = 'inline-block';
    startScanCamera();
  });

  stopBtn && stopBtn.addEventListener('click', () => {
    hideModal();
    resetUI();
});

  closeBtn && closeBtn.addEventListener('click', () => {
    hideModal();
    resetUI();
});
});



/* ===== DISABLED LEGACY CAMERA MODAL CODE ===== */
/* OLD CAMERA MODAL CODE
{{ ... }}
const scannerModalEl = document.getElementById('cameraScannerModal');
const scannerModal = bootstrap.Modal.getOrCreateInstance(scannerModalEl);
if (scannerModalEl) {
    // Stop scanning when modal is about to hide
    scannerModalEl.addEventListener('hide.bs.modal', () => { stopScanCamera(); });
            // Automatically start scanning when modal opens
        const modalInstanceKey = 'cameraScannerModalInstance';
    // scannerModalEl.addEventListener('shown.bs.modal', startScanCamera);
    // Stop scanning and reset UI when modal closes
        scannerModalEl.addEventListener('hidden.bs.modal', () => { // on hide cleanup and dispose
        
        stopScanCamera();
    const preview = document.getElementById('cameraPreview');
        const startBtn = document.getElementById('startCameraBtn');
        const stopBtn = document.getElementById('stopCameraBtn');
        if (preview) preview.style.display = 'none';
        if (startBtn) startBtn.style.display = 'inline-block';
        if (stopBtn) stopBtn.style.display = 'none';
    // Remove lingering backdrop and modal-open class
    document.body.classList.remove('modal-open');
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        
    });
}
// Replace useCameraBtn to open modal
const useCameraBtnEl = document.getElementById('useCameraBtn');
console.log('pos-working useCameraBtnEl:', useCameraBtnEl);
if (useCameraBtnEl) useCameraBtnEl.addEventListener('click', () => {
    scannerModal.show();
});
// Bind start/stop buttons
const startBtnEl = document.getElementById('startCameraBtn');
if (startBtnEl) startBtnEl.addEventListener('click', startScanCamera);
const stopBtnEl = document.getElementById('stopCameraBtn');
if (stopBtnEl) stopBtnEl.addEventListener('click', stopScanCamera);
// Bind Close Camera button to stop the camera
const closeCameraBtnEl = document.getElementById('closeCameraBtn');
if (closeCameraBtnEl) closeCameraBtnEl.addEventListener('click', () => { scannerModal.hide(); });

*/

function openCameraScanner() {
    const modalEl = document.getElementById('cameraScannerModal');
    if (!modalEl) {
        showMessage('Camera scanner modal not found', 'error');
        return;
    }
    // Dispose any existing modal instance
    const existing = bootstrap.Modal.getInstance(modalEl);
    if (existing) existing.dispose();
    const modal = new bootstrap.Modal(modalEl, { backdrop: false });
    modal.show();
}

// Initialize customer selection for loyalty points
function initializeCustomerSelection() {
    const customerSelect = document.getElementById('customerSelect');
    if (!customerSelect) return;
    customerSelect.addEventListener('change', function() {
        const customerId = this.value;
        const customerInfoEl = document.getElementById('customerInfo');
        const pointsEl = document.getElementById('customerPoints');
        const loyaltyInput = document.getElementById('loyaltyPointsUse');
        if (customerId) {
            const points = parseInt(this.options[this.selectedIndex].dataset.points) || 0;
            selectedCustomer = { id: parseInt(customerId), loyalty_points: points };
            if (customerInfoEl) customerInfoEl.style.display = 'block';
            if (pointsEl) pointsEl.textContent = points;
            if (loyaltyInput) {
                loyaltyInput.disabled = false;
                loyaltyInput.max = points;
                loyaltyInput.value = 0;
                loyaltyInput.addEventListener('input', updateOrderSummary);
            }
        } else {
            selectedCustomer = null;
            if (customerInfoEl) customerInfoEl.style.display = 'none';
            if (loyaltyInput) {
                loyaltyInput.disabled = true;
                loyaltyInput.value = 0;
            }
        }
        updateOrderSummary();
    });
}
