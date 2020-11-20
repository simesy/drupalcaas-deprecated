#!/usr/bin/env bash
IFS=$'\n\t'
set -euo pipefail

curl -sS https://platform.sh/cli/installer | php

if [ -z "${CIRCLE_PULL_REQUEST}" ]; then
  PLATFORMSH_ENVIRONMENT=${CIRCLE_BRANCH}
else
  PR_NUMBER="$(echo ${CIRCLE_PULL_REQUEST} | grep / | cut -d/ -f7-)"
  PLATFORMSH_ENVIRONMENT="pr-${PR_NUMBER}"
fi
echo "export PLATFORMSH_ENVIRONMENT=${PLATFORMSH_ENVIRONMENT}" >> $BASH_ENV
