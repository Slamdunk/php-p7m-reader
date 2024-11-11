<?php

declare(strict_types=1);

namespace Slam\P7MReader;

use SplFileObject;
use Symfony\Component\Process\Process;

final class P7MReader implements P7MReaderInterface
{
    private SplFileObject $p7mFile;
    private SplFileObject $contentFile;
    private SplFileObject $certFile;

    private function __construct(SplFileObject $p7mFile, SplFileObject $contentFile, SplFileObject $certFile)
    {
        $this->p7mFile     = $p7mFile;
        $this->contentFile = $contentFile;
        $this->certFile    = $certFile;
    }

    /** @throws P7MReaderException */
    public static function decodeFromFile(SplFileObject $p7m, ?string $tmpFolder = null): P7MReaderInterface
    {
        $tmpFolder ??= \sys_get_temp_dir();

        $contentFilename = \tempnam($tmpFolder, \time() . '_');
        \assert(false !== $contentFilename);

        $crtFilename = \tempnam($tmpFolder, \time() . '.crt_');
        \assert(false !== $crtFilename);

        $opensslBinary = \trim(self::exec(['which', 'openssl']));
        \assert(isset($opensslBinary[0]));

        self::exec([$opensslBinary, 'pkcs7', '-inform', 'DER', '-in', $p7m->getPathname(), '-print_certs', '-out', $crtFilename]);
        self::exec([$opensslBinary, 'cms', '-verify', '-in', $p7m->getPathname(), '-inform', 'DER', '-noverify', '-signer', $crtFilename, '-out', $contentFilename, '-no_attr_verify']);

        return new self($p7m, new SplFileObject($contentFilename), new SplFileObject($crtFilename));
    }

    /** @throws P7MReaderException */
    public static function decodeFromBase64(string $p7mBase64Content, ?string $tmpFolder = null): P7MReaderInterface
    {
        $tmpFolder ??= \sys_get_temp_dir();
        $p7mFilename = \tempnam($tmpFolder, \time() . '.p7m_');
        \assert(false !== $p7mFilename);
        \file_put_contents($p7mFilename, \base64_decode($p7mBase64Content, true));

        return self::decodeFromFile(new SplFileObject($p7mFilename), $tmpFolder);
    }

    /** @param string[] $command */
    private static function exec(array $command): string
    {
        $process = new Process($command);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new P7MReaderException($process->getErrorOutput());
        }

        return $process->getOutput();
    }

    public function getP7mFile(): SplFileObject
    {
        return $this->p7mFile;
    }

    public function getContentFile(): SplFileObject
    {
        return $this->contentFile;
    }

    public function getCertFile(): SplFileObject
    {
        return $this->certFile;
    }

    /** @return array<string, mixed> */
    public function getCertData(): array
    {
        return (array) \openssl_x509_parse((string) \file_get_contents($this->certFile->getPathname()));
    }
}
