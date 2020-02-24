<?php
namespace Sedehi\ElasticCollection\Traits;

use Carbon\Carbon;

trait HasDates
{
    /**
     * @param $model
     * @param $data
     * @return mixed
     */
    protected function formatDates($model, $data)
    {
        foreach ($model->getDates() as $dateField) {
            if (isset($data[$dateField])) {
                $data[$dateField] = Carbon::createFromDate($data[$dateField]);
            }
        }
        return $data;
    }

}
