<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, Uuid, UploadFiles;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];
    const THUMB_FILE_MAX_SIZE = 1024 * 5;
    const BANNER_FILE_MAX_SIZE = 1024 * 10;
    const TRAILER_FILE_MAX_SIZE = 1024 * 1024 * 1;
    const VIDEO_FILE_MAX_SIZE = 1024 * 1024 * 50;

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'video_file',
        'thumb_file',
        'banner_file',
        'trailer_file'
    ];

    protected $date = ['deleted_at'];

    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer'
    ];

    public $incrementing = false;

    public static $fileFields = ['video_file', 'thumb_file', 'banner_file', 'trailer_file'];

    static public function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();

            /** @var Video $obj */
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);

            $obj->uploadFiles($files);

            \DB::commit();

            return $obj;
        } catch (\Exception $e) {
            if (isset($obj)) {
                // Need to delete files upload
                $obj->deleteFiles($files);
            }
            \DB::rollBack();
            throw $e;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        $files = $this->extractFiles($attributes);

        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);

            if ($saved) {
                // Upload new files, and delete old one
                $this->uploadFiles($files);
            }

            \DB::commit();

            if ($saved && count($files)) {
                $this->deleteOldFiles();
            }

            return $saved;
        } catch (\Exception $e) {
            // Need to delete files upload
            $this->deleteFiles($files);
            \DB::rollBack();

            throw $e;
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    public static function handleRelations(Video $video, array $attributes)
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }

        if (isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }

    protected function uploadDir()
    {
        return $this->id;
    }
}
