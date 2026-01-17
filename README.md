# 🚛 Vintara Logistics DriverHub

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white)
![Composer](https://img.shields.io/badge/Composer-Required-885630?logo=composer&logoColor=white)
![Status](https://img.shields.io/badge/Status-Active-success)
![License](https://img.shields.io/badge/License-Private-red)

**DriverHub** es la plataforma central de gestión para **Vintara Logistics VTC**.  
Funciona como un portal integral donde los conductores pueden **gestionar su progreso**, **recompensas**, **soporte**, y acceder a los **recursos oficiales de la empresa**, mientras que la administración dispone de herramientas completas de control y moderación.

---

## ✨ Características

### 👨‍✈️ Para Conductores

- **Acceso Seguro**
  - Inicio de sesión individual con gestión de sesiones.
  - Control de baneos y restricciones.
- **Dashboard**
  - Centro de noticias, anuncios y eventos.
  - Accesos rápidos a las secciones principales.
- **Tienda del Conductor**
  - **Sistema de Puntos**: Gana puntos por kilómetros recorridos y logros.
  - **Logros (Achievements)**: Desbloquea hitos especiales como:
    - *Safe Driver*
    - *Fuel Saver*
  - **Recompensas**:
    - Canjea puntos por recompensas físicas o digitales.
  - **Historial de Canjes**:
    - Consulta el estado de tus solicitudes.
- **Sistema de Tickets**
  - Soporte integrado para reportar problemas o realizar consultas.
  - Comunicación directa con el staff.
- **Descargas**
  - Skins oficiales de la VTC.
  - Mods y documentación interna.

---

### 🛠️ Para Administración

- **Gestión de Usuarios**
  - Crear, editar y eliminar cuentas de conductores.
  - Gestión de IDs de **TruckersMP** y **Trucky**.
  - Banear / desbanear usuarios con motivo y duración.
- **Gestión de la Tienda**
  - Crear y administrar **Logros**.
  - Crear y administrar **Recompensas** (stock y coste en puntos).
  - Procesar solicitudes de canje.
- **Gestión de Tickets**
  - Ver, responder, cerrar y archivar tickets de soporte.

---

## 📂 Estructura del Proyecto

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

## 🧰 Instalación y Configuración

### 📋 Requisitos Previos

- PHP **7.4 o superior**
- MySQL o MariaDB
- Composer

---

### 1️⃣ Instalar Dependencias

```bash
composer install
```

---

### 2️⃣ Configuración del Entorno (`.env`)

```ini
ENV=TEST
DB_HOST="localhost"
DB_USER="root"
DB_PASSWORD=""
DB_NAME="vintara_db"
POINTS_PER_KM=1
TRUCKY_API_KEY="your_jwt_token_here"
TRUCKY_WEBHOOK_SECRET="your_webhook_secret_here"
```

---

### 3️⃣ Base de Datos

Importa el archivo `vintara_db.sql` en tu base de datos MySQL/MariaDB.

---

## 🌍 Internacionalización

Soporte multilenguaje (ES / EN) mediante `includes/i18n.php`.

---

## ©️ Licencia

© 2026 **Vintara Logistics**  
Uso interno exclusivo.
