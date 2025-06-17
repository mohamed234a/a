# Manual API Test Scenarios for Auto-Entrepreneur Tunisie

## 1. Authentication
### Register
- Endpoint: `POST /backend/api.php?route=api/auth/register`
- Body: `{ "email": "testuser@email.com", "password": "Test1234", "name": "Test User", "type": "client" }`
- Expect: `success: true`, `user_id` returned

### Login
- Endpoint: `POST /backend/api.php?route=api/auth/login`
- Body: `{ "email": "testuser@email.com", "password": "Test1234" }`
- Expect: `token` and `user` returned

## 2. User CRUD (as admin)
### Get All Users
- Endpoint: `GET /backend/api.php?route=api/admin`
- Header: `Authorization: Bearer <admin_token>`
- Expect: List of users

### Edit User
- Endpoint: `PUT /backend/api.php?route=api/admin&user_id=<id>`
- Header: `Authorization: Bearer <admin_token>`
- Body: `{ "name": "New Name", "email": "new@email.com" }`
- Expect: `User updated successfully`

### Delete User
- Endpoint: `DELETE /backend/api.php?route=api/admin&user_id=<id>`
- Header: `Authorization: Bearer <admin_token>`
- Expect: `User deleted successfully`

## 3. Service CRUD (as autoentrepreneur)
### Create Service
- Endpoint: `POST /backend/api.php?route=api/services`
- Header: `Authorization: Bearer <ae_token>`
- Body: `{ "title": "Test Service", "description": "Desc", "category": "Web", "price": 100 }`
- Expect: `success: true`, `service_id` returned

### Edit Service
- Endpoint: `PUT /backend/api.php?route=api/services&id=<id>`
- Header: `Authorization: Bearer <ae_token>`
- Body: `{ "title": "Updated Service" }`
- Expect: `success: true`

### Delete Service
- Endpoint: `DELETE /backend/api.php?route=api/services&id=<id>`
- Header: `Authorization: Bearer <ae_token>`
- Expect: `success: true`

## 4. Permissions
- Try to create/edit/delete a service as a client (should fail with 403)
- Try to delete another admin (should be restricted or logged)

## 5. Frontend Integration
- Register/login via frontend UI
- Create/edit/delete service via frontend
- Admin actions via admin dashboard

---

**How to test:**
- Use Postman or curl for API endpoints.
- Use the frontend UI for integration tests.
- Check responses and database for expected changes.

**Note:**
- Replace `<admin_token>`, `<ae_token>`, `<id>` with actual values from login/register responses.
