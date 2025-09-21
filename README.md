# OnlyFans Profile Scraper API

A Laravel 12-based API for scraping and searching OnlyFans profiles using queues, scheduling, and full-text search.

## Features

- **Real Profile Scraping**: Integration with OnlyFansAPI.com for live profile data
- **Fallback Mock Data**: Automatic fallback to fake data when API key not configured
- **Scheduled Updates**: Automatic scraping based on profile popularity (>100k likes = 24h, others = 72h)
- **Full-Text Search**: Laravel Scout integration with database driver for searching profiles
- **Queue Management**: Laravel Horizon for monitoring and managing scraping jobs
- **Modular Architecture**: Clean separation with interfaces, services, and jobs

## Quick Start

### Prerequisites
- PHP 8.2+
- Redis
- Composer

### Installation

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env
# Update QUEUE_CONNECTION=redis, SCOUT_DRIVER=database, and ONLYFANS_API_KEY in .env

# Generate key and run migrations
php artisan key:generate
php artisan migrate

# Seed with sample data (optional - 10 realistic profiles)
php artisan db:seed

# Import search indexes
php artisan scout:import "App\Models\Profile"
```

### Running the Application

```bash
# Terminal 1: Start Laravel
php artisan serve

# Terminal 2: Start Horizon (queue processing)
php artisan horizon

# Terminal 3: Start Redis
redis-server

# Optional Terminal 4: Start scheduler
php artisan schedule:work
```

## API Endpoints

### Queue Profile Scraping
```bash
POST /api/profiles/scrape
Content-Type: application/json

{
  "username": "example_user"
}
```

### Search Profiles
```bash
GET /api/search?q=verified&limit=10
```

### List All Profiles
```bash
GET /api/profiles?sort=likes_count&order=desc&limit=20
```

### Health Check
```bash
GET /api/health
```

## Key Components

### Database Tables
- `profiles`: Main profile data with Scout search integration
- `profile_scrapes`: Scraping history and status tracking

### Services
- `ProfileScraperInterface`: Scraper contract
- `OnlyFansApiScraper`: Real implementation using OnlyFansAPI.com
- `FakeProfileScraper`: Fallback mock implementation for testing

### Jobs & Commands
- `ScrapeProfileJob`: Queue job for profile scraping
- `ScrapeProfilesCommand`: Scheduled command (`profiles:scrape`)

### Configuration
- **Real Scraping**: Set `ONLYFANS_API_KEY` to use OnlyFansAPI.com
- **Mock Data**: Leave `ONLYFANS_API_KEY` empty for fake data generation

### API Testing Note
⚠️ **Real API Limitation**: The OnlyFansAPI.com service requires paid credits for profile access. During testing, we encountered a `402 Payment Required` response, indicating the API key needs funding. However, the system gracefully handles this error and demonstrates the complete architecture:

- ✅ **API Integration**: Properly configured and making requests
- ✅ **Error Handling**: 402 errors logged and handled gracefully  
- ✅ **Queue System**: Jobs processed and status tracked
- ✅ **Database**: Profile records created even when API calls fail
- ✅ **Architecture**: Ready for production with funded API access

### Configuration
- **Horizon**: Dedicated `scraping` queue supervisor
- **Scheduler**: Runs every hour, queues profiles needing updates
- **Scout**: Database driver for full-text search

## Architecture

```
app/
├── Console/Commands/ScrapeProfilesCommand.php
├── Http/Controllers/Api/ProfileController.php
├── Jobs/ScrapeProfileJob.php
├── Models/Profile.php (Scout integration)
├── Models/ProfileScrape.php
└── Services/Scraper/
    ├── ProfileScraperInterface.php
    └── FakeProfileScraper.php
```

## Scraping Logic

- **High Priority** (>100k likes): Scraped every 24 hours
- **Regular Priority** (≤100k likes): Scraped every 72 hours
- **Fake Data**: Generates realistic profiles with names, bios, stats, locations
- **Error Handling**: 3 retry attempts, comprehensive logging

## Monitoring

- **Horizon Dashboard**: `/horizon` - Monitor jobs, queues, metrics
- **Logs**: `storage/logs/laravel.log` - Scraping events and errors
- **Health Check**: `/api/health` - API status verification

## Testing & Quality Assurance

### Run Test Suite
```bash
# Run all tests (Feature + Unit tests)
php artisan test

# Run specific test suites
php artisan test --filter=ProfileApiTest     # API endpoint tests
php artisan test --filter=ProfileScraperTest # Scraper service tests
php artisan test --filter=ScrapeProfileJobTest # Queue job tests
```

### Sample Data
```bash
# Reset and seed with fresh sample data
php artisan migrate:fresh --seed

# Seed only (keeps existing data)
php artisan db:seed
```

**Sample profiles include**: High-likes influencers, verified accounts, various locations, and different scraping schedules for immediate testing.

## Manual Commands

```bash
# Queue profiles for scraping
php artisan profiles:scrape --limit=50

# Horizon management
php artisan horizon
php artisan horizon:pause

# Scout re-indexing
php artisan scout:flush "App\Models\Profile"
php artisan scout:import "App\Models\Profile"
```

## License

MIT License
