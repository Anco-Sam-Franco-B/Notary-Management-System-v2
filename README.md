# Notary-Management-System-v2

Notary Management System (v2) is a lightweight PHP web application to manage documents and notary agreements between buyers and sellers. It provides a web UI to upload or capture documents (PDF/images), view document details, add agreement metadata, and generate/export PDF content using the included FPDF library.

This README was generated from an analysis of the repository contents (PHP pages, assets, and the embedded FPDF library). It summarizes the app, how to run it, the main files and folders, an example database schema to get started, and recommended next steps.

---

## Table of contents

- [Features](#features)
- [Tech stack](#tech-stack)
- [Repository layout](#repository-layout)
- [Requirements](#requirements)
- [Installation & setup](#installation--setup)
- [Database schema (example)](#database-schema-example)
- [Usage](#usage)
- [Development notes & security recommendations](#development-notes--security-recommendations)
- [Contributing](#contributing)
- [License](#license)
- [What I did and what's next](#what-i-did-and-whats-next)

---

## Features

- User session-based access (pages check `$_SESSION['username']`) — login required.
- Upload PDF documents (file input) and capture images via client camera (JS hooks present).
- Store and display document metadata (name, size, created_at, status).
- Add "Agreement" metadata (seller name, buyer name, etc.) for documents with "Pending" status.
- Read/preview documents in-browser.
- PDF generation utilities via the bundled FPDF library (tutorials/docs packaged).
- Client-side search/filter for document and agreement tables (assets/js/main.js).
- UI layout and styling under `assets/css/ccc.css`.

---

## Tech stack

- PHP (server-side)
- MySQL / MariaDB (expected, accessed via included `DBconfig.php` and `mysqli_*` calls)
- HTML / CSS / JavaScript for frontend
- FPDF library bundled under `fpdf/` for PDF generation

---

## Repository layout (high-level)

- `index.php`, `login.php`, `readDoc.php`, `testing.php`, ... — application pages
- `includes/`
  - `configs/DBconfig.php` — database connection/config (edit with your DB credentials)
  - `pages/header.php` — common header, loads site title from `settings` table
- `assets/`
  - `css/ccc.css` — app styles
  - `js/main.js` — client-side behaviors (search, file preview, camera capture)
- `fpdf/` — FPDF library and documentation (tutorials, doc pages, makefont, etc.)
- `README.md` — this file (generated)
- Other resources: sample pages and utilities (`testing.php`, `readDoc.php`) demonstrate upload/preview/agreement flows.

Key files inspected:
- `readDoc.php` — shows document detail view, formatting file sizes, status handling, agreement form when status is "Pending".
- `testing.php` — demonstrates new document form (file input, camera capture UI).
- `includes/pages/header.php` — page header, loads site title from `settings` DB table.
- `assets/js/main.js` — search/filter and preview/camera UI functions.
- `fpdf/` — bundled library with docs, license, and examples.

---

## Requirements

- PHP 7.2+ (or a supported PHP 7.x/8.x version)
- MySQL or MariaDB
- Web server (Apache, Nginx, or PHP built-in server for development)
- GD or similar if images are manipulated (depends on app usage)
- Browser with camera support (for capture feature)

---

## Installation & setup

1. Clone the repository:
   ```
   git clone https://github.com/Anco-Sam-Franco-B/Notary-Management-System-v2.git
   cd Notary-Management-System-v2
   ```

2. Ensure your webserver document root points to the project folder or run PHP built-in server for quick testing:
   ```
   php -S 0.0.0.0:8000
   ```

3. Create a MySQL database and user for the app.

4. Configure database connection:
   - Open `includes/configs/DBconfig.php` and set DB host, database name, username and password.

5. Create the required tables. Use the example schema below (customize as needed), then import into your DB.

6. Place uploaded files directory (if required) and ensure webserver has write permissions:
   - Example: `uploads/` (create and `chmod` as needed).

7. Visit the app in a browser and log in (create a user or seed the `users` table as needed).

---

## Database schema (example)

Use this as a starting point — adapt column types, indexes, and constraints to your needs.

```sql
-- Example minimal schema (MySQL)
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL DEFAULT 'Notary Management System',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `documents` (
  `doc_id` INT AUTO_INCREMENT PRIMARY KEY,
  `doc_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(512) DEFAULT NULL, -- path to uploaded PDF or generated file
  `size` BIGINT DEFAULT 0,
  `mime_type` VARCHAR(100) DEFAULT 'application/pdf',
  `status` VARCHAR(50) DEFAULT 'Pending', -- Pending, Approved, Rejected, etc.
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `uploader_id` INT DEFAULT NULL,
  FOREIGN KEY (`uploader_id`) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE `agreements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `doc_id` INT NOT NULL,
  `seller_name` VARCHAR(255) NOT NULL,
  `buyer_name` VARCHAR(255) NOT NULL,
  `notes` TEXT,
  `status` VARCHAR(50) DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`doc_id`) REFERENCES documents(doc_id) ON DELETE CASCADE
);
```

Notes:
- The repository uses `documents` and `settings` tables (observed in code). The example above matches typical usage found across the PHP files.
- Adjust storage paths, column names and types to match the actual `DBconfig.php` and any SQL provided with the project if present.

---

## Usage

- Log in through `login.php` (there is a session check in pages like `readDoc.php` and `testing.php`)
- Upload a document via the New Doc page (example: `testing.php` shows file input and camera capture flow).
- View a document's details through `readDoc.php?docid=<id>` — this page shows size, date, status and an "Add Agreement" form for Pending documents.
- Use the search box (client-side) to filter documents and agreements in table views.

FPDF usage:
- The `fpdf/` folder contains the FPDF library and several example/tutorial files (e.g., `tutorial/tuto3.php`, `tutorial/tuto4.php`). Use those as references to generate programmatic PDFs if needed.

---

## Development notes & security recommendations

- Use prepared statements (PDO or mysqli prepared statements) instead of inline string interpolation to prevent SQL injection. E.g., replace constructs like:
  ```php
  $read = mysqli_query($con, "SELECT * FROM documents WHERE doc_id='$doc_id'");
  ```
  with prepared statements.

- Sanitize and validate all user input (POST/GET) and file uploads (check MIME types, file sizes, and scan for malicious content).

- Store password hashes using password_hash() and verify with password_verify(). Do not store plaintext passwords.

- Use secure session handling:
  - Regenerate session IDs on login.
  - Set secure and httponly cookie flags.
  - Use appropriate session timeout and logout flows.

- Protect file uploads directories (disallow PHP execution in uploads folders) and prefer storing user-provided files outside webroot or enforce strong access controls.

- Consider migrating to modern frameworks or adopt templating to separate presentation and logic for maintainability.

---

## Contributing

If you'd like help expanding this README or improving the codebase, I can:
- Generate a SQL seed file tailored to the app (create admin user and settings).
- Replace inline SQL calls with prepared statements.
- Add missing pages (register, admin panel) or documentation for FPDF usage in-app.
- Create unit / integration test suggestions.

Please tell me which area you want to prioritize.

---

## License

- The bundled FPDF library includes its own license (see `fpdf/license.txt`) — ensure you comply with its terms.
- The rest of the project does not include an explicit license file in the repository snapshot I analyzed. If you intend to open-source this project, add a LICENSE file (MIT, Apache-2.0, etc.) as appropriate.

---

## What I did and what's next

I analyzed your repository files (I inspected pages such as `readDoc.php`, `testing.php`, the header include, `assets/js/main.js`, `assets/css/ccc.css`, and the bundled `fpdf/` library) and used that information to write this README.md describing the application, installation steps, an example DB schema, and security/development suggestions. If you want, I can now:

- Generate and add a SQL seed file that matches the app's expected tables,
- Produce a ready-to-run `config.sample.php` for `includes/configs/DBconfig.php`,
- Or convert vulnerable inline SQL calls to prepared statements across the codebase.

Tell me which of the above you'd like me to do next and I will prepare the corresponding files/patches.