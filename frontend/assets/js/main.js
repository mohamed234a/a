// Auto-Entrepreneur Tunisie - Main JavaScript

// Configuration
const CONFIG = {
    API_BASE_URL: 'http://localhost:8000/backend/api.php',
    STORAGE_KEYS: {
        TOKEN: 'ae_token',
        USER: 'ae_user'
    }
};

// API Client
class ApiClient {
    static async request(endpoint, options = {}) {
        const url = `${CONFIG.API_BASE_URL}?route=${endpoint}`;
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        // Add auth token if available
        const token = Auth.getToken();
        if (token) {
            defaultOptions.headers.Authorization = `Bearer ${token}`;
        }
        
        const config = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error?.message || data.message || 'Request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }
    
    static async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    }
    
    static async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    static async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    static async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
}

// Authentication
class Auth {
    static getToken() {
        return localStorage.getItem(CONFIG.STORAGE_KEYS.TOKEN);
    }
    
    static setToken(token) {
        localStorage.setItem(CONFIG.STORAGE_KEYS.TOKEN, token);
    }
    
    static removeToken() {
        localStorage.removeItem(CONFIG.STORAGE_KEYS.TOKEN);
    }
    
    static getUser() {
        const userStr = localStorage.getItem(CONFIG.STORAGE_KEYS.USER);
        return userStr ? JSON.parse(userStr) : null;
    }
    
    static setUser(user) {
        localStorage.setItem(CONFIG.STORAGE_KEYS.USER, JSON.stringify(user));
    }
    
    static removeUser() {
        localStorage.removeItem(CONFIG.STORAGE_KEYS.USER);
    }
    
    static isLoggedIn() {
        return !!this.getToken();
    }
    
    static logout() {
        this.removeToken();
        this.removeUser();
        window.location.href = '/frontend/index.html';
    }
    
    static async login(email, password) {
        const response = await ApiClient.post('api/auth/login', { email, password });
        
        if (response.success) {
            this.setToken(response.data.token);
            this.setUser(response.data.user);
            return response.data;
        }
        
        throw new Error(response.error?.message || 'Login failed');
    }
    
    static async register(userData) {
        const response = await ApiClient.post('api/auth/register', userData);
        
        if (response.success) {
            return response.data;
        }
        
        throw new Error(response.error?.message || 'Registration failed');
    }
}

// Services API
class ServicesAPI {
    static async getAll(filters = {}) {
        const params = new URLSearchParams(filters);
        const endpoint = `api/services${params.toString() ? '?' + params.toString() : ''}`;
        const response = await ApiClient.get(endpoint);
        return response.data;
    }
    
    static async getById(id) {
        const response = await ApiClient.get(`api/services/${id}`);
        return response.data;
    }
    
    static async create(serviceData) {
        const response = await ApiClient.post('api/services', serviceData);
        return response.data;
    }
    
    static async update(id, serviceData) {
        const response = await ApiClient.put(`api/services/${id}`, serviceData);
        return response.data;
    }
    
    static async delete(id) {
        const response = await ApiClient.delete(`api/services/${id}`);
        return response.data;
    }
    
    static async getFeatured(limit = 6) {
        const response = await ApiClient.get(`api/services/featured?limit=${limit}`);
        return response.data;
    }
}

// Categories API
class CategoriesAPI {
    static async getAll(withCount = false) {
        const endpoint = `api/categories${withCount ? '?with_count=true' : ''}`;
        const response = await ApiClient.get(endpoint);
        return response.data;
    }
    
    static async getById(id) {
        const response = await ApiClient.get(`api/categories/${id}`);
        return response.data;
    }
}

// Users API
class UsersAPI {
    static async getAutoentrepreneurs(filters = {}) {
        const params = new URLSearchParams(filters);
        const endpoint = `api/autoentrepreneurs${params.toString() ? '?' + params.toString() : ''}`;
        const response = await ApiClient.get(endpoint);
        return response.data;
    }
    
    static async getAutoentrepreneurById(id) {
        const response = await ApiClient.get(`api/autoentrepreneurs/${id}`);
        return response.data;
    }
    
    static async searchAutoentrepreneurs(query, filters = {}) {
        const params = new URLSearchParams({ q: query, ...filters });
        const response = await ApiClient.get(`api/autoentrepreneurs/search?${params.toString()}`);
        return response.data;
    }
    
    static async getCurrentUser() {
        const response = await ApiClient.get('api/users/me');
        return response.data;
    }
    
    static async updateProfile(profileData) {
        const response = await ApiClient.put('api/users/me', profileData);
        return response.data;
    }
}

// UI Utilities
class UI {
    static showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        // Insert at top of body
        document.body.insertBefore(alertDiv, document.body.firstChild);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }
    
    static showSuccess(message) {
        this.showAlert(message, 'success');
    }
    
    static showError(message) {
        this.showAlert(message, 'error');
    }
    
    static showWarning(message) {
        this.showAlert(message, 'warning');
    }
    
    static showLoading(element) {
        const spinner = document.createElement('div');
        spinner.className = 'spinner';
        spinner.id = 'loading-spinner';
        
        if (element) {
            element.appendChild(spinner);
        } else {
            document.body.appendChild(spinner);
        }
    }
    
    static hideLoading() {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            spinner.remove();
        }
    }
    
    static formatPrice(price) {
        return `${parseFloat(price).toFixed(2)} DT`;
    }
    
    static formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    
    static truncateText(text, maxLength = 100) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
}

// Form Validation
class Validator {
    static email(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    static required(value) {
        return value && value.toString().trim().length > 0;
    }
    
    static minLength(value, min) {
        return value && value.toString().length >= min;
    }
    
    static maxLength(value, max) {
        return value && value.toString().length <= max;
    }
    
    static numeric(value) {
        return !isNaN(value) && !isNaN(parseFloat(value));
    }
    
    static positive(value) {
        return this.numeric(value) && parseFloat(value) > 0;
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Update navigation based on auth status
    updateNavigation();
    
    // Add global event listeners
    addGlobalEventListeners();
});

function updateNavigation() {
    const isLoggedIn = Auth.isLoggedIn();
    const user = Auth.getUser();
    
    // Update nav items based on auth status
    const authLinks = document.querySelectorAll('[data-auth-required]');
    const guestLinks = document.querySelectorAll('[data-guest-only]');
    
    authLinks.forEach(link => {
        link.style.display = isLoggedIn ? 'block' : 'none';
    });
    
    guestLinks.forEach(link => {
        link.style.display = isLoggedIn ? 'none' : 'block';
    });
    
    // Update user info if logged in
    if (isLoggedIn && user) {
        const userNameElements = document.querySelectorAll('[data-user-name]');
        userNameElements.forEach(el => {
            el.textContent = `${user.first_name} ${user.last_name}`;
        });
    }
}

function addGlobalEventListeners() {
    // Logout buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-logout]')) {
            e.preventDefault();
            Auth.logout();
        }
    });
    
    // Form submissions
    document.addEventListener('submit', function(e) {
        if (e.target.matches('[data-form]')) {
            e.preventDefault();
            handleFormSubmission(e.target);
        }
    });
}

async function handleFormSubmission(form) {
    const formType = form.dataset.form;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    try {
        UI.showLoading(form);
        
        switch (formType) {
            case 'login':
                await handleLogin(data);
                break;
            case 'register':
                await handleRegister(data);
                break;
            case 'service-create':
                await handleServiceCreate(data);
                break;
            default:
                throw new Error('Unknown form type');
        }
    } catch (error) {
        UI.showError(error.message);
    } finally {
        UI.hideLoading();
    }
}

async function handleLogin(data) {
    const result = await Auth.login(data.email, data.password);
    UI.showSuccess('Connexion réussie!');
    
    // Redirect based on user type
    const user = result.user;
    if (user.user_type === 'autoentrepreneur') {
        window.location.href = '/frontend/dashboard.html';
    } else {
        window.location.href = '/frontend/index.html';
    }
}

async function handleRegister(data) {
    await Auth.register(data);
    UI.showSuccess('Inscription réussie! Vous pouvez maintenant vous connecter.');
    window.location.href = '/frontend/login.html';
}

async function handleServiceCreate(data) {
    // Process images if provided
    if (data.images) {
        data.images = data.images.split(',').map(url => url.trim()).filter(url => url);
    }
    
    // Process tags if provided
    if (data.tags) {
        data.tags = data.tags.split(',').map(tag => tag.trim()).filter(tag => tag);
    }
    
    const service = await ServicesAPI.create(data);
    UI.showSuccess('Service créé avec succès!');
    window.location.href = `/frontend/service.html?id=${service.id}`;
}
