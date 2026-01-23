# arpitap.site
https://arpitap.site/
Site PSW


Username: Arpita
Password: Cornflower4817!



Grader Username: grader
Grader Password: graderpsw



Github Deployment
I created a deployment script, deploy.php, in my web root (e.g., /var/www/arpitap.site/public_html/) that executes a git pull command to  
refresh the site's content. You then configured a GitHub Webhook in your repository settings to send a POST request to this script's URL
upon every push event. To ensure this communication remains uninterrupted despite your site's basic authentication, I modified your Apache
configuration file to explicitly allow public access to the deploy.php file, while also ensuring that the www-data user has the necessary
permissions and SSH keys to pull code from GitHub securely.

Compression 
After enabling compression, the primary change observed in DevTools is the divergence between Transferred Size and Resource Size in the  
Network tab. The former reflects the smaller, compressed file sent over the wire, while the latter shows the original file size.
Additionally, the Response Headers now include a Content-Encoding field of gzip, verifying that the server-side compression was successful. 



