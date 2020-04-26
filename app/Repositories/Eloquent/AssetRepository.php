<?php

namespace App\Repositories\Eloquent;

use App\Asset;
use App\Assets\UploadedAsset;
use App\Assets\AssetableContract;
use App\Processors\Image\ImageVariantProcessor;
use App\Repositories\Contracts\AssetRepositoryContract;

use Closure;

class AssetRepository implements AssetRepositoryContract
{
    /**
     * @var \App\Processors\Image\ImageVariantProcessor
     */
    private ImageVariantProcessor $processor;

    /**
     * AssetRepository constructor.
     *
     * @param  \App\Processors\Image\ImageVariantProcessor  $processor
     */
    public function __construct(ImageVariantProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @inheritDoc
     */
    public function create(
        AssetableContract $model,
        string $type,
        UploadedAsset $file,
        array $variants = [],
        Closure $process = null
    ): Asset {
        return $model->assets()->create([
            'type' => $type,
            'disk' => $file->disk,
            'visibility' => $file->visibility,
            'sort' => $this->nextSortFor($model, $type),
            'path' => $file->path,
            'original_name' => $file->originalName,
            'extension' => $file->extension,
            'mime' => $file->mime,
            'size' => $file->size,
            'caption' => $file->originalName,
            'variants' => $file->isImage ?
                $this->processor->generateVariants($file, $variants, $process) :
                [],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function nextSortFor(AssetableContract $model, string $type): int
    {
        $last = $model->assets()->where('type', $type)->sorted()->get()->last();

        return !is_null($last) ? $last->sort + 1 : 1;
    }

    /**
     * @inheritDoc
     */
    public function remove($assets): void
    {
        // TODO: Implement remove() method.
    }
}
