# United Misrata Matches Manager

A comprehensive WordPress plugin for managing football club matches with advanced features like team logos, detailed match views, and multiple display formats.

## Features

- **Match Management**: Add, edit, and delete matches with detailed information.
- **Team Logos**: Upload and display team logos in various views.
- **Multiple Display Formats**:
  - **Cards View**: Visually appealing cards for recent and upcoming matches.
  - **Timeline View**: Chronological timeline of matches with team logos.
  - **Table View**: Traditional table format for comprehensive match data.
  - **Tabs View**: Filter matches by status (Upcoming, Past, All) with icons.
- **Shortcodes**: Easy-to-use shortcodes for embedding matches on any page.
- **Custom Post Type**: Matches are managed as a custom post type for better organization.
- **Responsive Design**: Fully responsive layouts for all display formats.

## Installation

1. Upload the `united-misrata-matches-manager` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin from the WordPress admin dashboard.
3. Configure plugin settings (if any) in the Settings menu.

## Usage

### Shortcodes

Use the following shortcodes to display matches on your website:

- `[ummm_matches_cards]` - Display matches in cards view
- `[ummm_matches_timeline]` - Display matches in timeline view
- `[ummm_matches_table]` - Display matches in table view
- `[ummm_matches_tabs]` - Display matches in tabs view

### Custom Post Type

1. Go to **Matches** in the WordPress admin menu.
2. Click **Add New** to create a new match.
3. Fill in the match details:
   - **Match Title**: Team A vs Team B
   - **Date & Time**: Set the match date and time
   - **Competition**: Select the competition
   - **Venue**: Enter the match venue
   - **Home Team**: Select the home team
   - **Away Team**: Select the away team
   - **Home Score**: Enter the home team's score
   - **Away Score**: Enter the away team's score
   - **Status**: Set the match status (Upcoming, Past, Cancelled)
   - **Description**: Add match description
   - **Featured Image**: Upload team logos for home and away teams

## Development

### File Structure

```
united-misrata-matches-manager/
├── united-misrata-matches-manager.php  # Main plugin file
├── includes/
│   ├── class-ummm-plugin.php           # Plugin initialization
│   ├── class-ummm-cpt.php              # Custom post type registration
│   ├── class-ummm-shortcodes.php       # Shortcode handlers
│   ├── class-ummm-admin.php            # Admin interface
│   ├── class-ummm-frontend.php         # Frontend display
│   └── class-ummm-settings.php         # Settings management
├── admin/
│   ├── css/
│   │   └── admin-style.css             # Admin styles
│   └── js/
│       └── admin-script.js             # Admin scripts
├── frontend/
│   ├── css/
│   │   └── frontend-style.css          # Frontend styles
│   └── js/
│       └── frontend-script.js          # Frontend scripts
├── templates/
│   ├── cards-view.php                  # Cards template
│   ├── timeline-view.php               # Timeline template
│   ├── table-view.php                  # Table template
│   └── tabs-view.php                   # Tabs template
├── assets/
│   └── images/
│       └── placeholder-logo.png        # Default team logo
└── README.md
```

### Adding New Features

1. Create a new class in the `includes/` directory.
2. Register the class in `class-ummm-plugin.php`.
3. Add necessary styles in `admin/css/` or `frontend/css/`.
4. Add scripts in `admin/js/` or `frontend/js/`.
5. Create or update templates in `templates/` if needed.

## Customization

### Customizing Display

To customize the display of matches, you can:

1. **Override Templates**: Copy the template files from `templates/` to your theme's directory (e.g., `your-theme/united-misrata-matches-manager/`) and modify them.
2. **Use CSS**: Add custom CSS to your theme's `style.css` or create a custom plugin for overrides.
3. **Filter Hooks**: Use the provided filter hooks in the plugin files to modify data before display.

### Available Filters

- `ummm_match_data`: Filter match data before display
- `ummm_cards_template`: Filter the cards template path
- `ummm_timeline_template`: Filter the timeline template path
- `ummm_table_template`: Filter the table template path
- `ummm_tabs_template`: Filter the tabs template path

## Troubleshooting

### Common Issues

1. **Shortcode not working**:
   - Ensure the plugin is activated.
   - Check that the shortcode is used correctly (no extra spaces or characters).
   - Verify that the shortcode is placed in a post or page content, not in a widget (unless the widget supports shortcodes).

2. **Team logos not displaying**:
   - Check that team logos are uploaded as featured images for the match.
   - Ensure the logo files are valid image formats (JPG, PNG, GIF).
   - Verify that the plugin has permission to access the uploads directory.

3. **Styles not loading**:
   - Check browser console for JavaScript errors.
   - Clear browser cache and any caching plugins.
   - Verify that the plugin files are not corrupted.

### Debug Mode

To enable debug mode, add the following to your `wp-config.php` file:

```php
define('UMMM_DEBUG', true);
define('UMMM_DEBUG_LOG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);
```

## License

This plugin is developed for personal use and may be distributed under the terms of the GNU General Public License, version 2 or later.

## Support

For support and feature requests, please contact the development team.

---

**Plugin Version**: 1.0.0
**Last Updated**: 2026-04-22
