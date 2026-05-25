# Darwin Art Company — Online Art Store

A database-driven e-commerce web application for a Darwin-based art studio, replacing their existing static website with a full browse-and-purchase experience.

Built for **HIT326 Server-Side Web Development**, Charles Darwin University, by Group 21.

**Repository:** <https://github.com/s371930/HIT326-Team-21>

---

## Features

- Browse artworks with category filtering
- Shopping cart (guest checkout — no account required)
- Order placement with email confirmation to buyer and seller
- Password-protected admin panel for managing products, news posts, and customer testimonials
- Testimonial moderation queue (admin approves before public display)
- Mobile-responsive frontend (Bootstrap 5)

## Tech Stack

- **Backend:** PHP 8.x
- **Database:** MySQL 8.x (via XAMPP)
- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
- **Architecture:** MVC (Model–View–Controller)
- **Security:** PDO prepared statements, bcrypt password hashing, session-based authentication, HTML output escaping

---

## Installation

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) 8.x (provides Apache, PHP, and MySQL)
- A modern web browser (Chrome, Firefox, Edge)
- Git

### 1. Clone the repository

Clone into your XAMPP `htdocs` folder so Apache can serve it:

\`\`\`bash
cd C:\xampp\htdocs\
git clone https://github.com/s371930/HIT326-Team-21.git darwin-art-store
\`\`\`

### 2. Start XAMPP services

Open the XAMPP Control Panel and start both **Apache** and **MySQL**.

### 3. Import the database

1. Open phpMyAdmin: <http://localhost/phpmyadmin>
2. Click the **Import** tab
3. Choose the file \`sql/create_load.sql\` from this project
4. Click **Go**

This creates the \`darwin_art\` database with all tables and seed data (10 sample products, a default admin user, and one approved testimonial).

### 4. Configure the application

1. In the project root, copy \`config.example.php\` to \`config.php\`
2. Open \`config.php\` and update the database credentials if your MySQL setup differs from the XAMPP defaults
   - Default XAMPP MySQL credentials: user \`root\`, empty password — these are already filled in for you

### 5. Open the application

Visit <http://localhost/darwin-art-store/> in your browser.

### Default Admin Login

| Field | Value |
|------|-------|
| Username | \`admin\` |
| Password | \`Admin@2025\` |

The default password should be changed after first login via the admin panel.

---

## Project Structure

\`\`\`
darwin-art-store/
├── config.example.php      Template configuration (commit)
├── config.php              Local configuration (gitignored)
├── index.php               Front controller / entry point
├── sql/
│   └── create_load.sql     Schema + seed data
├── app/
│   ├── core/               Database wrapper, auth helpers
│   ├── models/             Data layer (one class per table)
│   ├── controllers/        Request handlers
│   └── views/              Page templates
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
└── docs/
    └── erd.png             Entity-relationship diagram
\`\`\`

---

## Team — Group 21

| Member | Primary Responsibilities | Report Sections |
|--------|--------------------------|-----------------|
| Abdurrahman Aliyu | Database schema, SQL scripts, security layer (PDO wrapper, password hashing, config protection) | Installation, Database Tests, Security |
| Tithila Welihinda | Backend models, product catalogue, testimonials moderation | Working Code Description (shared), Database Tests support |
| Romik Gurung | Shopping cart, checkout controller, session logic | Application Tests, Working Code Description (shared) |
| Opeoluwa Adetayo | Email dispatch, admin panel CRUD | Overview, Project Approach |
| Kinley Wangmo | Frontend views, Bootstrap responsiveness, news feature | Strategy, Lessons Learned, Conclusion |

---

## Course Information

**Unit:** HIT326 Server-Side Web Development
**Institution:** Charles Darwin University
**Semester:** Semester 1, 2026
**Assignment:** Group Project (Option 2 — Darwin Art Company)