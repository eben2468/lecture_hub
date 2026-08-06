# Nadics LectureHub — Smart Lecture Management System (SLMS)

> **Every Student Hears. Every Lecture Lives.**

## About

Nadics LectureHub is an enterprise-grade Smart Lecture Management System built by **Nadics Solutions**. Designed to digitize classroom teaching across African universities, SLMS combines live lecture streaming, attendance automation, AI-powered transcription, and a comprehensive learning management platform.

## Requirements

| Component | Version |
|-----------|---------|
| PHP | >= 8.0 |
| MySQL/MariaDB | >= 10.4 / 8.0 |
| Apache | >= 2.4 |
| mod_rewrite | Enabled |

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/nadics/lecturehub.git "lecture_hub"
cd "lecture_hub"
```

### 2. Environment Setup

```bash
cp .env.example .env
# Edit .env with your database credentials
```

### 3. Database Setup

Import the schema via phpMyAdmin or command line:

```bash
mysql -u root -p < database/schema.sql
```

### 4. Directory Permissions

Ensure the following directories are writable:

```
storage/logs/
storage/cache/
storage/sessions/
storage/temp/
public/uploads/
```

### 5. Apache Configuration

Ensure `mod_rewrite` is enabled in your Apache configuration:

```bash
# XAMPP: Check httpd.conf
LoadModule rewrite_module modules/mod_rewrite.so
```

### 6. Access the Application

```
http://localhost/lecture_hub/public/
```

## Architecture

```
├── app/            # Controllers, Models, Services, Repositories, Middleware
├── config/         # Configuration files
├── core/           # Framework engine (Router, Database, ORM, etc.)
├── database/       # Migrations, seeders, schema
├── public/         # Web root (entry point, assets, uploads)
├── resources/      # Views, layouts, components, localization
├── routes/         # Web & API route definitions
├── storage/        # Logs, cache, sessions, temp files
└── tests/          # Unit & feature tests
```

## Technology Stack

- **Backend:** PHP 8 MVC (Laravel-inspired architecture)
- **Database:** MariaDB 10.4 / MySQL 8
- **Frontend:** HTML5, CSS3, JavaScript ES6, Bootstrap 5
- **Charts:** Chart.js
- **Icons:** Font Awesome 6
- **Real-time:** WebRTC, WebSockets
- **Design:** Responsive, Dark Mode, Mobile-First

## Color Palette

| Role | Color |
|------|-------|
| Primary | `#0F172A` |
| Secondary | `#2563EB` |
| Accent | `#06B6D4` |
| Success | `#10B981` |
| Warning | `#F59E0B` |
| Danger | `#EF4444` |

## Modules

1. **Authentication** — Login, Registration, 2FA, Roles & Permissions
2. **University** — Universities, Faculties, Departments, Programmes, Courses
3. **User Management** — Admins, Lecturers, TAs, Students, Guests
4. **Lectures** — Create, Schedule, Start, Record, Replay, Library
5. **Live Streaming** — Audio/Video, Screen Sharing, Recording
6. **Attendance** — QR Code, GPS, Face Recognition, RFID
7. **Course Materials** — Slides, PDFs, Videos, Assignments
8. **Assessment** — Quiz, Exam, Submission, Grading, Results
9. **AI Module** — Speech-to-Text, Summaries, Flashcards, AI Assistant
10. **Notifications** — Email, SMS, Push, In-App, Announcements
11. **Analytics** — Student Performance, Attendance, Course, Charts
12. **Settings** — General, SMTP, SMS, Storage, API Keys, Security

## Security

- CSRF Protection
- XSS Prevention
- SQL Injection Prevention (PDO Prepared Statements)
- Input Validation & Sanitization
- Secure File Uploads
- Session Regeneration
- Password Hashing (bcrypt)
- Rate Limiting
- Audit & Activity Logs

## License

Proprietary — © 2026 Nadics Solutions. All rights reserved.
