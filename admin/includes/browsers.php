<?php
$browsers = [
    'Edge' => 'Edg(?:e)?\/([0-9.]+)',
    'Chrome' => 'Chrome\/([0-9.]+)',
    'Firefox' => 'Firefox\/([0-9.]+)',
    'Safari' => 'Version\/([0-9.]+).*Safari',
    'Opera' => '(?:Opera|OPR)\/([0-9.]+)',
    'Internet Explorer' => '(?:MSIE\s|Trident\/[0-9.]+;.*rv:)([0-9.]+)',
    'Samsung Internet' => 'SamsungBrowser\/([0-9.]+)',
    'UCBrowser' => 'UCBrowser\/([0-9.]+)',
    'Brave' => 'Brave\/([0-9.]+)',
    'Vivaldi' => 'Vivaldi\/([0-9.]+)',
    'PaleMoon' => 'PaleMoon\/([0-9.]+)',
    'Waterfox' => 'Waterfox\/([0-9.]+)',
    'SeaMonkey' => 'SeaMonkey\/([0-9.]+)',
    'Iceweasel' => 'Iceweasel\/([0-9.]+)',
    'IceCat' => 'IceCat\/([0-9.]+)',
    'Yandex' => 'YaBrowser\/([0-9.]+)',
    'Maxthon' => 'Maxthon\/([0-9.]+)',
    'QQBrowser' => 'QQBrowser\/([0-9.]+)',
    'Baidu' => 'Baidu\/([0-9.]+)',
    'Tor' => 'Tor\/([0-9.]+)',
    'Lynx' => 'Lynx\/([0-9.]+)',
    'Netscape' => 'Netscape\/([0-9.]+)',
    'Konqueror' => 'Konqueror\/([0-9.]+)'
];

$oses = [
    'Windows' => 'Windows NT ([0-9.]+)',
    'macOS' => 'Mac OS X ([0-9_\.]+)',
    'Linux' => 'Linux|X11',
    'Ubuntu' => 'Ubuntu\/([0-9.]+)',
    'Android' => 'Android ([0-9.]+)',
    'iOS' => '(?:iPhone OS|CPU OS) ([0-9_\.]+)',
    'Chrome OS' => 'CrOS',
    'Windows Phone' => 'Windows Phone ([0-9.]+)',
    'BlackBerry' => 'BlackBerry|BB10',
    'FreeBSD' => 'FreeBSD',
    'NetBSD' => 'NetBSD',
    'OpenBSD' => 'OpenBSD',
    'Solaris' => 'Solaris|SunOS',
    'Symbian' => 'SymbianOS\/([0-9.]+)',
    'Tizen' => 'Tizen\/([0-9.]+)',
    'KaiOS' => 'KaiOS\/([0-9.]+)',
    'HarmonyOS' => 'HarmonyOS\/([0-9.]+)',
    'watchOS' => 'watchOS ([0-9_\.]+)',
    'Wear OS' => 'Wear OS ([0-9.]+)'
];

$devices = [
    'Mobile' => 'Mobile|Android|iPhone|iPod|Windows Phone|BlackBerry|BB10|Symbian|KaiOS',
    'Tablet' => 'iPad|Kindle|Nexus 7|Nexus 9|Nexus 10|Tablet|SM-T|Tab',
    'Desktop' => 'Windows NT|Mac OS X|Linux|X11|CrOS|FreeBSD|NetBSD|OpenBSD|Solaris',
    'Wearable' => 'Watch|Wear OS|watchOS',
    'TV' => 'SmartTV|WebOS|tvOS',
    'Console' => 'PlayStation|Xbox|Nintendo',
    'E-Reader' => 'Kindle|Kobo|Nook'
];

$engines = [
    'Blink' => 'Chrome|Edg|OPR|SamsungBrowser|UCBrowser|Brave|Vivaldi|YaBrowser',
    'WebKit' => 'Safari|Version|AppleWebKit',
    'Gecko' => 'Firefox|PaleMoon|Waterfox|SeaMonkey|Iceweasel|IceCat',
    'Trident' => 'MSIE|Trident',
    'Presto' => 'Opera\/9|Opera\/8|Opera\/7',
    'KHTML' => 'Konqueror',
    'EdgeHTML' => 'Edge\/([0-9.]+)'
];

$bots = [
    'Googlebot' => 'Googlebot',
    'Bingbot' => 'Bingbot',
    'Slurp' => 'Yahoo! Slurp',
    'DuckDuckBot' => 'DuckDuckBot',
    'Baiduspider' => 'Baiduspider',
    'YandexBot' => 'YandexBot',
    'AhrefsBot' => 'AhrefsBot',
    'MJ12bot' => 'MJ12bot',
    'SemrushBot' => 'SemrushBot',
    'DotBot' => 'DotBot',
    'Sogou Spider' => 'Sogou',
    'Exabot' => 'Exabot',
    'Twitterbot' => 'Twitterbot',
    'Facebot' => 'Facebot',
    'Applebot' => 'Applebot',
    'BLEXBot' => 'BLEXBot',
    'ArchiveBot' => 'ArchiveBot',
    'Siteimprove' => 'Siteimprove',
    'Screaming Frog' => 'Screaming Frog'
];

$brands = [
    'Apple' => 'iPhone|iPad|iPod|Macintosh',
    'Samsung' => 'SAMSUNG|Galaxy|SM-',
    'Huawei' => 'Huawei|HUAWEI',
    'Xiaomi' => 'Xiaomi|Redmi|Mi ',
    'Amazon' => 'Kindle|Fire',
    'Google' => 'Pixel|Nexus',
    'OnePlus' => 'OnePlus',
    'Oppo' => 'Oppo|OPPO',
    'Vivo' => 'Vivo|VIVO',
    'Sony' => 'Sony|Xperia',
    'LG' => 'LG-',
    'Motorola' => 'Moto|Motorola',
    'Nokia' => 'Nokia',
    'BlackBerry' => 'BlackBerry|BB10',
    'Asus' => 'Asus|Zenfone',
    'Lenovo' => 'Lenovo',
    'HTC' => 'HTC',
    'ZTE' => 'ZTE',
    'Alcatel' => 'Alcatel',
    'Microsoft' => 'Lumia|Surface',
    'Realme' => 'Realme|REALME'
];
?>
