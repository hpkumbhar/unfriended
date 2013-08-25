unfriended
======

A script to periodically determine who has unfriended you from Facebook

Running live at http://www.michaeljcardillo.com/unfriended/

Configuration
======

Run the following SQL query to setup the required database.

`CREATE DATABASE unfriended; USE unfriended; CREATE TABLE friends(user_id BIGINT, friend_id BIGINT);`

Then, update appinfo.conf with your database and Facebook App info.
