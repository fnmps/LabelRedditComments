# LabelRedditComments
A software with an interface to label reddit comments

## How to use this software

1) Start a mysql server in your server.

Create the database:

CREATE DATABASE `name of the database`

Create the table where the comments (to be labeled) should be:


CREATE TABLE `name of table1` (

  `id` int(11) NOT NULL AUTO_INCREMENT,
  
  `author` varchar(255) NOT NULL,
  
  `subreddit` varchar(255) NOT NULL,
  
  `thread_id` varchar(255) NOT NULL,
  
  `comment` text CHARACTER SET utf8mb4 NOT NULL,
  
  `permalink` varchar(255) NOT NULL,
  
  PRIMARY KEY (`id`),
  
  UNIQUE KEY `permalink` (`permalink`)
  
)

Create the table where the labeled comments should be:

CREATE TABLE `name of table2` (

  `comment_id` int(11) NOT NULL,
  
  `comment_segment_id` int(11) NOT NULL,
  
  `author` varchar(255) NOT NULL,
  
  `comment_segment` text NOT NULL,
  
  `label` varchar(255) NOT NULL,
  
  UNIQUE KEY `unique_index` (`comment_id`,`comment_segment_id`)
  
)

2) Change the following files where "#####" are:

database.php -> change the database connection parameters and the table1 and table2 names

getusercomments.py -> change the database connection parameters and the table1 name

reddit/config.php -> change your reddit application id, secret and redirect url

login.php -> change your reddit application id and redirect url

labeling.php -> change the labels and label names you want (label is the value to be put on the database, and the name the text to be shown to the user)

