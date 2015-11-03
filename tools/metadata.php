<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Piwigo\Image\Metadata\Itpc;

$filename = 'sample.jpg';
echo 'Informations are read from '.$filename.'<br><br><br>';

$iptc_result = array();
$imginfo = array();
getimagesize($filename, $imginfo);
if (isset($imginfo['APP13']))
{
  $iptc = iptcparse($imginfo['APP13']);
  if (is_array($iptc))
  {
    foreach (array_keys($iptc) as $iptc_key)
    {
      if (isset($iptc[$iptc_key][0]))
      {
        if ($iptc_key == '2#025')
        {
          $value = implode(
            ',',
            array_map(
              'Itpc::cleanValue',
              $iptc[$iptc_key]
              )
            );
        }
        else
        {

          $value = Itpc::cleanValue($iptc[$iptc_key][0]);
        }

        $iptc_result[$iptc_key] = $value;
      }
    }
  }

  echo 'IPTC Fields in '.$filename.'<br>';
  $keys = array_keys($iptc_result);
  sort($keys);
  foreach ($keys as $key)
  {
    echo '<br>'.$key.' = '.$iptc_result[$key];
  }
}
else
{
  echo 'no IPTC information';
}

echo '<br><br><br>';
echo 'EXIF Fields in '.$filename.'<br>';
$exif = read_exif_data($filename);
echo '<pre>';
print_r($exif);
echo '</pre>';
