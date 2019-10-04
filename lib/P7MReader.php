<?php

declare(strict_types=1);

namespace Slam\P7MReader;

use SplFileObject;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class P7MReader
{
    /**
     * @var string
     */
    private $bin;

    /**
     * @var SplFileObject
     */
    private $p7m;

    /**
     * @var SplFileObject
     */
    private $originalFile;

    /**
     * @var SplFileObject
     */
    private $certFile;

    public function __construct(SplFileObject $p7m)
    {
        $process = Process::fromShellCommandline('command -v openssl');
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->bin =  \trim($process->getOutput());
        $this->p7m = $p7m;

        $originalFile = \substr($this->p7m->getPathname(), 0, -4);

        // Verifica del file p7m
        $process = Process::fromShellCommandline(
            \sprintf('%s cms -verify -out /dev/null -inform DER -noverify -in %s 2>&1',
                $this->bin,
                \escapeshellarg($this->p7m->getPathname())
            )
        );
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Decodifica del file p7m
        $process = Process::fromShellCommandline(
            \sprintf('%s smime -verify -inform DER -in %s -noverify -out %s',
                $this->bin,
                \escapeshellarg($this->p7m->getPathname()),
                \escapeshellarg($originalFile)
            )
        );
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->originalFile = new SplFileObject($originalFile);

        $certFile   = $this->p7m->getPathname() . '.crt';

        // Estrazione certificato
        $process = Process::fromShellCommandline(
            \sprintf(
                '%s pkcs7 -inform DER -print_certs -in %s -out %s',
                $this->bin,
                \escapeshellarg($this->p7m->getPathname()),
                \escapeshellarg($certFile)
            )
        );
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->certFile = new SplFileObject($certFile);
    }

    public function getP7mFile(): SplFileObject
    {
        return $this->p7m;
    }

    public function getOriginalFile(): SplFileObject
    {
        return $this->originalFile;
    }

    public function getCertFile(): SplFileObject
    {
        return $this->certFile;
    }

    public function getCertData(): array
    {
        return \openssl_x509_parse(\file_get_contents($this->certFile->getPathname()));
    }
}
