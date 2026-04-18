# Event Organiser Extras

Custom WordPress shortcodes for the [Event Organiser](https://wordpress.org/plugins/event-organiser/) plugin, intended for reusable event displays in builders such as FacetWP and Elementor and to lesser extent PHP templates if you must.

## Shortcodes

This plugin registers the following shortcodes:

- `[event_occurrence]` -> `5 Tuesdays`
- `[event_recurrence]` -> `every month on the second Thursday`
- `[event_date]` -> `April 8 - May 6, 2025`
- `[event_date time="true"]` -> `April 8 - May 6, 2025 @ 7:00 pm – 8:30 pm`
- `[event_date time="true" timezone="true" occurrence="true"]` -> `5 Tuesdays | April 8 - May 6, 2025 @ 7:00 pm – 8:30 pm EDT`
- `[event_times]` -> `7:00 pm – 8:30 pm EDT`

Supported attributes:

- `[event_date]` supports `time`, `timezone`, `occurrence`
- `[event_times]` supports `timezone`
- These are boolean shortcode attributes passed as string values, for example `time="true"` or `timezone="false"`

## Requirements

- WordPress
- [Event Organiser](https://github.com/stephenharris/Event-Organiser)

## Usage

These shortcodes should be used in the context of an Event Organiser event post.
