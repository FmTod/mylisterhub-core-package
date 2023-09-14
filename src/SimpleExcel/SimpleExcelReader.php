<?php

namespace MyListerHub\Core\SimpleExcel;

use OpenSpout\Reader\CSV\Options as CSVOptions;
use OpenSpout\Reader\CSV\Reader as CSVReader;
use Spatie\SimpleExcel\ReaderFactory;
use Spatie\SimpleExcel\SimpleExcelReader as BaseClass;

class SimpleExcelReader extends BaseClass
{
    public static function create(string $file, string $type = '', bool $useMimeType = false): static
    {
        return new static($file, $type, $useMimeType);
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(protected string $path, protected string $type = '', protected bool $useMimeType = false)
    {
        $this->csvOptions = new CSVOptions();

        $this->reader = match (true) {
            $this->useMimeType => ReaderFactory::createFromFileByMimeType($this->path),
            ! empty($this->type) => ReaderFactory::createFromType($this->type),
            default => ReaderFactory::createFromFile($this->path),
        };

        $this->setReader();
    }

    protected function setReader(): void
    {
        $options = $this->reader instanceof CSVReader ? $this->csvOptions : null;

        $this->reader = match (true) {
            $this->useMimeType => ReaderFactory::createFromFileByMimeType($this->path, $options),
            ! empty($this->type) => ReaderFactory::createFromType($this->type, $options),
            default => ReaderFactory::createFromFile($this->path, $options),
        };
    }
}
