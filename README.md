# 🏫 CampusTrace — School Lost & Found Portal

> A web-based system to help reunite lost items with their rightful owners on campus. Built to solve false ownership claims, verification issues, and poor tracking.

---

## 📋 Table of Contents
- [About the Project](#about-the-project)
- [Key Features](#key-features)
- [Technologies Used](#technologies-used)
- [Installation & Setup](#installation--setup)
- [Admin Access](#admin-access)
- [Project Structure](#project-structure)

---

## 🎯 About the Project

### The Problem
- Vague descriptions lead to false claims
- No proper verification before returning items
- No chain-of-custody or hold period tracking
- Unclaimed items pile up with no disposal process

### Our Solution
CampusTrace implements:
- ✅ Structured reporting with detailed fields
- ✅ Claim verification system with proof requirements
- ✅ Separate admin login for extra security
- ✅ Admin review workflow
- ✅ Automatic hold periods based on item type
- ✅ Complete handoff and archive logging

---

## ✨ Key Features

### For All Users
- Register & Login (School ID based)
- Report Lost Items — category, color, brand, location, description
- Post Found Items — same detailed format
- Browse All Items — filter by type, category, status
- Claim Items — submit proof of ownership for admin review
- My Posts — view your own reports and claims

### For Admins
- **Separate Admin Login** — `/pages/auth/admin_login.php`
- Admin Dashboard — pending claims, active items, expiring hold
- Claim Review — approve/reject with verification checks
- Handoff Logging — record item return details
- Archive Management — dispose/donate/shred unclaimed items
- Automatic Hold Enforcement — 7–60 days based on sensitivity

---

## 🛠️ Technologies Used

| Component | Technology |
|---|---|
| Frontend | HTML5, CSS3 |
| Backend | PHP |
| Database | MariaDB / MySQL |
| Local Server | XAMPP (Apache + MySQL) |

---

## ⚙️ Installation & Setup

### Prerequisites
- XAMPP installed
- PHP 8.2+

### Step 1 — Place Files
Copy all project folders into:
D:\Xampp\htdocs
├── assets/
├── config/
├── includes/
└── pages/


### Step 2 — Start Servers
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL**

### Step 3 — Database Setup
1. Open `http://localhost/phpmyadmin`
2. Create database: `campustrace`
3. Go to **SQL** tab → paste the schema code → click **Go**
4. Verify `config/db_connect.php`:
   ```php
   $host = 'localhost';
   $dbname = 'campustrace';
   $username = 'root';
   $password = '';
   
### Step 4 — Access the System
http://localhost/pages/auth/register.php      ← Create account
http://localhost/pages/auth/login.php         ← Student / User Login
http://localhost/pages/auth/admin_login.php   ← Admin Only Login
http://localhost/pages/index.php              ← Dashboard

🔐 Admin Access
Option 1 — Use Separate Admin Login
Go to: http://localhost/pages/auth/admin_login.php
Login using your admin account credentials
Directly access Admin Dashboard ✅
Option 2 — Upgrade Existing Account
Go to http://localhost/phpmyadmin → campustrace → users
Click Edit on your account
Change role from student to admin → Save
Logout → Login again → Admin Panel appears in navigation ✅

Project Structure
htdocs/
├── assets/css/styles.css
├── config/db_connect.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── session_check.php
│   └── functions.php
├── pages/
│   ├── auth/
│   │   ├── register.php
│   │   ├── login.php              ← Student / General Login
│   │   ├── admin_login.php        ← Admin Only Login
│   │   └── logout.php
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── verify_claim.php
│   │   └── archive.php
│   ├── index.php
│   ├── lost_report.php
│   ├── found_report.php
│   ├── items_list.php
│   ├── item_detail.php
│   └── my_items.php
└── README.md

Security Features
Passwords hashed — never stored as plain text
Input sanitization — prevents SQL injection
Session protection — blocks unauthorized access
Role-based gates — only admins can access admin pages
Separate admin login — extra layer of access control
Claim verification — requires specific proof details not just general descriptions

Team
Project Name: CampusTrace
Stack: HTML, CSS, PHP, MariaDB, XAMPP
Purpose: Hackathon / School Project
