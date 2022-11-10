# CS 425 Fall 2022 Final Project
## Group 8 - Len Washington III, Rajan Savani, Zack Chaffee

## Connections
Domain/Hostname: ```lenwashingtoniii.com```<br>
Website: ```https://cs425.lenwashingtoniii.com```<br>
SSH Port: ```5069```<br>
PostgreSQL Port: ```5078```<br>
Login Structure: ```ssh [your_username]@lenwashingtoniii.com -p 5069 -i {Path to ssh key I gave you}```

Directory Structure: Everything related to the project is stored in ```/cs425``` for ease.

## Git
To update the running version on the server, run the following commands.<br>
```git fetch```<br>
```git reset --hard origin/{branch}```, replacing ```{branch}``` with the name of the current branch, usually ```master```.

## Website
All parts of the project that access the website's API is inside the public_html folder. <br>
__ANYTHING WITHIN THIS FOLDER IS PUBLIC TO THE INTERNET IF NOT SECURED__. Normal internet files like HTML, CSS, even PHP are fine within this folder. But don't add private info. (If you're worried about PHP files getting grabbed without permission, they're run before being sent to the client, so the client only sees the end result. An example is https://cs425.lenwashingtoniii.com/api/datetime.php)