# MovieMate Authentication System Setup

This guide will help you set up the user authentication system for MovieMate.

## Prerequisites

- XAMPP or similar local development environment
- MySQL/MariaDB server running
- phpMyAdmin access

## Setup Instructions

### 1. Create the Database

Open phpMyAdmin and run the SQL script to create the database and tables.

The database will be created with the following structure:
- Database: `moviemate`
- Table: `users` with columns for username, email, password, preferred_genre, is_admin, created_at, updated_at

### 2. Create the Admin User

1. Navigate to: `http://localhost/MovieMate/private/setup_admin.php`
2. The script will create an admin account with:
   - **Username:** admin
   - **Password:** Password123!
3. After creation, **delete the setup_admin.php file** for security

### 3. File Structure

The authentication system uses the following files:

**Backend:**
- `private/db.php` - Database connection and auth functions
- `private/auth.php` - Login/signup request handler
- `private/setup_admin.php` - Admin setup script (delete after use)
- `private/setup.sql` - Database schema

**Frontend:**
- `public/index.php` - Main page with login/signup modal
- `public/profile.php` - User profile and genre preferences
- `public/settings.php` - User settings (password, email, username)
- `public/admin.php` - Admin panel for user management

**Styling:**
- `public/site/style/auth.css` - Authentication and modal styles
- `public/site/style/pages.css` - Profile and settings page styles
- `public/site/style/admin.css` - Admin panel styles

**JavaScript:**
- `public/site/js/auth.js` - Authentication handling and form submission

## Security Features

### SQL Injection Protection
All database queries use **prepared statements** with parameterized queries to prevent SQL injection:

```php
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

### Password Security
- Passwords are hashed using **bcrypt** (PHP's `password_hash()` function)
- Passwords are verified using `password_verify()` for safe comparison

```php
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$is_valid = password_verify($input_password, $hashed_password);
```

### XSS Prevention
All user input is escaped using `htmlspecialchars()` when displayed:

```php
echo htmlspecialchars($username);
```

### CSRF Protection
Forms use POST method and session-based authentication. Consider adding tokens for additional protection.

## Features

### User Registration
- Username validation (unique)
- Email validation (unique)
- Password strength requirement (minimum 6 characters)
- Form validation with error messages

### User Login
- Username/password authentication
- Session management
- Error messages for invalid credentials
- Secure password comparison

### User Profile
- View current user information
- Select preferred movie genre
- Admin badge for admin users
- Responsive design

### User Settings
- Change username (with uniqueness check)
- Change email (with uniqueness check)
- Change password (with current password verification)
- All changes use prepared statements for security

### Admin Panel
- View all users in a table
- Delete users (except own account)
- User statistics
- Genre preferences visible
- Admin/Regular user badges

### Session Management
- Session variables store user ID, username, and admin status
- Protected pages check `isLoggedIn()` before allowing access
- Logout functionality clears session data

## Configuration

### Database Connection
Edit `private/db.php` to change database credentials:

```php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "moviemate";
```

### Admin Credentials
The default admin account is:
- **Username:** admin
- **Password:** Password123!

**Important:** Change the admin password immediately after first login!

## Troubleshooting

### "Connection failed" error
- Ensure MySQL server is running
- Check database credentials in `private/db.php`
- Verify database name is `moviemate`

### "Undefined array key 'candidates'" error (from chatbot)
- This is a separate issue from authentication
- Check the chatbot API configuration in `private/call.php`

### Login page not showing
- Ensure `auth.js` and `auth.css` are loaded correctly
- Check browser console for JavaScript errors
- Verify session is started with `session_start()`

### Protected pages showing "You are not logged in"
- Check that `db.php` is included before accessing user functions
- Verify session cookies are enabled in browser
- Clear browser cache and try again

## Best Practices

1. **Change Admin Password** - Change from default "Password123!" after setup
2. **Delete setup_admin.php** - Remove after creating admin account
3. **HTTPS in Production** - Always use HTTPS for login pages in production
4. **Regular Backups** - Backup user database regularly
5. **Monitor Admin Activity** - Keep track of admin panel access
6. **Update Regularly** - Keep software and dependencies updated

## Additional Security Notes

- Session timeout could be implemented for auto-logout after inactivity
- Email verification for new registrations could be added
- Password reset functionality could be implemented
- Two-factor authentication could be added for admin accounts
- Rate limiting on login attempts could prevent brute force attacks
- CSRF tokens could be added to all forms for additional protection

## Support

For issues or questions, refer to:
- `private/db.php` - Core authentication functions with comments
- `private/auth.php` - Request handler
- `public/index.php` - Frontend integration example
