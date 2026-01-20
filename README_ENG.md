# 🚛 Vintara Logistics DriverHub

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-Required-885630?logo=composer&logoColor=white)
![Status](https://img.shields.io/badge/Status-Active-success)
![License](https://img.shields.io/badge/License-Private-red)

**DriverHub** is the central management platform for **Vintara Logistics VTC**.  
It works as an all-in-one portal where drivers can **track their progress**, **manage rewards**, **contact support**, and access **official company resources**, while administrators have full control and moderation tools.

---

## ✨ Features

### 👨‍✈️ For Drivers

- **Secure Access**
  - Individual login with session management.
  - Ban and restriction enforcement.
- **Dashboard**
  - News, announcements, and event updates.
  - Quick access to main sections.
- **Driver Store**
  - **Points System**: Earn points based on kilometers driven and achievements.
  - **Achievements**: Unlock special milestones such as:
    - *Safe Driver*
    - *Fuel Saver*
  - **Rewards**:
    - Redeem points for physical or digital rewards.
  - **Redemption History**:
    - Track the status of your reward requests.
- **Ticket System**
  - Integrated support to report issues or ask questions.
  - Direct communication with staff.
- **Downloads**
  - Official VTC skins.
  - Mods and internal documentation.

---

### 🛠️ For Administration

- **User Management**
  - Create, edit, and delete driver accounts.
  - Manage **TruckersMP** and **Trucky** IDs.
  - Ban / Unban users with:
    - Reason
    - Duration
- **Store Management**
  - Create and manage **Achievements**.
  - Create and manage **Rewards**:
    - Stock
    - Point cost
  - Process reward redemption requests.
- **Ticket Management**
  - View, reply, close, and archive support tickets.

---

## 📂 Project Structure

```text
/
├── index.php
├── dashboard.php
├── admin_create.php
├── admin_edit.php
├── includes/
│   ├── auth_logic.php
│   ├── i18n.php
│   ├── db.php
│   ├── store_view.php
│   ├── tickets_view.php
│   ├── admin_table_view.php
│   └── admin_store_view.php
├── assets/
└── vintara_db.sql
```

---

## 🧰 Installation & Setup

### 📋 Prerequisites

- PHP **7.4 or higher**
- MySQL or MariaDB
- Composer

---

### 1️⃣ Install Dependencies

```bash
composer install
```

---

### 2️⃣ Environment Configuration (`.env`)

```ini
ENV=TEST
DB_HOST="localhost"
DB_USER="root"
DB_PASSWORD=""
DB_NAME="vintara_db"
POINTS_PER_KM=1
TRUCKY_API_KEY=your_jwt_token_here
TRUCKY_WEBHOOK_SECRET=your_webhook_secret_here
```

---

### 3️⃣ Database Setup

Import `vintara_db.sql` into your database.

---

## 🌍 Internationalization

Multi-language support (ES / EN) via `includes/i18n.php`.

---

## ©️ License

© 2026 **Vintara Logistics**
