#!/bin/sh

set -e

for file in TestAssets/*gpg
do
    printf "Decrypting $file ... "
    gpg --decrypt --batch --passphrase="$GPG_PASSPHRASE" --output "$(printf "%s" "$file" | sed 's/\.gpg$//')" "$file"
    rm "$file"
    printf "done\n"
done
