import praw
import MySQLdb
import sys

redditor = sys.argv[1]
code = sys.argv[2]
   
client_id='###########' # -> YOUR REDDIT APP ID
client_secret='####################' # -> YOUR REDDIT APP SECRET
redirect_uri='http://##########.###/LabelDataset/labeling.php' # -> YOUR REDDIT APP REDIRECT URL

db = MySQLdb.connect(host="#########",       # your database host, usually localhost
                     user="#########",       # your database username
                     passwd="#########",     # your username password
                     db="#########")         # name of the database


table_name = '#########' # -> table to insert the comments fetched from reddit
reddit_app_name = '##########' # -> the name of your reddit app


add_author_comment = ("INSERT INTO " + table_name + " (author, subreddit, thread_id, comment, permalink) VALUES (%s, %s, %s, %s, %s)")


r = praw.Reddit(user_agent='extractdataset')

# If you don't want to use user authentication, remove this part ============================
# Notice that without authentication it will take twice as long to get the comments from reddit
r.set_oauth_app_info(client_id=client_id, client_secret=client_secret, redirect_uri=redirect_uri)
access_information = r.get_access_information(code)
r.set_access_credentials(**access_information)
r.config.api_request_delay = 1

user = r.get_me()
# ============================================================================================
# If you don't want to use authentication uncomment this next line
#user = r.get_redditor(redditor)

thing_limit = 200
gen = user.get_comments(limit=thing_limit)
comments = list(gen)

author_comments = []
comment_no = 0
for comment in comments:
    print comment_no
    author_comments.append((redditor,
            comment.subreddit.display_name,
            comment.link_id.split('_')[1], 
            comment.body, 
            comment.permalink))
    comment_no += 1
    
cur = db.cursor()
cur.execute('''SET NAMES 'utf8mb4' ''')

cur.executemany(add_author_comment, author_comments)

db.commit()
db.close()
