#!/bin/bash
sudo -u webapp php /var/app/current/bin/console --env=prod cache:clear
