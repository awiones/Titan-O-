<p align="center">
  <img src="assets/img/images.PNG" alt="Titan 'O' Logo" width="300">
</p>

<p align="center">
  <a href="https://github.com/awiones/Titan-O-/stargazers"><img alt="GitHub stars" src="https://img.shields.io/github/stars/awiones/Titan-O-"></a>
  <a href="https://github.com/awiones/Titan-O-/network"><img alt="GitHub forks" src="https://img.shields.io/github/forks/awiones/Titan-O-"></a>
  <a href="https://github.com/awiones/Titan-O-/issues"><img alt="GitHub issues" src="https://img.shields.io/github/issues/awiones/Titan-O-"></a>
  <a href="https://github.com/awiones/Titan-O-/blob/main/LICENSE"><img alt="GitHub license" src="https://img.shields.io/github/license/awiones/Titan-O-"></a>
</p>

# Titan 'O'

Titan 'O' is a web-based platform that allows you to run Ollama AI models offline on your website. This project provides a user-friendly interface for interacting with AI models, managing settings, and viewing performance metrics.

## Preview

<p align="center">
  <img src="assets/img/screenshot.png" alt="Titan 'O' Screenshot" width="600">
</p>


## Features

- **Real-Time AI Chat**: Engage in real-time conversations with AI models.
- **Model Management**: Easily add, remove, and switch between different AI models.
- **Performance Metrics**: View detailed performance metrics for each model.
- **User Settings**: Customize your experience with various settings including theme, language, and notifications.
- **Offline Access**: Run AI models offline by accessing your local Ollama instance.

## Installation

1. Clone the repository:
    ```sh
    git clone https://github.com/awiones/Titan-O-.git
    cd Titan-O-
    ```

2. Ensure you have MySQL installed and running on your machine.

3. Navigate to the [chat](http://_vscodecontentref_/4) directory and configure your database connection in `config.php`:
    ```php
    // filepath: ../chat/config/config.php
    <?php
    $host = 'localhost';
    $db = 'your_database_name';
    $user = 'your_database_user';
    $pass = 'your_database_password';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
    ```

4. Import the [mysql.txt](http://_vscodecontentref_/5) file into your MySQL database:
    ```sh
    mysql -u your_database_user -p your_database_name < mysql.txt
    ```

5. Start your local server to serve the project files.


## Usage

1. Open your browser and navigate to the local server address.
2. Register or log in to access the chat interface.
3. Use the settings page to customize your experience and manage AI models.
4. Start chatting with the AI models and view performance metrics.

## Contributing

Contributions are welcome! Please fork the repository and create a pull request with your changes.

## License

This project is licensed under the ISC License. See the LICENSE file for details.

## Contact

For any questions or inquiries, please contact the project maintainer at [awiones@gmail.com](mailto:awiones@gmail.com).
