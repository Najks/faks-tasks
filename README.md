# Faks Tasks

A full-stack academic task tracker built with **Laravel 12** (REST API) and **Vue 3** (SPA). Students can organise their coursework by subject, track task statuses, record grades, and monitor pending work — all in one place.

---

## Tech Stack

| Layer | Technology                                              |
|---|---------------------------------------------------------|
| Backend | PHP 8.4 · Laravel 12 · Laravel Sanctum (token auth)     |
| Database | PostgreSQL 16                                           |
| Frontend | Vue 3 · TypeScript · Vue Router 5 · Bootstrap 5 · Axios |
| Build | Vite 7 · vue-tsc                                        |
| Infrastructure | Docker · Docker Compose · Nginx                         |

---

## Features

- **Authentication** — register, log in / out, update profile, forgot-password & reset-password flows (via Laravel Sanctum tokens)
- **Subjects** — create, rename and delete the university subjects you're enrolled in
- **Tasks** — full CRUD per subject: title, description, due date, grade, and status
- **Task statuses** — assign and update a status (e.g. *pending*, *in progress*, *done*) to every task
- **Overview dashboard** — at-a-glance counts of total and pending tasks per subject
- **Statistics panel** — grade / progress summaries
- **Protected routes** — Vue Router guards redirect unauthenticated users to the login page

---

## Project Structure

```
faks-tasks/
├── backend/          # Laravel 12 application
│   ├── app/
│   │   ├── Http/Controllers/   # AuthController, SubjectController, TaskController, TaskStatusController
│   │   ├── Models/             # User, Subject, Task, TaskStatus
│   │   └── Policies/           # TaskPolicy
│   ├── database/
│   │   ├── migrations/
│   │   ├── factories/
│   │   └── seeders/
│   ├── routes/api.php          # All API routes
│   └── nginx/default.conf      # Nginx config for the PHP-FPM container
├── frontend/         # Vue 3 + TypeScript SPA
│   ├── src/
│   │   ├── components/         # Page & UI components
│   │   ├── composables/        # useAuth, useSubjects, useApiRequest, …
│   │   ├── router/             # Vue Router configuration
│   │   └── services/           # Axios service layer
│   └── vite.config.ts
├── nginx/            # Reverse-proxy config (routes /api → backend, / → frontend)
├── docker-compose.yml
└── .env.example
```

---

## Getting Started

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/) & [Docker Compose](https://docs.docker.com/compose/install/) (v2+)

### 1. Clone the repository

```bash
git clone <repo-url> faks-tasks
cd faks-tasks
```

### 2. Configure environment variables

```bash
cp .env.example .env
```

Edit `.env` and set at minimum:

| Variable | Description |
|---|---|
| `APP_KEY` | Generate after the containers start (see step 4) |
| `DB_DATABASE` | PostgreSQL database name |
| `DB_USERNAME` | PostgreSQL user |
| `DB_PASSWORD` | PostgreSQL password |

### 3. Start the containers

```bash
docker compose up -d --build
```

This starts four services:

| Container | Role |
|---|---|
| `faks-db` | PostgreSQL 16 database |
| `faks-backend` | Laravel app (PHP-FPM) |
| `faks-api` | Nginx — serves the Laravel API |
| `faks-frontend` | Nginx — serves the built Vue SPA |

### 4. Bootstrap the application

Run the following **once** after the containers are up:

```bash
# Generate the Laravel application key
docker exec faks-backend php artisan key:generate

# Run database migrations (and optional seeders)
docker exec faks-backend php artisan migrate --seed
```

The application is now accessible at **http://localhost** (or the `APP_URL` you configured).

---

## API Reference

All endpoints are prefixed with `/api`.

### Auth

| Method | Endpoint | Auth required | Description |
|---|---|---|---|
| `POST` | `/auth/register` | No | Create a new account |
| `POST` | `/auth/login` | No | Obtain a Sanctum token |
| `POST` | `/auth/logout` | Yes | Revoke the current token |
| `GET` | `/auth/me` | Yes | Get the authenticated user |
| `PATCH` | `/auth/me` | Yes | Update profile |
| `POST` | `/auth/password/request` | No | Send a password-reset email |
| `POST` | `/auth/password/reset` | No | Reset password with token |

### Subjects

| Method | Endpoint | Auth required | Description |
|---|---|---|---|
| `GET` | `/subjects` | No | List all subjects |
| `GET` | `/subjects/mine` | Yes | List the authenticated user's subjects |
| `POST` | `/subjects` | No | Create a subject |
| `GET` | `/subjects/{id}` | No | Get a subject |
| `PATCH` | `/subjects/{id}` | No | Update a subject |
| `DELETE` | `/subjects/{id}` | No | Delete a subject |

### Tasks

| Method | Endpoint | Auth required | Description |
|---|---|---|---|
| `GET` | `/tasks` | Yes | List the user's tasks |
| `POST` | `/tasks` | Yes | Create a task |
| `GET` | `/tasks/{id}` | Yes | Get a task |
| `PUT/PATCH` | `/tasks/{id}` | Yes | Update a task |
| `DELETE` | `/tasks/{id}` | Yes | Delete a task |
| `PATCH` | `/tasks/{id}/status` | Yes | Update only the task status |
| `GET` | `/tasks/user/{userId}` | No | Get all tasks for a user |

### Task Statuses

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/status` | List all statuses |
| `POST` | `/status` | Create a status |
| `GET` | `/status/{id}` | Get a status |
| `PUT` | `/status/{id}` | Update a status |
| `DELETE` | `/status/{id}` | Delete a status |

---

## Frontend Pages

| Route | Component | Description |
|---|---|---|
| `/` | `HomePage` | Dashboard with subject + task overview |
| `/login` | `LoginPage` | Login form |
| `/register` | `RegisterPage` | Registration form |
| `/user` | `LookoutPage` | User overview / stats |
| `/subjects/manage` | `ManageSubjectsPage` | Create, rename, delete subjects |
| `/subject/:id` | `SubjectDetailPage` | Tasks list for a subject |
| `/tasks/:id` | `TaskDetailEditor` | View and edit a single task |
| `/password/forgot` | `ForgotPasswordPage` | Request password reset |
| `/password/reset` | `ResetPasswordPage` | Complete password reset |

---

## Running Tests

```bash
docker exec faks-backend php artisan test
```

---

## Development (without Docker)

### Backend

```bash
cd backend
composer install
cp .env.example .env       # configure DB_* and APP_KEY
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

The Vite dev server proxies `/api` requests to the Laravel backend automatically (see `vite.config.ts`).

---

## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).

