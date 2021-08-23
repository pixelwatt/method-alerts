# Method Alerts

This plugin implements a system for displaying alerts on specific pages or posts, loading alerts through the browser to keep performance impact low. **This plugin requires CMB2 and a theme built with Bootstrap 5 (or Bootstrap's CSS for the '.alert' class and applicable modifier classes).**

## Functionality

Whenever you modify or add an alert, a json file containing all currently-active and future/perpetual alerts is written to the method-alerts/ subdirectory in wp-uploads/.

When a user visits a post, the post ID is passed to an alerts loader js file, which downloads the json file and checks to see if any alerts are available for that post's ID. If so, those alerts are pushed to any elements with the .method-alerts-container class (you must add this container yourself to your theme's templates, wherever you wish to display alerts).

The URL for the alerts file has a partial timestamp appended to the end of its URL, which changes every 1000 seconds, breaking any browser caches. This means that the user always has a current set of alerts, and means that new alerts may take up to 1000 seconds to appear. As long as the user's alerts data is up to date, scheduled alerts will appear the second they are scheduled to display, and be removed in the same fashion, as the browser is executing all the logic to determine whether or not each alert should appear.

By offsetting the burden of loading alerts to the user's browser, agressive caching can be used on the host server without interupting alert scheduling.

## Controlling Targetable Post Types

By default, only pages are available as alert targets. This can easily be changed, however, by adding a few lines of code to your theme's functions.php file.

For example, to include posts as available targets, you'd include something like the following:

```
function my_theme_change_method_alerts_query( $args ) {   
	$args['post_type'] = array( 'page', 'post' );
    return $args;
} 
add_filter( 'method_alerts_query_args', 'my_theme_change_method_alerts_query', 1 );
```

## Tasks / Roadmap:
- [X] Add ability to schedule alerts.
- [ ] Add plugin documentation to the Method Wiki.