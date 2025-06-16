# Frontend Documentation — Plateforme Auto-Entrepreneurs Tunisie

## Structure
- `index.html` — Landing page
- `login.html` — Login page
- `register.html` — Registration page
- `css/style.css` — Main styles
- `js/main.js` — Main JS (language switch, helpers)
- `assets/` — Images, logos, etc.

## Components (HTML/CSS/JS)
- **Card**: Used for service/freelancer display
- **Button**: `.btn` class for all buttons
- **FormInput**: Standard `<input>`/`<select>`/`<textarea>`
- **FilterSidebar**: (to be added in service listing)

## Pages (to implement)
- `profile.html` — Auto-entrepreneur/client profile
- `services.html` — Service listing/search
- `service.html` — Service detail
- `dashboard.html` — User dashboard
- `admin.html` — Admin dashboard

## API Integration
- All forms use `fetch()` to call backend API endpoints (see backend README)
- JWT token is stored in `localStorage` after login and sent in `Authorization` header for protected routes

## How to Plug Frontend to Backend
- Place `frontend/` and `backend/` in the same root folder
- Update API URLs in JS if needed (default: `backend/index.php?route=...`)
- Serve with Apache or use `php -S localhost:8000` in backend folder for local dev

## Responsiveness
- Uses CSS media queries for mobile/tablet/desktop

---
Add more pages and components as needed for full functionality.
