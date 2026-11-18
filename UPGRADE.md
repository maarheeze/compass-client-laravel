# Upgrade guide

Breaking changes per major version, newest first.

## 1.x to 2.0

The uptime checks can no longer be set with environment variables:
`COMPASS_CLIENT_UPTIME_CHECK_PATHS` and `COMPASS_CLIENT_UPTIME_CHECK_ROUTES` are
removed. If you used them, publish the config and move their values into
`uptime_checks`.

Each path and route is checked every 15 minutes, or at the number of minutes
given with it: `'/contact' => 60`. The status response reports each endpoint as
an object with a `path` and a `frequency_minutes`, instead of a path string.

The key is no longer the same on every site. Set `COMPASS_CLIENT_KEY` to the key
Compass generated for the site, shown on the site's page in Compass.
