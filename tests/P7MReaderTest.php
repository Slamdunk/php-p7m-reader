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
     * @dataProvider provideOkCases
     */
    public function testWorkingSample(SplFileObject $referenceP7m, SplFileObject $referenceXmlOutput, SplFileObject $referenceCrtOutput): void
    {
        $p7mToWorkOn = $this->prepareP7mToWorkOn($referenceP7m);
        $p7mReader   = P7MReader::decodeFromFile($p7mToWorkOn, __DIR__ . '/TempOutput');

        self::assertFileEquals($referenceP7m->getPathname(), $p7mReader->getP7mFile()->getPathname());
        self::assertFileEquals($referenceXmlOutput->getPathname(), $p7mReader->getContentFile()->getPathname());
        self::assertFileEquals($referenceCrtOutput->getPathname(), $p7mReader->getCertFile()->getPathname());
        self::assertArrayHasKey('subject', $p7mReader->getCertData());
    }

    /**
     * @dataProvider provideOkCases
     */
    public function testDecodingFromString(SplFileObject $referenceP7m, SplFileObject $referenceXmlOutput, SplFileObject $referenceCrtOutput): void
    {
        $p7mToWorkOn      = $this->prepareP7mToWorkOn($referenceP7m);
        $p7mContent       = (string) \file_get_contents($p7mToWorkOn->getPathname());
        $p7mContentBase64 = \base64_encode($p7mContent);

        $p7mReader = P7MReader::decodeFromBase64($p7mContentBase64, __DIR__ . '/TempOutput');

        self::assertFileEquals($referenceP7m->getPathname(), $p7mReader->getP7mFile()->getPathname());
        self::assertFileEquals($referenceXmlOutput->getPathname(), $p7mReader->getContentFile()->getPathname());
        self::assertFileEquals($referenceCrtOutput->getPathname(), $p7mReader->getCertFile()->getPathname());
        self::assertArrayHasKey('subject', $p7mReader->getCertData());
    }

    /**
     * @return SplFileObject[][]
     */
    public function provideOkCases(): array
    {
        return [
            'OK.xml.p7m' => [
                new SplFileObject(__DIR__ . '/TestAssets/OK.xml.p7m'),
                new SplFileObject(__DIR__ . '/TestAssets/OK.xml'),
                new SplFileObject(__DIR__ . '/TestAssets/OK.crt'),
            ],
            'OK2.xml.p7m' => [
                new SplFileObject(__DIR__ . '/TestAssets/OK2.xml.p7m'),
                new SplFileObject(__DIR__ . '/TestAssets/OK2.xml'),
                new SplFileObject(__DIR__ . '/TestAssets/OK2.crt'),
            ],
        ];
    }

    private function prepareP7mToWorkOn(SplFileObject $referenceP7m): SplFileObject
    {
        $draftP7m = \sprintf('%s/TempOutput/%s', __DIR__, $referenceP7m->getBasename());
        \copy($referenceP7m->getPathname(), $draftP7m);

        return new SplFileObject($draftP7m);
    }

    public function testTamperedSample(): void
    {
        $tamperedFile = new SplFileObject(__DIR__ . '/TestAssets/TAMPERED.xml.p7m');

        $this->expectException(P7MReaderException::class);
        $this->expectExceptionMessage('asn1');

        P7MReader::decodeFromFile($tamperedFile, __DIR__ . '/TempOutput');
    }
}
