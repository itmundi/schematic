<?php

namespace NerdsAndCompany\Schematic\Interfaces;

/**
 * Schematic Element Mapping Interface.
 *
 * Sync Craft Setups.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015-2018, Nerds & Company
 * @license   MIT
 *
 * @see      http://www.nerds.company
 */
interface MappingInterface
{
    /**
     * Export given records.
     *
     * @param array $records
     *
     * @return array
     */
    public function export(array $records): array;

    /**
     * Import given definitions.
     *
     * @param array $records
     *
     * @return array
     */
    public function import(array $definitions, array $records): array;
}
