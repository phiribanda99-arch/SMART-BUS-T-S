# SBMS - Smart Bus Management System (Zambia)

A complete bus ticketing and transportation management platform for Zambia, built with HTML5, CSS3, Vanilla JavaScript, PHP, and MySQL.

## 🚀 Features

### Passenger Features
- **Bus Booking System** - Search, book, and manage ticket reservations
- **Multiple Payment Methods** - MTN MoMo, Airtel Money, Zamtel Kwacha, Credit Card, Cash
- **Booking Management** - View active bookings, cancellations, refunds
- **Travel History** - Track past journeys and receipts
- **Loyalty Program** - Accumulate points on every booking
- **Ratings & Reviews** - Rate operators and buses
- **Support Tickets** - Create and track customer support requests
- **Notifications** - Real-time alerts for bookings and promotions

### Administrator Features (Conceptual Level)
- **User Management** - Create, edit, delete users and manage roles
- **Business Analytics** - Revenue reports, transaction metrics, payment breakdown analysis
- **Revenue Reports** - Daily, Weekly, Monthly, Yearly revenue insights
- **CSV Export** - Export reports and analytics data
- **System Audit Logs** - Track all system activities and changes

### Operator/Driver Features
- **Trip Management** - Schedule and manage bus routes
- **Passenger Lists** - View bookings and occupancy
- **Driver Profile** - Manage license and vehicle information

## 📁 Project Structure

```
SBTS/
├── index.html              # Main passenger-facing frontend application
├── admin.html              # Administrator dashboard (conceptual level)
├── api.php                 # PHP backend API for database operations
├── sbts_database.sql       # Complete database schema (20 tables)
├── extension.sql           # Additional database extensions
├── .gitignore              # Git ignore rules
└── README.md               # This file
```

## 🛠️ Technology Stack

**Frontend:**
- HTML5, CSS3, Vanilla JavaScript
- Glass-morphism UI design with CSS variables
- Local Storage for state management
- Single Page Application (SPA)

**Backend:**
- PHP 7.4+
- MySQL 5.7+
- RESTful API architecture
- Role-based access control

**Database:**
- 20 normalized relational tables
- Support for multiple bus operators/companies
- Unlimited user capacity
- Multi-payment method tracking

## 📋 Database Schema

### Core Tables
1. **users** - Base user account table
2. **passengers** - Extended passenger profiles with loyalty tracking
3. **operators** - Driver/operator management
4. **admins** - Administrator role assignments
5. **bus_operators_company** - Multi-company support

### Operations
6. **buses** - Vehicle management and tracking
7. **routes** - Route definitions and pricing
8. **stations** - Terminal and station management
9. **schedules** - Trip scheduling and occupancy tracking

### Bookings & Payments
10. **bookings** - Reservation management
11. **payment_methods** - Payment option configuration
12. **transactions** - Payment processing and history

### Analytics & Support
13. **ratings_reviews** - Passenger and driver feedback
14. **revenue_reports** - Business analytics data
15. **support_tickets** - Customer support tracking
16. **activity_logs** - System audit trail
17. **notifications** - User alerts and messages

### Additional
18. **promo_codes** - Promotional discounts
19. **maintenance_records** - Vehicle servicing
20. **monthly_revenue** - Monthly metrics (admin reports)

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser (Chrome, Firefox, Safari, Edge)
- Local server (Apache, Nginx, or PHP built-in server)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/NAKITA44/SMART-BUS-MANAGEMENT-SYSTEM.git
   cd SMART-BUS-MANAGEMENT-SYSTEM
   ```

2. **Set up the database:**
   ```bash
   mysql -u root < sbts_database.sql
   ```

3. **Configure PHP API:**
   - Open `api.php` and verify MySQL credentials:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = "";
     $dbname = "sbts_db";
     ```

4. **Run local server:**
   ```bash
   # Using PHP built-in server
   php -S localhost:8000
   
   # Or use Apache/Nginx with proper document root
   ```

5. **Access the application:**
   - **Passenger Dashboard:** `http://localhost:8000/index.html`
   - **Admin Dashboard:** `http://localhost:8000/admin.html`

## 👤 Default Test Accounts

> **Note:** Database comes empty for production testing. Create admin account through admin panel first.

### Admin Login
- **Email:** admin@sbts.zm
- **Password:** Admin@123
- **Role:** Administrator

## 🔐 Architecture & Access Control

### User Roles
1. **Admin** - Full system access, user management, analytics
2. **Operator** - Bus operator/company management
3. **Driver** - Trip and passenger management
4. **Passenger** - Booking and personal account management
5. **Support** - Customer support management

### Conceptual Level (Admin-Only)
The following features are restricted to **Admin role only** and marked as "Conceptual Level":

- ✅ User Management (Create, Edit, Delete users)
- ✅ Business Analytics & Reports
- ✅ Revenue Analysis
- ✅ System Audit Logs
- ✅ CSV Data Exports
- ✅ Promotional Management

These features are **not accessible** to regular passengers through the main UI.

## 📱 API Endpoints

### Base URL
```
POST http://localhost:8000/api.php
```

### Supported Actions

#### Get State
```json
{
  "action": "get_state"
}
```

#### Save State
```json
{
  "action": "save_state",
  "data": {
    "users": [],
    "buses": [],
    "bookings": [],
    "transactions": []
  }
}
```

## 🤝 Contributing

To contribute to SBTS:

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/your-feature-name`
3. **Make** your changes
4. **Commit:** `git commit -m "Add your feature description"`
5. **Push:** `git push origin feature/your-feature-name`
6. **Create** a Pull Request

### Contribution Guidelines
- Follow existing code style and structure
- Add comments for complex logic
- Test all changes before submitting PR
- Update documentation for new features
- Ensure admin restrictions are maintained

## 🐛 Reporting Issues

Found a bug? Please report it by creating an issue with:
- Clear title and description
- Steps to reproduce
- Expected vs. actual behavior
- Screenshots (if applicable)
- Your environment (OS, Browser, PHP version)

## 📝 License

SBTS is open-source software. See LICENSE file for details.

## 👥 Team

**Lead Developer:** NAKITA44

**Contributors:** [Add your name here when contributing]

## 📞 Support

For questions or support:
- Create an issue on GitHub
- Contact the development team
- Check documentation in code comments

## 🎯 Roadmap

### v1.0 (Current)
- ✅ Core passenger booking system
- ✅ Payment integration framework
- ✅ Admin dashboard
- ✅ Multi-operator support

### v1.1 (Planned)
- Mobile app (Flutter/React Native)
- Real-time notifications
- SMS integration
- Mobile money gateway integration

### v2.0 (Planned)
- GPS tracking
- Dynamic pricing
- Insurance integration
- Affiliate program

---

**Last Updated:** July 2026
**Version:** 1.0-alpha
**Status:** Active Development
