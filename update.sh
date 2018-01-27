#!/usr/bin/env bash

if  [ ! -n "$1" ]; then
    echo 'Please Input Version';
    exit;
fi


if  [ ! -n "$2" ]; then
    echo 'Please Input Commit Message';
    exit;
fi

git tag -d $1
git push origin :refs/tags/$1


git add .
git commit -m "$2"
git push

git tag $1
git push origin --tags
