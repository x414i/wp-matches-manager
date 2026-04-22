=== United Misrata Matches Manager ===
Contributors: alittihadalmisrati
Tags: football, matches, sports, club, arabic, rtl
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

نظام احترافي لإدارة مباريات نادي الاتحاد المصراتي لكرة القدم والرياضات الأخرى.

== Description ==

United Misrata Matches Manager is a comprehensive, modular WordPress plugin built for Al-Ittihad Al-Misrati SC. It enables the club to manage all matches across different sports and teams, and display them beautifully on the frontend via a powerful shortcode.

**Key Features:**

* Custom Post Type: المباريات (Matches)
* Custom Post Statuses: قادمة / مباشرة / انتهت / مؤجلة
* Three Taxonomies: Teams, Sports, Competitions (with default Arabic terms)
* Full meta boxes for all match data: teams, date/time, stadium, score, referee
* Flexible shortcode `[united_matches]` with 6 parameters
* 4 display views: Cards, Table, Timeline, Tabs
* Dark theme + Green accent (#267d34) design
* Fully RTL (Arabic) layout
* Mobile-first responsive design
* Divi Builder compatible
* Lightweight — no external JS/CSS frameworks
* Transient caching for shortcode performance
* Arabic admin UI with inline documentation page

== Installation ==

1. Upload the `united-misrata-matches-manager` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to المباريات → إضافة مباراة to add your first match.
4. Use the shortcode `[united_matches]` in any page or Divi module.

== Frequently Asked Questions ==

= How do I display only upcoming matches? =

Use: `[united_matches view="cards" status="upcoming"]`

= How do I filter by team? =

Use the team taxonomy slug: `[united_matches team="الفريق-الأول"]`

= Is this compatible with Divi builder? =

Yes. The shortcode renders clean, scoped HTML that does not conflict with Divi's styles.

== Changelog ==

= 1.0.0 =
* Initial release.
