# Shekel Mobility Assessment Project

## Table of Contents

1. Introduction
2. Features
3. Requirements
4. Installation
5. Configuration
6. Running the Application
7. Running Tests
8. API Endpoints
9. License

## Introduction

The Shekel Mobility Assessment Project is a Laravel-based application designed to manage user registrations, logins, wallet transactions, and discounts. This project demonstrates a robust backend API for user authentication, wallet management, and discount application.

## Features

- **User Registration**: Allows new users to register with their name, email, and password.
- **User Login**: Authenticates users and generates an access token.
- **Wallet Management**: Enables users to credit their wallet.
- **Discount Management**: Provides functionality to create and apply discount codes.

## Requirements

- PHP >= 7.4
- Composer
- Laravel 8.x
- MySQL or any other supported database

## Installation

```bash
# Clone the repository
git clone [https://github.com/Emmanuel-Olawuni/shekel.git](https://github.com/Emmanuel-Olawuni/shekel.git)
cd Shekel
```
# Install dependencies
```bash
composer install
```

# Create a copy of the .env file
```bash
cp .env.example .env
```

# Generate the application key
```bash
php artisan key:generate
```

#Database Configuration:

Open the .env file and update the following variables with your database information

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
 ```
# Generate your secret key

```bash
php artisan jwt:secret
```

Set the JWT_SECRET on your env folder to the secrent key

# Migrate the database

```bash
php artisan migrate
```

# Start the local development server
```bash
php artisan serve
```

# Run unit tests
```bash
php artisan test --testsuite=Unit
```

# API Documentation
[https://www.postman.com/emma2001/workspace/shekel/collection/23922527-4a2c8eda-f1e7-492a-b838-c50ec16b1d58?action=share&creator=23922527](https://www.postman.com/emma2001/workspace/shekel/collection/23922527-4a2c8eda-f1e7-492a-b838-c50ec16b1d58?action=share&creator=23922527)



