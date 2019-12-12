#!/bin/sh

set -e

if [ -n "$1" ]; then
    password="$1"
else
    stty -echo
    printf "Password: "
    read password
    stty echo
    printf "\n"
fi

for file in TestAssets/*gpg
do
    printf "Decrypting $file ... "
    printf "%s" "$password" | gpg --batch --passphrase-fd 0 "$file"
    rm "$file"
    printf "done\n"
done
