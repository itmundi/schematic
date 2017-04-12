<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\Craft;
use Craft\AssetSourceRecord;
use Craft\AssetSourceModel;

/**
 * Schematic Assets Service.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2017, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
class AssetSources extends Base
{
    /**
     * @return AssetSourcesService
     */
    private function getAssetSourcesService()
    {
        return Craft::app()->assetSources;
    }

    /**
     * @param $sourceTypeId
     *
     * @return array|mixed|null
     */
    public function getSourceTypeById($sourceTypeId)
    {
        return AssetSourceRecord::model()->findByAttributes(['id' => $sourceTypeId]);
    }

    /**
     * @param $sourceTypeHandle
     *
     * @return array|mixed|null
     */
    public function getSourceTypeByHandle($sourceTypeHandle)
    {
        return AssetSourceRecord::model()->findByAttributes(['handle' => $sourceTypeHandle]);
    }

    /**
     * Import asset source definitions.
     *
     * @param array $assetSourceDefinitions
     * @param bool  $force
     *
     * @return Result
     */
    public function import(array $assetSourceDefinitions, $force = false)
    {
        Craft::log(Craft::t('Importing Asset Sources'));

        foreach ($assetSourceDefinitions as $assetSourceHandle => $assetSourceDefinition) {
            $assetSource = $this->populateAssetSource($assetSourceHandle, $assetSourceDefinition);

            if (!Craft::app()->assetSources->saveSource($assetSource)) {
                $this->addErrors($assetSource->getAllErrors());
            }
        }

        return $this->getResultModel();
    }

    /**
     * Populate asset source.
     *
     * @param string $assetSourceHandle
     * @param array  $assetSourceDefinition
     *
     * @return AssetSourceModel
     */
    private function populateAssetSource($assetSourceHandle, array $assetSourceDefinition)
    {
        $assetSource = AssetSourceRecord::model()->findByAttributes(['handle' => $assetSourceHandle]);
        $assetSource = $assetSource ? AssetSourceModel::populateModel($assetSource) : new AssetSourceModel();
        $defaultAssetSourceSettings = array(
            'publicURLs' => true,
        );

        $assetSource->setAttributes([
            'handle' => $assetSourceHandle,
            'type' => $assetSourceDefinition['type'],
            'name' => $assetSourceDefinition['name'],
            'sortOrder' => $assetSourceDefinition['sortOrder'],
            'settings' => array_merge($defaultAssetSourceSettings, $assetSourceDefinition['settings']),
        ]);

        if (array_key_exists('fieldLayout', $assetSourceDefinition)) {
            $fieldLayout = Craft::app()->schematic_fields->getFieldLayout($assetSourceDefinition['fieldLayout']);
            $assetSource->setFieldLayout($fieldLayout);
        }

        return $assetSource;
    }

    /**
     * Export all asset sources.
     *
     * @param array $data
     *
     * @return array
     */
    public function export(array $data = [])
    {
        Craft::log(Craft::t('Exporting Asset Sources'));

        $assetSources = $this->getAssetSourcesService()->getAllSources();

        $assetSourceDefinitions = [];
        foreach ($assetSources as $assetSource) {
            $assetSourceDefinitions[$assetSource->handle] = $this->getAssetSourceDefinition($assetSource);
        }

        return $assetSourceDefinitions;
    }

    /**
     * @param AssetSourceModel $assetSource
     *
     * @return array
     */
    private function getAssetSourceDefinition(AssetSourceModel $assetSource)
    {
        return [
            'type' => $assetSource->type,
            'name' => $assetSource->name,
            'sortOrder' => $assetSource->sortOrder,
            'settings' => $assetSource->settings,
            'fieldLayout' => Craft::app()->schematic_fields->getFieldLayoutDefinition($assetSource->getFieldLayout()),
        ];
    }
}
