# Event Organiser Extras

Extends the Event Organiser plugin with event display shortcodes, an out-of-the-box event meta sidebar with registration link logic, and a future events admin menu link.

## Features

### Shortcodes

This plugin registers the following shortcodes:

- `[eox_event_occurrence]` -> 5 Tuesdays
- `[eox_event_recurrence]` -> every month on the second Thursday
- `[eox_event_date]` -> April 8 - May 6, 2025
- `[eox_event_date time="true"]` -> April 8 - May 6, 2025 @ 7:00 pm – 8:30 pm
- `[eox_event_date time="true" timezone="true" occurrence="true"]` -> 5 Tuesdays | April 8 - May 6, 2025 @ 7:00 pm – 8:30 pm EDT
- `[eox_event_times]` -> 7:00 pm – 8:30 pm EDT
- `[eox_event_sidebar_meta]` -> event details sidebar markup with theme override support

Supported attributes:

- `[eox_event_date]` supports `time`, `timezone`, `occurrence`
- `[eox_event_times]` supports `timezone`
- These are boolean shortcode attributes passed as string values, for example `time="true"` or `timezone="false"`

They must be used in the context of an Event Organiser event.

### Event Sidebar

The `[eox_event_sidebar_meta]` shortcode provides an out-of-the-box event sidebar for single event pages. It includes default markup and styles for common event details, plus registration link logic for single and recurring events. If you need custom markup, copy the default template into your theme at `event-organiser-extras/event-sidebar-meta.php` and customize it there.

The sidebar uses CiviCRM Event Organiser for registration links. For recurring events, EOX limits the active occurrence links before display. By default it shows the next active registration link.

#### Recurring link display modes

- `next` shows the next active registration link. (default)
- `all` shows all active recurring registration links.
- `limit` shows a fixed number of active recurring registration links. Use `eox/register_links/recurring/limit` to set the number.

Example:

```php
add_filter( 'eox/register_links/recurring/display_mode', function() {
	return 'limit';
} );

add_filter( 'eox/register_links/recurring/limit', function() {
	return 3;
} );
```
#### Styling
When the template is overridden in the theme, the plugin does not enqueue its default sidebar stylesheet. In that case, the theme is expected to provide CSS in `style.css`. You can copy the styles from `assets/css/event-organiser-extras.css` to get started.

### Admin Menu

Adds a **Future Events** submenu item under the Events admin menu. It links to the existing Event Organiser events list filtered with `eo_interval=future` parameter.

## Requirements

- WordPress
- [WPCV Event Organiser](https://develop.tadpole.cc/plugins/wpcv-event-organiser)
- [CiviCRM Event Organiser](https://github.com/christianwach/civicrm-event-organiser) required if you need registration running through CiviCRM
