<?php

/*
 * Creates CSS file with data-uri versions of background-images
 *
 * usage: input_file [web_root]
 *
 * @author Mikhail Yurasov, 2012
 * @version 1.0
 */

// Get input file

if (isset($argv[1]))
{
  $inputFile = $argv[1];

  if (false === ($inputFile = realpath($inputFile)))
  {
    echo "Input file not found\n";
    exit(1);
  }
}
else
{
  displayUsage();
  exit(1);
}

// Get web root
$webRoot = isset($argv[2]) ? $argv[2] : '';

if (false === ($webRoot = realpath($webRoot)))
{
  echo "Web root not found\n";
  exit(1);
}

// Process

// read file
$content = file_get_contents($inputFile);

// get css selectors/rules
$matches = array();
preg_match_all('#([^}/]+){([^}]*)#', $content, $matches);
$selectors = $matches[1];
$rules = $matches[2];

// iterate through selectors

for ($i = 0; $i < count($selectors); $i++)
{
  $rules[$i] = trim($rules[$i]);
  $rules[$i] = explode("\n", $rules[$i]);

  for ($r = 0; $r < count($rules[$i]); $r++)
  {
    $rule = $rules[$i][$r];

    // find image links

    $matches = array();

    if (
      preg_match('#background.*:.*url\((.*)\)#i', $rule, $matches)
      && !stristr($rule, '@no-data-uri')
    )
    {
      $url = $matches[1];
      $url = trim($url, ' "\'');
      $file = $webRoot . '/' . $url;

      if (!file_exists($file))
      {
        echo "File '$url' not found\n";
        exit(1);
      }

      // get mime type
      $fi = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($fi, $file);
      finfo_close($fi);

      $content = file_get_contents($file);
      $content = base64_encode($content);

      $dataUri = 'data:' . $mime . ';base64,' . $content;

      $rule = trim($selectors[$i]) . " {\n\tbackground-image: url('$dataUri');\n}";
      echo $rule, "\n";

      break; // next selector
    }
  }
}


//

function displayUsage()
{
  echo "Usage: php " . basename(__FILE__) . " input_file [web_root]\n";
}