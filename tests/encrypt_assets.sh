#!/bin/sh

set -e

stty -echo
printf "Password: "
read password
stty echo
printf "\n"

for file in TestAssets/*
do
    printf "Encrypting $file ... "
    printf "%s" "$password" | gpg --batch --yes --passphrase-fd 0 --symmetric --armor --output "$file"".gpg" "$file"
    rm "$file"
    printf "done\n"
done
