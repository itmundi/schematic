<?php

namespace Craft;

/**
 * Schematic Service.
 *
 * Sync Craft Setups.
 *
 * @author    Itmundi
 * @copyright Copyright (c) 2015, Itmundi
 * @license   MIT
 *
 * @link      http://www.itmundi.nl
 */
class SchematicService extends BaseApplicationComponent
{
    /**
     * Import from Yaml file.
     *
     * @param string $file
     * @param bool   $force if set to true items not included in import will be deleted
     *
     * @return Schematic_ResultModel
     */
    public function importFromYaml($file, $force = false)
    {
        $yaml = IOHelper::getFileContents($file);
        $datamodel = Schematic_DataModel::fromYaml($yaml);

        return $this->importDataModel($datamodel, $force);
    }

    /**
     * Export to Yaml file.
     *
     * @param string $file
     *
     * @return bool
     */
    public function exportToYaml($file)
    {
        $datamodel = $this->exportDataModel();
        $yaml = Schematic_DataModel::toYaml($datamodel);

        return IOHelper::writeToFile($file, $yaml);
    }

    /**
     * Import data model.
     *
     * @param Schematic_DataModel $model
     * @param bool                $force if set to true items not in the import will be deleted
     *
     * @return Schematic_ResultModel
     */
    private function importDataModel(Schematic_DataModel $model, $force)
    {
        // Import schema
        $pluginImportResult = craft()->schematic_plugins->import($model->plugins);
        $assetImportResult = craft()->schematic_assets->import($model->assets);
        $fieldImportResult = craft()->schematic_fields->import($model->fields, $force);
        $globalImportResult = craft()->schematic_globals->import($model->globals, $force);
        $sectionImportResult = craft()->schematic_sections->import($model->sections, $force);
        $userGroupImportResult = craft()->schematic_userGroups->import($model->userGroups, $force);
        $userImportResult = craft()->schematic_users->import($model->users, true);

        // Verify results
        $result = new Schematic_ResultModel();
        $result->consume($pluginImportResult);
        $result->consume($assetImportResult);
        $result->consume($fieldImportResult);
        $result->consume($globalImportResult);
        $result->consume($sectionImportResult);
        $result->consume($userGroupImportResult);
        $result->consume($userImportResult);

        /** @var Schematic_AbstractService[] $services */
        $services = craft()->plugins->callFirst('registerMigrationService');
        if (is_array($services)) {
            foreach ($services as $handle => $service) {
                if ($services instanceof Schematic_AbstractService && array_key_exists($handle, $model->pluginData)) {
                    $hookResult = $service->import($model->pluginData[$handle], $force);
                    $result->consume($hookResult);
                }
            }
        }

        return $result;
    }

    /**
     * Export data model.
     *
     * @return array
     */
    private function exportDataModel()
    {
        $fieldGroups = craft()->fields->getAllGroups();
        $sections = craft()->sections->getAllSections();
        $globals = craft()->globals->getAllSets();
        $userGroups = craft()->userGroups->getAllGroups();

        $export = array(
            'assets' => craft()->schematic_assets->export(),
            'fields' => craft()->schematic_fields->export($fieldGroups),
            'plugins' => craft()->schematic_plugins->export(),
            'sections' => craft()->schematic_sections->export($sections),
            'globals' => craft()->schematic_globals->export($globals),
            'userGroups' => craft()->schematic_userGroups->export($userGroups),
            'users' => craft()->schematic_users->export(),
        );

        /** @var Schematic_AbstractService[] $services */
        $services = craft()->plugins->callFirst('registerMigrationService');
        if (is_array($services)) {
            $export['pluginData'] = array();
            foreach ($services as $handle => $service) {
                if ($service instanceof Schematic_AbstractService) {
                    $export['pluginData'][$handle] = $service->export();
                }
            }
        }

        return $export;
    }
}
