# Event Organiser Extras

WordPress shortcodes for the Event Organiser plugin, intended for reusable event displays in query builders and to lesser extent PHP templates if you must.

## Shortcodes

This plugin registers the following shortcodes:

- `[eox_event_occurrence]` -> 5 Tuesdays
- `[eox_event_recurrence]` -> every month on the second Thursday`
- `[eox_event_date]` -> April 8 - May 6, 2025
- `[eox_event_date time="true"]` -> April 8 - May 6, 2025 @ 7:00 pm – 8:30 pm
- `[eox_event_date time="true" timezone="true" occurrence="true"]` -> 5 Tuesdays | April 8 - May 6, 2025 @ 7:00 pm – 8:30 pm EDT
- `[eox_event_times]` -> 7:00 pm – 8:30 pm EDT
- `[eox_sidebar_meta]` -> event details sidebar markup with theme override support

Supported attributes:

- `[eox_event_date]` supports `time`, `timezone`, `occurrence`
- `[eox_event_times]` supports `timezone`
- These are boolean shortcode attributes passed as string values, for example `time="true"` or `timezone="false"`

They must be used in the context of an Event Organiser event

## Requirements

- WordPress
- [WPCV Event Organiser](https://develop.tadpole.cc/plugins/wpcv-event-organiser)
- [CiviCRM Event Organiser](https://github.com/christianwach/civicrm-event-organiser) integration for registration link output

## Event Meta Sidebar Template

This plugin includes a default sidebar template for the `[eox_sidebar_meta]` shortcode. If you need custom markup, copy it into your theme at `event-organiser-extras/event-meta-event-single-sidebar.php` and customize it there.

When the template is overridden in the theme, the plugin does not enqueue its default sidebar stylesheet. In that case, the theme is expected to provide CSS in `style.css`. You can copy the styles from `assets/css/event-organiser-extras.css` to get started.
