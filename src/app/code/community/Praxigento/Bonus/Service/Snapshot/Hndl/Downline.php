<?php
/**
 * Copyright (c) 2015, Praxigento
 * All rights reserved.
 */
use Praxigento_Bonus_Config as Config;
use Praxigento_Bonus_Model_Own_Snap_Downline as SnapDownline;

/**
 * Downline routines handler for the service.
 *
 * User: Alex Gusev <alex@flancer64.com>
 */
class Praxigento_Bonus_Service_Snapshot_Hndl_Downline {

    /**
     * Transform array of the "customer => parent" ID data to downline snapshot data.
     *
     * @param $arrIds - $array[$ownId] = $parentId
     * @param $periodValue
     *
     * @return Praxigento_Bonus_Model_Own_Snap_Downline[]
     */
    public function transformIdsToSnapItems(&$arrIds, $periodValue) {
        /* scan IDs data level by level and re-construct tree in the memory */
        $result = array();
        /* 0 is the virtual root level, all root nodes are from level 1 */
        $level = 1;
        $arrPrevLevelIds = array();
        do {
            $countBefore = count($arrIds);
            $arrCurrLevelIds = array();
            foreach($arrIds as $ownId => $parentId) {
                $depth = $level;
                if($ownId == $parentId) {
                    /* this is one of the root nodes */
                    $path = Config::FORMAT_PATH_SEPARATOR;
                } else {
                    /* this is not root */
                    if(in_array($parentId, $arrPrevLevelIds)) {
                        $parentItem = $result[ $parentId ];
                        $path = $parentItem[ SnapDownline::ATTR_PATH ] . $parentId . Config::FORMAT_PATH_SEPARATOR;
                    } else {
                        /* skip low levels element */
                        continue;
                    }
                }
                /* process found element and unset it in the source array */
                $snap = array(
                    SnapDownline::ATTR_CUSTOMER_ID => $ownId,
                    SnapDownline::ATTR_PARENT_ID   => $parentId,
                    SnapDownline::ATTR_PERIOD      => $periodValue,
                    SnapDownline::ATTR_PATH        => $path,
                    SnapDownline::ATTR_DEPTH       => $depth
                );
                /* set snap data to result and to current level IDs registry and clean up processed item */
                $result[ $ownId ] = $snap;
                $arrCurrLevelIds[] = $ownId;
                unset($arrIds[ $ownId ]);
            }
            /* switch level IDs arrays */
            $arrPrevLevelIds = $arrCurrLevelIds;
            $level++;
            $countAfter = count($arrIds);
            /* exit loop when all aggregated items are processed or items processing is stopped  */
        } while(
            ($countAfter > 0) &&
            ($countBefore != $countAfter)
        );
        return $result;
    }
}