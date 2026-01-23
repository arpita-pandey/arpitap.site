# arpitap.site
**URL:** [https://arpitap.site/](https://arpitap.site/)

---

## 🔐 Site Authentication
To access the protected areas of the site, please use the following credentials:

| User Role | Username | Password |
| :--- | :--- | :--- |
| **Admin** | Arpita | Cornflower4817! |
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
