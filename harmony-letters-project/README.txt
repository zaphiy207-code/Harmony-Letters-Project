Harmony Letters Project
--------------------
1. PROJECT OVERVIEW

Harmony Letters Project is a music review website that allows users to explore albums and songs, share their opinions, and discover what other listeners think about the music.

The website features two albums as of now, Atlas: Year One by Sleeping At Last and Birthplace by Novo Amor, containing a total of 40 songs. Users can create accounts, sign in, rate songs, write reviews, and view community-submitted reviews.

The project was developed in 4 days altogether as part of a university web development assignment.

--------------------
2. FEATURES

- Album browsing: Explore the Atlas: Year One and Birthplace albums including the description of the album.
- Song pages: View information about individual songs within the album.
- User accounts: Register an account and sign in.
- Song ratings: Rate songs using a five-star rating system.
- Written reviews: Submit reviews with categories such as Emotional, Lyrics, Production, and Personal Story.
- Community review board: Browse reviews submitted by other users.
- Review voting: Vote on community reviews as helpful or not helpful (though not fully finalized)
- Administrator functions: Access administrative features for managing website records, according to assigned permissions. (only for admin and moderator)

--------------------
3. TECHNOLOGIES USED

- HTML5 — website structure
- CSS3 — styling and layout
- JavaScript — interactive elements and review form behaviour
- PHP — server-side processing, authentication, and database operations
- MySQL — storing user accounts, albums, songs, and reviews
- XAMPP — local development environment
- phpMyAdmin — database management

--------------------
4. PROJECT STRUCTURE

Important project files and folders include:

HTML:
- 'index.html' — homepage
- 'albums.html' — album listing
- 'reviews.html' — community review board

CSS:
- `harmony.css` — website styles

PHP:
- 'signin.php' — user sign-in
- 'createacc.php' — account registration
- 'auth.php' — authentication and security helpers
- 'db_connect.php' — database connection
- 'admin.php' — administrator interface
- 'song-review.php' - individual song review page linked to database

OTHERS:
- 'js/' — JavaScript files for interactive website features
- 'api/' — PHP endpoints for processing requests, including review submission and retrieval
- 'pictures/' — album artwork and other website images
- '.sql' — database setup script

Albums page with all of its songs listed are also included.

--------------------
5. SETUP

Install XAMPP with Apache, PHP, and MySQL.

Steps:
1. Clone or download this repository.
2. Place the project folder inside the XAMPP `htdocs` directory.
3. Start Apache and MySQL from the XAMPP Control Panel.
4. Open phpMyAdmin in your browser at 'http://localhost/phpmyadmin'.
5. Create the database and import the provided SQL setup script.
6. Check 'db_connect.php' and update the database credentials if necessary.
7. Open the website at 'http://localhost/harmony-letters/', replacing 'harmony-letters' if the project folder has a different name.

It is important to have the subfolder name to be similar to the project folder name saved in htdocs.

--------------------
6. DATABASE SETUP

The project uses a MySQL database named 'webdevmusicrater'.

The database stores information about:

- Users and account roles
- Artists and albums
- Songs and their associated albums
- Song reviews and ratings
- Community review votes

Import the supplied SQL script into phpMyAdmin to create the database tables and initial data.

Important: Check the SQL script before importing it. If it contains a command to drop and recreate the database, importing it again may erase existing data.

--------------------
7. HOW TO NAVIGATE

1. Open the homepage.
2. Create an account and sign in to submit a review.
3. Browse the available albums and songs.
4. Open a song's review page.
5. Select a star rating and review category, then write and submit your review.
6. Visit the community review board to view submitted reviews.

Guests can browse the website, however submitting reviews still requires authentication.

--------------------
8. TESTING

The following core functions were tested locally:

- Account registration and database storage
- User sign-in
- Review submission
- Review storage in the database
- Displaying submitted reviews on the community review board

Additional testing can cover guest access, duplicate review prevention, review voting, and administrator permissions.

--------------------
9. CREDITS

Developed by:
Jonathan John Tan Wei Ming        [B20260483]
Izzatul Iffah binti Ariffin       [B20250034]
Nur Zafirah binti Haji Julkipeli  [B20250051]

Course:
Bsc in Computing (Major in Data Analytics)
Bsc in Computing (Major in Software Development)

Academic year: Semester 3 Jul/Nov 2026
