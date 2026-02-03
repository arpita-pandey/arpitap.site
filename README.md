# arpitap.site
**Github URL:** https://github.com/arpita-pandey/arpitap.site
**URL:** [https://arpitap.site/](https://arpitap.site/)

---

## 🔐 Site Authentication
To access the protected areas of the site, please use the following credentials:
| User Role | Username | Password |
| :--- | :--- | :--- |
| **Admin** | arpita | Cornflower4817! |


## 🔐 Server Authentication
To access the server, please use the following credentials:

| User Role | Username | Password |
| :--- | :--- | :--- |
| **Grader** | grader | graderpsw |


---

## 🚀 GitHub Auto-Deployment
I implemented a "Push-to-Deploy" workflow to automate site updates. 

### Implementation Details:
* **Deployment Script:** A PHP script (`deploy.php`) is located in the web root. When triggered, it executes a `git pull` command to synchronize the server with the latest changes from the GitHub repository.
* **GitHub Webhook:** Configured a webhook in the GitHub repository settings to send a POST request to the `deploy.php` URL immediately upon every `push` event.
* **Apache Configuration:** To ensure the webhook can trigger the update without being blocked by Basic Authentication, I modified the Apache site configuration to explicitly allow public access to `deploy.php` using the `Files` directive and `Satisfy Any`.
* **Security:** The `www-data` user was configured with the necessary SSH keys and permissions to pull code from GitHub securely via the server.



---

## ⚡ Compression & Performance
Server-side compression was enabled to optimize delivery speed and reduce bandwidth usage.

### DevTools Observations:
* **Size Divergence:** In the **Network** tab, there is a significant difference between **Transferred Size** and **Resource Size**. The transferred size is much smaller, representing the compressed data sent over the wire.
* **Header Verification:** The **Response Headers** now include the `Content-Encoding: gzip` field, confirming that the Apache `mod_deflate` module is successfully compressing the HTML content before it leaves the server.


### Server Header Modification:
To modify the HTTP Server response header, I configured Apache as a reverse proxy for itself. The public HTTPS virtual host on port 443 
proxies all requests to an internal Apache virtual host running on port 8080. I then rewrote the Server header in the proxy response layer 
using mod_headers. This approach ensures the header is modified after Apache’s core header injection, guaranteeing that the custom Server 
value is returned in all HTTPS responses.


## Motamo Dashboard: 
* **Live Site Link:** https://arpitap.site/matomo/
*  **Process:** I successfully installed and configured Matomo on my Ubuntu server by creating a dedicated MySQL database and user, downloading the official source package to my web root, and completing the web-based installation wizard. After setting up the administrative Super User account, I integrated the platform into my site by embedding the provided JavaScript tracking snippet into the header of my index.html and hello.php files. This allows for real-time visitor analytics, which can be verified via the live dashboard link provided in this document.


## HW 2: 
* **Free Choice Analytics:** I chose Umami because it provides a lightweight, privacy-oriented analytics workflow (aggregate metrics rather than individual session replay), is straightforward to integrate with a single script, and offers enough reporting for course needs (pageviews, referrers, devices, countries). It also avoids the complexity of larger ad-tech-style tools while still providing actionable traffic insights.


