=== upBlock - Block Unwanted HTTP Requests ===
Contributors: uptools
Tags: performance, security, http, monitoring, requests
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Monitor, log and block unwanted HTTP API calls in WordPress admin to improve performance while maintaining essential functionality.

== Description ==

upBlock helps you identify and control unwanted HTTP requests in your WordPress admin dashboard while maintaining essential functionality like updates and security checks.

WordPress plugins and themes often load various external resources in your admin dashboard, including unwanted advertisements, news feeds, and third-party analytics. While some of these connections are necessary (like security and update checks), many are just:

* Slowing down your admin dashboard with unnecessary background requests
* Potentially exposing your site to unwanted tracking and security risks
* Consuming server resources with unrequested external content

= Key Features =

* Monitor all HTTP requests made from your WordPress admin
* Block unwanted domains and URL patterns
* Detailed request logging with response times
* View top domains making requests
* Improve admin performance by blocking unnecessary requests
* Enhanced security through request monitoring
* Automatic log cleanup for optimal database performance

= Developer Friendly =

Add your own blocked domains and URLs using filters:

`
// Filter blocked domains
add_filter('upblock_blocked_domains', function($domains) {
    $domains[] = 'example.com';
    return $domains;
});

// Filter blocked URLs
add_filter('upblock_blocked_urls', function($urls) {
    $urls[] = 'https://example.com/api/';
    return $urls;
});
`

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/upblock` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to upTools > upBlock in your admin menu to start monitoring and blocking requests

== Frequently Asked Questions ==

= Will this plugin block WordPress updates? =

No, upBlock is designed to maintain essential WordPress functionality like updates and security checks while blocking unwanted requests.

= How does request blocking work? =

You can block requests in two ways:
1. Add domains to the blocklist
2. Add URL patterns to block specific endpoints

= How long are logs kept? =

You can configure the log retention period from 1 to 365 days. Automatic cleanup ensures your database stays optimized.

= Is it compatible with caching plugins? =

Yes, upBlock is compatible with all major caching plugins and has been tested with popular WordPress themes and page builders.

== Screenshots ==

1. Main Dashboard - Monitor HTTP requests and blocked domains
2. Settings Page - Configure blocking rules and logging options
3. Top Domains - View which services are making the most requests

== Changelog ==

= 1.0.0 =
* Initial release
* Core monitoring functionality
* Domain blocking system
* Logging system implementation
* Admin interface

== Upgrade Notice ==

= 1.0.0 =
Initial release of upBlock. Start monitoring and blocking unwanted HTTP requests in your WordPress admin.

== Additional Information ==

= Security =

* Please report security issues to security@uptools.io
* We take security seriously and will respond promptly
* Keep the plugin updated and regularly review blocked domains

= Performance =

The plugin is designed with performance in mind:
* Minimal impact on front-end
* Efficient database queries
* Automatic log cleanup
* Optimized blocking system

= Support =

For support and documentation, please:
1. Check our [documentation](https://github.com/uptools-io/upblock/wiki)
2. Visit our [issues page](https://github.com/uptools-io/upblock/issues)
3. Review our [discussions](https://github.com/uptools-io/upblock/discussions)

For security issues, please report them directly through our [security page](https://github.com/uptools-io/upblock/security). 