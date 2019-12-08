<?php

declare(strict_types=1);

namespace Slam\P7MReader;

use SplFileObject;

interface P7MReaderInterface
{
    public function getP7mBase64Content(): string;

    public function getContentFile(): SplFileObject;

    public function getCertFile(): SplFileObject;

    /**
     * @return array<string, mixed>
     */
    public function getCertData(): array;
}
