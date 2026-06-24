#!/bin/sh
# Hostinger cron wrapper for CodeIgniter Tasks scheduler.
# hPanel cron (Custom, every minute):
#   /bin/sh /home/u597458177/domains/bigmrestaurant.com/public_html/opensourcepos/deploy/tasks-run.sh
#
# Test manually over SSH:
#   /bin/sh deploy/tasks-run.sh
#   tail -20 writable/logs/cron.log

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

/usr/bin/php spark tasks:run >> writable/logs/cron.log 2>&1
