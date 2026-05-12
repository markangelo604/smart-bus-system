// Utility Functions
const showToast = (message, type = 'info') => {
    const container = document.getElementById('toast-container') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
};

const createToastContainer = () => {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
};

const apiFetch = async (url, options = {}) => {
    try {
        const response = await fetch(`../api/${url}`, options);
        const text = await response.text();
        try {
            const data = JSON.parse(text);
            return data;
        } catch (e) {
            console.error("JSON Parse Error:", text);
            throw new Error("Invalid JSON response");
        }
    } catch (error) {
        showToast('Network error or server down', 'error');
        throw error;
    }
};

const checkSession = async (requiredRole = null) => {
    const res = await apiFetch('auth.php?action=session');
    if (!res.success) {
        window.location.href = '../index.html'; // Or relevant login page
        return null;
    }
    
    if (requiredRole && res.data.role !== requiredRole) {
        showToast('Unauthorized access', 'error');
        setTimeout(() => {
            window.location.href = `../${res.data.role}/dashboard.html`;
        }, 1000);
        return null;
    }
    
    const userNameEl = document.getElementById('user-name');
    if (userNameEl) userNameEl.textContent = res.data.name;
    
    return res.data;
};

const logout = async () => {
    await apiFetch('auth.php?action=logout', { method: 'POST' });
    window.location.href = '../index.html';
};

// Maps Loader
const loadGoogleMaps = async (callback) => {
    if (window.google && window.google.maps) {
        callback();
        return;
    }
    const res = await apiFetch('config.php?action=maps_key');
    if (res.success) {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${res.data}&libraries=places`;
        script.async = true;
        script.defer = true;
        script.onload = callback;
        document.head.appendChild(script);
    }
};

const formatStatus = (status) => {
    return status.toLowerCase().replace(' ', '-');
};

const setupSidebarActive = () => {
    const path = window.location.pathname.split('/').pop();
    document.querySelectorAll('.sidebar-nav a').forEach(link => {
        if (link.getAttribute('href') === path) {
            link.classList.add('active');
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    setupSidebarActive();
    const logoutBtn = document.getElementById('btn-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }
});
