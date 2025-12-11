# Trending Skills Auto-Update Setup Guide

## Overview
This guide explains how to set up automatic daily updates for the trending skills feature using cron jobs.

## Files Created
- `trending_skills_fetcher.php` - Main script that fetches and updates skills data
- `trending_skills_api.php` - API endpoint that serves skills data to the dashboard
- `create_trending_skills_table.sql` - Database table structure
- `setup_trending_skills.php` - Setup script to initialize the system

## Setup Steps

### 1. Initialize the System
Run the setup script to create the database table and insert initial data:
```bash
php setup_trending_skills.php
```

### 2. Set Up Cron Job

#### On Linux/Mac (cPanel, VPS, etc.):
```bash
# Edit crontab
crontab -e

# Add this line to run every day at midnight:
0 0 * * * /usr/bin/php /full/path/to/your/project/trending_skills_fetcher.php

# Alternative: Run every 6 hours
0 */6 * * * /usr/bin/php /full/path/to/your/project/trending_skills_fetcher.php
```

#### On Windows (Task Scheduler):
1. Open Task Scheduler
2. Create Basic Task
3. Set trigger to "Daily" at desired time
4. Set action to start a program:
   - Program: `php.exe`
   - Arguments: `C:\full\path\to\your\project\trending_skills_fetcher.php`

#### On Shared Hosting (cPanel):
1. Go to cPanel → Cron Jobs
2. Set schedule (e.g., Daily at 00:00)
3. Command: `/usr/bin/php /home/username/public_html/trending_skills_fetcher.php`

### 3. Manual Testing
Test the system manually:
```bash
# Test the fetcher script
php trending_skills_fetcher.php

# Test the API endpoint
curl http://yoursite.com/trending_skills_api.php
```

## API Integration

### Current Implementation
The system currently uses simulated data from multiple sources:
- GitHub trending repositories
- Stack Overflow tags
- Job board APIs (simulated)

### Real API Integration
To use real APIs, modify `trending_skills_fetcher.php`:

#### GitHub API Example:
```php
function fetchGitHubTrending() {
    $token = 'your_github_token'; // Get from GitHub Developer Settings
    $url = 'https://api.github.com/search/repositories?q=created:>2024-01-01&sort=stars&order=desc';
    
    $context = stream_context_create([
        'http' => [
            'header' => "Authorization: token $token\r\nUser-Agent: YourApp/1.0\r\n"
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $data = json_decode($response, true);
    
    // Process GitHub data...
}
```

#### Stack Overflow API Example:
```php
function fetchStackOverflowTrending() {
    $url = 'https://api.stackexchange.com/2.3/tags?order=desc&sort=popular&site=stackoverflow';
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    // Process Stack Overflow data...
}
```

## Features

### Dynamic Updates
- Skills data updates automatically every 24 hours
- Dashboard shows real-time trending information
- Fallback data available if APIs are unavailable

### Visual Indicators
- Trend arrows (up/down/stable)
- Popularity percentage bars
- Last update timestamp
- Loading states and error handling

### Performance
- Cached data in database
- Efficient API endpoints
- Fallback mechanisms for reliability

## Troubleshooting

### Common Issues

1. **Cron job not running:**
   - Check file paths are absolute
   - Verify PHP executable path
   - Check cron job logs

2. **API errors:**
   - Verify database connection
   - Check error logs
   - Test API endpoints manually

3. **Skills not updating:**
   - Check cron job execution
   - Verify database permissions
   - Test fetcher script manually

### Logs
Check these locations for error logs:
- Server error logs
- PHP error logs
- Cron job logs (`/var/log/cron` on Linux)

## Security Notes
- Use environment variables for API keys
- Implement rate limiting for API calls
- Validate and sanitize all input data
- Use HTTPS for API communications

## Future Enhancements
- Real-time API integrations
- Machine learning for trend prediction
- Industry-specific skill trends
- User preference learning
- Social media trend integration
