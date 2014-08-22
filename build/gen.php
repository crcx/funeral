<?php

function get_author($data)
{
  $author = "unknown";
  foreach ($data as $line)
  {
    if ($author == "next")
      $author = substr($line, 4, -10);
    if ($line == '<tr><th class="docinfo-name">Author:</th>')
      $author = "next";
  }
  echo "  Author: $author\n";
  return $author;
}


function get_title($data)
{
  $title = "unknown";

  foreach ($data as $line)
  {
    if (substr($line, 0, 77) == '<tr class="field"><th class="docinfo-name">Title:</th><td class="field-body">')
      $title = substr($line,77,-5);
  }
  echo "  Title: $title\n";
  return $title;
}


function get_series($data)
{
  $series = "none";

  foreach ($data as $line)
  {
    if (substr($line, 0, 78) == '<tr class="field"><th class="docinfo-name">Series:</th><td class="field-body">')
      $series = substr($line,78,-5);
  }
  echo "  Series: $series\n";
  return $series;
}


function get_series_number($data)
{
  $number = "none";

  foreach ($data as $line)
  {
    if (substr($line, 0, 78) == '<tr class="field"><th class="docinfo-name">Number:</th><td class="field-body">')
      $number = substr($line,78,-5);
  }
  echo "  Series Number: $number\n";
  return $number;
}


function create_opf($author, $title, $series, $number, $spine, $manifest)
{
  $opf  = '<?xml version="1.0"?>'."\n";
  $opf .= '<package version="2.0" xmlns="http://www.idpf.org/2007/opf" unique-identifier="BookId">'."\n";
  $opf .= '<metadata xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:opf="http://www.idpf.org/2007/opf">'."\n";
  $opf .= '  <dc:language>en-US</dc:language>'."\n";
  $opf .= '  <dc:publisher>Alexandria Archive</dc:publisher>'."\n";
  $opf .= '  <dc:identifier id="BookId">crcx:1234567890</dc:identifier>'."\n";
  $opf .= "  <dc:creator>$author</dc:creator>\n";
  if ($series != "none")
    $opf .= "  <dc:title>$series #$number $title</dc:title>\n";
  else
    $opf .= "  <dc:title>$title</dc:title>\n";
  $opf .= "</metadata>\n";
  $opf .= $manifest;
  $opf .= $spine;
  $opf .= "</package>\n";
  return $opf;
}


function create_ncx($data)
{
  $chapter = 1;
  $ncx  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
  $ncx .= '<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1">'."\n";
  $ncx .= '  <head>'."\n";
  $ncx .= '    <meta name="dtb:uid" content="crcx1234567890"/>'."\n";
  $ncx .= '    <meta name="dtb:depth" content="1"/>'."\n";
  $ncx .= '    <meta name="dtb:totalPageCount" content="0"/>'."\n";
  $ncx .= '    <meta name="dtb:maxPageNumber" content="0"/>'."\n";
  $ncx .= '  </head>'."\n";
  $ncx .= '  <docTitle>'."\n";
  $ncx .= '    <text>Sample .epub eBook</text>'."\n";
  $ncx .= '  </docTitle>'."\n";
  $ncx .= '  <navMap>'."\n";
  foreach ($data as $line)
  {
    if (substr($line, 0, 4) == "<h1>")
    {
      $ncx .= '    <navPoint id="chapter'.$chapter.'" playOrder="'.$chapter.'">'."\n";
      $ncx .= '      <navLabel>'."\n";
      $ncx .= '        <text>'.strtoupper(substr($line, 4, strlen($line)-9)).'</text>'."\n";
      $ncx .= '      </navLabel>'."\n";
      $ncx .= '      <content src="chapter_'.$chapter.'.xhtml"/>'."\n";
      $ncx .= '    </navPoint>'."\n";
      $chapter++;
    }
    if (substr($line, 0, 4) == "<h2>")
    {
      $ncx .= '    <navPoint id="chapter'.$chapter.'" playOrder="'.$chapter.'">'."\n";
      $ncx .= '      <navLabel>'."\n";
      $ncx .= '        <text>'.substr($line, 4, strlen($line)-9).'</text>'."\n";
      $ncx .= '      </navLabel>'."\n";
      $ncx .= '      <content src="chapter_'.$chapter.'.xhtml"/>'."\n";
      $ncx .= '    </navPoint>'."\n";
      $chapter++;
    }
  }
  $ncx   .= "</navMap>\n</ncx>\n";
  return $ncx;
}


function create_manifest($data)
{
  $chapter = 1;
  $image = "none";
  $img   = 1;
  $man   = "<manifest>\n";
  $man  .= '  <item id="ncx" href="toc.ncx" media-type="application/x-dtbncx+xml"/>'."\n";
  $man  .= '  <item id="style" href="alexandria.css" media-type="text/css"/>'."\n";
  foreach ($data as $line)
  {
    if (substr($line, 0, 4) == "<h1>" || substr($line, 0, 4) == "<h2>")
    {
      $man   .= '  <item id="chapter'.$chapter.'" href="chapter_'.$chapter.'.xhtml" media-type="application/xhtml+xml"/>'."\n";
      $chapter++;
    }
    if (substr($line, 0, 77) == '<tr class="field"><th class="docinfo-name">Image:</th><td class="field-body">')
    {
      $image = substr($line,84,-5);
      $man   .= '  <item id="image'.$img.'" href="images/'.$image.'" media-type="image/png"/>'."\n";
      $img++;
    }
  }
  $man   .= "</manifest>\n";
  return $man;
}


function get_images($data)
{
  $image = "none";
  $img   = 0;
  foreach ($data as $line)
  {
    if (substr($line, 0, 77) == '<tr class="field"><th class="docinfo-name">Image:</th><td class="field-body">')
    {
      $img++;
      if ($img == 1)
        mkdir("book/OEBPS/images");
      $image = substr($line,84,-5);
      echo "  #$img:  $image\n";
      copy("images/$image", "book/OEBPS/images/$image");
    }
  }
  return $img;
}


function create_spine($data)
{
  $chapter = 1;
  $spine = "<spine toc=\"ncx\">\n";
  foreach ($data as $line)
  {
    if (substr($line, 0, 4) == "<h1>" || substr($line, 0, 4) == "<h2>")
    {
      $spine .= '  <itemref idref="chapter'.$chapter.'"/>'."\n";
      $chapter++;
    }
  }
  $spine .= "</spine>\n";
  return $spine;
}


function split_chapters($data)
{
  $last = "none";
  $chapter = 1;
  foreach ($data as $line)
  {
    if (substr($line, 0, 20) == '<div class="section"')
    {
      if ($last != "none")
      {
        fwrite($last, '</body>'."\n".'</html>'."\n");
        fclose($last);
      }
      $last = fopen("book/OEBPS/chapter_$chapter.xhtml", "w");
      $chapter++;
      fwrite($last, '<?xml version="1.0" encoding="utf-8" ?>'."\n");
      fwrite($last, '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">'."\n");
      fwrite($last, '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">'."\n");
      fwrite($last, '<head>'."\n");
      fwrite($last, '<title>none</title>'."\n");
      fwrite($last, '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'."\n");
      fwrite($last, '<link href="alexandria.css" type="text/css" rel="stylesheet" />'."\n");
      fwrite($last, '</head>'."\n".'<body>'."\n");
    }
    if ($last != "none" && $line != "</body>" && $line != "</html>")
      fwrite($last, $line."\n\n");
  }
  if ($last != "none")
  {
    fwrite($last, '</body>'."\n".'</html>'."\n");
    fclose($last);
  }
}


function load_source($file)
{
  return explode("\n", file_get_contents($file));
}


function process()
{
  echo "Alexandria ePub Generator 2010.07.08\n";
  echo "Loading source...\n";
    $data = load_source("source.xhtml");
  echo "Scanning for metadata...\n";
    $title  = get_title($data);
    $author = get_author($data);
    $series = get_series($data);
    $number = get_series_number($data);
  echo "Splitting into chapters...\n";
    split_chapters($data);
  echo "Looking for images...\n";
    get_images($data);
  echo "Generating Spine...\n";
    $spine = create_spine($data);
  echo "Generating Manifest...\n";
    $manifest = create_manifest($data);
  echo "Generating NCX...\n";
    $ncx = create_ncx($data);
  echo "Combine metadata, spine, and manifest to make OPF...\n";
    $opf = create_opf($author, $title, $series, $number, $spine, $manifest);
  echo "Writing OPF...\n";
    $h = fopen("book/OEBPS/content.opf", "w");
    fwrite($h, $opf);
    fclose($h);
  echo "Writing Table of Contents...\n";
    $h = fopen("book/OEBPS/toc.ncx", "w");
    fwrite($h, $ncx);
    fclose($h);
}

process();
?>
