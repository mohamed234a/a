# Plateforme Auto-Entrepreneurs Tunisie

A fully functional freelancing platform for Tunisian auto-entrepreneurs to offer services directly to clients.

## 🚀 Features

- **User Authentication**: Registration and login for auto-entrepreneurs, clients, and admins
- **Service Management**: Create, view, update, and delete services
- **Profile Management**: Auto-entrepreneur profiles with portfolio, experience, and contact info
- **Review System**: Clients can leave reviews and ratings for services
- **Dashboard**: User statistics and service management
- **Admin Panel**: User and service moderation
- **Responsive Design**: Works on desktop, tablet, and mobile

## 🛠️ Setup Instructions

### Prerequisites
- PHP 8.4+ with MySQL extension
- MySQL/MariaDB server
- Web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd "Autoentre (Copie)"
   ```

2. **Set up the database**
   ```bash
   # Create database user
   sudo mysql -u root -e "CREATE USER IF NOT EXISTS 'autoentre_user'@'localhost' IDENTIFIED BY 'autoentre_pass';"
   sudo mysql -u root -e "GRANT ALL PRIVILEGES ON autoentre_db.* TO 'autoentre_user'@'localhost';"
   sudo mysql -u root -e "FLUSH PRIVILEGES;"
   
   # Import database schema
   sudo mysql -u root autoentre_db < backend/database.sql
   ```

3. **Configure database connection**
   - Database configuration is already set in `backend/config/config.php`
   - Default credentials: `autoentre_user` / `autoentre_pass`

4. **Start the development server**
   ```bash
   php -S localhost:8000
   ```

5. **Access the application**
   - Frontend: http://localhost:8000/frontend/index.html
   - Backend API: http://localhost:8000/backend/index.php

## 👥 Test Users

The application comes with pre-configured test users:

- **Auto-entrepreneur**: `ae1@email.com` / `password123`
- **Client**: `client1@email.com` / `password123`
- **Admin**: `admin@email.com` / `password123`

## 🗃️ Database Schema

- **users**: All users (auto-entrepreneur, client, admin)
- **autoentrepreneur_profiles**: Extended info for auto-entrepreneurs
- **services**: Service listings
- **reviews**: Ratings and comments
- **announcements**: Admin news

## 🛣️ API Endpoints

### Authentication
- `POST /api/auth/register` — Register new user
- `POST /api/auth/login` — User login

### Users & Profiles
- `GET /api/users/me` — Get current user profile
- `PUT /api/users/me` — Update profile
- `GET /api/autoentrepreneurs` — List auto-entrepreneurs
- `GET /api/autoentrepreneurs/:id` — Get specific profile

### Services
- `POST /api/services` — Create service (auth required)
- `GET /api/services` — List/search services
- `GET /api/services/:id` — Service details
- `PUT /api/services/:id` — Update service (auth required)
- `DELETE /api/services/:id` — Delete service (auth required)

### Reviews
- `POST /api/reviews` — Add review (auth required)
- `GET /api/services/:id/reviews` — List reviews for a service

### Dashboard
- `GET /api/dashboard` — User statistics (auth required)

## 🔒 Security Features

- Passwords hashed with bcrypt
- JWT for authentication
- Input validation & prepared statements
- CORS enabled for frontend-backend communication
- Role-based access control

## 🎨 Frontend Features

- Modern responsive design
- Real-time API integration
- User authentication flow
- Service browsing and filtering
- Profile management
- Review system
- Dashboard with statistics

## 📱 Usage

1. **Register** as an auto-entrepreneur or client
2. **Auto-entrepreneurs** can create profiles and publish services
3. **Clients** can browse services, view profiles, and leave reviews
4. **Dashboard** shows user statistics and service management
5. **Admin** can moderate users and services

## 🔧 Development

The application is built with:
- **Backend**: PHP with PDO for database access
- **Frontend**: Vanilla HTML, CSS, JavaScript
- **Database**: MySQL
- **Authentication**: Custom JWT implementation
- **API**: RESTful endpoints

## 📄 License

This project is open source and available under the MIT License.
