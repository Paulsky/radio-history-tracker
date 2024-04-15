
# Radio History Tracker

Track and manage your radio station's playlist history efficiently with the Radio History Tracker plugin. This plugin  
integrates seamlessly with your WordPress website, allowing you to capture and display detailed information about the  
tracks played on your radio streams.

For more WordPress plugins, explore our products at [Wijnberg Developments](https://products.wijnberg.dev).

## Features

- Integration with various metadata formats including XML, JSON, HTML, and direct stream data.
- Automatic updating of track history for each radio stream.
- Custom post type for radio streams and tracks, providing easy management and organization.
- Support for track artwork and metadata such as title, artist, and played timestamp.

## Requirements

- WordPress installed and activated.
- PHP version 7.0 or higher.
- Compatible with WooCommerce and popular WordPress themes.

## Installation

To install the plugin, follow these steps:

1. Download the `.zip` file from the [releases page](https://github.com/Paulsky/radio-history-tracker/releases).
2. In your WordPress admin dashboard, navigate to `Plugins` > `Add New`.
3. Click on `Upload Plugin` at the top of the page.
4. Select the downloaded `.zip` file and click `Install Now`.
5. After installation, activate the plugin.

The Radio History Tracker plugin is now ready to use.

## Getting started

After activating the plugin, follow these steps to configure and start using the Radio History Tracker:

### Configuration

1. Navigate to the 'Radio History Tracker' settings page located under the 'Settings' menu in the WordPress admin area.
2. Customize the settings options according to your preferences. You can hide / display the various Custom Post Types  
   and configure the cron time.
3. Save your changes.

### Usage

1. Once the plugin is configured, visit the 'Radio Streams' section to add and manage your radio streams.
2. Add a new radio stream by entering the necessary details such as stream URL, metadata type, and authentication  
   credentials (if required).
3. After adding a radio stream, the plugin will automatically start tracking the playlist history.
4. To view the tracked tracks, navigate to the 'Tracks' section. Here, you can see detailed information about each  
   played track, including title, artist, and played timestamp.
5. Customize the design of the custom post types in your theme options.

Now you're ready to track and manage your radio station's playlist history with ease!

### Shortcodes

The Radio History Tracker plugin includes several shortcodes that allow you to display track information on your pages and posts.

#### Latest tracks

Use the `[latest_tracks]` shortcode to show the most recently played tracks. You can specify how many tracks to display with the `number` parameter. For example:

`[latest_tracks number="10"]`

This displays the latest 10 tracks, sorted by the time they were last played.

#### Most played tracks

To display the tracks that have been played the most, use the `[most_played_tracks]` shortcode. You can also specify the number of tracks to show by using the `number` parameter. For example:

`[most_played_tracks number="10"]`

This will list the top 10 most played tracks.

### Cron setup (optional)

For enhanced accuracy and reliability, consider disabling the WordPress cron system and setting up a real cron job on  
your server. This will ensure that updating the playlist history is executed more accurately and consistently.

#### Steps:

1. **Disable WP Cron**: Add the following line to your `wp-config.php` file to disable the WordPress cron  
   system: `define('DISABLE_WP_CRON', true);`

2. **Set up a Real Cron Job**: Set up a cron job on your server to trigger the WordPress cron system at regular  
   intervals. Add a cron job using a command similar to the following:

   `*/3 * * * * curl -s -o /dev/null /path/to/wordpress/wp-cron.php`

By setting up a real cron job, tasks scheduled by the Radio History Tracker plugin will be executed accurately and  
consistently, ensuring the smooth operation of your radio station's playlist history tracking.

### Permalinks troubleshooting

If you encounter issues with permalinks not working properly after installing the plugin, try resetting the permalinks within WordPress. To do this, navigate to `Settings` > `Permalinks` in the WordPress admin dashboard, then simply click the "Save Changes" button without making any changes. This should refresh the permalinks and resolve any issues related to URL routing.

## Compatibility

This plugin should be compatible with most WordPress themes and plugins. If you encounter any compatibility issues,  
please report them to us.

# Language support

Currently supported languages:

- English
- Dutch (Nederlands)

If you would like to add support for a new language or improve existing translations, please let us know by opening an  
issue or contacting us through our website. You are also welcome to submit a pull request of course!

## Contributing

Your contributions are welcome! If you'd like to contribute to the project, feel free to fork the repository, make your  
changes, and submit a pull request.

## Development and deployment

To prepare your development work for submission, ensure you have `npm` installed and run `npm run deploy`. This command  
packages your changes into a `.zip` file, ready for deployment.

### Steps:

1. Ensure `npm` is installed.
2. Navigate to the project root.
3. Run `npm run deploy`.

The `.zip` file created is ready for use. Please ensure your changes adhere to the project's coding standards.

## Security

If you discover any security related issues, please email us instead of using the issue tracker.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE.txt) for more information.