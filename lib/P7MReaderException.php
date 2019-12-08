<?php

declare(strict_types=1);

namespace Slam\P7MReader;

use RuntimeException;

final class P7MReaderException extends RuntimeException
{
    /**
     * @param mixed $returnValue
     */
    public static function fromReturnValue($returnValue): self
    {
        $opensslErrors = [];
        while ($message = \openssl_error_string()) {
            $opensslErrors[] = $message;
        }

        return new self(\sprintf('openssl_pkcs7_verify return value: %s%sOpenSSL Errors:%s- %s',
            \var_export($returnValue, true),
            \PHP_EOL,
            \PHP_EOL,
            \implode(\PHP_EOL . '- ', $opensslErrors)
        ));
    }
}
