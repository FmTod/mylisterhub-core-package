<?php

namespace MyListerHub\Core\Models;

use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ImageFactory;
use InvalidArgumentException;
use League\Flysystem\UnableToCheckFileExistence;
use MyListerHub\Core\Concerns\Models\HasCamelCaseRelations;

class Image extends Model
{
    use HasCamelCaseRelations;
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
        'size',
        'name',
    ];

    public static function booted(): void
    {
        static::saving(function (Image $image) {
            $path = config('tenancy.filesystem.images.public', 'images');

            if (Str::startsWith($image->source, 'http')) {
                return;
            }

            if (isset($image->width, $image->height) || ! Storage::tenant('public')->exists("$path/$image->name")) {
                return;
            }

            try {
                $details = \Intervention\Image\Facades\Image::make(Storage::tenant('public')->get("$path/$image->name"));

                $image->update([
                    'width' => $details->width(),
                    'height' => $details->height(),
                ]);
            } catch (Exception) {
            }
        });
    }

    protected function name(): Attribute
    {
        return Attribute::get(
            fn ($value, $attributes) => empty($attributes['name'])
                ? Str::before(Str::afterLast($this->source, '/'), '?')
                : $attributes['name'],
        );
    }

    protected function url(): Attribute
    {
        return Attribute::get(function ($value, $attributes) {
            if (! isset($attributes['source'])) {
                return '';
            }

            if (Str::startsWith($this->source, 'http')) {
                return $this->source;
            }

            $path = config('tenancy.filesystem.images.public', 'images');

            return Storage::tenant('public')->tenantUrl("$path/$this->source");
        });
    }

    protected function size(): Attribute
    {
        return Attribute::get(function (): int {
            $imagesPath = config('tenancy.filesystem.images.public', 'images');

            $path = "$imagesPath/$this->name";

            if (Str::startsWith($this->source, 'http')) {
                return 0;
            }

            try {
                $exist = Storage::tenant('public')->fileExists($path);
            } catch (UnableToCheckFileExistence) {
                $exist = false;
            }

            if (!$exist) {
                return 0;
            }

            return Storage::tenant('public')->fileSize($path);
        });
    }

    public static function createFromFile(UploadedFile|File $file, bool $public = true): static
    {
        $path = config('tenancy.filesystem.images.public', 'images');
        $name = $file->getClientOriginalName();

        Storage::tenant($public ? 'public' : 'private')->putFileAs($path, $file, $name);

        $size = getimagesize($file->path());

        return static::create([
            'source' => $name,
            'name' => $name,
            'width' => $size[0],
            'height' => $size[1],
        ]);
    }

    /**
     * @throws \Throwable
     */
    public static function createFromUrl(string $url, bool $upload = false, bool $public = true): static
    {
        $path = config('tenancy.filesystem.images.public', 'images');
        $name = (string) Str::of($url)->afterLast('/')->before('?')->trim();

        throw_if(! $name, InvalidArgumentException::class, 'Could not guess the name of the image. Please provide a filename.');

        try {
            $file = file_get_contents($url);

            if ($upload) {
                Storage::tenant($public ? 'public' : 'private')->put("$path/$name", $file);
            }
        } catch (Exception $exception) {
            throw_if($upload, $exception);

            $file = null;
        }

        $details = $file ? \Intervention\Image\Facades\Image::make($file) : null;

        return static::create([
            'name' => $name,
            'source' => $upload ? $name : $url,
            'width' => $details?->width(),
            'height' => $details?->height(),
        ]);
    }

    protected static function newFactory(): ImageFactory
    {
        return new ImageFactory();
    }
}
