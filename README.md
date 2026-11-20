# Discover Your Age — Modern Age Calculator

A modern, responsive Age Calculator built with **HTML5**, **CSS3**, **PHP**, and **Vanilla JavaScript** (`DateTime` backend calculation), designed to run directly on **XAMPP** without any external frameworks.

## Project Structure

```text
age-calculator/
    index.php
    README.md
```

## Features

- **Accurate PHP `DateTime` Calculation**: Uses PHP's `DateTime` and `DateTime::diff()` to compute exact **Years**, **Months**, **Days**, and **Total Days Lived**.
- **Complete Validation**: Prevents future dates, rejects empty or invalid calendar inputs, and handles today's date (`0 years, 0 months, 0 days`) accurately.
- **Preserved Form State**: Keeps the selected date populated after `POST` submission.
- **Interactive Controls**: Includes **Calculate Age** and **Reset** buttons, inline accessible error messaging, and smooth count-up transitions.
- **Modern Glassmorphism UI**: Deep slate gradient backdrop, subtle floating ambient orbs, responsive layout for desktop, tablet, and mobile devices.

## XAMPP Installation & Setup Instructions

1. **Install XAMPP** (if not already installed) from [https://www.apachefriends.org/](https://www.apachefriends.org/).
2. **Locate your `htdocs` folder**:
   - **Windows**: `C:\xampp\htdocs\`
   - **macOS**: `/Applications/XAMPP/htdocs/`
   - **Linux**: `/opt/lampp/htdocs/`
3. **Copy the project folder**:
   Place the `age-calculator` directory inside `htdocs` so the path is:
   `htdocs/age-calculator/index.php`
4. **Start Apache**:
   Open the **XAMPP Control Panel** and click **Start** next to **Apache**.
5. **Open in your browser**:
   Visit:
   ```text
   http://localhost/age-calculator/
   ```

## Troubleshooting

- **Timezone Alignment**: PHP uses the timezone configured in `php.ini` (`date.timezone`). If "Today" differs from your local system clock, update `date.timezone` in `xampp/php/php.ini` (for example, `date.timezone = Asia/Kolkata` or `America/New_York`) and restart Apache.
- **Blank Page or Raw PHP Code**: Ensure you are accessing the site through `http://localhost/age-calculator/` via Apache rather than opening `index.php` directly with `file://`.
