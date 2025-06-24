<?php
// includes/browsers.php
/*
 * File: includes/browsers.php
 * Purpose: Contains regex patterns for browser detection
 * Format: 'BrowserName' => 'RegexPattern'
 * Maintenance: Add new browsers by appending to the $browsers array. Test regex with sample user agents.
 * Last Updated: 2025-06-24
 */
$browsers = [
    'Edge' => 'Edg\/([0-9.]+)', // Microsoft Edge
    'Chrome' => 'Chrome\/([0-9.]+)', // Google Chrome
    'Firefox' => 'Firefox\/([0-9.]+)', // Mozilla Firefox
    'Safari' => 'Version\/([0-9.]+).*Safari', // Apple Safari
    'Opera' => '(?:Opera|OPR)\/([0-9.]+)', // Opera
    'Internet Explorer' => '(?:MSIE ([0-9.]+)|rv:([0-9.]+))', // IE (including IE11 Trident)
    'UCBrowser' => 'UCBrowser\/([0-9.]+)', // UC Browser
    'SamsungBrowser' => 'SamsungBrowser\/([0-9.]+)', // Samsung Internet
    'QQBrowser' => 'QQBrowser\/([0-9.]+)', // QQ Browser
    'Baidu' => 'Baidu\/([0-9.]+)', // Baidu Browser
    'Yandex' => 'YaBrowser\/([0-9.]+)', // Yandex Browser
    'Vivaldi' => 'Vivaldi\/([0-9.]+)', // Vivaldi
    'Brave' => 'Brave\/([0-9.]+)', // Brave Browser
    'Maxthon' => 'Maxthon\/([0-9.]+)', // Maxthon
    'Tor' => 'Tor\/([0-9.]+)', // Tor Browser
    'PaleMoon' => 'PaleMoon\/([0-9.]+)', // Pale Moon
    'Waterfox' => 'Waterfox\/([0-9.]+)', // Waterfox
    'SeaMonkey' => 'SeaMonkey\/([0-9.]+)', // SeaMonkey
    'Avant' => 'Avant Browser\/([0-9.]+)', // Avant Browser
    'Konqueror' => 'Konqueror\/([0-9.]+)', // Konqueror
    'Lynx' => 'Lynx\/([0-9.]+)', // Lynx (text-based)
    'Netscape' => 'Netscape\/([0-9.]+)', // Netscape Navigator (legacy)
    'OmniWeb' => 'OmniWeb\/([0-9.]+)', // OmniWeb
    'Camino' => 'Camino\/([0-9.]+)', // Camino (legacy Mac browser)
    'Flock' => 'Flock\/([0-9.]+)', // Flock (discontinued)
    'K-Meleon' => 'K-Meleon\/([0-9.]+)', // K-Meleon
    'Shiira' => 'Shiira\/([0-9.]+)', // Shiira (discontinued)
    'iCab' => 'iCab\/([0-9.]+)', // iCab
    'Amaya' => 'Amaya\/([0-9.]+)', // W3C Amaya
    'Dillo' => 'Dillo\/([0-9.]+)', // Dillo
    'w3m' => 'w3m\/([0-9.]+)', // w3m (text-based)
    'Epiphany' => 'Epiphany\/([0-9.]+)', // GNOME Web (Epiphany)
    'Midori' => 'Midori\/([0-9.]+)', // Midori
    'QupZilla' => 'QupZilla\/([0-9.]+)', // QupZilla (now Falkon)
    'Falkon' => 'Falkon\/([0-9.]+)', // Falkon
    'Otter' => 'Otter\/([0-9.]+)', // Otter Browser
    'Netsurf' => 'NetSurf\/([0-9.]+)', // NetSurf
    'Arora' => 'Arora\/([0-9.]+)', // Arora (discontinued)
    'rekonq' => 'rekonq\/([0-9.]+)', // rekonq (discontinued)
    'Iceweasel' => 'Iceweasel\/([0-9.]+)', // Iceweasel (Debian Firefox fork)
    'IceCat' => 'IceCat\/([0-9.]+)', // GNU IceCat
    'Sleipnir' => 'Sleipnir\/([0-9.]+)', // Sleipnir
    'Comodo Dragon' => 'Dragon\/([0-9.]+)', // Comodo Dragon
    'SRWare Iron' => 'Iron\/([0-9.]+)', // SRWare Iron
    'Lunascape' => 'Lunascape\/([0-9.]+)', // Lunascape
    'GreenBrowser' => 'GreenBrowser\/([0-9.]+)', // GreenBrowser
    'Crazy Browser' => 'Crazy Browser\/([0-9.]+)', // Crazy Browser
    'TheWorld' => 'TheWorld\/([0-9.]+)', // TheWorld Browser
    '360Browser' => '360Browser\/([0-9.]+)', // 360 Secure Browser
    'Coc Coc' => 'CocCoc\/([0-9.]+)', // Coc Coc (Vietnam)
    'Puffin' => 'Puffin\/([0-9.]+)', // Puffin Browser
    'Dolphin' => 'Dolphin\/([0-9.]+)', // Dolphin Browser
    'Mercury' => 'Mercury\/([0-9.]+)', // Mercury Browser
    'Silk' => 'Silk\/([0-9.]+)', // Amazon Silk
    'Nintendo Browser' => 'NintendoBrowser\/([0-9.]+)', // Nintendo devices
    'PlayStation Browser' => 'PS4\/([0-9.]+)', // PlayStation browser
    'Xbox Browser' => 'Xbox\/([0-9.]+)', // Xbox browser
];
?>
