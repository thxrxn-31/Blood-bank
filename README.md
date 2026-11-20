# RaktSetu Blood Bank (HTML + CSS + JavaScript + PHP + MySQL)

## What you need
XAMPP (free). It installs PHP, MySQL and a web server on your computer in one go.
Download: https://www.apachefriends.org  (Windows, macOS, Linux)

## Step 1. Install and start XAMPP
1. Install XAMPP with the default options.
2. Open the **XAMPP Control Panel**.
3. Click **Start** next to **Apache** and next to **MySQL**. Both should turn green.

## Step 2. Put the project in the right folder
1. Unzip `bloodbank-php.zip`. You get a folder called `bloodbank-php`.
2. Copy that whole folder into XAMPP's web folder:
   - Windows: `C:\xampp\htdocs\`
   - macOS: `/Applications/XAMPP/htdocs/`
3. You should now have `htdocs/bloodbank-php/index.php`.

## Step 3. Create the database
1. In your browser open **http://localhost/phpmyadmin**
2. Click the **Import** tab at the top.
3. Click **Choose file** and pick `bloodbank-php/database/bloodbank.sql`.
4. Scroll down and click **Import**.
   This creates the `bloodbank` database, all tables, and sample data.

## Step 4. Open the website
Go to **http://localhost/bloodbank-php/**

Sample logins:
| Who   | Email                   | Password   |
|-------|-------------------------|------------|
| Admin | admin@raktsetu.test     | Admin@123  |
| Donor | ananya@raktsetu.test    | Donor@123  |

You can also register your own donor account from the site.

## How the database connects
Open `config.php`. It already holds the XAMPP defaults:
```
DB_HOST = localhost   DB_NAME = bloodbank   DB_USER = root   DB_PASS = (empty)
```
If you set a MySQL password, put it in `DB_PASS`. Nothing else needs changing.

## Troubleshooting
- **"Database connection failed"**: MySQL is not started in XAMPP, or step 3 was skipped.
- **Apache will not start**: another program uses port 80 (often Skype or IIS). Close it, or change Apache's port in XAMPP (Config > httpd.conf, `Listen 8080`), then use http://localhost:8080/bloodbank-php/
- **Page shows PHP code instead of the site**: you opened the file directly. Always use the `http://localhost/...` address.
- **Blank page**: check the Apache error log from the XAMPP Control Panel (Logs).
- Re-importing `bloodbank.sql` resets all data to the samples.

## Project layout
```
bloodbank-php/
  index.php        home, live stock, camps
  register.php     sign up as a donor     login.php / logout.php
  donors.php       search donors (auto-includes compatible groups)
  hospitals.php    stock by hospital      requests.php / request.php  post, accept
  dashboard.php    my profile, eligibility timer, donations
  camps.php  info.php (eligibility + compatibility chart)
  admin.php        approve hospitals, update stock, manage camps
  config.php       database connection
  includes/        shared header, footer, helper functions
  assets/          style.css, app.js
  database/bloodbank.sql   tables + sample data
```

## Built-in safety
Passwords are hashed (`password_hash`), all queries use prepared statements, forms carry CSRF tokens,
output is escaped, and a donor's phone number is shown to a requester only after the donor accepts.

## Before going live
Change the admin password, turn off sample accounts, use a MySQL user other than `root`, and serve over HTTPS.
