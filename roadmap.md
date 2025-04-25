# JRFC Website Project Roadmap

## 1. Project Overview

**Goal:** To build a community website for the JRFC group (70+ families) displaying member profiles, family details, and business information. The site will also include an administrative interface for managing member data.

**Target Audience:** Members of the JRFC group and an administrator.

**Core Features:**
* Homepage displaying all members in a card view (Photo, Name).
* Individual Member Profile pages showing detailed information (Personal, Family, Business, Photos).
* Admin panel for Adding, Editing, and Deleting member and family details.

**Technology Stack:**
* **Backend:** PHP
* **Database:** MySQL
* **Frontend:** HTML, CSS, potentially basic JavaScript (optional, for enhancements)
* **Server Environment (Development):** Localhost (e.g., XAMPP, WAMP, MAMP)
* **Server Environment (Production):** To be determined (Web hosting provider with PHP/MySQL support)

---

## 2. Phases & Milestones

### Phase 1: Planning & Design (Estimate: 1-2 Weeks)

* **Goal:** Finalize requirements, design the database structure, and create basic UI/UX mockups.
* **Tasks:**
    * [ ] **Requirement Finalization:**
        * Define *all* specific fields needed for:
            * Member Profile (e.g., Full Name, DOB, Photo, Contact Number, Email, Address)
            * Family Details (e.g., Spouse Name, Spouse Photo, Children Names/DOB/Photos)
            * Business Details (e.g., Business Name, Description, Logo, Website, Address)
        * Confirm data privacy considerations.
    * [ ] **Database Design:**
        * Define table structures (e.g., `members`, `family_members`, `businesses`, `admin_users`).
        * Specify column types, constraints (e.g., `PRIMARY KEY`, `FOREIGN KEY`, `NOT NULL`).
        * Define relationships between tables (e.g., one-to-many between `members` and `family_members`).
        * **Tables Schema (Initial Draft):**
            * `members` (member_id PK, name, photo_url, dob, contact, email, address, ...)
            * `family_details` (family_id PK, member_id FK, relation_type ENUM('spouse', 'child'), name, photo_url, dob, ...)
            * `business_details` (business_id PK, member_id FK, business_name, description, logo_url, website, ...)
            * `admin_users` (admin_id PK, username, password_hash, last_login)
    * [ ] **UI/UX Design:**
        * Create simple wireframes or mockups for:
            * Homepage (Card layout).
            * Member Profile Page.
            * Admin Login Page.
            * Admin Dashboard (Member List).
            * Admin Add/Edit Member Form.
    * [ ] **Setup Development Environment:**
        * Install local server stack (XAMPP/WAMP/MAMP).
        * Set up code editor (e.g., VS Code).
        * Initialize Git repository for version control.
        * Create the MySQL database (`u311361742_jrfc`) and user (`u311361742_jrfc`) locally. **Use a strong, unique password locally and DO NOT use the one mentioned in the prompt.**

---

### Phase 2: Backend Development (Estimate: 3-5 Weeks)

* **Goal:** Build the core server-side logic, database interactions, and admin functionalities.
* **Tasks:**
    * [ ] **Database Setup:**
        * Implement the designed database schema using SQL `CREATE TABLE` statements.
        * Create a secure PHP script/class for database connection (using PDO or MySQLi). **Store credentials securely (e.g., environment variables, separate config file outside web root), not directly in code.**
    * [ ] **Member Data Logic (CRUD):**
        * PHP functions/classes for:
            * Fetching all members for the homepage.
            * Fetching a specific member's details (including family, business) for the profile page.
            * Adding a new member.
            * Updating an existing member.
            * Deleting a member (and associated data like family/business).
    * [ ] **Family & Business Data Logic (CRUD):**
        * PHP functions/classes to manage related family and business details when adding/editing/deleting a member.
    * [ ] **Image Handling:**
        * Implement server-side logic for uploading photos (member, family, business logo).
        * Handle image storage (e.g., in an `uploads/` directory).
        * Implement image resizing/validation if necessary.
    * [ ] **Admin Authentication:**
        * Create Admin Login page logic (verify username/password hash).
        * Implement session management for logged-in admins.
        * Create Logout functionality.
        * Protect admin pages/actions, ensuring only logged-in admins can access them.
    * [ ] **Data Validation:**
        * Implement robust server-side validation for all data submitted via admin forms.
    * [ ] **Security:**
        * Use prepared statements (PDO/MySQLi) to prevent SQL injection.
        * Sanitize user input and escape output (prevent XSS).
        * Implement password hashing (e.g., `password_hash()`, `password_verify()`).

---

### Phase 3: Frontend Development (Estimate: 3-4 Weeks)

* **Goal:** Build the user interface based on the designs and connect it to the backend logic.
* **Tasks:**
    * [ ] **Basic HTML Structure & CSS Styling:**
        * Set up base HTML templates (header, footer).
        * Apply CSS for overall layout, typography, and color scheme.
    * [ ] **Homepage Implementation:**
        * Create PHP script to fetch all members (from backend functions).
        * Loop through members and display them in a Card View (HTML/CSS).
        * Link each card to the respective member profile page (e.g., `profile.php?id=MEMBER_ID`).
    * [ ] **Member Profile Page Implementation:**
        * Create `profile.php` script.
        * Get `member_id` from the URL (`$_GET['id']`).
        * Fetch specific member data (personal, family, business) using the ID.
        * Display the fetched data using HTML/CSS.
    * [ ] **Admin Interface Implementation:**
        * Create Admin Login page (HTML form).
        * Create Admin Dashboard (`admin/index.php`):
            * Display a list/table of all members.
            * Include links/buttons for Add, Edit, Delete actions.
        * Create Admin Add/Edit Member Form (`admin/edit_member.php`):
            * Build the form with all necessary fields (member, family, business).
            * Pre-populate the form with existing data when editing.
            * Handle form submission (POST request to a PHP script).
            * Include file input fields for photo uploads.
    * [ ] **Responsiveness:**
        * Ensure the website layout adapts well to different screen sizes (desktops, tablets, mobiles) using CSS media queries.
    * [ ] **Basic Interactivity (Optional):**
        * Add minor JavaScript for things like confirmation dialogs before deleting.

---

### Phase 4: Integration & Testing (Estimate: 2-3 Weeks)

* **Goal:** Ensure all parts work together correctly, test thoroughly, and fix bugs.
* **Tasks:**
    * [ ] **End-to-End Testing:**
        * Test the complete user flow (View homepage -> Click member -> View profile).
        * Test the complete admin flow (Login -> View list -> Add member -> Edit member -> Delete member -> Logout).
    * [ ] **Functionality Testing:**
        * Verify all CRUD operations work as expected.
        * Test image uploads, display, and updates.
        * Test form submissions and validation logic (valid and invalid data).
        * Ensure links and navigation work correctly.
    * [ ] **Cross-Browser/Device Testing:**
        * Test the website on major browsers (Chrome, Firefox, Safari, Edge).
        * Test on different device sizes (desktop, tablet, mobile).
    * [ ] **Security Testing:**
        * Check for common vulnerabilities (SQLi, XSS).
        * Ensure admin areas are properly protected.
    * [ ] **User Acceptance Testing (UAT):**
        * Have the designated admin and a few representative JRFC members test the site and provide feedback.
    * [ ] **Bug Fixing:**
        * Address all issues found during testing.

---

### Phase 5: Deployment (Estimate: 1 Week)

* **Goal:** Make the website live on a hosting server.
* **Tasks:**
    * [ ] **Choose Hosting Provider:** Select a provider that supports PHP and MySQL.
    * [ ] **Configure Production Environment:**
        * Set up PHP and MySQL on the server.
        * Create the production database and user. **Use a new, strong, unique password.**
    * [ ] **Deploy Code:** Upload website files (using FTP, Git, or hosting panel tools).
    * [ ] **Configure Production Database Connection:** Update the connection script/configuration file with production database credentials (**store securely!**).
    * [ ] **Migrate Data:** Import any necessary initial data (e.g., admin user).
    * [ ] **Configure Domain Name (if applicable):** Point DNS records to the hosting server.
    * [ ] **Final Live Testing:** Perform smoke tests on the live site to ensure core functionalities work.
    * [ ] **Setup Backups:** Configure regular automatic backups (files and database).

---

### Phase 6: Maintenance & Future Enhancements

* **Goal:** Ongoing upkeep and potential future improvements.
* **Tasks (Ongoing):**
    * Monitor website uptime and performance.
    * Apply security updates (PHP, MySQL, Server OS).
    * Regularly backup data.
    * Address any bugs reported by users.
* **Potential Future Features (Post-Launch):**
    * Member Search/Filtering functionality on the homepage.
    * Events Calendar for JRFC group activities.
    * Photo Gallery section.
    * Password Reset functionality for the admin.
    * Member login (if members need to edit their *own* basic info in the future).
    * Simple announcement board.

---

Okay, here's a detailed architecture proposal for the pages and files of your JRFC website, keeping the PHP + MySQL stack in mind. This structure aims for clarity, maintainability, and basic separation of concerns.

**I. Directory Structure**

```
/jrfc-website/
│
├── index.php             # Homepage - Displays member cards
├── profile.php           # Member Profile Page - Displays individual details
│
├── css/                  # CSS Stylesheets
│   └── style.css         # Main stylesheet
│   └── admin-style.css   # Styles specific to the admin area (optional)
│
├── images/               # Static images (logo, icons, etc.)
│   └── logo.png
│
├── uploads/              # Directory for user-uploaded images (members, family, business)
│   │                     # IMPORTANT: Ensure this directory has correct write permissions for the web server
│   └── members/          # Subdirectory for member photos (optional)
│   └── family/           # Subdirectory for family photos (optional)
│   └── businesses/       # Subdirectory for business logos (optional)
│
├── includes/             # Reusable PHP files (core logic, templates)
│   ├── config.php        # Database connection details & site settings (USE ENV VARS FOR SECRETS!)
│   ├── db_connect.php    # Script to establish MySQL connection using config details
│   ├── functions.php     # Core functions (fetch data, validation, image handling, etc.)
│   ├── header.php        # HTML Head, Navigation (shared by public pages)
│   ├── footer.php        # HTML Footer (shared by public pages)
│   ├── admin_header.php  # Header specific to admin pages (optional, if different layout needed)
│   ├── admin_footer.php  # Footer specific to admin pages (optional)
│   └── auth_check.php    # Checks if admin is logged in, redirects if not
│
├── admin/                # Admin Area
│   ├── index.php         # Admin Dashboard - List members, links to Add/Edit/Delete
│   ├── login.php         # Admin Login Form Page
│   ├── logout.php        # Handles Admin Logout action
│   ├── member_add.php    # Form page to Add a new member
│   ├── member_edit.php   # Form page to Edit an existing member
│   │
│   └── process/          # Scripts to handle form submissions (no HTML output, just logic & redirect)
│       ├── login_process.php    # Processes login form submission
│       ├── member_add_process.php # Processes new member form submission
│       ├── member_edit_process.php # Processes edit member form submission
│       └── member_delete_process.php # Processes delete member request
│
└── .htaccess             # Optional: For URL rewriting, security rules (e.g., block access to /includes)
```

**II. File Descriptions & Logic Flow**

1.  **Root Directory (`/jrfc-website/`)**
    * **`index.php` (Homepage)**
        * Includes `includes/config.php`, `includes/db_connect.php`, `includes/functions.php`.
        * Includes `includes/header.php`.
        * Calls a function (e.g., `getAllMembers()` from `functions.php`) to fetch all member data (name, photo) from the database.
        * Loops through the member data and generates HTML for each member card.
        * Each card links to `profile.php?id=[MEMBER_ID]`.
        * Includes `includes/footer.php`.
    * **`profile.php` (Member Profile)**
        * Includes `includes/config.php`, `includes/db_connect.php`, `includes/functions.php`.
        * Includes `includes/header.php`.
        * Retrieves `member_id` from the URL query string (`$_GET['id']`). Validates the ID.
        * Calls a function (e.g., `getMemberDetails($member_id)` from `functions.php`) to fetch all details for that specific member (personal, family, business) from the database (using JOINs).
        * Displays the fetched details using HTML.
        * Includes `includes/footer.php`.

2.  **`css/` Directory**
    * **`style.css`**: Contains all CSS rules for the public-facing pages (homepage, profile). Defines layout, card styles, typography, colors, responsiveness.
    * **`admin-style.css` (Optional)**: Specific styles for the admin section if it needs a different look and feel (tables, forms, etc.).

3.  **`images/` Directory**
    * Stores static assets like the JRFC logo, background images, icons, etc., used in the CSS or HTML directly.

4.  **`uploads/` Directory**
    * **Crucial:** This directory MUST be writable by the web server process (e.g., Apache, Nginx user) for PHP's `move_uploaded_file()` function to work. Permissions like `755` or `775` (depending on server setup) are often needed, but be cautious with `777`.
    * Stores all dynamic images uploaded via the admin panel. Subdirectories (`members/`, `family/`, etc.) help organize files.
    * PHP scripts in `admin/process/` will handle moving uploaded files here and storing the file path/name in the database.

5.  **`includes/` Directory**
    * **`config.php`**: **SECURITY:** Defines constants or variables for database connection (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`). **STRONGLY RECOMMENDED:** Load these from environment variables instead of hardcoding, especially the password. Example: `define('DB_PASS', getenv('JRFC_DB_PASSWORD'));`. Also can contain site-wide settings.
    * **`db_connect.php`**: Uses values from `config.php` to establish a database connection (using PDO or MySQLi). Stores the connection object in a variable (e.g., `$pdo` or `$conn`) for use by other scripts. Includes error handling for connection failures.
    * **`functions.php`**: Contains reusable PHP functions:
        * Database query functions (e.g., `getAllMembers()`, `getMemberDetails($id)`, `addMember($data)`, `updateMember($id, $data)`, `deleteMember($id)`, `getFamilyDetails($member_id)`, etc.). These functions should use the database connection object from `db_connect.php`. **Use prepared statements** for all queries involving user input to prevent SQL injection.
        * Input validation functions (e.g., `sanitizeInput($input)`, `validateEmail($email)`).
        * Image upload handling function (checks type, size, moves file, generates unique name).
        * Authentication functions (e.g., `checkAdminLogin()`, `hashPassword($password)`, `verifyPassword($password, $hash)`).
        * Helper functions (e.g., formatting dates, generating HTML snippets).
    * **`header.php` / `footer.php`**: Contain the common HTML `<head>` section (including CSS links), navigation bar, and the closing `</body>`, `</html>` tags plus any footer content/scripts. Included at the beginning/end of public pages (`index.php`, `profile.php`).
    * **`admin_header.php` / `admin_footer.php` (Optional)**: Similar to public ones but potentially with different navigation (e.g., Admin Menu) or styles. Used within the `admin/` pages.
    * **`auth_check.php`**:
        * Starts the session (`session_start()`).
        * Checks if an admin session variable (e.g., `$_SESSION['admin_logged_in']`) is set and true.
        * If not logged in, redirects the user to `admin/login.php`.
        * This script should be included at the *very top* of every page within the `admin/` directory (except `login.php` and `process/login_process.php`).

6.  **`admin/` Directory**
    * **`index.php` (Admin Dashboard)**
        * Includes `../includes/auth_check.php` (note the `../`).
        * Includes `../includes/config.php`, `../includes/db_connect.php`, `../includes/functions.php`.
        * Includes `../includes/admin_header.php` (or `../includes/header.php`).
        * Fetches all members using `getAllMembers()`.
        * Displays members in an HTML table with "Edit" and "Delete" links/buttons.
        * Provides a link/button to "Add New Member" (`member_add.php`).
        * Includes `../includes/admin_footer.php` (or `../includes/footer.php`).
    * **`login.php`**
        * Starts session (`session_start()`). If already logged in, redirect to `admin/index.php`.
        * Includes `../includes/admin_header.php` (or a simpler login header).
        * Displays an HTML form with "Username" and "Password" fields, submitting to `process/login_process.php`.
        * Includes `../includes/admin_footer.php`.
    * **`logout.php`**
        * Starts session (`session_start()`).
        * Unsets session variables (`unset($_SESSION['admin_logged_in']`, `unset($_SESSION['admin_username'])`).
        * Destroys the session (`session_destroy()`).
        * Redirects to `login.php`.
    * **`member_add.php`**
        * Includes `../includes/auth_check.php`.
        * Includes `../includes/config.php`, `../includes/db_connect.php`, `../includes/functions.php`.
        * Includes `../includes/admin_header.php`.
        * Displays an HTML form (`<form action="process/member_add_process.php" method="POST" enctype="multipart/form-data">`) with fields for all member, family, and business details. Includes file inputs for photos.
        * Includes `../includes/admin_footer.php`.
    * **`member_edit.php`**
        * Includes `../includes/auth_check.php`.
        * Includes `../includes/config.php`, `../includes/db_connect.php`, `../includes/functions.php`.
        * Includes `../includes/admin_header.php`.
        * Retrieves `member_id` from URL (`$_GET['id']`). Validates ID.
        * Fetches existing data for this member using `getMemberDetails($member_id)`.
        * Displays the same HTML form as `member_add.php` but pre-populates fields with fetched data. Includes a hidden input field for `member_id`. Sets form action to `process/member_edit_process.php`.
        * Includes `../includes/admin_footer.php`.

7.  **`admin/process/` Directory** (Scripts handling form logic)
    * **General Pattern:**
        * Check if the request method is POST.
        * Start session (`session_start()`).
        * Include necessary files (`../includes/config.php`, `../includes/db_connect.php`, `../includes/functions.php`).
        * **Validate and Sanitize ALL `$_POST` and `$_FILES` data.** This is critical for security.
        * Perform the required action (database query, file upload) using functions from `functions.php`.
        * Handle potential errors (database errors, validation errors, upload errors). Store error messages in the session to display back on the form page if needed.
        * Redirect back to an appropriate page (e.g., `admin/index.php` on success, back to the form `admin/member_add.php` on validation error) using `header('Location: ...'); exit;`.
    * **`login_process.php`**: Verifies credentials against the `admin_users` table (using `password_verify()`). If valid, sets session variables (`$_SESSION['admin_logged_in'] = true;`) and redirects to `admin/index.php`. If invalid, redirects back to `login.php` with an error message.
    * **`member_add_process.php`**: Handles image uploads, inserts data into `members`, `family_details`, `business_details` tables.
    * **`member_edit_process.php`**: Handles image uploads (potentially replacing old ones), updates data in relevant tables based on the submitted `member_id`.
    * **`member_delete_process.php`**: Usually triggered by a GET request with an ID (e.g., `member_delete_process.php?id=X`). **Crucial:** Implement CSRF protection if using GET for deletion, or preferably use POST via a small form/button. Deletes records from relevant tables based on `member_id`. Also, delete associated uploaded files from the `uploads/` directory.

8.  **`.htaccess` (Optional, for Apache servers)**
    * Can be used for:
        * **Clean URLs:** Rewriting `profile.php?id=123` to `/profile/123` (requires `mod_rewrite`).
        * **Security:** Blocking direct web access to directories like `includes/` or `config.php`.
            ```apache
            <Files config.php>
                Order allow,deny
                Deny from all
            </Files>
            <IfModule mod_rewrite.c>
                RewriteEngine On
                RewriteRule ^includes/ - [F,L]
            </IfModule>
            ```
        * Forcing HTTPS.
        * Setting custom error pages.

