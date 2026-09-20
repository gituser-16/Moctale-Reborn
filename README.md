# Moctale Reborn 🎬

## Features

- 🔐 **Authentication** — secure signup/login with hashed passwords and session management
- 🎞️ **Content Discovery** — real movie data pulled live from The Movie Database (TMDB) API, organized into Trending, Popular, and Top Rated carousels
- 🔍 **Search** — case-insensitive search across the movie catalog
- ❤️ **Watchlist** — logged-in users can save and remove movies from a personal list
- 🎨 **Custom UI** — dark theme with a distinct teal/charcoal design system, hover animations, and responsive carousels

## Tech Stack

- **Backend:** PHP (vanilla, no framework)
- **Database:** PostgreSQL via Supabase
- **External API:** [TMDB API](https://www.themoviedb.org/documentation/api) for movie data
- **Frontend:** HTML, CSS (custom stylesheet)

## Project Structure

moctale-clone/
├── api/
│ └── fetch-tmdb.php # Pulls movie data from TMDB into the database
├── assets/css/
│ └── style.css # Site-wide design system
├── config/
│ ├── db.example.php # Template for database connection
│ └── tmdb-key.example.php # Template for TMDB API key
├── includes/
│ └── auth-check.php # Reusable login-required guard
├── index.php # Homepage with carousels + search
├── content.php # Movie detail page + watchlist toggle
├── login.php / signup.php # Authentication pages
├── logout.php
└── watchlist.php # User's saved movies


## Setup

1. Clone the repo
2. Copy `config/db.example.php` → `config/db.php` and fill in your Supabase Postgres credentials
3. Copy `config/tmdb-key.example.php` → `config/tmdb-api-key.php` and add your [TMDB API key](https://www.themoviedb.org/settings/api)
4. In Supabase, create `users`, `movies`, and `watchlist` tables (see schema below)
5. Run `api/fetch-tmdb.php` once to populate the movies table
6. Serve the project with PHP (e.g. via XAMPP) and open `index.php`

## Database Schema

- **users**: id, name, email (unique), password (hashed), created_at
- **movies**: id, tmdb_id, title, poster_path, overview, category, release_date, created_at
- **watchlist**: id, user_id (FK → users.id), movie_id (FK → movies.id), created_at

## What I Learned

Building this project involved working across the full stack — designing a relational schema with foreign keys, writing raw SQL with prepared statements for security, implementing session-based authentication, integrating a third-party API, and building a cohesive UI from scratch.

## Disclaimer

This project is inspired by the concept and layout of [moctale.in](https://moctale.in) for educational purposes and is not affiliated with it.


