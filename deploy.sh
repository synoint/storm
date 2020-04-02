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
eb deploy synostorm-live-php73;
echo "deployed!";
echo "";
exit 0;
