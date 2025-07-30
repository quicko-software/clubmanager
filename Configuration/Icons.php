<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return call_user_func(function () {
  $result = [
    'tx-clubmanager_icon-be_mod_clubmanager' => [
      'provider' => SvgIconProvider::class,
      'source' => 'EXT:clubmanager/Resources/Public/Icons/be_mod_clubmanager3.svg',
    ],
    'icon_password-reset' => [
      'provider' => SvgIconProvider::class,
      'source' => 'EXT:clubmanager/Resources/Public/Icons/icon_password-reset.svg',
    ],
  ];
  foreach (['memberlist','mailtasks','settlements','events','membershipstatistics'] as $beModName) {
    $result["tx-clubmanager_icon-be_mod_$beModName"] = [
      'provider' => SvgIconProvider::class,
      'source' => "EXT:clubmanager/Resources/Public/Icons/be_mod_$beModName.svg",
    ];
  }
  return $result;
});



