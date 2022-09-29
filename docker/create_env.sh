#!/bin/sh
touch .env
echo "LOCAL_LINUX_USER=$(whoami)" > .env
echo "LOCAL_LINUX_USER_UID=$(id -u $(whoami))" >> .env
