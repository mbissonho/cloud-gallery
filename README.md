# Cloud Gallery

An sample application(scalable and cloud-ready) for image gallery API built with modern backend architecture principles, asynchronous processing, and deep integration with AWS-like services.

---

# About the Project

This project aims to be a reference about how you can start an API application that will use cloud resources and leverage its capacities by using the following approaches:

* 🔐 Secure user authentication
* 📤 Direct-to-S3 uploads using pre-signed URLs
* 🔎 Advanced search capabilities
* ⚡ Asynchronous thumbnail processing via queues
* 🧠 Search indexing with OpenSearch
* 🐳 Fully containerized infrastructure

The architecture follows clean separation of concerns and Laravel best practices, emphasizing scalability, maintainability, and performance.

---

# How To Use

After the repo is cloned in your machine, just run to install and start the project:

```shell
.docker/bin/install && .docker/bin/start
```

When the containers are already created, just run the starter command:

```shell
.docker/bin/start
```

Then you need to access the laravel app container, seed the development data and start queue worker to get background processing working:

```shell
.docker/bin/bash
```

```shell
make reset-data && ./artisan queue:work
```

# Technologies Used

## Backend

* PHP 8+
* Laravel Framework
* Laravel Scout (OpenSearch integration)
* AWS S3 (or LocalStack for local development)
* Queue Workers
* PHPUnit

## Infrastructure

* Docker
* Docker Compose
* Nginx
* LocalStack (AWS simulation)

## Frontend

* Vite
* React
* Tailwind CSS
* Axios HTTP Client

---

Key architectural decisions:

* Direct file upload to S3 to reduce backend load
* Background processing for thumbnails
* Indexed search layer for performance
* API Resources for consistent responses
* Form Requests for input validation

---

# 📎 Technical Highlights

This project demonstrates:

* Cloud-native backend architecture
* Scalable file upload strategy
* Queue-driven asynchronous processing
* Search indexing integration
* Clean layered design
* Fully containerized development environment
