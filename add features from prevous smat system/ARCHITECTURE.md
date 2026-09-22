# SBMS Architecture & System Design

## 📐 System Overview

The Smart Bus Management System (SBMS) is a three-tier architecture designed for scalability, security, and team collaboration.

```
┌─────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                       │
│  ┌──────────────────────┐          ┌──────────────────────┐  │
│  │   index.html         │          │   admin.html         │  │
│  │  (Passenger UI)      │          │   (Admin Level)      │  │
│  │ - Booking System     │          │ - User Management    │  │
│  │ - My Bookings        │          │ - Analytics/Reports  │  │
│  │ - Payment            │          │ - System Settings    │  │
│  │ - Support Tickets    │          │ - Audit Logs         │  │
│  └──────────────────────┘          └──────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                           │
                (HTTP POST / JSON)
                           │
┌─────────────────────────────────────────────────────────────┐
│                    BUSINESS LOGIC LAYER                      │
│  ┌────────────────────────────────────────────────────────┐ │
│  │              api.php (PHP Backend)                     │ │
│  │  - Request Validation                                 │ │
│  │  - User Authentication                                │ │
│  │  - Business Logic Processing                          │ │
│  │  - Error Handling                                     │ │
│  │  - CORS Management                                    │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                           │
                  (MySQLi Queries)
                           │
┌─────────────────────────────────────────────────────────────┐
│                     DATA ACCESS LAYER                        │
│  ┌────────────────────────────────────────────────────────┐ │
│  │          MySQL Database (sbts_db)                      │ │
│  │  20 Normalized Tables with Relationships             │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

## 🎯 Two-Interface Architecture

SBTS implements a **dual-interface design** separating user concerns:

### 1. **Passenger Interface** (`index.html`)
**Purpose:** Customer-facing application for booking and travel management

**Accessible To:**
- Passengers (regular users)
- Drivers/Operators (view their operations)
- Support Staff (assist passengers)

**Key Features:**
- 🎫 Bus Search & Booking
- 💳 Payment Processing
- 📱 Mobile-Friendly Design
- ⭐ Ratings & Reviews
- 🎁 Loyalty Program
- 📞 Support Ticketing
- 📍 Real-time Tracking

**Access Level:** Public (limited data visibility)

### 2. **Admin/Conceptual Level Interface** (`admin.html`)
**Purpose:** Administrative operations and business intelligence

**Accessible To:**
- Administrators only
- System-level operations only

**Key Features:**
- 👥 User Management (Create/Edit/Delete)
- 📊 Revenue Analytics & Reports
- 💹 Business Intelligence
- 🗂️ System Configuration
- 📋 Audit Logs & Activity Tracking
- 📥 CSV Export Functionality
- 🔧 Database Maintenance

**Access Level:** Restricted to Admin role (complete data visibility)

## 🔐 Security Architecture

### Role-Based Access Control (RBAC)

```
┌─────────────────────────────────────────────┐
│  ROLE HIERARCHY                             │
├─────────────────────────────────────────────┤
│ Admin                                       │
│ ├─ Full System Access                       │
│ ├─ User Management                          │
│ ├─ Business Reports                         │
│ └─ System Configuration                     │
├─────────────────────────────────────────────┤
│ Operator (Bus Company)                      │
│ ├─ Fleet Management                         │
│ ├─ Route Management                         │
│ ├─ Driver Management                        │
│ └─ Company Reports                          │
├─────────────────────────────────────────────┤
│ Driver/Operator                             │
│ ├─ Trip Management                          │
│ ├─ Passenger Lists                          │
│ ├─ Profile Management                       │
│ └─ Earnings Tracking                        │
├─────────────────────────────────────────────┤
│ Passenger                                   │
│ ├─ Booking Management                       │
│ ├─ Payment Processing                       │
│ ├─ Support Tickets                          │
│ └─ Personal Account                         │
├─────────────────────────────────────────────┤
│ Support Staff                               │
│ ├─ Ticket Management                        │
│ ├─ User Support                             │
│ ├─ Issue Resolution                         │
│ └─ Limited Reports                          │
└─────────────────────────────────────────────┘
```

### Implementation Pattern

All admin-restricted functions follow this pattern:

```javascript
function adminRestrictedOperation() {
  // Step 1: Role Check
  if (state.currentUser.role !== 'Admin') {
    showNotification('This feature is restricted to administrators only.', 'warning');
    return;
  }
  
  // Step 2: Proceed with operation
  // ... function logic ...
}
```

## 📦 Database Schema (20 Tables)

### User Management (5 tables)
```
users ─────────────┐
                   ├─→ passengers
admin ─────────────┤
                   ├─→ operators
                   └─→ bus_operators_company
```

### Operations (4 tables)
```
buses → schedules → bookings
         routes ─→ stations
```

### Payments (3 tables)
```
bookings → transactions → payment_methods
```

### Analytics (4 tables)
```
transactions → revenue_reports
ratings_reviews → promo_codes
activity_logs
```

### Support (2 tables)
```
support_tickets
notifications
```

### Maintenance (2 tables)
```
maintenance_records
monthly_revenue
```

## 🔄 Data Flow

### Booking Flow

```
1. Passenger Searches Routes
   ↓
2. System Queries Available Schedules
   ↓
3. Passenger Selects Bus & Seats
   ↓
4. System Creates Booking Record
   ↓
5. Passenger Initiates Payment
   ↓
6. Payment Gateway Processing
   ↓
7. Transaction Created
   ↓
8. Booking Status Updated to "Confirmed"
   ↓
9. Notification Sent to Passenger & Driver
   ↓
10. Receipt Generated
```

### Admin Analytics Flow

```
1. Admin Accesses Dashboard (admin.html)
   ↓
2. System Validates Admin Role
   ↓
3. Admin Selects Report Period (Daily/Weekly/Monthly/Yearly)
   ↓
4. System Queries revenue_reports table
   ↓
5. Analytics Engine Calculates Metrics
   ↓
6. Dashboard Updates with Charts & Stats
   ↓
7. Admin Can Export to CSV
```

## 🗄️ API Endpoints

### Base Configuration
```
Protocol: HTTP/HTTPS
Host: localhost (development) / production-server (production)
Port: 8000 (development) / 443 (production)
Endpoint: /api.php
Method: POST
Content-Type: application/json
```

### Supported Actions

#### 1. Get State
```json
{
  "action": "get_state"
}
```
**Returns:** Complete system state with all entities

#### 2. Save State
```json
{
  "action": "save_state",
  "data": {
    "users": [...],
    "buses": [...],
    "bookings": [...],
    "transactions": [...]
  }
}
```
**Returns:** Confirmation and updated state

## 🎯 Development Workflow

### For Passenger Features
1. Edit UI in `index.html`
2. Test locally in browser
3. Add database persistence in `api.php`
4. Test with `sbts_database.sql` schema
5. Create Pull Request
6. Merge to main after review

### For Admin Features
1. Edit UI in `admin.html`
2. Test role restrictions
3. Verify audit logging
4. Test CSV exports
5. Create Pull Request
6. Merge to main after review

### For Database Changes
1. Update schema in `sbts_database.sql`
2. Test data migrations
3. Update api.php if needed
4. Document schema changes
5. Create Pull Request

## 📱 Client-Side State Management

### State Structure
```javascript
state = {
  currentUser: {
    id, email, name, role, status
  },
  users: [],
  passengers: [],
  operators: [],
  buses: [],
  routes: [],
  schedules: [],
  bookings: [],
  transactions: [],
  ratings: [],
  support_tickets: [],
  notifications: []
}
```

### Storage Options
- **localStorage** (current) - For development
- **PHP Sessions** (recommended) - For production
- **JWT Tokens** (future) - For scalability

## 🚀 Performance Optimization

### Caching Strategy
- Cache route/schedule data (updated hourly)
- Cache payment methods (updated daily)
- Cache admin stats (updated per request)

### Database Optimization
- Index frequently queried columns
- Use pagination for large result sets
- Implement query caching
- Archive old transaction records

## 🔄 CI/CD Pipeline (Future)

```
1. Developer Pushes to GitHub
   ↓
2. GitHub Actions Runs Tests
   ↓
3. Lint Check (JavaScript/PHP)
   ↓
4. Unit Tests
   ↓
5. Integration Tests
   ↓
6. If All Pass → Deploy to Staging
   ↓
7. Manual Testing on Staging
   ↓
8. Deploy to Production
```

## 📊 Monitoring & Logging

### Log Levels
- `ERROR` - Critical failures
- `WARNING` - Potential issues
- `INFO` - Normal operations
- `DEBUG` - Development information

### Audit Logging
All admin operations logged:
- User created/modified/deleted
- Reports generated
- Settings changed
- Exports performed
- System maintenance

## 🎓 Architecture Principles

1. **Separation of Concerns** - UI, Business Logic, Data Access layers
2. **Role-Based Security** - Access controlled by user role
3. **Data Integrity** - Normalized schema with foreign keys
4. **Scalability** - Support for multiple companies/operators
5. **Auditability** - Complete activity logging
6. **Maintainability** - Clear code structure and documentation

## 📚 File Organization

```
SBTS/
├── index.html              # Passenger interface (3000+ lines)
├── admin.html              # Admin interface (1000+ lines)
├── api.php                 # Backend API (500+ lines)
├── sbts_database.sql       # Database schema
├── README.md               # Project documentation
├── CONTRIBUTING.md         # Contributor guidelines
├── GITHUB_SETUP.md         # GitHub setup instructions
├── ARCHITECTURE.md         # This file
├── .gitignore              # Git ignore rules
└── .git/                   # Git repository
```

---

**Version:** 1.0-alpha
**Last Updated:** July 2026
**Repository:** https://github.com/NAKITA44/SMART-BUS-MANAGEMENT-SYSTEM
**Maintained By:** NAKITA44 & Contributors
