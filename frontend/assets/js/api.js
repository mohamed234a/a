// Auto-Entrepreneur Tunisie - API Client

class API {
    static baseURL = 'http://localhost:8000/backend/api.php';
    
    static async request(endpoint, options = {}) {
        const url = `${this.baseURL}?route=${endpoint}`;
        
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };
        
        // Add auth token if available
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error?.message || data.message || 'Request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    static async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const fullEndpoint = queryString ? `${endpoint}&${queryString}` : endpoint;
        
        return this.request(fullEndpoint, {
            method: 'GET'
        });
    }
    
    static async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }
    
    static async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }
    
    static async delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }
    
    // Auth methods
    static async login(email, password) {
        const response = await this.post('api/auth/login', { email, password });
        
        if (response.success && response.data.token) {
            localStorage.setItem('auth_token', response.data.token);
            localStorage.setItem('user_data', JSON.stringify(response.data.user));
        }
        
        return response;
    }
    
    static async register(userData) {
        return this.post('api/auth/register', userData);
    }
    
    static logout() {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        window.location.href = 'index.html';
    }
    
    static isAuthenticated() {
        return !!localStorage.getItem('auth_token');
    }
    
    static getCurrentUser() {
        const userData = localStorage.getItem('user_data');
        return userData ? JSON.parse(userData) : null;
    }
    
    // Services methods
    static async getServices(filters = {}) {
        return this.get('api/services', filters);
    }
    
    static async getService(id) {
        return this.get(`api/services/${id}`);
    }
    
    static async createService(serviceData) {
        return this.post('api/services', serviceData);
    }
    
    static async updateService(id, serviceData) {
        return this.put(`api/services/${id}`, serviceData);
    }
    
    static async deleteService(id) {
        return this.delete(`api/services/${id}`);
    }
    
    static async getFeaturedServices(limit = 6) {
        return this.get('api/services/featured', { limit });
    }
    
    // Categories methods
    static async getCategories(withCount = false) {
        return this.get('api/categories', { with_count: withCount });
    }
    
    static async getCategory(id) {
        return this.get(`api/categories/${id}`);
    }
    
    // Users methods
    static async getAutoentrepreneurs(filters = {}) {
        return this.get('api/autoentrepreneurs', filters);
    }
    
    static async getAutoentrepreneur(id) {
        return this.get(`api/autoentrepreneurs/${id}`);
    }
    
    static async searchAutoentrepreneurs(query, filters = {}) {
        return this.get('api/autoentrepreneurs/search', { q: query, ...filters });
    }
    
    static async getCurrentUserProfile() {
        return this.get('api/users/me');
    }
    
    static async updateProfile(profileData) {
        return this.put('api/users/me', profileData);
    }
}

// Utility functions
class Utils {
    static showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }
    
    static showLoading(element) {
        element.innerHTML = `
            <div class="loading-state">
                <div class="spinner"></div>
                <p>Chargement...</p>
            </div>
        `;
    }
    
    static showError(element, message) {
        element.innerHTML = `
            <div class="error-state">
                <div class="error-icon">😞</div>
                <p>${message}</p>
                <button class="btn btn-primary" onclick="location.reload()">Réessayer</button>
            </div>
        `;
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
    
    static generateStars(rating, maxStars = 5) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;
        const emptyStars = maxStars - fullStars - (hasHalfStar ? 1 : 0);
        
        return '⭐'.repeat(fullStars) + 
               (hasHalfStar ? '⭐' : '') + 
               '☆'.repeat(emptyStars);
    }
    
    static debounce(func, wait) {
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
    
    static validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    static validatePhone(phone) {
        const re = /^[\+]?[0-9\s\-\(\)]{8,}$/;
        return re.test(phone);
    }
}

// Form validation helper
class FormValidator {
    constructor(form) {
        this.form = form;
        this.errors = {};
    }
    
    addRule(fieldName, validator, message) {
        if (!this.rules) this.rules = {};
        if (!this.rules[fieldName]) this.rules[fieldName] = [];
        
        this.rules[fieldName].push({ validator, message });
        return this;
    }
    
    validate() {
        this.errors = {};
        
        if (!this.rules) return true;
        
        for (const [fieldName, rules] of Object.entries(this.rules)) {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (!field) continue;
            
            const value = field.value.trim();
            
            for (const rule of rules) {
                if (!rule.validator(value, this.form)) {
                    this.errors[fieldName] = rule.message;
                    break;
                }
            }
        }
        
        this.displayErrors();
        return Object.keys(this.errors).length === 0;
    }
    
    displayErrors() {
        // Clear previous errors
        this.form.querySelectorAll('.field-error').forEach(el => el.remove());
        this.form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        
        // Display new errors
        for (const [fieldName, message] of Object.entries(this.errors)) {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.classList.add('error');
                
                const errorEl = document.createElement('div');
                errorEl.className = 'field-error';
                errorEl.textContent = message;
                
                field.parentNode.appendChild(errorEl);
            }
        }
    }
}

// Common validation rules
const ValidationRules = {
    required: (value) => value.length > 0,
    email: (value) => Utils.validateEmail(value),
    phone: (value) => Utils.validatePhone(value),
    minLength: (min) => (value) => value.length >= min,
    maxLength: (max) => (value) => value.length <= max,
    numeric: (value) => !isNaN(value) && !isNaN(parseFloat(value)),
    positive: (value) => parseFloat(value) > 0
};

// Export for use in other files
window.API = API;
window.Utils = Utils;
window.FormValidator = FormValidator;
window.ValidationRules = ValidationRules;
