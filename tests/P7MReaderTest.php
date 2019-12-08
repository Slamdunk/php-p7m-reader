<?php

declare(strict_types=1);

namespace Slam\P7MReader\Tests;

use PHPUnit\Framework\TestCase;
use Slam\P7MReader\P7MReader;
use Slam\P7MReader\P7MReaderException;
use SplFileObject;

/**
 * @covers \Slam\P7MReader\P7MReader
 * @covers \Slam\P7MReader\P7MReaderException
 */
final class P7MReaderTest extends TestCase
{
    /**
     * @var SplFileObject
     */
    private $referenceP7m;
    /**
     * @var SplFileObject
     */
    private $referenceXmlOutput;
    /**
     * @var SplFileObject
     */
    private $referenceCrtOutput;
    /**
     * @var SplFileObject
     */
    private $p7mToWorkOn;

    protected function setUp(): void
    {
        $this->referenceP7m       = new SplFileObject(__DIR__ . '/TestAssets/OK.xml.p7m');
        $this->referenceXmlOutput = new SplFileObject(__DIR__ . '/TestAssets/OK.xml');
        $this->referenceCrtOutput = new SplFileObject(__DIR__ . '/TestAssets/OK.crt');

        $draftP7m = __DIR__ . '/TempOutput/OK.xml.p7m';
        \copy($this->referenceP7m->getPathname(), $draftP7m);

        $this->p7mToWorkOn = new SplFileObject($draftP7m);
    }

    public function testWorkingSample(): void
    {
        $p7mReader = P7MReader::decodeFromFile($this->p7mToWorkOn, __DIR__ . '/TempOutput');

        self::assertSame(\base64_encode((string) \file_get_contents($this->referenceP7m->getPathname())), $p7mReader->getP7mBase64Content());
        self::assertFileEquals($this->referenceXmlOutput->getPathname(), $p7mReader->getContentFile()->getPathname());
        self::assertFileEquals($this->referenceCrtOutput->getPathname(), $p7mReader->getCertFile()->getPathname());
        self::assertArrayHasKey('subject', $p7mReader->getCertData());
    }

    public function testDecodingFromString(): void
    {
        $p7mContent       = (string) \file_get_contents($this->p7mToWorkOn->getPathname());
        $p7mContentBase64 = \base64_encode($p7mContent);

        $p7mReader = P7MReader::decodeFromBase64($p7mContentBase64, __DIR__ . '/TempOutput');

        self::assertSame($p7mContentBase64, $p7mReader->getP7mBase64Content());
        self::assertFileEquals($this->referenceXmlOutput->getPathname(), $p7mReader->getContentFile()->getPathname());
        self::assertFileEquals($this->referenceCrtOutput->getPathname(), $p7mReader->getCertFile()->getPathname());
        self::assertArrayHasKey('subject', $p7mReader->getCertData());
    }

    public function testTamperedSample(): void
    {
        $tamperedFile = new SplFileObject(__DIR__ . '/TestAssets/TAMPERED.xml.p7m');

        $this->expectException(P7MReaderException::class);
        $this->expectExceptionMessage('asn1 parse error');

        P7MReader::decodeFromFile($tamperedFile, __DIR__ . '/TempOutput');
    }
}
