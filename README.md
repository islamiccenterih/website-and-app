# Islamic Center Information Hub

A complete public website, Admin CMS, and Student panel built for **normal shared hosting**.

Content flows in one direction:

**Admin Panel → MySQL → Public Website / Student Panel**

No Node.js process, Docker, Redis, or VPS is required. Upload the files, import the database, and set `config/config.php`.

On the English public site, Islamic words (Qur’an, Salah, Zakat, course names, and so on) appear in Arabic or Urdu with the English in brackets. Hindi, Urdu, and Arabic language modes are unchanged. Turn this off under **Admin → Settings** (“Arabic and Urdu terms on the English website”), or ask to **Back to English** / **convert in English again**. New pages still follow the same word list while the switch is on.

## Selected stack

| Layer | Choice | Why |
| --- | --- | --- |
| Language | PHP 8.1+ (developed on 8.3) | Standard on cPanel / shared hosting |
| Database | MySQL 5.7+ / MariaDB 10.3+ | Default shared-hosting database |
| Architecture | Lightweight custom MVC | No Composer, no Laravel, no vendor folder |
| Front end | HTML5, CSS3, small vanilla JS | No build step |
| URLs | Apache `.htaccess` rewrite | Clean SEO paths such as `/courses/tajweed-ul-quran` |
| Moon data | AlAdhan + sunrisesunset.io | Free, no API key, swap later in `app/Services/MoonService.php` |

Public pages share one layout language: gold kicker, serif heading, diamond rule, then the section content. Dark blocks use a blurred mosque backdrop. The home hero starts with Bismillah and the center heading on a 10-second looping Masjid-e-Nabawi–inspired video (original generated artwork, about 850 KB, muted autoplay). After one second a closable gold-framed box fades in above Bismillah with a Qur’anic ayah, Hindi meaning, and surah name (the ayah, meaning, and reference change every hour, IST). Closing it returns the hero to that first look. A stats strip, services, Pillars of Islam, and a circular scroll-percentage control follow. Mosque green and antique gold are the site colors. Reference branding, copy, and images were not copied.

## Requirements

- PHP **8.1 or newer** (8.2 / 8.3 recommended)
- Extensions: `pdo_mysql`, `mbstring`, `gd`, `curl` or `allow_url_fopen`, `fileinfo`, `json`
- Apache with `mod_rewrite` (or equivalent rewrite to `public/index.php`)
- MySQL / MariaDB with `utf8mb4`

Optional: Tesseract OCR on the server. If it is not installed, calendar images can still be uploaded and dates entered manually. Extracted text is **never** published without admin review.

## Local preview

```bash
mysql -u root -p -e "CREATE DATABASE islamic_center CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p islamic_center < database/schema.sql
mysql -u root -p islamic_center < database/seed.sql
cp config/config.example.php config/config.php
# edit config.php
php -d upload_max_filesize=12M -d post_max_size=32M -S 127.0.0.1:43125 -t public public/router.php
```

## Shared hosting deployment

### 1. Upload files

**Preferred (document root = `public/`)**

Upload the project so that:

```
/home/USER/islamic-center/app
/home/USER/islamic-center/config
/home/USER/islamic-center/database
/home/USER/islamic-center/storage
/home/USER/islamic-center/public   ← set this folder as the domain document root
```

In cPanel: Domains → your domain → Document Root → `islamic-center/public`.

**Alternative (everything inside `public_html`)**

1. Place `app`, `config`, `database`, and `storage` **one level above** `public_html` if the host allows it.
2. Copy the **contents** of `public/` into `public_html/` (`index.php`, `.htaccess`, `assets/`, `uploads/`).
3. `public/index.php` already loads `../app/bootstrap.php`, so this layout works without code changes.

If the host forces the entire repository into `public_html`, the root `.htaccess` rewrites requests into `public/` and blocks `app/`, `config/`, `database/`, and `storage/`.

### 2. Configure

1. Copy `config/config.example.php` to `config/config.php`.
2. Set database host, name, user, and password.
3. Set `app.url` to your live URL, for example `https://example.com` (no trailing slash).
4. If the site lives in a subfolder, set `app.base_path` (example: `islamic-center/public`).
5. Set `app.env` to `production`.
6. Make `storage/` and `public/uploads/` writable (`0755` folders, `0644` files).

### 3. Database

In phpMyAdmin:

1. Create a database with collation `utf8mb4_unicode_ci`.
2. Import `database/schema.sql` (includes live-class tables).
3. Import `database/seed.sql` (demo / placeholder content).

If an older database is missing live-class tables, the app creates them on the next request. You do not import a separate SQL file.

### 4. Confirm PHP

In the hosting panel, select PHP 8.1+ and enable `pdo_mysql`, `gd`, `mbstring`, and `fileinfo`.

## Demo logins (change immediately)

| Role | Email | Password |
| --- | --- | --- |
| Owner (full Admin panel) | admin@example.com | Admin@12345 |
| Student | student@example.com | Student@12345 |

Managers and editors do not share the owner login. The owner creates a **panel member**, sets that person’s email and password, ticks the sections they may open, and hands them those credentials. They sign in at the same `/admin/login` page and only see what was granted.

Placeholder public content (courses, activities, gallery, about, calendar) is clearly marked so you can replace it from the Admin Panel without editing templates.

## What the Admin Panel controls

- **Pages** — every public page in English. Open a page to change its menu name (header and footer update together) and the content on that page. Home, About, Contact, Qibla, Zakat, Ramadan, Moon Timing, and the rest live here.
- **Header & Footer** — extra links, Show/Hide, student login button, footer columns, copyright. Rename website pages under Pages, not here.
- Courses (including slug, fees, duration, online/offline, images, publish)
- Social activities — sections (Workshops, Seminars, …) and the events under each, including photographs
- Gallery images (a single list — no albums)
- Center program cards on Home
- Center Updates and Fatawa
- Islamic calendar image, notes, and dated events
- Students and results
- **Course enquiries** — applications from the Apply form on every public course page (name, email, phone, WhatsApp, address). Separate from Contact messages. Tick some or Select all, then download an Excel file. Managers with Messages access can open this list too.
- Live classes (create a course-specific room, go live, attendance)
- **Live now** — public website broadcast. Press Go live to start with this device’s camera in 16:9 landscape. After you are live, switch camera, share the screen, or go fullscreen. Viewers open `/live` with play, volume, mini player, fullscreen, and live comments. This is not a student class. About 10–100 watchers. HTTPS is required on hosting.
- Site name, logo, SEO, moon location
- **Panel members** (owner only) — create a manager or editor with their own email and password, and tick which sections they may view and edit
- Homepage hero image (optional — replaces the video banner), About photos, course images, and a contact page photograph

Save a field in the Admin Panel and the public website shows the new value on the next page load. Draft records stay off the public site. Published records appear immediately.

## Languages

A top bar on every public page (desktop and mobile) offers **English, Hindi, Urdu, and Arabic**. The choice is saved in a cookie. Urdu and Arabic switch the site to right-to-left. Menu labels, buttons, and common headings change with the language. Text you type yourself in the Admin Panel stays as you wrote it unless it matches a known English phrase.

## Panel members

The seeded admin is the **owner** and keeps full access, including **Panel members**. New accounts created there are members only; they cannot add other members or open sections you did not tick.

1. Sign in as owner at `/admin/login`.
2. Open **Panel members** and choose **Add a panel member**.
3. Enter their name, job title (Manager, Editor, …), email, and a password of at least 8 characters.
4. Tick the sections they may view and edit (Courses, Gallery, and so on). Leave the rest unticked.
5. Give them that email and password. They sign in at the same Admin login page.

The sidebar shows only granted sections. Typing a blocked URL sends them back to the dashboard. Disabling or removing the account, or changing their ticks, takes effect on their next page load. Columns for this are added automatically on first request (`job_title`, `panel_role`, `permissions` on `admins`).

## Student panel

Students sign in from **Student Login** in the public header. The same browser stays signed in for **15 days**; after that they must sign in again. Sign out, a password change, or an admin disabling the account ends the session sooner. Each student sees **only their own** profile (name, photo, phone), courses, results, and live classes. A student can be enrolled in more than one course. Saved profile details stay until that student or an administrator changes them. Public course applications are **not** shown in the student panel.

They can view their profile, **only their own published results**, and **Join class** for a live room belonging to **any course they are enrolled in**. Video, voice, and chat stay on this website. Join with camera & mic, or mic only. If peer-to-peer video cannot connect (typical phone/Wi-Fi NAT), the room still shows picture and sound over HTTPS, the same way public **Live now** does. People and Chat open as separate side panels. Use HTTPS or localhost so the browser allows camera and microphone.

## Moon Timing

- Home prayer times: `https://api.aladhan.com/v1/timingsByCity` (Karachi method, Hanafi Asr), cached until midnight IST per city
- Hijri date: `https://api.aladhan.com/v1/gToH`
- Moonrise / moonset / phase: `https://api.sunrisesunset.io/json`
- Location comes from Admin → Settings (latitude, longitude, timezone)
- Responses are cached as files under `storage/cache/` until midnight IST. Gold and silver rates refresh every day. If a live feed is down, the last saved rates are used.
- If a provider is down, the last good day’s data is shown instead of a blank page. Fresh data is fetched again the next day.

To switch providers later, edit `app/Services/MoonService.php` only.

## Islamic Calendar

The public `/islamic-calendar` page is a working Hijri month calendar (today, previous/next month, and a month/year jump). Hijri–Gregorian day maps for 1446–1450 are stored in `public/assets/data/islamic-calendar/` so the page still works if the source API is later removed. Missing months are fetched from AlAdhan `hToGCalendar` and saved into that same folder. Major dates (Ashura, Mawlid, Ramadan, the two Eids, and others) are marked on the grid.

Admin → Islamic Calendar remains available for optional uploaded month images and center-specific dates. Extracted OCR text is never published without review. Dummy or placeholder admin entries are not shown on the public page.

## Islamic Holidays

The public `/islamic-holidays` page opens with **Eid ul-Fitr** (عید الفطر) and **Eid al-Adha** (عید الاضحیٰ) as observed in **India**, then a full list of major Islamic days. A year filter covers **2026–2031**. Hijri days are converted with AlAdhan, then shifted by one civil day to match Indian moon sighting (Eid ul-Fitr 2026: 21 March in India, not 20 March Saudi). The Centre still confirms the final day after the moon is seen.

## Qibla, Zakat, and Ramadan

- **Qibla Direction** (`/qibla-direction`) — live phone compass to the Kaaba. Tap **Start compass** at the top and allow Location. The Kaaba mark is calculated from **your phone GPS** (great-circle to Makkah), anywhere in India or the world — not from the Firozabad center. Until GPS is allowed the Kaaba mark stays hidden so a wrong Qibla is not shown. The hub number is the direction you face; turn until the Kaaba mark sits under the gold notch. Open in Chrome or Samsung Internet, not WhatsApp/Facebook. If the rose turns the wrong way, use Reverse compass.
- **Zakat Calculator** (`/zakat-calculator`) — gold, silver, cash, bank, business stock, receivables, shares, crypto, and debts. Nisab is 87.48 g gold / 612.36 g silver (editable). Spot prices come from gold-api.com and frankfurter.app (with backups), cached until midnight IST, and the last good rates are stored in `public/assets/data/zakat-spot.json`.
- **Ramadan Mode** (`/ramadan-mode`) — today’s Sehri (Fajr) and Iftar (Maghrib) for any Indian city, with English and Hijri dates, a countdown to 1 Ramadan, a full roza calendar, and duas. Daily times from AlAdhan `timingsByCity`; the month from `hijriCalendarByCity` (Karachi method, Hanafi). Default city Firozabad.

Qibla, Zakat, and Ramadan each have their own Admin section. Copy, duas, nisab weights, and the Qibla fallback location are edited there.

## Fatawa

- **Public page** (`/fatawa`) — today’s fatwa first, then previous days. Each fatwa has its own URL (`/fatawa/{slug}`).
- **Languages** — Admin may fill Arabic, English, and/or Hindi. Empty languages are not shown. The visitor’s site language is listed first when that translation exists.
- **Questions** — Anyone can ask on a fatwa in text, with an image/PDF/Word attachment, or both. The administration answers from **Admin → Worship → Fatawa**. The answer appears under that question on the public page.

## Center Updates

- **Public page** (`/center-updates`) — a cream page header, today’s notice as text, then earlier updates as a text grid (no list pictures). Each update has its own URL (`/center-updates/{slug}`). Pictures live only inside the article.
- **Compose** — Admin → Website → Center Updates. The editor is the same layout as the public article: title, date, text, pictures, uploaded video, or a YouTube/Vimeo link. After a picture is inserted, drag its gold corner to set that picture’s size. What is composed is what the website shows.

## Project structure

```
app/                 PHP application (not publicly executable)
  Controllers/
  Core/              Router, auth, CSRF, uploads, validation
  Models/
  Services/          Moon API adapter, optional OCR
  Views/
config/              config.php (local) and config.example.php
database/            schema.sql + seed.sql
public/              Document root: index.php, assets, uploads
storage/             logs and API cache
```

## Production notes

- Change both demo passwords.
- Keep `config/config.php` outside the web root when possible.
- `public/uploads/.htaccess` disables PHP execution in the upload directory.
- Contact form uses CSRF, a honeypot field, and a per-IP rate limit.
- Passwords use `password_hash()` / `password_verify()`.
- SQL uses PDO prepared statements.
- Output is escaped with `e()` except a small HTML allow-list for long descriptions.

## Extending later

Tables and panels are already separated for enrollment, attendance, fees, certificates, and a teacher role. Add new controllers under `app/Controllers` and routes in `app/routes.php` without replacing the public design.
