Okay, here are the updated Roadmap and Architecture documents incorporating the Member Signup flow with Admin Approval and specifying Tailwind CSS for the UI.

---

# JRFC Website Project Roadmap (Updated)

## 1. Project Overview

**Goal:** To build a community website for the JRFC group (70+ families) displaying member profiles, family details, and business information. The site will allow **members to sign up using a one-time code** and require **admin approval** before profiles are publicly visible. Includes an administrative interface for managing members, approvals, and codes.

**Target Audience:** Members of the JRFC group (including prospective signups) and an administrator.

**Core Features:**
* Homepage displaying all **approved** members in a card view (Photo, Name).
* Individual Member Profile pages showing detailed information (Personal, Family, Business, Photos) for **approved** members.
* **Public Signup Page:** Allows users with a valid one-time code to submit their profile information for review.
* **Admin Panel:**
    * Manage Members (Add Manually - Optional, Edit, Delete).
    * **Approve/Reject pending member signups.**
    * **Generate and manage one-time signup codes.**
* **UI:** Built using **Tailwind CSS (Latest Browser CDN Version)**.

**Technology Stack:**
* **Backend:** PHP
* **Database:** MySQL
* **Frontend:** HTML, **Tailwind CSS (v4 Browser CDN)**, potentially basic JavaScript.
* **Server Environment (Development):** Localhost (e.g., XAMPP, WAMP, MAMP)
* **Server Environment (Production):** To be determined (Web hosting provider with PHP/MySQL support)

---

## 2. Phases & Milestones

### Phase 1: Planning & Design (Estimate: 1-2 Weeks)

* **Goal:** Finalize requirements, design the database structure (including signup flow), create basic UI/UX mockups (using Tailwind concepts), and set up the environment.
* **Tasks:**
    * \[ ] **Requirement Finalization:**
        * Define *all* specific fields needed (Member, Family, Business).
        * Define the exact workflow for signup: Code Entry -> Form Fill -> Submit -> Pending Status -> Admin Review -> Approve/Reject -> Profile Visible/Removed.
        * Confirm data privacy considerations.
    * \[ ] **Database Design:**
        * Define table structures: `members`, `family_details`, `business_details`, `admin_users`, `one_time_codes`.
        * **Update `members` table:** Add a `status` column (e.g., `ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'`).
        * Define `one_time_codes` table: `code_id`, `code_value` (UNIQUE), `status` ('unused', 'used'), `created_at`, `used_at`, `member_id` (FK, NULLABLE - links to the member *after* successful submission, even if pending).
        * Specify column types, constraints, relationships.
        * **Tables Schema (Updated Draft):**
            * `members` (member_id PK, name, ..., `status` ENUM(...) DEFAULT 'pending')
            * `family_details` (...)
            * `business_details` (...)
            * `admin_users` (...)
            * `one_time_codes` (code_id PK, code_value UNIQUE, status ENUM('unused','used'), ..., member_id FK NULL)
    * \[ ] **UI/UX Design (Tailwind Focused):**
        * Create simple wireframes/mockups considering Tailwind's utility-first approach for:
            * Homepage (Card layout).
            * Member Profile Page.
            * **Public Signup Page (`register_family.php`)** (including code field).
            * **Signup Success / Pending Approval Page.**
            * Admin Login Page.
            * Admin Dashboard (Member List - Approved).
            * **Admin Pending Approvals Page (`admin/manage_pending.php`).**
            * **Admin Code Management Page (`admin/manage_codes.php`).**
            * Admin Edit Member Form.
    * \[ ] **Setup Development Environment:**
        * Install local server stack.
        * Set up code editor.
        * Initialize Git repository.
        * Create MySQL database/user.

---

### Phase 2: Backend Development (Estimate: 4-6 Weeks)

* **Goal:** Build server-side logic, database interactions, admin functions, signup/approval flow, and code management.
* **Tasks:**
    * \[ ] **Database Setup:**
        * Implement the **updated database schema** (SQL `CREATE TABLE`, `ALTER TABLE`).
        * Create secure PHP script/class for DB connection (PDO/MySQLi). **Store credentials securely.**
    * \[ ] **Core Member/Family/Business Logic (CRUD - Admin Focused):**
        * Functions for admin editing/deleting **approved** members.
        * Functions for fetching data for profile pages (only approved).
    * \[ ] **Signup & Approval Logic:**
        * Function `submitMemberProfile($data)`: Validates data, handles uploads, inserts into `members` with `status = 'pending'`, inserts related family/business data. Returns new `member_id`.
        * Function `getAllPendingMembers()`: Fetches members with `status = 'pending'`.
        * Function `approveMember($member_id)`: Updates member status to 'approved'.
        * Function `rejectMember($member_id)`: Updates member status to 'rejected' OR deletes the member record and associated data/files.
        * Modify `getAllMembers()`: Ensure it **only** fetches members with `status = 'approved'` for the public homepage.
    * \[ ] **One-Time Code Logic:**
        * Function `generateUniqueCode()`: Creates secure, unique code.
        * Function `storeCode($code)`: Saves code to DB as 'unused'.
        * Function `validateCode($code)`: Checks if code exists and is 'unused'. Returns code details (`code_id`) if valid.
        * Function `markCodeAsUsed($code_id, $member_id)`: Updates code status to 'used', sets `used_at`, links `member_id` (even if member is pending).
        * Functions for Admin code management (`getAllCodes`, `deleteCode` etc.).
    * \[ ] **Image Handling:**
        * Implement logic for uploading/storing images from both Admin and Public Signup forms.
        * Ensure rejected member uploads are deleted (if rejecting involves deletion).
    * \[ ] **Admin Authentication & Authorization:**
        * Implement admin login/logout/session management.
        * Use `auth_check.php` to protect all admin pages.
    * \[ ] **Data Validation:**
        * Implement robust server-side validation for **both Admin forms and the Public Signup form**.
    * \[ ] **Security:**
        * Prepared statements (PDO/MySQLi).
        * Input sanitization, output escaping (XSS prevention).
        * Password hashing (`password_hash()`).
        * CSRF protection (especially on public forms and admin actions).

---

### Phase 3: Frontend Development (Estimate: 4-5 Weeks)

* **Goal:** Build the user interface using **Tailwind CSS** and connect it to the backend logic.
* **Tasks:**
    * \[ ] **Setup Tailwind CSS:**
        * Include the **Tailwind CSS v4 Browser CDN script** in `includes/header.php`: `<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>`.
    * \[ ] **Basic HTML Structure & Layout (Tailwind):**
        * Set up base HTML templates (`header.php`, `footer.php`) using Tailwind classes for structure and default styling.
    * \[ ] **Component Styling (Tailwind):**
        * Style reusable components (buttons, cards, forms, tables) using Tailwind utility classes.
    * \[ ] **Homepage Implementation (Tailwind):**
        * Fetch **approved** members via PHP.
        * Display members in a responsive Card View styled with Tailwind.
        * Link cards to `profile.php?id=MEMBER_ID`.
    * \[ ] **Member Profile Page Implementation (Tailwind):**
        * Fetch specific **approved** member data.
        * Display details using Tailwind for layout and styling.
    * \[ ] **Public Signup Implementation (Tailwind):**
        * Create `register_family.php` page styled with Tailwind.
        * Build the form including the `one_time_code` field and all member/family/business fields.
        * Ensure form elements are styled correctly using Tailwind.
        * Create `signup_success.php` page with a "Pending Approval" message, styled with Tailwind.
    * \[ ] **Admin Interface Implementation (Tailwind):**
        * Style Admin Login (`login.php`).
        * Style Admin Dashboard (`admin/index.php`) displaying **approved** members (table/list styled with Tailwind).
        * Style Admin Edit Member Form (`admin/member_edit.php`) using Tailwind.
        * **Style Admin Pending Approvals Page (`admin/manage_pending.php`)**: Display pending members (table/list), include Approve/Reject buttons styled with Tailwind.
        * **Style Admin Code Management Page (`admin/manage_codes.php`)**: Form for generation, table for viewing codes, styled with Tailwind.
    * \[ ] **Responsiveness (Tailwind):**
        * Leverage Tailwind's built-in responsive design utilities (e.g., `md:`, `lg:`) to ensure adaptability.
    * \[ ] **Basic Interactivity (Optional JS):**
        * Add JS for confirmation dialogs (e.g., before deleting/rejecting).

---

### Phase 4: Integration & Testing (Estimate: 2-3 Weeks)

* **Goal:** Ensure all parts work together correctly, test thoroughly (including new flows), fix bugs.
* **Tasks:**
    * \[ ] **End-to-End Testing:**
        * **Signup Flow:** Test code validation (invalid/used/valid), form submission, pending status, approval, profile visibility, rejection.
        * Admin Flows: Login, View Approved, Edit, Delete, Manage Codes, Manage Pending Approvals.
        * Public Flow: View Homepage, View Profile.
    * \[ ] **Functionality Testing:**
        * Verify all CRUD operations (Admin & Public Submission).
        * Verify Approval/Rejection logic.
        * Verify Code Generation/Validation/Expiration.
        * Test image uploads/updates/deletions.
        * Test form validation (server-side) for all forms.
        * Test links and navigation.
    * \[ ] **Cross-Browser/Device Testing:**
        * Test on major browsers, ensuring Tailwind CSS renders correctly.
        * Test responsiveness on different screen sizes.
    * \[ ] **Security Testing:**
        * Check for SQLi, XSS, CSRF vulnerabilities.
        * Ensure admin areas and actions are protected.
        * Verify code validation logic prevents unauthorized submissions.
    * \[ ] **User Acceptance Testing (UAT):**
        * Have admin test all admin functions (especially approvals, code management).
        * Have representative members test the signup process with provided codes.
    * \[ ] **Bug Fixing:** Address all issues.

---

### Phase 5: Deployment (Estimate: 1 Week)

* **Goal:** Make the website live on a hosting server.
* **Tasks:** (Largely the same, ensure server supports PHP/MySQL)
    * \[ ] Choose Hosting Provider.
    * \[ ] Configure Production Environment (PHP, MySQL).
    * \[ ] Deploy Code.
    * \[ ] Configure Production DB Connection (**securely**).
    * \[ ] Migrate Data (Admin user, potentially pre-generated codes).
    * \[ ] Configure Domain Name.
    * \[ ] Final Live Testing (including signup with a test code).
    * \[ ] Setup Backups.

---

### Phase 6: Maintenance & Future Enhancements

* **Goal:** Ongoing upkeep and potential future improvements.
* **Tasks (Ongoing):** (Largely the same)
    * Monitor uptime/performance.
    * Apply security updates.
    * Regular backups.
    * Address bugs.
* **Potential Future Features (Post-Launch):**
    * Email notifications (pending approval, approved, new code generated).
    * Member Search/Filtering (for approved members).
    * Events Calendar.
    * Photo Gallery.
    * Admin Password Reset.
    * Allow *approved* members to log in and edit *their own* profiles.

---
---

# JRFC Website Architecture (Updated)

This architecture incorporates the public signup flow with one-time codes, admin approval, and specifies **Tailwind CSS** for the UI.

**I. Database Schema Highlights**

* **`members` Table:**
    * Includes `status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'`.
* **`one_time_codes` Table:**
    * `code_id`, `code_value` (UNIQUE), `status` ('unused', 'used'), `created_at`, `used_at`, `member_id` (FK, NULLABLE - linked after submission).

**II. Directory Structure (Updated)**

New files/directories are marked with `(+)`. Modified files indicated with `(M)`.

```
/jrfc-website/
│
├── index.php                 (M) # Homepage - Displays APPROVED members
├── profile.php               (M) # Member Profile - Displays details for APPROVED members
├── register_family.php       (+) # Public signup form (requires one-time code)
├── process_registration.php  (+) # Processes public signup, sets status to 'pending'
├── signup_success.php        (+) # Page shown after successful signup submission (pending approval message)
│
├── css/                      # (Potentially empty or minimal if using only Tailwind CDN)
│   └── style.css             # (Optional: For non-Tailwind custom styles if needed)
│
├── images/
│   └── logo.png
│
├── uploads/
│   ├── businesses/
│   ├── family/
│   └── members/
│
├── includes/
│   ├── config.php
│   ├── db_connect.php
│   ├── functions.php         (M) # Added/Modified functions for signup, approval, codes
│   ├── header.php            (M) # Added Tailwind CDN script, link to register_family.php
│   ├── footer.php
│   ├── admin_header.php      (M) # Added link to manage_pending.php, manage_codes.php (Optional)
│   ├── admin_footer.php      # (Optional)
│   └── auth_check.php
│
├── admin/
│   ├── index.php             (M) # Admin Dashboard - List APPROVED members, links to other admin pages
│   ├── login.php
│   ├── logout.php
│   ├── member_add.php        # (Optional: If admin needs to add members manually bypassing approval)
│   ├── member_edit.php       (M) # Form page to Edit an APPROVED member
│   ├── manage_codes.php      (+) # Admin page to generate/view/manage codes
│   ├── manage_pending.php    (+) # Admin page to view and approve/reject PENDING members
│   │
│   └── process/
│       ├── login_process.php
│       ├── member_add_process.php # (Optional: For manual admin additions)
│       ├── member_edit_process.php(M) # Processes edits for approved members
│       ├── member_delete_process.php(M) # Processes deletes for approved members
│       ├── generate_codes_process.php (+) # Processes code generation requests
│       ├── approve_member_process.php (+) # Processes member approval
│       └── reject_member_process.php  (+) # Processes member rejection
│
├── database.sql              (M) # Includes updated table schemas
├── .htaccess
└── ... (Other project files like roadmap.md)
```

**III. File Descriptions & Logic Flow (New & Modified Files)**

1.  **Root Directory (`/jrfc-website/`)**
    * `index.php` (M): Includes header/footer. Calls function (e.g., `getAllApprovedMembers()`) from `functions.php`. Displays members with `status = 'approved'` using **Tailwind CSS** cards.
    * `profile.php` (M): Includes header/footer. Retrieves `member_id`. Calls function (e.g., `getMemberDetails($member_id)`) but should ideally check if the status is 'approved' before displaying. Displays details using **Tailwind CSS**.
    * `register_family.php` (+): Public page. Includes header/footer. Displays a form styled with **Tailwind CSS**, requiring `one_time_code` and member/family/business details. Submits to `process_registration.php`.
    * `process_registration.php` (+): Handles POST from `register_family.php`. **Validates code** (`validateCode`), **validates/sanitizes data**. On success, calls `submitMemberProfile()` (inserts data with `status='pending'`), gets new `member_id`, calls `markCodeAsUsed()` linking code and `member_id`. Redirects to `signup_success.php`. Handles errors, redirects back to form with messages (using sessions). **Implement CSRF protection.**
    * `signup_success.php` (+): Simple page. Includes header/footer. Displays a "Submission successful, pending admin approval" message, styled with **Tailwind CSS**.

2.  **`includes/` Directory**
    * `functions.php` (M):
        * **Added:** `getAllApprovedMembers()`, `submitMemberProfile()`, `getAllPendingMembers()`, `approveMember()`, `rejectMember()`, `generateUniqueCode()`, `storeCode()`, `validateCode()`, `markCodeAsUsed()`, `getAllCodes()`, etc.
        * Ensure all DB functions use prepared statements.
        * Include robust validation functions.
    * `header.php` (M):
        * **Added Tailwind CSS v4 Browser CDN:** `<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>` within the `<head>`.
        * **Added link** in navigation to `register_family.php`.
    * `admin_header.php` (M): (If used) **Add links** to `admin/manage_pending.php` and `admin/manage_codes.php`.

3.  **`admin/` Directory**
    * `index.php` (M): Includes `auth_check.php`, header/footer. Displays list/table of **approved** members. Links to `manage_pending.php`, `manage_codes.php`, `member_edit.php`, etc. Styled with **Tailwind CSS**.
    * `member_edit.php` (M): Form to edit details of an **approved** member. Styled with **Tailwind CSS**.
    * `manage_codes.php` (+): Includes `auth_check.php`, header/footer. Form to generate codes (submits to `generate_codes_process.php`). Table displaying codes (status, value, etc.). Styled with **Tailwind CSS**.
    * `manage_pending.php` (+): Includes `auth_check.php`, header/footer. Calls `getAllPendingMembers()`. Displays pending members in a table/list with key details. Provides "Approve" and "Reject" buttons/links for each, pointing to respective processing scripts (`approve_member_process.php?id=X`, `reject_member_process.php?id=X`). Styled with **Tailwind CSS**.

4.  **`admin/process/` Directory**
    * `member_edit_process.php` (M): Handles editing of **approved** members.
    * `member_delete_process.php` (M): Handles deletion of **approved** members (and associated data/files). **Implement CSRF protection.**
    * `generate_codes_process.php` (+): Handles POST from `manage_codes.php`. Generates requested number of unique codes using functions, stores them, redirects back.
    * `approve_member_process.php` (+): Includes `auth_check.php`. Takes `member_id` via GET/POST. Calls `approveMember($member_id)`. Redirects back to `manage_pending.php` with success/error message. **Implement CSRF protection.**
    * `reject_member_process.php` (+): Includes `auth_check.php`. Takes `member_id` via GET/POST. Calls `rejectMember($member_id)` (updates status or deletes). Redirects back to `manage_pending.php` with success/error message. **Implement CSRF protection.**

