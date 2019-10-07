<?php

declare(strict_types=1);

namespace Slam\P7MReader;

use SplFileObject;

final class P7MReader implements P7MReaderInterface
{
    /**
     * @var string
     */
    private $p7mBase64Content;

    /**
     * @var SplFileObject
     */
    private $contentFile;

    /**
     * @var SplFileObject
     */
    private $certFile;

    private function __construct(string $p7mBase64Content, SplFileObject $contentFile, SplFileObject $certFile)
    {
        $this->p7mBase64Content = $p7mBase64Content;
        $this->contentFile      = $contentFile;
        $this->certFile         = $certFile;
    }

    /**
     * @throws P7MReaderException
     */
    public static function decodeFromFile(SplFileObject $p7m, string $tmpFolder = null): P7MReaderInterface
    {
        $p7mContent       = \file_get_contents($p7m->getPathname());
        $p7mContentBase64 = \base64_encode($p7mContent);

        return self::decodeFromBase64($p7mContentBase64, $p7m->getBasename(), $tmpFolder);
    }

    public static function decodeFromBase64(string $p7mBase64Content, string $p7mFilename, string $tmpFolder = null): P7MReaderInterface
    {
        $tmpFolder          = $tmpFolder ?? \sys_get_temp_dir();
        $p7mContentForSmime = \chunk_split($p7mBase64Content, 76, \PHP_EOL);

        $smimeFilename = \tempnam($tmpFolder, $p7mFilename . '.smime_');
        \file_put_contents($smimeFilename, \sprintf(<<<'EOF'
MIME-Version: 1.0
Content-Disposition: attachment; filename="smime.p7m"
Content-Type: application/x-pkcs7-mime; smime-type=signed-data;name="smime.p7m"
Content-Transfer-Encoding: base64

%s
EOF
            , $p7mContentForSmime)
        );

        $contentFilename = \tempnam($tmpFolder, \substr($p7mFilename, 0, -4) . '_');
        $crtFilename     = \tempnam($tmpFolder, \substr($p7mFilename, 0, -4) . '.crt_');

        if (true !== ($returnValue = \openssl_pkcs7_verify($smimeFilename, \PKCS7_NOVERIFY, $crtFilename))) {
            throw P7MReaderException::fromReturnValue($returnValue);
        }

        if (true !== ($returnValue = \openssl_pkcs7_verify($smimeFilename, \PKCS7_NOVERIFY, $crtFilename, [], $crtFilename, $contentFilename))) {
            throw P7MReaderException::fromReturnValue($returnValue);
        }

        return new self($p7mBase64Content, new SplFileObject($contentFilename), new SplFileObject($crtFilename));
    }

    public function getP7mBase64Content(): string
    {
        return $this->p7mBase64Content;
    }

    public function getContentFile(): SplFileObject
    {
        return $this->contentFile;
    }

    public function getCertFile(): SplFileObject
    {
        return $this->certFile;
    }

    public function getCertData(): array
    {
        return (array) \openssl_x509_parse((string) \file_get_contents($this->certFile->getPathname()));
    }
}
