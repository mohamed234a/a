// Main JS for Plateforme Auto-Entrepreneurs Tunisie

// API Configuration
const API_BASE = '../backend/index.php?route=';

// API Client
class ApiClient {
  static async request(endpoint, options = {}) {
    const url = API_BASE + endpoint;
    const token = localStorage.getItem('jwt');

    const config = {
      headers: {
        'Content-Type': 'application/json',
        ...(token && { 'Authorization': `Bearer ${token}` })
      },
      ...options
    };

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || 'Request failed');
      }

      return data;
    } catch (error) {
      console.error('API Error:', error);
      throw error;
    }
  }

  // Auth methods
  static async login(email, password) {
    return this.request('api/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password })
    });
  }

  static async register(userData) {
    return this.request('api/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData)
    });
  }

  // User methods
  static async getCurrentUser() {
    return this.request('api/users/me');
  }

  static async updateProfile(profileData) {
    return this.request('api/users/me', {
      method: 'PUT',
      body: JSON.stringify(profileData)
    });
  }

  static async getAutoentrepreneurs() {
    return this.request('api/autoentrepreneurs');
  }

  static async getAutoentrepreneur(id) {
    return this.request(`api/autoentrepreneurs/${id}`);
  }

  // Service methods
  static async getServices(filters = {}) {
    const params = new URLSearchParams(filters);
    const endpoint = params.toString() ? `api/services&${params}` : 'api/services';
    return this.request(endpoint);
  }

  static async getService(id) {
    return this.request(`api/services/${id}`);
  }

  static async createService(serviceData) {
    return this.request('api/services', {
      method: 'POST',
      body: JSON.stringify(serviceData)
    });
  }

  static async updateService(id, serviceData) {
    return this.request(`api/services/${id}`, {
      method: 'PUT',
      body: JSON.stringify(serviceData)
    });
  }

  static async deleteService(id) {
    return this.request(`api/services/${id}`, {
      method: 'DELETE'
    });
  }

  // Review methods
  static async getServiceReviews(serviceId) {
    return this.request(`api/services/${serviceId}/reviews`);
  }

  static async createReview(reviewData) {
    return this.request('api/reviews', {
      method: 'POST',
      body: JSON.stringify(reviewData)
    });
  }

  // Dashboard
  static async getDashboard() {
    return this.request('api/dashboard');
  }
}

// Utility functions

function isLoggedIn() {
  return !!localStorage.getItem('jwt');
}

function getCurrentUser() {
  const userStr = localStorage.getItem('user');
  return userStr ? JSON.parse(userStr) : null;
}

function logout() {
  localStorage.removeItem('jwt');
  localStorage.removeItem('user');
  window.location.href = 'login.html';
}

function showError(message) {
  // Create or update error display
  let errorDiv = document.getElementById('error-message');
  if (!errorDiv) {
    errorDiv = document.createElement('div');
    errorDiv.id = 'error-message';
    errorDiv.style.cssText = 'background: #fee; color: #c00; padding: 1rem; margin: 1rem 0; border-radius: 4px; border: 1px solid #fcc;';
    document.body.insertBefore(errorDiv, document.body.firstChild);
  }
  errorDiv.textContent = message;
  errorDiv.style.display = 'block';

  // Auto-hide after 5 seconds
  setTimeout(() => {
    errorDiv.style.display = 'none';
  }, 5000);
}

function showSuccess(message) {
  // Create or update success display
  let successDiv = document.getElementById('success-message');
  if (!successDiv) {
    successDiv = document.createElement('div');
    successDiv.id = 'success-message';
    successDiv.style.cssText = 'background: #efe; color: #060; padding: 1rem; margin: 1rem 0; border-radius: 4px; border: 1px solid #cfc;';
    document.body.insertBefore(successDiv, document.body.firstChild);
  }
  successDiv.textContent = message;
  successDiv.style.display = 'block';

  // Auto-hide after 3 seconds
  setTimeout(() => {
    successDiv.style.display = 'none';
  }, 3000);
}
