<?php

namespace MyListerHub\Core\Actions;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Imagick;
use MyListerHub\Core\Concerns\Actions\AsAction;
use MyListerHub\Core\Dto\MediaData;
use MyListerHub\Core\Models\Image;
use MyListerHub\Core\PdfToImage\PDF;
use Spatie\LaravelData\DataCollection;

class UploadPdfAsImage
{
    use AsAction;

    /**
     * @return \Spatie\LaravelData\DataCollection<\MyListerHub\Core\Dto\MediaData>
     *
     * @throws \Spatie\PdfToImage\Exceptions\PdfDoesNotExist|\Spatie\PdfToImage\Exceptions\PageDoesNotExist
     */
    public function handle(UploadedFile|File $file, int $page = null): DataCollection
    {
        $imagesPath = config('tenancy.filesystem.images.public', 'images');
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext = $file->getClientOriginalExtension();

        $pdf = new PDF($file);
        $pdf->setAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $pdf->setBackgroundColor('white');
        $pages = $pdf->getNumberOfPages();

        if ($page) {
            return new DataCollection(MediaData::class, [
                $this->savePage($pdf, $filename, $imagesPath, $ext, $page),
            ]);
        }

        $images = [];

        for ($i = 1; $i <= $pages; $i++) {
            $images[] = $this->savePage($pdf, $filename, $imagesPath, $ext, $i);
        }

        return new DataCollection(MediaData::class, $images);
    }

    /**
     * @throws \Spatie\PdfToImage\Exceptions\PageDoesNotExist
     */
    protected function savePage(PDF $pdf, string $filename, string $imagesPath, string $ext, int $page): ?MediaData
    {
        $imageName = "{$filename}_$page.jpg";
        $storagePath = Storage::disk('local')->path("app/public/$imagesPath/$imageName");

        if (! $pdf->setPage($page)->saveImage($storagePath)) {
            return null;
        }

        $size = getimagesize($storagePath);

        Storage::tenant('public')->putFileAs($imagesPath, $storagePath, $imageName);
        Storage::disk('local')->delete($storagePath);

        /** @var Image $image */
        $image = Image::updateOrCreate([
            'source' => $imageName,
            'width' => $size[0],
            'height' => $size[1],
        ]);

        return MediaData::from([
            'id' => $image->id,
            'name' => $image->name,
            'url' => $image->url,
            'size' => $image->size,
            'ext' => $ext,
            'type' => 'document',
        ]);
    }
}
