# Symfony Event Management API

A RESTful API built with Symfony for event booking system. Attendees will book the events. 

## Features
- Managing Events
  - Users should be able to create, update, delete, and list events.
-Managing Attendees
  - Users should be able to register attendees and manage their information.
- Booking System
  - Users should be able to book an event.
  - The system should prevent overbooking and duplicate bookings.
- Authentication & Authorization (Implementation not required, only mention how it would be structured)
  - Assume that API consumers must be authenticated to manage events.
  - Attendees should be able to register without authentication
---

##  Tech Stack

- PHP 8.2.12
- Symfony 5.11.0
- Doctrine ORM
- MySQL
- Symfony Validator
- Composer 2.2.6

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-username/symfony-event-api.git
cd symfony-event-api
```

### 2. Install the bundles

```bash
composer update
```

### 3. Import the DB and Test DB

```bash
Database is available in /DB folder
```

### 4. Update the .env and .env.test

```bash
.env - DATABASE_URL="mysql://root:@127.0.0.1:3306/symfony_event_booking?serverVersion=8.0&charset=utf8mb4"

.env.test - DATABASE_URL="mysql://root:@127.0.0.1:3306/symfony_event_booking_test?serverVersion=8.0"
```

### 5. Run the symfony server 

```bash
symfony serve
```

### 6. Run the tests

```bash
php bin/phpunit
```

## Authentication and Authorization

```
Authentication & Authorization Design:
 
 - API consumers managing events must authenticate using JWT tokens.
 - Attendees register for events without any authentication.
 - Access is controlled via Symfony security firewalls and access_control rules:
     - Public POST access to /api/events/{eventId}/attendees for attendee registration.
     - All /api/events endpoints require authenticated users with USER_ROLE.
 
 This separation ensures secure management of events while allowing open attendee registration.
 ```
