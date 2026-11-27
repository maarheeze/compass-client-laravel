# Upgrade guide

Breaking changes per major version, newest first.

## 2.x to 3.0

PHP 8.4 is required.

Compass reads the status with a `POST` instead of a `GET`. Upgrade Compass to a
version that posts, or it can no longer read the site.

Only `/` was checked for uptime by default; nothing is checked now. If you
relied on that default, add `'/'` to `uptime_checks.paths` in the published
config.

The status reports when the scheduler last ran, recorded each time
`schedule:run` starts in the default cache store. The scheduler and the web
server need to share that store, as a database or Redis store does; with the
`file` store on one server they share it too, but with the `array` store they
do not.

If you extend `Status`, its constructor takes three more parameters:
`Illuminate\Contracts\Console\Kernel $consoleKernel` after `$config`, and
`Illuminate\Console\Scheduling\Schedule $schedule` and
`SchedulerLastRun $schedulerLastRun` after `$maintenanceMode`. Pass them on to
`parent::__construct()`.

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
