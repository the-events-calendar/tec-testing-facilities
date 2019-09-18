# With_Post_Remapping Trait

Remap post A to post B in WordPress cache to make sure any request for post A will return post B.  
Provides wrapper around post remapping methods to make snapshot testing easier.

> Note that requests for post B will return post B.

Why use post remapping?

> To get posts (Events, Venues and Organizers) that are consistently the same across time and test cases.

This is especially useful in snapshot testing.

## Requirements

The trait uses WordPress functions and systems, WordPress should be loaded in the context of the test to use this trait 
successfully.

## Basic usage

First of all `use` the trait in your test case:
```php
<?php

use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;

class RemapTest extends \Codeception\TestCase\WPTestCase {
	use With_Post_Remapping;
}
```

Then make sure there are remap templates in the `tests/_data/remap` folder of the project you're testing.  
In this example we have templates for events, venues and organizers:

```
tests/_data/remap
├── events
│   ├── featured
│   │   └── 1.json
│   └── single
│       ├── 1.json
│       ├── 1.template.json
│       └── 2.json
├── organizers
│   ├── 1.json
│   └── 1.template.json
└── venues
    ├── 1.json
    └── 1.template.json
```

You're free to use the available ones, or add new ones if required. The trait does not require a specific directory structure to work as long as all remap templates live under the `tests/_data/remap` directory.  

A remap template is the JSON version of a post and its custom fields.  
As an example, here is the full content of the `events/featured/1.json`, from The Events Calendar:

```json
{
  "ID": 7,
  "post_author": "0",
  "post_date": "2019-06-11 13:59:35",
  "post_date_gmt": "2019-06-11 13:59:35",
  "post_content": "",
  "post_title": "Test Event - +9 days",
  "post_excerpt": "",
  "post_status": "publish",
  "comment_status": "open",
  "ping_status": "closed",
  "post_password": "",
  "post_name": "test-event-9-days-2",
  "to_ping": "",
  "pinged": "",
  "post_modified": "2019-06-11 13:59:35",
  "post_modified_gmt": "2019-06-11 13:59:35",
  "post_content_filtered": "",
  "post_parent": 0,
  "guid": "http:\/\/test.tri.be\/?tribe_events=test-event-9-days-2",
  "menu_order": 0,
  "post_type": "tribe_events",
  "post_mime_type": "",
  "comment_count": "0",
  "filter": "raw",
  "meta_input": {
    "_tribe_featured": [
      "1"
    ],
    "_EventStartDate": [
      "2019-06-20 13:01:20"
    ],
    "_EventStartDateUTC": [
      "2019-06-20 13:01:20"
    ],
    "_EventEndDate": [
      "2019-06-20 17:01:20"
    ],
    "_EventEndDateUTC": [
      "2019-06-20 17:01:20"
    ],
    "_EventTimezoneAbbr": [
      "CEST"
    ],
    "_EventOrigin": [
      "events-calendar"
    ],
    "_EventShowMap": [
      "1"
    ],
    "_EventShowMapLink": [
      "1"
    ]
  }
}
```

To get a fully-functional event object that is "hydrated" from the template data call the `With_Post_Remapping::get_mock_event` method:

```php
public function test_with_mock_event(  ) {
    $event = $this->get_mock_event( 'events/single/1.json' );

    $this->assertInstanceOf( WP_Post::class, $event );
}
```

The same can be done with the `get_mock_organizer` and `get_mock_venue` methods.  
Each method will return, respectively, `WP_Post` objects with properties added by the `tribe_get_event`, `tribe_get_organizer_object` and `tribe_get_venue_object` functions.  

### Dynamic templates

Templates can have static, fixed, values, like the `events/single/1.json` or have dynamic, Handlebars-like placeholders.  

As an example this is a dynamic template that will create the data for a Venue, the dynamic part is the ID:

```json
{
  "ID": {{ id }},
  "post_author": 1,
  "post_date": "2019-09-17 16:00:32",
  "post_date_gmt": "2019-09-17 14:00:32",
  "post_content": "Venue {{ id }} content",
  "post_title": "Venue {{ id }} title",
  "post_excerpt": "Venue {{ id }} excerpt",
  "post_status": "publish",
  "comment_status": "closed",
  "ping_status": "closed",
  "post_password": "",
  "post_name": "venue-{{ id }}",
  "to_ping": "",
  "pinged": "",
  "post_modified": "2019-09-17 16:00:32",
  "post_modified_gmt": "2019-09-17 14:00:32",
  "post_content_filtered": "",
  "post_parent": 0,
  "guid": "http://products.tribe/?post_type=tribe_venue&#038;p={{ id }}",
  "menu_order": 0,
  "post_type": "tribe_venue",
  "post_mime_type": "",
  "comment_count": 0,
  "filter": "raw",
  "meta_input": {
    "_VenueOrigin": [
      "events-calendar"
    ],
    "_EventShowMapLink": [
      "1"
    ],
    "_EventShowMap": [
      "1"
    ],
    "_VenueShowMapLink": [
      "1"
    ],
    "_VenueShowMap": [
      "1"
    ],
    "_VenueAddress": [
      "100 Rue de Le Chat"
    ],
    "_VenueCity": [
      "Paris"
    ],
    "_VenueCountry": [
      "France"
    ],
    "_VenueProvince": [
      "Ile de France"
    ],
    "_VenueState": [
      "Ile de France"
    ],
    "_VenueZip": [
      "75019"
    ],
    "_VenuePhone": [
      "11223344"
    ],
    "_VenueURL": [
      "http://venue.org"
    ],
    "_VenueStateProvince": [
      "Ile de France"
    ],
    "_VenueOverwriteCoords": [
      "0"
    ],
    "_VenueGeoAddress": [
      "100 Rue de Le Chat Ile de France Paris 75019 France"
    ],
    "_VenueLat": [
      "48.8624784"
    ],
    "_VenueLng": [
      "2.3648085"
    ]
  }
}
```

At runtime the `{{ id }}` placeholder will be replaced with the value of the `id` template variable passed to the `get_mock_venue` method.  

```php
public function test_with_mock_venue(  ) {
    $venue = $this->get_mock_venue( 'venues/1.template.json', [ 'id' => 23 ] );

    $this->assertInstanceOf( WP_Post::class, $venue );
}
```

The guarantee is that, provided the same data, the template will always render the same.  
For readability and maintenance purposes, please add the `.template.json` extension to all dynamic template files.  

The `With_Post_Remapping::get_mock_event` and `With_Post_Remapping::get_mock_organizer` methods support dynamic templates as well.

### Advanced usage of Event mock builder

In our plugins the Event object plays a central role and it's the center of much of our testing and, especially, snapshot testing.  

While replicating templates and using dynamic templates goes a long way, setting up some more complex fixtures (especially for snapshot testing), can require too much boilerplate code.  

For events the `With_Post_Remapping::mock_event` method comes to the rescue:

```php
public function test_with() {
    // Build a mock event based on the 'events/single/1.json' template...
    $event = $this->mock_event( 'events/single/1.json' )

                  // ...add a venue based on the 'venues/1.json' template to it...
                  ->with_venue( 'venues/1.json' )

                  // ...add 2 organizers based on the 'organizers/1.template.json' template to it...
                  ->with_organizers( 2, 'organizers/1.template.json', [
                      [ 'id' => 101 ],
                      [ 'id' => 102 ]
                  ] )

                  // ...make it a recurring event (just the flag, not full recurrence details)...
                  ->is_recurring()

                  // ...feature it...
                  ->is_featured()

                  // ...make it an all-day event...
                  ->is_all_day()

                  // ...make it a 3 day multi-day event...
                  ->is_multi_day( 3 )

                  // ...finally build and return the mock event.
                  ->get();

    $this->assertInstanceOf( WP_Post::class, $event );
}
```

The method will enrich an event, it will not remove flags from it (e.g. it cannot make a featured event not featured), so take some time to choose the correct starting template.

