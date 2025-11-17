<?php

namespace App\Domain\Import\Parsers;

use App\Domain\Import\Contracts\ImportParserInterface;
use App\Domain\Import\Enums\ImportSource;
use App\Domain\Import\Exceptions\ParserNotFoundException;

class ParserFactory
{
    private array $parsers = [];

    public function __construct()
    {
        $this->registerParser(new CsvParser());
    }

    public function registerParser(ImportParserInterface $parser): void
    {
        $this->parsers[$parser->getSupportedSource()->value] = $parser;
    }

    public function make(ImportSource $source): ImportParserInterface
    {
        if (!isset($this->parsers[$source->value])) {
            throw new ParserNotFoundException($source);
        }

        return $this->parsers[$source->value];
    }
}
