## CHANGELOG

# v1.0.2

A new color scheme, "Unstyled", can now be applied to alerts. If this color scheme is chosen for an alert, its output will not include any Bootstrap markup, and can easily be styled without the need for CSS overrides.

---

# v1.0.1

When generating the alerts json file, the .alert-link css class is now automatically added to any links contained within an alert's content.

---

# v1.0.0

This initial release of Method Alerts allows you to add and maintain alerts via the added method_alerts post type, and supports individual page targeting and scheduling. For alerts to appear, you must add '<div class="method-alerts-container"></div>' to your template files, at the location you wish for alerts to appear.