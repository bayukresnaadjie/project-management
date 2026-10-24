# 🚀 ProjectHub — Project Management System

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-CDN-06B6D4?style=flat&logo=tailwindcss&logoColor=white)

Sistem manajemen project berbasis web yang terinspirasi dari Jira. Mendukung Kanban board dengan drag & drop, Sprint management, Burndown chart, dan multi-workspace.

---

## ✨ Fitur Utama

### 🏢 Workspace & Project
- Multi-workspace per organisasi / tim
- Setiap workspace bisa memiliki banyak project
- Undang anggota dengan role berbeda (Owner, Admin, Member, Viewer)
- Project tipe: **Kanban**, **Scrum**, **Basic**

### 📋 Kanban Board
- Drag & drop task antar kolom
- Quick add task langsung dari board
- Filter task per assignee & prioritas
- Shortcut keyboard `N` untuk buat task baru
- Warna kolom & badge prioritas berwarna

### ✅ Task Management
- Tipe task: Task, Bug, Feature, Improvement, Epic
- Prioritas: Critical 🔴, High 🟠, Medium 🟡, Low 🟢
- Sub-task bersarang
- Multi-assignee per task
- Label / tag berwarna per project
- Estimasi jam & story point
- Progress tracker (0-100%)
- Due date dengan overdue alert
- Komentar & reply
- Lampiran file (upload / download)
- Log aktivitas per task

### 🏃 Sprint (Scrum)
- Buat & kelola sprint
- Backlog management
- Mulai & selesaikan sprint
- Pindahkan task belum selesai ke backlog otomatis
- **Burndown chart** SVG real-time

### 📊 Laporan
- Task per kolom (board)
- Task per prioritas
- Task per anggota + progress individu
- Grafik aktivitas 14 hari

### 🔔 Notifikasi
- Notif saat task di-assign
- Notif saat task diselesaikan
- Notif saat ada komentar baru
- Badge counter di sidebar

---

## 👥 Role & Akses

| Role | Workspace | Project | Task |
|---|---|---|---|
| **Owner** | Full akses | Full akses | Full akses |
| **Admin** | Kelola member | Full akses | Full akses |
| **Manager** | — | Kelola project & member | Full akses |
| **Member** | — | Lihat & akses | Buat & update task |
| **Viewer** | — | Hanya lihat | Hanya lihat |

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 11 (PHP 8.3) |
| Database | MySQL 8.0 |
| Frontend | Blade + Tailwind CSS (CDN) |
| Auth | Laravel built-in Auth |
| Storage | Laravel Storage (local) |
| Chart | SVG native (Burndown) |

---

## ⚙️ Cara Install

### Requirements
- PHP >= 8.2
- Composer
- MySQL 8.0
- Node.js (opsional)

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/bayukresnaadjie/project-management.git
cd project-management

# 2. Install dependencies
composer install

# 3. Copy environment
cp .env.example .env

# 4. Generate key
php artisan key:generate

# 5. Buat database di MySQL
# CREATE DATABASE project_management;

# 6. Edit .env sesuaikan database
# DB_DATABASE=project_management
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Migrate & seed data demo
php artisan migrate:fresh --seed

# 8. Link storage
php artisan storage:link

# 9. Jalankan server
php artisan serve
```

Akses di: **http://127.0.0.1:8000**

---

## 🔑 Akun Demo

| Email | Password | Role |
|---|---|---|
| admin@pm.test | password | Admin (Owner) |
| rina@pm.test | password | Project Manager |
| dika@pm.test | password | Developer (Member) |
| sari@pm.test | password | Designer (Member) |
| fajar@pm.test | password | Frontend Dev (Member) |
| tono@pm.test | password | QA Engineer (Member) |

---

## 🗄️ Struktur Database

```
users                   — Data user + jabatan
workspaces              — Workspace organisasi
workspace_members       — Member workspace + role
projects                — Project dalam workspace
project_members         — Member project + role
boards                  — Kolom Kanban per project
sprints                 — Sprint untuk Scrum project
labels                  — Label/tag per project
tasks                   — Task utama & sub-task
task_assignees          — Multi-assignee per task
task_labels             — Label per task
task_comments           — Komentar & reply
task_attachments        — File lampiran task
task_activities         — Log aktivitas per task
notifications           — Notifikasi per user
```

---

## 📁 Struktur Project

```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── WorkspaceController.php
│   ├── ProjectController.php
│   ├── TaskController.php
│   ├── SprintController.php
│   ├── LaporanController.php
│   └── NotificationController.php
├── Models/
│   ├── Workspace.php
│   ├── Project.php
│   ├── Board.php
│   ├── Sprint.php
│   ├── Label.php
│   ├── Task.php
│   ├── TaskComment.php
│   ├── TaskAttachment.php
│   ├── TaskActivity.php
│   └── Notification.php
resources/views/
├── layouts/app.blade.php
├── auth/login.blade.php
├── dashboard.blade.php
├── workspace/
├── project/
│   ├── board.blade.php     ← Kanban board
│   ├── create.blade.php
│   └── settings.blade.php
├── task/show.blade.php
├── sprint/
│   ├── index.blade.php
│   └── burndown.blade.php  ← Burndown chart
├── laporan/index.blade.php
└── notification/index.blade.php
```

---

## 📸 Preview

### Dashboard
- Statistik task (total, selesai, in progress, overdue)
- Daftar task yang di-assign ke user
- Project aktif dengan progress bar
- Feed aktivitas terbaru

### Kanban Board
- Kolom: To Do → In Progress → Review → Done
- Drag & drop antar kolom
- Filter per assignee & prioritas
- Badge tipe, prioritas, due date, assignee di setiap card

### Sprint & Burndown
- Kelola sprint dengan backlog
- Burndown chart real-time berbasis SVG
- Statistik story point selesai vs ideal

---

## 🔮 Rencana Pengembangan

- [ ] Real-time update dengan WebSocket (Laravel Echo + Pusher)
- [ ] Export laporan ke PDF / Excel
- [ ] Gantt chart / Timeline view
- [ ] Time tracking per task
- [ ] Integrasi GitHub / GitLab (commit link ke task)
- [ ] Mobile app (React Native)
- [ ] API REST untuk integrasi pihak ketiga

---

## 👨‍💻 Developer

**Bayu Kresna Adjie**
- GitHub: [@bayukresnaadjie](https://github.com/bayukresnaadjie)
- LinkedIn: [bayu-kresna-adjie](https://www.linkedin.com/in/bayu-kresna-adjie-a883301ab)
- Lokasi: Yogyakarta, Indonesia

---

## 📄 Lisensi

Project ini dibuat untuk keperluan portofolio. Silakan digunakan sebagai referensi.
