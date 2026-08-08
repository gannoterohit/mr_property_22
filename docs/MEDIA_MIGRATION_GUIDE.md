# Uploaded Media Migration

Room photos are not stored inside the database. The database stores paths such as
`rooms/room_xxxxx.jpg`; the actual files live under `storage/app/public`.

## Move Site With Images

On the old machine/server:

```bash
php artisan media:export
```

Copy the generated zip from `storage/app/media-backups` to the new machine/server.

On the new machine/server:

```bash
php artisan media:import storage/app/media-backups/public-media-YYYYmmdd-HHmmss.zip
php artisan storage:link
php artisan media:audit
```

If `media:audit` reports missing files, copy those files from the old
`storage/app/public` folder into the same path on the new system.
