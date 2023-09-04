<?php

declare(strict_types=1);

namespace Slam\P7MReader\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Slam\P7MReader\P7MReader;
use Slam\P7MReader\P7MReaderException;
use SplFileObject;

#[CoversClass(P7MReader::class)]
#[CoversClass(P7MReaderException::class)]
final class P7MReaderTest extends TestCase
{
    #[DataProvider('provideOkCases')]
    public function testWorkingSample(SplFileObject $referenceP7m, SplFileObject $referenceXmlOutput, SplFileObject $referenceCrtOutput): void
    {
        $p7mToWorkOn = $this->prepareP7mToWorkOn($referenceP7m);
        $p7mReader   = P7MReader::decodeFromFile($p7mToWorkOn, __DIR__ . '/TempOutput');

        self::assertFileEquals($referenceP7m->getPathname(), $p7mReader->getP7mFile()->getPathname());
        self::assertFileEquals($referenceXmlOutput->getPathname(), $p7mReader->getContentFile()->getPathname());
        self::assertFileEquals($referenceCrtOutput->getPathname(), $p7mReader->getCertFile()->getPathname());
        self::assertArrayHasKey('subject', $p7mReader->getCertData());
    }

    #[DataProvider('provideOkCases')]
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

    /** @return SplFileObject[][] */
    public static function provideOkCases(): array
    {
        return [
            'OK.txt.p7m'  => [
                new SplFileObject(__DIR__ . '/TestAssets/OK.txt.p7m'),
                new SplFileObject(__DIR__ . '/TestAssets/OK.txt'),
                new SplFileObject(__DIR__ . '/TestAssets/OK.crt'),
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
        $tamperedFile = new SplFileObject(__DIR__ . '/TestAssets/TAMPERED.txt.p7m');

        $this->expectException(P7MReaderException::class);
        $this->expectExceptionMessage('asn1');

        P7MReader::decodeFromFile($tamperedFile, __DIR__ . '/TempOutput');
    }
}
