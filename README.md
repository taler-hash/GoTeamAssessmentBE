# 🗂️ Task Manager App

A user-based task management system built with Laravel v12 and Nuxt v4. This app allows users to manage tasks efficiently with features like search, completion, deletion, and date grouping. Designed for productivity and ease of use.

---

## ✨ Features

- 🔐 User-based task management  
- 🔎 Searchable tasks with focus capability  
- ✅ Mark tasks as complete  
- 🗑️ Delete tasks  
- 📅 Group tasks by date

---

## 🧰 Prerequisites

Make sure the following tools are installed before setup:

- Node.js  
- Composer  
- PHP v8 or later  
- pnpm  
- Docker
- Mac OS or any Linux Distro

---

## 🧱 Frameworks & Packages

- `Laravel v12`  
- `Nuxt v4`  
- `nuxt-auth-sanctum`  
- `PrimeVue`  
- `Tailwind CSS v4`  
- `Sanctum`  

---

## ⚙️ Installation Guide

### 1️⃣ API Setup

1. Clone the backend repository\
   ``
   git clone https://github.com/taler-hash/GoTeamAssessmentBE.git``
   
2. Go to the directory \
  ``
  cd GoTeamAssessmentBE``

3. Install dependencies \
  ``
  composer install``

4. Set up environment file \
  ``
  cp .env.example .env``

5. Generate application key \
  ``
  php artisan key:generate``

6. Install Laravel Sail \
  ``
  php artisan sail:install``

7. Start the Sail environment \
  ``
  ./vendor/bin/sail up``
8. Open a new terminal in the same directory and run migrations \
  ``
  ./vendor/bin/sail artisan migrate``
7. Visit http://localhost in your browser — it should display: \
  ``
  { "Hello": "World" }``

---
### 2️⃣ Client Setup
1. Clone the frontend repository\
   ``
   git clone https://github.com/taler-hash/GoTeamAssessmentFE.git``
   
2. Go to the directory \
  ``
  cd GoTeamAssessmentFE``

3. Install dependencies \
  ``
  pnpm install``

4. Set up environment file \
  ``
  echo "BASE_API_URL=http://localhost/api" > .env``

5. Generate application key \
  ``
  pnpm dev``

5. Access the frontend via http://localhost:3000

---

### 👨‍💻 Developer Info
Developed By: Jurie Tylier Pedrogas \
Development Time: 10+ hours

PS:Right now my schedule in my current work is from 09:00AM to 12:00AM including weekends. If i have more time develop i will update the pattern from service pattern to repository pattern in the Backend and in the frontend i will use meilisearch with redis to make the search capability much faster and  update user info functionality with forgot password to send in email right now it is the MVP (minimum viable product) just to finish whats listed on the requirements