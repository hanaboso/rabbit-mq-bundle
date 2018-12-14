#!/usr/bin/env bash

getent passwd dev || groupadd dev -g ${DEV_GID} && useradd -m -u ${DEV_UID} -g ${DEV_GID} dev
export HOME=/home/dev

exec "$@"

chmod -R 774 /srv/project/var/log && chown -R dev:dev /srv/project/var/log
chmod -R 774 /srv/project/var/cache && chown -R dev:dev /srv/project/var/cache
