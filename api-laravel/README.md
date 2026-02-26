# Backend (Laravel API)

The backend layer serves as the core orchestration engine for the Cloud Gallery, managing the entire lifecycle of images, user authentication, and advanced search indexing. It is designed to handle media storage and processing efficiently by leveraging cloud-native patterns.

## Core Responsibilities

### Image Lifecycle & Storage
The API facilitates a decoupled upload process to ensure high availability and performance. Instead of acting as a proxy for large files, it generates secure pre-signed URLs that allow clients to upload images directly to cloud storage. 

Once an upload is initiated, the system tracks the image through various states:
- **Processing:** The initial state where the record is created, and the system waits for external processing (like thumbnail generation) to complete.
- **Available:** Triggered after successful processing, making the image visible and searchable within the gallery.

### Advanced Search & Indexing
To provide a fast and responsive user experience, the backend integrates a dedicated search engine. Every image, along with its metadata and associated tags, is indexed to support:
- Full-text search across titles and descriptions.
- Efficient filtering by categories and tags.
- Personalized search results for specific user profiles.


