<?php

declare(strict_types=1);

namespace Elgentos\StructuredData\ViewModel\Schema;

interface SchemaInterface
{
    public function getStructuredData(): array;

    public function getSerializedData(): string;
}
