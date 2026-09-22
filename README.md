# Moctale Reborn 🎬

A movie discovery & review platform inspired by [Moctale](https://moctale.in), built as a university project. Browse trending, popular, and top-rated titles, search the catalog, view cast & crew, save favorites to a personal watchlist, and share your verdict through the signature Moctale Reborn Meter and Vibe Chart.

## Features

- 🔐 **Authentication** — secure signup/login with hashed passwords and session management
- 🎞️ **Content Discovery** — real movie data from TMDB, organized into Trending, Popular, and Top Rated carousels
- 🔍 **Search** — case-insensitive search across the movie catalog
- 🎭 **Cast & Crew** — actor photos, character names, director, genres, and TMDB rating per movie
- 📊 **Moctale Reborn Meter** — a custom community verdict system (Skip / Timepass / Definitely Watch / Perfect Movie) shown as live percentage bars
- 🌈 **Vibe Chart** — shows which vibes (Action, Thriller, Drama, Mystery, Romance) users felt fit each movie
- ✍️ **User Reviews** — written comments tied to real accounts, alongside verdict and vibe tags
- ❤️ **Watchlist** — logged-in users can save and remove movies from a personal list
- 🎨 **Custom UI** — dark theme with a distinct teal/charcoal design system
- ℹ️ **About & Contact pages**

## Tech Stack

- **Backend:** PHP (vanilla, no framework)
- **Database:** PostgreSQL via Supabase
- **External API:** [TMDB API](https://www.themoviedb.org/documentation/api)
- **Frontend:** HTML, CSS (custom stylesheet)

## Project Structure

moctale-clone/
├── api/
│ └── fetch-tmdb.php # Pulls movies, genres, cast, crew, ratings from TMDB
├── assets/css/
│ └── style.css # Site-wide design system
├── config/
│ ├── db.example.php # Template for database connection
│ └── tmdb-key.example.php # Template for TMDB API key
├── includes/
│ ├── auth-check.php # Reusable login-required guard
│ └── footer.php # Shared footer with About/Contact links
├── index.php # Homepage with carousels + search
├── content.php # Movie detail page: cast, Moctale Meter, Vibe Chart, reviews, watchlist
├── login.php / signup.php # Authentication pages
├── logout.php
├── watchlist.php # User's saved movies
├── about.php
└── contact.php


## Setup

1. Clone the repo
2. Copy `config/db.example.php` → `config/db.php` and fill in your Supabase Postgres credentials
3. Copy `config/tmdb-key.example.php` → `config/tmdb-api-key.php` and add your [TMDB API key](https://www.themoviedb.org/settings/api)
4. In Supabase, create `users`, `movies`, `watchlist`, and `reviews` tables (see schema below)
5. Run `api/fetch-tmdb.php` once to populate the movies table
6. Serve the project with PHP (e.g. via XAMPP) and open `index.php`

## Database Schema

- **users**: id, name, email (unique), password (hashed), created_at
- **movies**: id, tmdb_id, title, poster_path, overview, category, release_date, genres, cast, director, tmdb_rating, created_at
- **watchlist**: id, user_id (FK → users.id), movie_id (FK → movies.id), created_at
- **reviews**: id, user_id (FK → users.id), movie_id (FK → movies.id), verdict, vibes, comment, created_at

## What I Learned

Building this project involved designing a relational schema with foreign keys, writing raw SQL with prepared statements for security, implementing session-based authentication, integrating a third-party API (including handling network reliability issues with retries), aggregating user-generated data into live statistics (the Moctale Meter and Vibe Chart), and building a cohesive custom UI from scratch.

## Disclaimer

This project is inspired by the concept and layout of [moctale.in](https://moctale.in) for educational purposes and is not affiliated with it.