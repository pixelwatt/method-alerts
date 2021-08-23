# Method Alerts

This plugin implements a system for displaying alerts on specific pages or posts, loading alerts through the browser to keep performance impact low. **This plugin requires CMB2 and a theme built using Bootstrap 5.**

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
- [ ] Add ability to schedule alerts.