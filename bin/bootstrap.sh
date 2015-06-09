#!/usr/bin/env bash

# Meant to be run from root directory of project
# ./bin/bootstrap
DROPBOX_FOLDER=aj-dev/kennethware

# All config files for project
files=("config/deploy/production.rb")

for file in "${files[@]}"
do
  if [ -e $file ]
  then
    rm $file
  fi
  ln -s "$HOME/Dropbox/$DROPBOX_FOLDER/$file" $file
done

# project specific code or symlinks
# cd config
# ln -s database.development.yml database.yml
