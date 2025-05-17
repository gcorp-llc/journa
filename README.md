# Journa - Multilingual News Platform

Journa is a modern, multilingual news platform built with Next.js and TypeScript, designed to deliver news content in multiple languages (Persian, English, and Arabic). It leverages server-side rendering (SSR) and static site generation (SSG) for optimal performance and SEO. The platform features dynamic news fetching, a robust state management system using Zustand, and advanced SEO capabilities with a dynamically generated sitemap and robots.txt.
Features

Multilingual Support: Supports Persian (fa), English (en), and Arabic (ar) with next-intl for seamless localization.
Dynamic News Content: Fetches news from a backend API with fields like title, content, slug, views, and source URL.
State Management: Uses Zustand for efficient client-side state management of news and ads.
SEO Optimization: Includes a dynamic sitemap (/sitemap.xml) and robots.txt for better search engine indexing.
Responsive Design: Optimized for all devices with a clean and modern UI.
Views Tracking: Tracks and displays the number of views for each news article.
Type-Safe Development: Built with TypeScript for robust type checking and maintainability.

## Tech Stack

Frontend: Next.js 13/14, TypeScript, React
State Management: Zustand
Localization: next-intl
Styling: Tailwind CSS (optional, based on project setup)
API: RESTful API (backend at https://core.journa.ir)
Linting: ESLint with TypeScript plugin
Build Tool: Yarn

### Prerequisites
Before you begin, ensure you have the following installed:

Node.js: Version 18 or higher
Yarn: Version 1.x or higher
Git: For cloning the repository

### Installation

Clone the Repository:
```
git clone https://github.com/gcorp-llc/journa.git
cd journa
```


Install Dependencies:
```
yarn install
```


Set Up Environment Variables:Create a .env.local file in the root directory and add the following:
```
NEXT_PUBLIC_BASE_URL=https://journa.ir
BASE_URL=https://core.journa.ir


NEXT_PUBLIC_BASE_URL: The public URL of the frontend.
BASE_URL: The backend API URL.
```

Run the Development Server:
```
yarn dev
```

Open http://localhost:3000 in your browser to see the application.


Building for Production
To create an optimized production build:
yarn build

To start the production server:
yarn start

### Note: If you encounter memory issues during the build, increase Node.js memory allocation:

```
NODE_OPTIONS=--max_old_space_size=8192 yarn build
```



## API Integration
The frontend communicates with a backend API at https://core.journa.ir. Key endpoints include:

GET /news: Fetches news articles with fields like id, slug, title, content, published_at, views, and source_url.
GET /search: Searches news articles based on query, locale, and pagination.
POST /news/[slug]/increment-views: Increments the view count for a specific news article.

Ensure the backend API is running and accessible.

## SEO and Metadata

Sitemap: Generated dynamically at /sitemap.xml, including homepages (/[locale]) and news pages (/[locale]/news/[slug]) for each locale.
Robots.txt: Configured at /robots.txt to allow indexing of public pages and disallow private routes (e.g., /api/, /admin/).
Structured Data: News articles include JSON-LD structured data for improved SEO.

## Development Guidelines

TypeScript: Always define types for props, state, and API responses in the types/ directory.
Linting: Run yarn lint to check code quality:yarn lint


Formatting: Use Prettier for consistent code formatting:yarn format


Commits: Follow conventional commit messages (e.g., feat: add sitemap generation, fix: resolve memory issue).

## Troubleshooting

Out of Memory Error:

Increase Node.js memory allocation:NODE_OPTIONS=--max_old_space_size=8192 yarn build


Clear the Next.js cache:rm -rf .next


Check LVE limits on CloudLinux servers (if applicable). See CloudLinux Documentation.


TypeScript Errors:

Ensure tsconfig.json and .eslintrc.json are correctly configured.
Run yarn tsc --noEmit to check types.


API Issues:

Verify that the backend API (BASE_URL) is accessible and returns the expected data structure.
Check network logs in the browser or server logs for errors.



Contributing

Fork the repository: https://github.com/gcorp-llc/journa.git.
Create a feature branch (git checkout -b feature/your-feature).
Commit your changes (git commit -m 'feat: your feature description').
Push to the branch (git push origin feature/your-feature).
Open a Pull Request on GitHub.

License
This project is licensed under the MIT License. See the LICENSE file for details.
Contact
For questions or support, contact the project maintainers at:

Email: support@journa.ir
GitHub Issues: gcorp-llc/journa/issues


Built with ❤️ by the Dr Manhattan
