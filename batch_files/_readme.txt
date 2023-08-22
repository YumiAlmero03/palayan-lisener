#SETUP [if windows]

open Run window, type shell:startup to open the Startup folder.
copy all files in Copy Me folder and paste them at Startup

Note: it must be in order Xampp startup must be the first one to open

import local_db in phpmyadmin

#for adding new biometric
setup fix ip for biometric
get the ip addres and use "ping [bio_ip]" to check if the biometric and the local server is connected
keep testing till its you can recieve ping
add the ip at server and wait for atleast 2mins
it you hear "Thank you" from the biometric it means its connected


#to update lisener

open cmd and run "git pull"