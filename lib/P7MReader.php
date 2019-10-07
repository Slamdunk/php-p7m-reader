<?php

declare(strict_types=1);

namespace Slam\P7MReader;

use SplFileObject;

final class P7MReader implements P7MReaderInterface
{
    /**
     * @var SplFileObject
     */
    private $p7m;

    /**
     * @var SplFileObject
     */
    private $contentFile;

    /**
     * @var SplFileObject
     */
    private $certFile;

    private function __construct(SplFileObject $p7m, SplFileObject $contentFile, SplFileObject $certFile)
    {
        $this->p7m         = $p7m;
        $this->contentFile = $contentFile;
        $this->certFile    = $certFile;
    }

    public static function decode(SplFileObject $p7m, string $tmpFolder = null): self
    {
        $tmpFolder   = $tmpFolder ?? \sys_get_temp_dir();
        $p7mFilename = $p7m->getPathname();

        $p7mContentForSmime = \file_get_contents($p7mFilename);
        $p7mContentForSmime = \base64_encode($p7mContentForSmime);
        $p7mContentForSmime = \chunk_split($p7mContentForSmime, 76, \PHP_EOL);

        $smimeFilename = \tempnam($tmpFolder, $p7mFilename . '.smime');
        \file_put_contents($smimeFilename, \sprintf(<<<'EOF'
MIME-Version: 1.0
Content-Disposition: attachment; filename="smime.p7m"
Content-Type: application/x-pkcs7-mime; smime-type=signed-data;name="smime.p7m"
Content-Transfer-Encoding: base64

%s
EOF
, $p7mContentForSmime));

        $contentFilename = \tempnam($tmpFolder, \substr($p7mFilename, 0, -4));
        $crtFilename     = \tempnam($tmpFolder, \substr($p7mFilename, 0, -4) . '.crt');

        if (true !== \openssl_pkcs7_verify($smimeFilename, \PKCS7_NOVERIFY | \PKCS7_NOSIGS, $crtFilename)) {
            throw new P7MReaderException(\openssl_error_string());
        }

        if (true !== \openssl_pkcs7_verify($smimeFilename, \PKCS7_NOVERIFY | \PKCS7_NOSIGS, $crtFilename, [], $crtFilename, $contentFilename)) {
            throw new P7MReaderException(\openssl_error_string());
        }

        return new self($p7m, new SplFileObject($contentFilename), new SplFileObject($crtFilename));
    }

    public function getP7mFile(): SplFileObject
    {
        return $this->p7m;
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
