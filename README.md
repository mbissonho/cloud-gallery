# Cloud Gallery

An sample application (scalable and cloud-ready) for image gallery API built with modern backend architecture principles, asynchronous processing, and deep integration with AWS-like services.

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

Install the Localstack [AWS CLI](https://github.com/localstack/awscli-local)

```shell
pip install awscli-local[ver1]
```

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

You will be able to access the frontend on http://localhost:5173

# Technologies Used

## Backend

* PHP 8+
* Laravel Framework
* Laravel Scout (OpenSearch integration)
* AWS S3 (or LocalStack for local development)
* Queue Workers
* PHPUnit
* Docker Compose

[See more](api-laravel/README.md)

## Cloud Provider - Localstack(Simulated AWS)

* Nginx
* LocalStack (AWS simulation)
* Docker Compose

[See more](awslocalstack/README.md)

## Frontend

* Vite
* React
* Tailwind CSS
* Axios HTTP Client
* Docker Compose

[See more](spa-react/README.md)

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

---

# 🗺️ Roadmap

Next features and improvements planned for this project:

* 💳 **Checkout System:** Implementation of a checkout flow to allow users to acquire original high-resolution images.
* 🔄 **Infinite Scroll:** Enhancement of the image search and gallery view with infinite scroll for a more seamless user experience.
* ☁️ **Real AWS Deployment:** A comprehensive guide and demonstration on how to deploy this project using real AWS infrastructure, utilizing services like **AWS CDK**, **Amazon ECR**, **Amazon ECS**, and others.
