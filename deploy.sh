#!/usr/bin/env bash
if [[ -n $(git status --porcelain) ]]
then
    echo "repo is dirty";
    exit 1;
fi

git checkout master;
git push;
composer install;
yarn install;
yarn encore prod;
rm -rf ./var/cache/*

# include local env for New Relic license
. .env
. .env.local
# replace with New Relic license
sed -i "s/_THIS_KEY_IS_REPLACED_DURING_DEPLOYMENT_/${NEW_RELIC_LICENSE}/" .ebextensions/66_new_relic_php.config
sed -i "s/_THIS_KEY_IS_REPLACED_DURING_DEPLOYMENT_/${NEW_RELIC_LICENSE}/" .ebextensions/67_newrelic_infra.config

eb deploy survey-81;

# restore replacement string
sed -i "s/${NEW_RELIC_LICENSE}/_THIS_KEY_IS_REPLACED_DURING_DEPLOYMENT_/" .ebextensions/66_new_relic_php.config
sed -i "s/${NEW_RELIC_LICENSE}/_THIS_KEY_IS_REPLACED_DURING_DEPLOYMENT_/" .ebextensions/67_newrelic_infra.config

# save deployment in NewRelic
./bin/console nr:deploy

echo "deployed!";
echo "";
exit 0;
