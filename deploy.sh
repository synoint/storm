#!/usr/bin/env bash
if [[ -n $(git status --porcelain) ]]
then
    echo "repo is dirty";
    exit 1;
fi

git checkout master;
git push;
composer install;
composer dump-autoload --no-dev --classmap-authoritative;
yarn install;
yarn encore prod;

# include local env for New Relic license
. .env
. .env.local
# replace with New Relic license
sed -i "s/_THIS_KEY_IS_REPLACED_DURING_DEPLOYMENT_/${NEW_RELIC_LICENSE}/" .ebextensions/07_newrelic.config

eb deploy survey-80;

# restore replacement string
sed -i "s/${NEW_RELIC_LICENSE}/_THIS_KEY_IS_REPLACED_DURING_DEPLOYMENT_/" .ebextensions/07_newrelic.config

## Fix ODM permission bug
chmod -R 777 ./var/cache/dev/doctrine
composer dump-autoload
echo "deployed!";
echo "";
exit 0;
