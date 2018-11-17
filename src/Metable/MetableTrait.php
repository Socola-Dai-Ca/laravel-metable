<?php

namespace NguyenTranChung\Metable\Metable;

trait MetableTrait
{
    protected $deleteKeys = [];
    protected $metaDatas = [];

    /**
     * @var array
     */
    protected $dataTypes = ['boolean', 'integer', 'double', 'string', 'array', 'object'];

    public static function bootMetableTrait()
    {
        static::saved(function ($entity) {
            $entity->updateOrCreateMetas();
            $entity->deleteMetas();
        });

        static::deleting(function (Metable $entity) {
            $entity->deleteAllMetas();
        });
    }

    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function metas()
    {
        return $this->morphMany(config('metable.meta_model'), 'model');
    }

    public function updateOrCreateMetas()
    {
        if (count($this->metaDatas)) {
            foreach ($this->metaDatas as $value) {
                $this->metas()->updateOrCreate($value[0], $value[1]);
            }
        }
    }

    public function deleteMetas()
    {
        if (count($this->deleteKeys)) {
            $this->metas()->whereIn('key', $this->deleteKeys)->delete();
        }
    }

    public function deleteAllMetas()
    {
        $this->metas()->delete();
    }

    public function setMeta($key, $value = null, $delete = false)
    {
        $type = gettype($key);
        switch ($type) {
            case 'string':
                $this->setMetaSingle($key, $value, $delete);
                break;
            case 'array':
                $this->setMetaArray($key);
                break;
            default:
                # code...
                break;
        }
    }

    public function unsetMeta($key)
    {
        $type = gettype($key);
        switch ($type) {
            case 'string':
                $this->setMetaSingle($key, null, true);
                break;
            case 'array':
                foreach ($key as $k) {
                    $this->setMetaSingle($k, null, true);
                }
                break;
        }
    }

    protected function setMetaSingle($key, $value = null, $delete = false)
    {
        if ($delete) {
            $this->deleteKeys[] = $key;
        } else {
            $this->metaDatas[] = [
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => gettype($value),
                ],
            ];
        }
    }

    protected function setMetaArray($metas)
    {
        if (is_array($metas)) {
            foreach ($metas as $key => $value) {
                $this->setMeta($key, $value);
            }
        }
    }
}