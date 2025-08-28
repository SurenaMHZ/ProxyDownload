A lightweight proxy server for securely serving downloadable files with advanced controls.  
This project provides **two implementations**: one in **PHP** and another in **Node.js**.  

Both versions support:
- **Hashed, unpredictable download links**  
- **Authentication (optional, toggleable)**  
- **Time-limited access (optional, toggleable)**  
- **Download speed limiting**  
- **Concurrent server deployment without link collisions**  
- **Configurable limits (time, bandwidth, tokens, etc.)**

---

## 📂 Project Structure

```
/download-proxy
│
├── php/
│   ├── config.php
│   ├── generate.php
│   └── download.php
│
├── node/
│   ├── config.json
│   ├── server.js
│   ├── package.json
│
└── README.md
```

---

## ⚙️ Features

- 🔒 **Secure hashed links**: Users cannot predict or guess download URLs.  
- ⏳ **Time limits**: Generated links can automatically expire after a configurable duration.  
- 🚦 **Rate limiting**: Control maximum download speed per client.  
- 🔑 **Authentication**: Toggle ON/OFF; when enabled, only requests with valid tokens are allowed.  
- 🔀 **Multi-server safe**: Hashes are unique per server instance.  
- ⚡ **Lightweight**: Works on small VPS servers with minimal resource usage.  

---

## 🚀 PHP Version

### Requirements
- PHP 7.4+  
- Apache or Nginx with `mod_rewrite` enabled  
- VPS or hosting environment  

### Installation
1. Copy files from `/php` into your server root.  
2. Edit `config.php` to set your preferences:  
   - Enable/disable authentication  
   - Enable/disable expiration time  
   - Set speed limits  
   - Configure secret key for hashing  

### Usage
1. Generate a secure download link:
   ```
   https://yourserver.com/generate.php?file=https://example.com/file.zip&auth_token=YOUR_SECRET
   ```
2. Output will return a **hashed download URL** like:
   ```
   https://yourserver.com/download.php?file=https://example.com/file.zip&nonce=abc123&expire=1695000000&token=xyz456
   ```
3. Share the hashed URL with your users.  

### Example Config (`config.php`)
```php
return [
    'secret_key' => 'CHANGE_THIS_SECRET',
    'require_auth' => true,
    'auth_token' => 'MY_SUPER_SECRET_TOKEN',
    'enable_expiry' => true,
    'expiry_time' => 3600, // in seconds
    'limit_speed' => true,
    'speed_kb' => 100,     // KB/s
    'max_file_size_mb' => 0
];
```

---

## 🚀 Node.js Version

### Requirements
- Node.js 18+  
- npm or yarn  
- VPS or hosting environment  

### Installation
1. Copy files from `/node` into your server.  
2. Install dependencies:  
   ```bash
   npm install
   ```
3. Start the server:  
   ```bash
   node server.js
   ```

### Usage
1. Generate a secure download link via API:  
   ```
   GET http://yourserver:3000/generate?file=https://example.com/file.zip&auth_token=YOUR_SECRET
   ```
   Response:
   ```json
   {
     "download_url": "/download?file=https://example.com/file.zip&nonce=abc123&expire=1695000000&token=xyz456",
     "expire_at": "2025-09-18T12:00:00Z"
   }
   ```
2. Share the generated download URL.  

### Example Config (`config.json`)
```json
{
  "secretKey": "CHANGE_THIS_SECRET",
  "requireAuth": true,
  "authToken": "MY_SUPER_SECRET_TOKEN",
  "enableExpiry": true,
  "expiryTime": 3600,
  "limitSpeed": true,
  "speedKB": 100,
  "maxFileMB": 0,
  "port": 3000
}
```

---

## 🔐 Security Notes
- Always change the `secret_key` (PHP) or `secretKey` (Node.js) before using in production.  
- If authentication is enabled, you must provide the correct `authToken` when generating links.  
- Keep your VPS firewall and server software updated.  

---

## ⚡ Advanced Tips
- For **single-use links**, add a Redis or database backend to track used nonces.  
- Implement **rate limiting** at Nginx or Node.js level for DoS protection.  
- Use **TLS** via Nginx or Apache for secure downloads.  
- For large files, ensure VPS memory and network limits are adequate.  

---

## 📖 License
MIT License — free to use, modify, and distribute with attribution.

---

## ✨ Author
Developed by **Surena Zahedi**  
GitHub: [https://github.com/SurenaMHZ](https://github.com/SurenaMHZ)
