SELECT tx_clubmanager_domain_model_location.*,
       DEGREES(IFNULL(ACOS((SIN(RADIANS(51.5550718900)) * SIN(RADIANS(tx_clubmanager_domain_model_location.latitude))) +
                           (COS(RADIANS(51.5550718900)) * COS(RADIANS(tx_clubmanager_domain_model_location.latitude)) *
                            COS(RADIANS((11.4956247900 - tx_clubmanager_domain_model_location.longitude))))) * 60 * 1.1515 *
                      1.609344, 0)) AS distance
FROM `tx_clubmanager_domain_model_location`
         INNER JOIN `tx_clubmanager_domain_model_member` `tx_clubmanager_domain_model_member`
                    ON `tx_clubmanager_domain_model_member`.`uid` = tx_clubmanager_domain_model_location.uid
WHERE (`tx_clubmanager_domain_model_location`.`hidden` = 0)
  AND (`tx_clubmanager_domain_model_location`.`deleted` = 0)
  AND (`tx_clubmanager_domain_model_member`.`hidden` = 0)
  AND (`tx_clubmanager_domain_model_member`.`deleted` = 0)
  AND (`tx_clubmanager_domain_model_member`.`state` = 2)
  AND (`tx_clubmanager_domain_model_location`.`pid` IN (8))
  AND (((((`tx_clubmanager_domain_model_location`.`deleted` = 0) AND
          (`tx_clubmanager_domain_model_member`.`deleted` = 0))) AND
        (((`tx_clubmanager_domain_model_location`.`hidden` = 0) AND
          (`tx_clubmanager_domain_model_member`.`hidden` = 0))) AND
        (((`tx_clubmanager_domain_model_location`.`starttime` <= 1759404480) AND
          (`tx_clubmanager_domain_model_member`.`starttime` <= 1759404480))) AND
        (((((`tx_clubmanager_domain_model_location`.`endtime` = 0) OR
            (`tx_clubmanager_domain_model_location`.`endtime` > 1759404480))) AND
          (((`tx_clubmanager_domain_model_member`.`endtime` = 0) OR
            (`tx_clubmanager_domain_model_member`.`endtime` > 1759404480)))))))
ORDER BY `distance` ASC, `tx_clubmanager_domain_model_location`.`lastname`