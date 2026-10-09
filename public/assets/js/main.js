/**
 * Main JavaScript file - handles all client-side functionality
 */

// ==========================================
// DROPDOWN FUNCTIONALITY
// ==========================================
function toggleDropdown() {
    const dropdown = document.getElementById("profileDropdown");
    if (dropdown) {
        dropdown.classList.toggle("hidden");
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const container = event.target.closest('.profile-dropdown-container');
    if (!container) {
        const dropdown = document.getElementById("profileDropdown");
        if (dropdown && !dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
        }
    }
});

// ==========================================
// FORM VALIDATION
// ==========================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('border-red-500');
            isValid = false;
        } else {
            input.classList.remove('border-red-500');
        }
    });
    
    return isValid;
}

// ==========================================
// TOAST NOTIFICATION
// ==========================================
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast-message');
    if (!toast) return;
    
    const colors = {
        success: 'bg-green-600',
        error: 'bg-red-600',
        info: 'bg-blue-600'
    };
    
    toast.innerText = message;
    toast.className = `fixed top-5 left-1/2 transform -translate-x-1/2 -translate-y-10 ${colors[type] || colors.success} text-white px-6 py-3 rounded-lg shadow-lg border border-amber-400 opacity-0 transition-all duration-300 z-50`;
    
    setTimeout(() => {
        toast.classList.remove('opacity-0', '-translate-y-10');
        toast.classList.add('opacity-100', 'translate-y-0');
    }, 50);
    
    setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', '-translate-y-10');
    }, 3000);
}

// ==========================================
// AJAX HELPERS
// ==========================================
function ajaxRequest(url, data, method = 'POST') {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
    .then(response => response.json())
    .catch(error => {
        console.error('AJAX Error:', error);
        return { status: 'error', message: 'Connection error' };
    });
}

// ==========================================
// NOTIFICATION HANDLERS (AJAX)
// ==========================================
function handleAction(id, action) {
    const data = {
        ajax_action: action,
        action_id: id
    };
    
    ajaxRequest('', data)
        .then(response => {
            if (response.status === 'success') {
                showToast(response.message);
                
                if (action === 'mark_read') {
                    const statusEl = document.getElementById('status-text-' + id);
                    if (statusEl) {
                        statusEl.innerHTML = '<span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full border border-green-200"><i class="fas fa-check-circle mr-1"></i> Read</span>';
                    }
                    const btnWrap = document.getElementById('mark-read-btn-wrap-' + id);
                    if (btnWrap) btnWrap.innerHTML = '';
                } else if (action === 'delete') {
                    const box = document.getElementById('notif-box-' + id);
                    if (box) box.remove();
                    
                    const container = document.getElementById('notificationsContainer');
                    if (container && container.querySelectorAll('[id^="notif-box-"]').length === 0) {
                        location.reload();
                    }
                }
            } else {
                showToast('❌ ' + (response.message || 'Action failed!'), 'error');
            }
        });
}

// ==========================================
// LOADING STATE
// ==========================================
function showLoading(button) {
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Loading...';
    }
}

function hideLoading(button, originalText) {
    if (button) {
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

// ==========================================
// INITIALIZATION
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Quarter Management System loaded successfully');
});