# Smart Bus Transport System

## Design Document

### 1. Introduction
This document presents the system design for the Smart Bus Transport System, a web-based application designed to manage bus routes, schedules, bookings, payments, and passenger travel records. The project supports multiple user roles including administrators, operators, and passengers.

The system is intended to provide a practical solution for intercity and regional bus operations. It enables staff to register buses and routes, assign schedules, and track seat bookings, while passengers can search trips and book journeys efficiently.

### 2. Objectives of the System
The design supports the following objectives:
- manage transport fleet information;
- organize route records and schedule planning;
- enable seat booking and payment capture;
- issue tickets and maintain travel history;
- provide role-based access to operational data.

### 3. Scope
The proposed system covers:
- user authentication and role-based access;
- bus registration and status management;
- route and district mapping;
- trip schedule creation;
- seat reservation and booking confirmation;
- payment tracking and ticket issuance;
- passenger travel history and records.

The system is focused on the business flow for a passenger transport company and is suitable for a small to medium transport operation.

### 4. Users and Roles

#### 4.1 Administrator
The administrator manages the overall platform and controls the core system records. Responsibilities include:
- registering and monitoring buses;
- creating and activating routes;
- managing trip schedules;
- reviewing bookings and operational information.

#### 4.2 Operator
The operator supports the daily running of the system by handling schedule setup and fleet visibility. This role works closely with routes and availability.

#### 4.3 Passenger
The passenger represents a customer who wants to:
- search available routes;
- choose a trip by date and destination;
- book a seat;
- complete payment;
- receive a ticket and view trip history.

### 5. System Architecture
The application follows a simple three-tier structure:

1. Presentation Layer
   - PHP pages and HTML forms
   - booking forms, login screens, dashboards, and tables
2. Application Layer
   - PHP business logic
   - validation, role checks, booking logic, session handling
3. Data Layer
   - MySQL database with relational tables for users, routes, buses, schedules, bookings, and payments

This structure is suitable for a standard server-side PHP project without requiring a full framework.

### 6. Functional Design

#### 6.1 Authentication Module
The system authenticates users through the users table. The login process validates the email and password, verifies whether the account is active, and loads the appropriate session value.

Key design features:
- hashed password storage;
- session-based authorization;
- redirection based on role;
- CSRF protection on forms.

#### 6.2 Bus Management Module
The bus module stores vehicle information such as:
- registration number;
- make and model;
- year;
- bus type;
- seating capacity;
- current status.

This module supports operational tracking and capacity planning.

#### 6.3 Route Management Module
A route is defined by:
- route code;
- origin and destination;
- origin and destination districts;
- distance and duration;
- fare;
- active/inactive status.

The route module supports schedule planning and trip search operations.

#### 6.4 Scheduling Module
The scheduling module links routes and buses to create a travel assignment. Each schedule contains:
- route_id;
- bus_id;
- travel_date;
- departure_time;
- arrival_time;
- fare;
- available_seats;
- status.

Validation ensures that the bus is available, the times are logical, and duplicate schedules are prevented.

#### 6.5 Booking and Payment Module
The booking module allows passengers to book seats for a schedule. Each booking includes:
- reference number;
- user_id;
- schedule_id;
- seat_number;
- amount;
- booking_status.

During booking, the system verifies seat availability and ensures that a seat cannot be double-booked. Payment records are created immediately as part of the same transaction flow.

#### 6.6 Ticketing Module
When a booking is confirmed, a ticket is generated with:
- ticket number;
- booking reference;
- QR code or equivalent ticket identifier;
- time issued.

This gives each passenger a confirmation of the trip booked.

### 7. Data Design
The database is organized around relational entities. The main tables are:

- users
- drivers
- buses
- routes
- schedules
- bookings
- payments
- tickets
- travel_history

#### 7.1 Core Relationships
- one user can have many bookings;
- one route can have many schedules;
- one bus can be assigned to many schedules;
- one schedule can have many bookings;
- one booking can have one payment;
- one booking can have one ticket;
- one passenger can have many travel_history records.

This design supports the complete lifecycle of booking, travel, and payment.

### 8. Interface Design
The interface is structured as a web application with forms and dashboard panels.

#### 8.1 Login and Registration Interface
The system provides a login form and a passenger registration form. It is designed to be simple and easy to use:
- email field;
- password field;
- quick account creation for new passengers;
- clear user feedback on validation errors.

#### 8.2 Admin Dashboard
The admin dashboard shows:
- route count;
- bus count;
- driver count;
- booking count;
- bus registration form;
- route creation form;
- schedule creation form.

#### 8.3 Passenger Dashboard
The passenger dashboard allows users to:
- search trips by origin, district, destination, and date;
- review available schedules;
- choose a seat and payment method;
- confirm booking;
- view travel history and generated ticket.

### 9. Security Design
The current system includes several security measures:
- session-based user tracking;
- hashed user passwords;
- CSRF token validation on state-changing requests;
- role checks before accessing restricted pages.

Recommended improvements for production use:
- stronger input sanitization and validation;
- encryption for sensitive data;
- logging of admin activities;
- integration with real payment gateways;
- better session security and rate limiting.

### 10. Non-Functional Requirements
The system should satisfy the following quality attributes:
- reliability for routine booking operations;
- usability for both staff and passengers;
- maintainability through modular code structure;
- scalability for additional routes and users;
- performance for standard daily operations.

### 11. Deployment Design
The system is designed for a standard web hosting environment with:
- Apache or Nginx web server;
- PHP runtime;
- MySQL database server;
- project files stored in a public web root.

The application is suitable for local deployment using XAMPP or a similar environment.

### 12. Risk and Limitation Analysis
The system is a strong prototype, but some limitations remain:
- no live external payment integration;
- limited analytics and reporting;
- basic UI and design styling;
- minimal automation and testing;
- no advanced monitoring and audit tools.

These limitations are manageable and can be addressed through later feature expansion.

### 13. Conclusion
The Smart Bus Transport System is designed as a practical, manageable, and functional web application for transport management and passenger booking. The architecture balances simplicity, maintainability, and operational usefulness.

The system successfully covers the main business workflow: users sign in, routes and buses are managed, trips are scheduled, passengers book seats, payments are recorded, and tickets are generated. It is a strong foundation for a small transport company or academic project and can be expanded into a more advanced production platform with additional reporting, integrations, and stronger security controls.
