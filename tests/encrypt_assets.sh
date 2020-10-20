#!/bin/sh

set -e

for file in TestAssets/*
do
    printf "Encrypting $file ... "
    gpg --batch --yes --passphrase="$GPG_PASSPHRASE" --symmetric --armor --output "$file"".gpg" "$file"
    rm "$file"
    printf "done\n"
done
