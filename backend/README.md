# Plateforme Auto-Entrepreneurs Tunisie

A freelancing platform for Tunisian auto-entrepreneurs to offer services directly to clients.

## 🚀 Project Setup

1. **Clone the repository**
2. Import `database.sql` into your MySQL server
3. Copy `backend/config/config.php` and set your DB credentials
4. Serve the `backend` folder with Apache (mod_rewrite enabled)
5. Access via `http://localhost/Autoentre/backend/`

## 🗃️ Database Schema
- **users**: All users (auto-entrepreneur, client, admin)
- **autoentrepreneur_profiles**: Extended info for auto-entrepreneurs
- **services**: Service listings
- **reviews**: Ratings and comments
- **announcements**: Admin news

## 🛣️ API Endpoints (REST)

### Auth
- `POST /api/auth/register` — Register (email/phone)
- `POST /api/auth/login` — Login

### Users & Profiles
- `GET /api/users/me` — Get current user profile
- `PUT /api/users/me` — Update profile
- `GET /api/autoentrepreneurs` — List auto-entrepreneurs
- `GET /api/autoentrepreneurs/:id` — Get profile

### Services
- `POST /api/services` — Create service
- `GET /api/services` — List/search services
- `GET /api/services/:id` — Service details
- `PUT /api/services/:id` — Update service
- `DELETE /api/services/:id` — Delete service

### Reviews
- `POST /api/reviews` — Add review
- `GET /api/services/:id/reviews` — List reviews for a service

### Dashboard
- `GET /api/dashboard` — User stats

### Admin
- `GET /api/admin/users` — List users
- `GET /api/admin/services` — List services
- `POST /api/admin/announcements` — Add announcement
- `POST /api/admin/moderate` — Moderate content

## 🔒 Security
- Passwords hashed with bcrypt
- JWT for authentication
- Input validation & prepared statements
- GDPR compliant

## 🏃 Run Locally
- Import DB, set config, serve with Apache
- Use Postman or frontend to test endpoints

---

For full endpoint details, see code comments in `routes/` and `controllers/`.
