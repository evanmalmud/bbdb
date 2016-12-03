BBDB is a web-based program to process, store and analyze matches from the Cyanide game Blood Bowl, based on the Games Workshop tabletop game of the same name. It can also organize the matches into competitions, so you can manage your own league outside of the in-game client. It has been built to theoretically support other clients (such as Blood Bowl 2) but the code to process matches from other clients has not yet been built.

## Requirements

You must have access to a website hosting solution that allows PHP (5.5 or later) and MySQL, and have reasonable computing skills - eg comfortable manually editing text files, and uploading files to your web space. Some basic SQL knowledge will be a distinct advantage.

## Installation

These instructions assume a standard setup on a hosted website, perhaps one that has cPanel available.

1) Create a mysql database called bbdb (or any other name). It may be possible to use a pre-existing database as long as none of the table names are used (all BBDB tables are prefixed with bb_ or staging_), but the wisdom of doing this is dubious.
2) Assign a username & password to that database and make a note of them. 
3) If your database is not called bbdb, then open up the file called "bbdb_create_db.sql" and do a find & replace to change all instances of bbdb to whatever your database is called (there are three instances of bbdb in the file).
4) Run the contents of the file called "bbdb_create_db.sql" against the database from step 1. Congratulations, your BBDB database is ready! Now we need to confiugre a few things in the front end.
5) You need to give the login details for that database to the front end; this is done via the file inc/header1.php. Look for where it says YOUR_DB_HERE, YOUR_USERNAME_HERE and YOUR_PASSWORD_HERE and replace those values with your own. Also, change YOUR_URL_HERE to be the URL of the directory where you will be hosting BBDB make sure to include a trailing forward-slash.
6) Repeat this for the files header1_with_login.php and header1_with_logout.php.
7) Upload all the files and folders to your website.
8) Open up the website in your browser. Hopefully the home page should display. Then navigate to initial_setup.php whereupon you will find a small form to fill in with final setup instructions. Once this is done, the installation is complete.


If you need help with these steps 1 & 2, and have a cPanel-based hosting solution, you can follow [these instructions](http://www.inmotionhosting.com/support/website/database-setup/create-database).

## User logins

Your first login is created as part of step 7 above. This is a superadmin, with full rights to do anything.

You can create logins for further people via this link -> /admin/createuser.php. There is, as yet, no facility for users to create their own logins. The same login can be used by any number of people.

## Further administration information

A user (a BBDB entity) is a seperate thing to a coach (a blood bowl entity). A coach is someone who manages a blood bowl team. Unfortunately the blood bowl client does not contain information about coaches, so these have to be manually added.

Currently you can only add new coaches directly via manual SQL.

However matching coaches to teams can be done via the web front end. Simply login with an account with the requisite privilages, then go to /admin/ in your web browser. Here you can create coaches, assign coaches to blood bowl teams, and also create new competitions to put your fixtures into.

## Domains

A domain (or community) is a group of people who play blood bowl in their own self-contained world. As an admin, you can cater for serveral communities, if you wish. This means that users will only see information relating to their community, which is good thing. They are unlikely to be interested in details of other leagues/matches.

All users can only see data from one domain at a time. However if you are a superadmin, you can change what domain you are currently viewing. You can do this via /admin/change_community.php.

## Security

If you are holding data that people have uploaded, then it is important you keep that data secure. It is thus STRONGLY recommended that you utilise HTTPS to encrpyt the data between your users and your web server. The passwords are stored in the database in a hashed format using the current best-practice algorithm - meaning it is very difficult for you or anyone else to know what the password is.

If you do not have HTTPS, it is recommended to create logins and passwords yourself, and for those passwords to be "throwaway" ones that are not used anywhere else. You can even give the same login to many users.

## API

There is some basic API functionality built in. Anyone using the API will need a key; currently this has to be done manually directly into the database, in the bb_api_user table. The list of api calls available is in bb_api_type (currently only the match detail is available - so 3rd parties can create match report images, like in the old Blood Bowl Manager). All api calls area logged in bb_api_call. Please note that usual community-level access does not apply to access via api calls.

----
### A note on code quality

I believe that code should be well-commented, clear and well-structured. I do not think this code meets these standards - it was written sometimes on a fairly rushed basis, grabbing spare minutes here and there. I was impatient to get a minimum viable product out there... seeing some sort of end product is what motivates me. Also, PHP allows a wide range of formats/methodologies, and I have never sat down and learnt to do it "the proper way" (whatever that is). I have coded in PHP since I was a very novice coder, and I've always been more into databases... PHP represented a quick'n'dirty front end to the database, and I've never really tried to progress my PHP skills since then. It's always been something done in my spare time. So, for those people who try and edit the code - sorry it's a bit of a mess at times, I hope it's good enough to work from. And to potential future employers - please be assured that I truly do believe in code releases, clear, consice, readable code, good commenting and so on and if I had a full time job in a language these aspects would surely shine through. You may find other code I have uploaded to GitHub that meets these standards.

Licensed under the MIT license.