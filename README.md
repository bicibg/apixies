# Apixies.io

Apixies is a robust API toolkit designed to simplify development and improve security through inspection tools and utilities for developers.

![Apixies Logo](https://apixies.io/logo.png)

## 🚀 Features

- **API Inspectors**:
    - Email Inspector - Validate and inspect email addresses
    - Security Headers Inspector - Check website security headers
    - SSL Health Inspector - Analyze SSL certificate health and configuration
    - User Agent Inspector - Parse and analyze user agent strings

- **Developer Tools**:
    - API Dashboard with usage analytics
    - Sandbox environment for testing
    - Comprehensive documentation
    - API key management system

- **Security Features**:
    - Correlation ID tracking
    - Request logging
    - Input sanitization
    - Secure headers

## 💻 Technology Stack

- **Framework**: Laravel 12
- **PHP**: 8.3+
- **Database**: MySQL/PostgreSQL
- **Frontend**: Tailwind CSS, Alpine.js
- **Admin Panel**: Filament
- **Deployment**: Docker-ready

## 📋 Requirements

- PHP 8.3 or higher
- Composer
- Node.js & NPM
- Database (MySQL, PostgreSQL)

## ⚙️ Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/bicibg/apixies.git
   ```

2. Navigate to the project directory:
   ```bash
   cd apixies
   ```

3. Install PHP dependencies:
   ```bash
   composer install
   ```

4. Install JavaScript dependencies:
   ```bash
   npm install
   ```

5. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

6. Generate application key:
   ```bash
   php artisan key:generate
   ```

7. Configure your database in the `.env` file.

8. Run migrations:
   ```bash
   php artisan migrate
   ```

9. Build assets:
   ```bash
   npm run build
   ```

10. Start the development server:
    ```bash
    php artisan serve
    ```

## 🧪 API Usage

### Authentication

Apixies API uses API key authentication. You can generate an API key from the web interface after registering an account.

```bash
curl -X GET "https://api.apixies.io/v1/health" \
  -H "X-API-Key: your-api-key-here"
```

### Available Endpoints

- `/api/v1/health` - Check API health
- `/api/v1/readiness` - Check API readiness
- `/api/v1/email-inspector` - Email validation and inspection
- `/api/v1/ssl-health-inspector` - SSL certificate validation
- `/api/v1/security-headers-inspector` - Security headers inspection
- `/api/v1/user-agent-inspector` - User agent parsing
- `/api/v1/html-to-pdf` - Convert HTML to PDF

## 📚 Documentation

Comprehensive documentation is available at [https://apixies.io/docs](https://apixies.io/docs).

The documentation includes:
- Getting started guide
- Authentication information
- Endpoint details
- Code examples
- Best practices

## 🛠️ Administration

The admin panel can be accessed at `/admin` after setup. It provides:
- API usage analytics
- User management
- Suggestion management
- System configuration

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the project
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgements

- [Laravel](https://laravel.com)
- [Filament](https://filamentphp.com)
- [Tailwind CSS](https://tailwindcss.com)

---

Made with ❤️ by Bugra Ergin © 2025
