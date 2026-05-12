# 🚌 D'Rising Sun Transport: Smart Bus Management System

A cloud-enabled, full-stack MVP designed for provincial bus transport modernization. This system digitizes ticketing, fleet monitoring, and incident reporting across three specialized portals.

---

## 🚀 Quick Start (Local Development)

### 1. Prerequisites
- **WAMP Server** or **XAMPP** (PHP 8.2+, MySQL 8.0+)
- **Google Maps API Key** (See [Setup Guide](#-google-maps-api-key-setup) below)

### 2. Database Setup
1. Open **phpMyAdmin**.
2. Create a new database: `smart_bus`.
3. Import the `schema.sql` file located in the root directory.

### 3. Environment Variables
1. Copy `.env.example` to a new file named `.env`.
2. Open `.env` and configure your credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=smart_bus
   DB_USER=root
   DB_PASS=your_password
   GOOGLE_MAPS_API_KEY=your_key_here
   ```

### 4. Run the App
1. Place the project folder in your `www` or `htdocs` directory.
2. Visit `http://localhost/smart-bus-system/` in your browser.

---

## 👥 Test Credentials
| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@drisingsun.com` | `password` |
| **Driver** | `driver1@drisingsun.com` | `password` |
| **Passenger** | `pass1@example.com` | `password` |

---

## 🗺️ Google Maps API Key Setup

To enable the map features (Route Preview, Live Tracking, Incident Pinning), follow these steps:

1.  Go to the [Google Cloud Console](https://console.cloud.google.com/).
2.  Create a new project named `Smart Bus System`.
3.  Navigate to **APIs & Services > Library**.
4.  Search for and **Enable** these three APIs:
    *   **Maps JavaScript API**
    *   **Directions API**
    *   **Geocoding API**
5.  Go to **APIs & Services > Credentials**.
6.  Click **Create Credentials > API Key**.
7.  Copy the key and paste it into your `.env` file.

---

## ☁️ GCP Deployment Guide

### 1. Cloud SQL (MySQL) Setup
1. In GCP Console, go to **SQL** and click **Create Instance**.
2. Choose **MySQL** and set a root password.
3. Once created, go to **Databases** and create `smart_bus`.
4. Use the **Cloud SQL Auth Proxy** or GCP's built-in query tool to import `schema.sql`.

### 2. Cloud Run Deployment
1. **Containerize**: The project includes a `Dockerfile`. Ensure you have the [Google Cloud SDK](https://cloud.google.com/sdk) installed.
2. **Build & Push**:
   ```bash
   gcloud builds submit --tag gcr.io/[PROJECT_ID]/smart-bus
   ```
3. **Deploy**:
   ```bash
   gcloud run deploy smart-bus \
     --image gcr.io/[PROJECT_ID]/smart-bus \
     --platform managed \
     --add-cloudsql-instances [INSTANCE_CONNECTION_NAME] \
     --set-env-vars "DB_HOST=127.0.0.1,DB_NAME=smart_bus,DB_USER=root,DB_PASS=[PASSWORD],GOOGLE_MAPS_API_KEY=[KEY]"
   ```
   *Note: Use `127.0.0.1` as the host when connecting via the Cloud SQL instance connection.*

---

## 📂 File Structure
- `/api/`: PHP REST API endpoints (PDO based).
- `/admin/`: Full administrative control panel.
- `/driver/`: Status updates and incident reporting.
- `/passenger/`: Search, visual seat selection, and tracking.
- `/assets/`: Shared CSS, Vanilla JS utilities, and design tokens.
- `Dockerfile`: Configuration for GCP Cloud Run.
- `schema.sql`: Database initialization script.

---

## 🎨 Design System
- **Colors**: Deep Navy (`#1A2B4A`), Golden Accent (`#F5A623`).
- **Typography**: Inter (Body), Poppins (Headings).
- **Icons**: Font Awesome 6.
- **Components**: KPI Cards, Custom Modals, Toast Notifications, Visual Seat Grids.

---
© 2026 D'Rising Sun Transport Modernization Project.
