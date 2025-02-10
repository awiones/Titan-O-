<p align="center">
  <img src="https://github.com/awiones/Titan-O-/blob/main/images/logos.jpg" alt="Titan 'O' Logo" width="300">
</p>

<div align="center">

[![GitHub stars](https://img.shields.io/github/stars/awiones/Titan-O-)](https://github.com/awiones/Titan-O-/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/awiones/Titan-O-)](https://github.com/awiones/Titan-O-/network)
[![GitHub issues](https://img.shields.io/github/issues/awiones/Titan-O-)](https://github.com/awiones/Titan-O-/issues)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/awiones/Titan-O-/blob/main/LICENSE)
[![Made with Love](https://img.shields.io/badge/Made%20with-Love-ff69b4.svg)](https://github.com/awiones/Titan-O-)
[![Ollama Compatible](https://img.shields.io/badge/Ollama-Compatible-success.svg)](https://ollama.ai)

</div>

# Titan 'O' - Your Offline AI Chat Platform

Titan 'O' is a powerful web-based platform that enables seamless integration of Ollama AI models into your offline website. Experience enterprise-grade AI capabilities with a sleek, user-friendly interface designed for both developers and end-users.

## 🌟 Key Features

- **Offline AI Processing**: Run AI models locally without internet dependency
- **Real-Time Chat Interface**: Engage in fluid conversations with multiple AI models
- **Advanced Model Management**:
  - Easy model switching and configuration
  - Custom model parameter tuning
  - Model performance monitoring
- **Robust Security**: Local processing ensures data privacy.
- **Performance Analytics Dashboard**:
  - Response time metrics
  - Token usage tracking
  - Model performance comparisons

## 🖥️ Preview

<table align="center">
  <tr>
    <td align="center">
      <img src="https://github.com/awiones/Titan-O-/blob/main/images/buktie2.PNG" alt="Chat Interface" width="480">
    </td>
    <td align="center">
      <img src="https://github.com/awiones/Titan-O-/blob/main/images/login.PNG" alt="Login Page" width="480">
    </td>
  </tr>
</table>

<table align="center">
  <tr>
    <td align="center">
      <img src="https://github.com/awiones/Titan-O-/blob/main/images/settings.PNG" alt="Settings Page" width="700">
    </td>
    <td align="center">
      <img src="https://github.com/awiones/Titan-O-/blob/main/images/bukti.PNG" alt="Chat Page" width="700">
    </td>
  </tr>
</table>

## 🚀 Quick Start

### Prerequisites
- MySQL Server
- PHP 7.4+
- Web Server (Apache/Nginx)
- [Ollama](https://ollama.ai) installed locally

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/awiones/Titan-O-.git
   cd Titan-O-
   ```

2. **Configure Database**
  Update the database connection settings within your project's configuration file.  The specific file name and location will depend on the project's framework.  Ensure the connection details (host, username,   password, database name) match your database setup.

3. **Import Database Schema**
  Execute the SQL script located in database-sql.txt against your database.  You can typically do this using a database client (like phpMyAdmin, MySQL Workbench, etc.) or via the command line:

  ```
  mysql -u <your_username> -p <your_database_name> < database-sql.txt
  ```

4. **Configure Web Server**
   - Point your web server to the project directory
   - Ensure PHP has write permissions for the `/storage` directory

5. **Start Services**
   ```bash
   # Start Ollama service
   ollama serve
   
   # Start your web server
   # Example for PHP's built-in server:
   php -S localhost:8000
   ```

## 💡 Advanced Usage

### Custom Model Configuration
```json
{
  "model": "llama2",
  "parameters": {
    "temperature": 0.7,
    "top_p": 0.9,
    "max_tokens": 2048
  }
}
```

### API Integration
```php
$client = new TitanO\Client();
$response = $client->chat([
    'model' => 'llama2',
    'message' => 'Hello, how are you?'
]);
```

## 🤝 Contributing

We welcome contributions! Here's how you can help:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please read our [Contributing Guidelines](CONTRIBUTING.md) for details.

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🔗 Links

- [Documentation](https://github.com/awiones/Titan-O-/wiki)
- [Issue Tracker](https://github.com/awiones/Titan-O-/issues)
- [Ollama Official Website](https://ollama.ai)

## 📧 Contact

Awiones - [@awiones](https://github.com/awiones) - awiones@gmail.com

Project Link: [https://github.com/awiones/Titan-O-](https://github.com/awiones/Titan-O-)

---

<div align="center">
  <sub>Built with ❤️ by Awiones</sub>
</div>
