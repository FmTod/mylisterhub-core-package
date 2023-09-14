<?php

namespace MyListerHub\Core\Concerns\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use MyListerHub\Core\Models\Image;

trait HasImages
{
    /**
     * Validation rules for models with custom attributes.
     *
     * @return string[]
     */
    public static function imageValidationRules(): array
    {
        return [
            'images' => 'sometimes|nullable|array',
            'images.*' => 'sometimes|nullable',
            'images.*.id' => 'sometimes|nullable|integer|exists:images,id',
        ];
    }

    /**
     * Get product images.
     */
    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable')->withPivot(['order'])->orderByRaw('-`imageables`.`order` DESC');
    }

    /**
     * Sync images based on the provided data.
     */
    public function createImages(Collection|array $images, bool $detaching = true, bool $copyImageFromUrl = false): static
    {
        if (! $images instanceof Collection) {
            $images = collect($images);
        }

        $uploadedImages = $images
            ->mapWithKeys(function (mixed $imageData, int $index) use ($copyImageFromUrl) {
                if ($imageData instanceof Image) {
                    return [$imageData->id => ['order' => $imageData->pivot?->order ?? $index]];
                }

                if ($imageData instanceof UploadedFile) {
                    $image = Image::createFromFile($imageData);

                    return [$image->id => ['order' => $index]];
                }

                if (is_string($imageData)) {
                    $image = Image::createFromUrl($imageData, $copyImageFromUrl);

                    return [$image->id => ['order' => $index]];
                }

                if (is_array($imageData)) {
                    $image = match (true) {
                        isset($imageData['id']) => Image::find($imageData['id']),
                        isset($imageData['source']) => Image::updateOrCreate(['source' => $imageData['source']], $imageData),
                        isset($imageData['url']) => Image::createFromUrl($imageData['url'], $copyImageFromUrl),
                        default => null,
                    };

                    if (! $image) {
                        return null;
                    }

                    return [$image->id => ['order' => $imageData['order'] ?? $index]];
                }

                return null;
            })
            ->filter();

        $this->images()->sync($uploadedImages, $detaching);

        return $this;
    }

    /**
     * Sync given images with the current imageable model.
     *
     * @param  \Illuminate\Support\Collection|Image[]  $images
     */
    public function syncImages(Collection|array $images, bool $detaching = true): static
    {
        if (! $images instanceof Collection) {
            $images = collect($images);
        }

        $imageIds = $images->mapWithKeys(fn (Image $image) => [$image->id => ['order' => $image->pivot?->order]]);

        $this->images()->sync($imageIds, $detaching);

        return $this;
    }
}
