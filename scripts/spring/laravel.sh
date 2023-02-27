#!/bin/sh

chmod 666 storage/logs/*.log

chmod 777 storage/logs \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/app
