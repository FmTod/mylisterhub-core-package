<?php

namespace MyListerHub\Core\PdfToImage;

use Imagick;

class PDF extends \Spatie\PdfToImage\Pdf
{
    public $alphaChannel;

    protected $backgroundColor;

    public function setAlphaChannel(?int $alphaChannel)
    {
        $this->alphaChannel = $alphaChannel;

        return $this;
    }

    public function setBackgroundColor(?string $backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    public function getImageData(string $pathToImage): Imagick
    {
        $this->imagick = parent::getImageData($pathToImage);

        if ($this->backgroundColor !== null) {
            $this->imagick->setImageBackgroundColor($this->backgroundColor);
        }

        if ($this->alphaChannel !== null) {
            $this->imagick->setImageAlphaChannel($this->alphaChannel);
        }

        return $this->imagick;
    }

    protected function getRemoteImageData(string $pathToImage): Imagick
    {
        $this->imagick = parent::getRemoteImageData($pathToImage);

        if ($this->backgroundColor !== null) {
            $this->imagick->setImageBackgroundColor($this->backgroundColor);
        }

        if ($this->alphaChannel !== null) {
            $this->imagick->setImageAlphaChannel($this->alphaChannel);
        }

        return $this->imagick;
    }
}
