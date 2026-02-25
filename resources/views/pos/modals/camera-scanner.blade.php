<!-- Camera Barcode Scanner Modal -->
<div id="cameraScannerModal" class="camera-modal-overlay">
    <div class="camera-modal-dialog">
        <div class="camera-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cameraScannerModalLabel">
                    <i class="fas fa-camera me-2"></i>
                    {{ __('messages.camera_barcode_scanner') }}
                </h5>
                
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div id="cameraStatus" class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('messages.camera_scanner_instructions') }}
                    </div>
                    
                    <!-- Camera Preview -->
                    <div id="cameraPreview" class="position-relative mb-3" style="display: block;">
                        <video id="cameraVideo" width="100%" height="300" autoplay muted playsinline style="border-radius: 8px; background: #000;"></video>
                        <div id="scanOverlay" class="position-absolute top-50 start-50 translate-middle">
                            <div class="scan-line"></div>
                            <div class="scan-corners">
                                <div class="corner top-left"></div>
                                <div class="corner top-right"></div>
                                <div class="corner bottom-left"></div>
                                <div class="corner bottom-right"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scan Result -->
                    <div id="scanResult" class="alert alert-success" style="display: none;">
                        <i class="fas fa-check-circle me-2"></i>
                        <span id="scanResultText"></span>
                    </div>
                    
                    <!-- Error Message -->
                    <div id="scanError" class="alert alert-danger" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <span id="scanErrorText"></span>
                    </div>
                    
                    <!-- Manual Barcode Input -->
                    <div class="mt-3">
                        <label for="manualBarcode" class="form-label">{{ __('messages.manual_barcode_entry') }}</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="manualBarcode" placeholder="{{ __('messages.enter_barcode_manually') }}">
                            <button class="btn btn-primary" type="button" id="processManualBarcode">
                                <i class="fas fa-search me-2"></i>
                                {{ __('messages.search') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="startCameraBtn">
                    <i class="fas fa-camera me-2"></i>
                    {{ __('messages.start_camera') }}
                </button>
                <button type="button" class="btn btn-warning" id="stopCameraBtn" style="display: none;">
                    <i class="fas fa-stop me-2"></i>
                    {{ __('messages.stop_camera') }}
                </button>
                <button type="button" class="btn btn-danger ms-2" id="closeCameraBtn">
                    <i class="fas fa-times me-2"></i>
                    {{ __('messages.close_camera') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Modal Overlay Styles */
.camera-modal-overlay {
  position: fixed;
  inset: 0;
  display: none;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 2000;
}
.camera-modal-overlay.show {
  display: flex;
}
.camera-modal-dialog {
  display: flex;
  justify-content: center;
  align-items: center;
}
.camera-modal-content {
  background: #fff;
  border-radius: .3rem;
  max-width: 90%;
  max-height: 90%;
  overflow: auto;
  padding: 1rem;
}

/* Camera Scanner Styles */
.scan-line {
    width: 200px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #ff6b9d, transparent);
    animation: scanLine 2s ease-in-out infinite;
}

.scan-corners {
    position: relative;
    width: 200px;
    height: 200px;
    border: 2px solid transparent;
}

.corner {
    position: absolute;
    width: 20px;
    height: 20px;
    border: 3px solid #ff6b9d;
}

.corner.top-left {
    top: -2px;
    left: -2px;
    border-right: none;
    border-bottom: none;
}

.corner.top-right {
    top: -2px;
    right: -2px;
    border-left: none;
    border-bottom: none;
}

.corner.bottom-left {
    bottom: -2px;
    left: -2px;
    border-right: none;
    border-top: none;
}

.corner.bottom-right {
    bottom: -2px;
    right: -2px;
    border-left: none;
    border-top: none;
}

@keyframes scanLine {
    0% { transform: translateY(-100px); opacity: 0; }
    50% { opacity: 1; }
    100% { transform: translateY(100px); opacity: 0; }
}

#cameraVideo {
    max-width: 100%;
    height: auto;
}
/* Constrain preview and Quagga streams */
#cameraPreview {
    width: 100%;
    max-width: 500px;
    height: 300px;
    margin: auto;
    overflow: hidden;
    position: relative;
}

#cameraPreview video,
#cameraPreview canvas {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    position: absolute !important;
    top: 0;
    left: 0;
}
</style>
    {{--







    if (false) {
        console.log('Camera scanner modal script loaded - DISABLED');
    
    const cameraScannerModal = new bootstrap.Modal(document.getElementById('cameraScannerModal'));
    const cameraVideo = document.getElementById('cameraVideo');
    const cameraPreview = document.getElementById('cameraPreview');
    const cameraStatus = document.getElementById('cameraStatus');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const stopCameraBtn = document.getElementById('stopCameraBtn');
    const scanResult = document.getElementById('scanResult');
    const scanError = document.getElementById('scanError');
    const manualBarcodeInput = document.getElementById('manualBarcode');
    const processManualBarcodeBtn = document.getElementById('processManualBarcode');
    
    // Debug: Check if buttons exist
    const useCameraBtn = document.getElementById('useCameraBtn');
    const lowStockBtn = document.getElementById('lowStockBtn');
    const syncOfflineBtn = document.getElementById('syncOfflineBtn');
    
    console.log('Button check:', {
        useCameraBtn: !!useCameraBtn,
        lowStockBtn: !!lowStockBtn,
        syncOfflineBtn: !!syncOfflineBtn
    });
    
    let currentStream = null;
    let isScanning = false;
    const codeReader = new ZXing.BrowserBarcodeReader();
    
    // Show camera scanner modal
    if (useCameraBtn) {
        useCameraBtn.addEventListener('click', function() {
            console.log('Use camera button clicked');
            cameraScannerModal.show();
        });
        console.log('Use camera button event listener attached');
    } else {
        console.error('useCameraBtn not found');
    }
    
    // Start camera
    startCameraBtn.addEventListener('click', async function() {
        try {
            await startCamera();
        } catch (error) {
            showError('{{ __("messages.camera_access_error") }}');
        }
    });
    
    // Stop camera
    stopCameraBtn.addEventListener('click', function() {
        stopCamera();
    });
    
    // Process manual barcode
    processManualBarcodeBtn.addEventListener('click', function() {
        const barcode = manualBarcodeInput.value.trim();
        if (barcode) {
            processBarcode(barcode);
        }
    });
    
    // Enter key for manual barcode
    manualBarcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            processManualBarcodeBtn.click();
        }
    });
    
    // Clean up when modal is closed
    document.getElementById('cameraScannerModal').addEventListener('hidden.bs.modal', function() {
        stopCamera();
        resetModal();
        // Remove lingering backdrop and modal-open class
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    });
    
    async function startCamera() {
        try {
            const constraints = {
                video: {
                    facingMode: 'environment', // Use back camera if available
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            };
            
            let stream;
            try {
                stream = await navigator.mediaDevices.getUserMedia(constraints);
            } catch (error) {
                console.warn('Preferred camera not available, falling back to default camera', error);
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
            }
            currentStream = stream;
            cameraVideo.srcObject = currentStream;
            await cameraVideo.play();
            
            cameraPreview.style.display = 'block';
            startCameraBtn.style.display = 'none';
            stopCameraBtn.style.display = 'inline-block';
            
            cameraStatus.innerHTML = `
                <i class="fas fa-camera me-2"></i>
                {{ __('messages.camera_active_scanning') }}
            `;
            cameraStatus.className = 'alert alert-success';
            
            // Start scanning simulation (replace with actual barcode scanning library)
            startBarcodeDetection();
            
        } catch (error) {
            console.error('Camera access error:', error);
            showError('{{ __("messages.camera_access_denied") }}');
        }
    }
    
    function stopCamera() {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
        try {
            codeReader.reset();
        } catch (e) {
            console.warn('Code reader reset error:', e);
        }
        
        cameraVideo.srcObject = null;
        cameraPreview.style.display = 'none';
        startCameraBtn.style.display = 'inline-block';
        stopCameraBtn.style.display = 'none';
        isScanning = false;
        
        cameraStatus.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            {{ __('messages.camera_scanner_instructions') }}
        `;
        cameraStatus.className = 'alert alert-info';
    }
    
    function startBarcodeDetection() {
        if (isScanning) return;
        isScanning = true;
        try {
            codeReader.decodeFromVideoDevice(null, 'cameraVideo', (result, err) => {
                if (result) {
                    processBarcode(result.getText ? result.getText() : result.text);
                    stopCamera();
                }
            });
        } catch(error) {
            console.error('Camera scan error:', error);
            showError('{{ __("messages.camera_access_error") }}');
        }
    }
    
    function processBarcode(barcode) {
        // Hide previous results
        scanError.style.display = 'none';
        scanResult.style.display = 'block';
        document.getElementById('scanResultText').textContent = `{{ __('messages.barcode_scanned') }}: ${barcode}`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        scanBarcode(barcode, csrfToken);
        setTimeout(() => {
            cameraScannerModal.hide();
        }, 1500);
    }
    
    function showError(message) {
        document.getElementById('scanErrorText').textContent = message;
        scanError.style.display = 'block';
        scanResult.style.display = 'none';
    }
    
    function resetModal() {
        scanResult.style.display = 'none';
        scanError.style.display = 'none';
        manualBarcodeInput.value = '';
        
        cameraStatus.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            {{ __('messages.camera_scanner_instructions') }}
        `;
        cameraStatus.className = 'alert alert-info';
    }
    
    // Check camera availability
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        startCameraBtn.disabled = true;
        startCameraBtn.innerHTML = '<i class="fas fa-times me-2"></i>{{ __("messages.camera_not_supported") }}';
    }
    
    // Low stock alerts button functionality
    if (lowStockBtn) {
        lowStockBtn.addEventListener('click', function() {
            console.log('Low stock button clicked');
            // This should be handled by the low-stock-alerts modal script
        });
        console.log('Low stock button event listener attached');
    } else {
        console.error('lowStockBtn not found');
    }
    
    // Sync offline button functionality
    if (syncOfflineBtn) {
        syncOfflineBtn.addEventListener('click', async function() {
            console.log('Sync offline button clicked');
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("messages.syncing") }}';
            btn.disabled = true;
            
            try {
                const response = await fetch('{{ route("pos.sync-offline") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('{{ __("messages.offline_sales_synced", ["count" => "' + result.synced_count + '"]) }}');
                } else {
                    alert('{{ __("messages.sync_error") }}');
                }
            } catch (error) {
                console.error('Sync error:', error);
                alert('{{ __("messages.sync_error") }}');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }
    // Close Camera button handling
    const closeCameraBtn = document.getElementById('closeCameraBtn');
    if (closeCameraBtn) {
        closeCameraBtn.addEventListener('click', function() {
            stopCamera();
            resetModal();
            cameraScannerModal.hide();
        });
    }
    }
    --}}
</script>
