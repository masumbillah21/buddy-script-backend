# Appifylab Social Platform API (Backend)

This is the Laravel-based backend API service for the Appifylab Social Platform, supplying token authentication, post feeds, event/article postings, nested comments/replies, dynamic reactions, and Swagger documentation.

---

## 🌐 Production & Live Deployment Links

* **Live Web Application**: [http://buddy-script.masum-billah.com/](http://buddy-script.masum-billah.com/)
* **Live API Base Endpoint**: [http://buddy-api.masum-billah.com/api](http://buddy-api.masum-billah.com/api)
* **Live OpenAPI / Swagger Documentation**: [http://buddy-api.masum-billah.com/api/documentation](http://buddy-api.masum-billah.com/api/documentation)

---

## Tech Stack & Architecture
* **Framework**: Laravel 13 (PHP 8.4)
* **Database**: MySQL / MariaDB (Namecheap cPanel Shared Hosting & Docker PostgreSQL for local dev)
* **CI/CD Pipeline**: GitHub Actions SSH & SCP automated deployment
* **API Documentation**: OpenAPI / Swagger (via DarkaOnline/L5-Swagger)

---

## Features Implemented
1. **User Authentication**:
   * Uses **Laravel Sanctum** token-based API authentication.
   * Endpoints for User Registration, Login, Logout, and Current User profile fetching.
2. **Posts Management**:
   * Support for **Text, Image, Video, and Event** posts.
   * Public and private visibility options.
   * Dynamic post deletions with automatic media asset cleanup.
3. **Comment & Reply Trees**:
   * One-level deep nested reply structure.
   * Real-time counter updates on parent comments (`replies_count`) and posts (`comments_count`).
4. **Dynamic Reactions System**:
   * Supports 6 distinct reaction types: `like`, `love`, `haha`, `wow`, `sad`, `angry`.
   * Real-time reaction toggling and swapping for **both Posts and Comments**.
   * Batch distinct active reaction types loader to optimize SQL query speeds on feeds.
5. **Interactive Swagger Documentation**:
   * OpenAPI endpoints fully documented and interactive.
   * Available live at: [http://buddy-api.masum-billah.com/api/documentation](http://buddy-api.masum-billah.com/api/documentation)
   * Available locally at: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

---

## Database Seeding
To support immediate testing, we configured the following seeder structure inside `DatabaseSeeder`:
1. **`UserSeeder`**: Seeds 5 default users with password set to `12345678` and assigns custom profile avatar image paths:
   * **Dylan Field** (`dylan@figma.com`)
   * **Steve Jobs** (`steve@apple.com`)
   * **Ryan Roslansky** (`ryan@linkedin.com`)
   * **Satya Nadella** (`satya@microsoft.com`)
2. **`PostSeeder`**: Seeds 3 image posts for *each* of the 5 users.
3. **`CommentSeeder`**: Seeds standard comments and nested reply chains.

---

## Setup & Development

### 1. Local Docker Setup
```bash
docker compose up --build -d
```
The backend service will listen on [http://localhost:8000](http://localhost:8000).

### 2. Run Database Migrations and Seeders
```bash
docker compose exec backend php artisan migrate:fresh --seed
```

### 3. Generate Swagger OpenAPI Docs
```bash
docker compose exec backend php artisan l5-swagger:generate
```
