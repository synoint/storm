#!/usr/bin/env bash
if [[ -n $(git status --porcelain) ]]
then 
    echo "repo is dirty";
    exit 1;
fi
git checkout master;
git push;
yarn encore prod;
composer dump-autoload -o --no-dev;
eb deploy;
echo "deployed!";
composer dump-autoload;
echo "";
exit 0;
