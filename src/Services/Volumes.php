<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft;
use craft\base\VolumeInterface;
use craft\base\Model;
use craft\volumes\Local;
use NerdsAndCompany\Schematic\Schematic;

/**
 * Schematic Asset Sources Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
class Volumes extends Base
{
    /**
     * Get all asset transforms
     *
     * @return VolumeInterface[]
     */
    protected function getRecords()
    {
        return Craft::$app->volumes->getAllVolumes();
    }

    /**
     * Save a record
     *
     * @param Model $record
     * @param array $definition
     * @return boolean
     */
    protected function saveRecord(Model $record, array $definition)
    {
        $record->setAttributes($definition['attributes']);
        return Craft::$app->volumes->saveVolume($record);
    }

    /**
     * Delete a record
     *
     * @param Model $record
     * @return boolean
     */
    protected function deleteRecord(Model $record)
    {
        return Craft::$app->volumes->deleteVolume($record);
    }
}