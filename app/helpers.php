<?php

use Thunder\Shortcode\ShortcodeFacade;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

function generateUserFolder($id)
{
  return md5(md5($id . md5($id)));
}

function userFolder()
{
  return $_COOKIE['_userfolder'];
}

function setUserFolder($id)
{
  if (!Storage::disk('public')->exists('uploads')) {
    Storage::disk('public')->makeDirectory('uploads');
    Storage::disk('public')->makeDirectory('thumbs');
  }

  $dir = generateUserFolder($id, $time = time() + 60 * 60 * 24 * 30 * 12 * 10);
  if (!Storage::disk('public')->exists('uploads/' . $dir)) {
    Storage::disk('public')->makeDirectory('uploads/' . $dir);
    Storage::disk('public')->makeDirectory('thumbs/' . $dir);
  }

  setcookie('_userfolder', $dir, $time, '/');
}

function removeCookie($key, $path = '/')
{
  if (isset($_COOKIE[$key])) {
    unset($_COOKIE[$key]);
    setcookie($key, '', time() - 3600, $path);
    return true;
  }
  return false;
}

function shortcodes()
{
  $facade = new ShortcodeFacade();
  $facade->addHandler('p', 'paragraph');
  $facade->addHandler('g', 'image');
  $facade->addHandler('url', 'getUrl');
  $facade->addHandler('pangkat', 'superscript');
  $facade->addHandler('sub', 'subscript');
  $facade->addHandler('tabel', 'table');
  $facade->addHandler('baris', 'row');
  $facade->addHandler('kolom', 'col');
  return $facade;
}

function soalShortcode()
{
  $facade = new ShortcodeFacade();
  $facade->addHandler('teks', 'text');
  $facade->addHandler('soal', 'soal');
  $facade->addHandler('opsi', 'opsi');
  $facade->addHandler('jawaban', 'jawaban');
  return $facade;
}

function shortcode($content)
{
  $facade = shortcodes();
  return $facade->process($content);
}

function parseSoal($content)
{
  $facade = soalShortcode();
  return $facade->process($content);
}

function paragraph(ShortcodeInterface $s)
{
  $keys = array_keys($s->getParameters());
  return '<div style="width: 100% !important;text-indent: ' . ($s->getParameter('indent') ?? 0) . 'rem" class="' . (in_array('tengah', $keys) ? 'text-center' : (in_array('kanan', $keys) ? 'text-right' : 'text-left')) . '">
    ' . $s->getContent() . '
    <div class="clearfix"></div>
    </div>';
}

function getUrl($url)
{
  if (!filter_var($url, FILTER_VALIDATE_URL) !== false) {
    $url = url('uploads/' . userFolder() . '/' . $url);
  }

  return $url;
}

function image(ShortcodeInterface $s)
{
  $keys = array_keys($s->getParameters());
  $url = getUrl($s->getContent());
  $width = null;
  $height = null;
  foreach ($keys as $v) {
    if (strpos($v, 'px') !== false || strpos($v, 'cm') !== false || strpos($v, 'mm') !== false || strpos($v, '%') !== false || is_numeric($v) || strpos($v, 'auto') !== false) {
      if (is_null($width)) {
        $width = is_numeric($v) ? $v . 'px' : $v;
      } else {
        $height = is_numeric($v) ? $v . 'px' : $v;
      }
    }
  }

  return sprintf("<div class='m-1 %s'><img style='max-width: 100%%;width:%s;height:%s' src='%s'></div>", (in_array("kiri", $keys) ? "float-left" : (in_array("kanan", $keys) ? "float-right" : (in_array("tengah", $keys) ? "flex justify-center" : (in_array("auto", $keys) ? "inline-block" : "flex")))), $width, $height, $url);
}

function superscript(ShortcodeInterface $s)
{
  return sprintf('<sup>%s</sup>', $s->getContent());
}

function subscript(ShortcodeInterface $s)
{
  return sprintf('<sub>%s</sub>', $s->getContent());
}

function table(ShortcodeInterface $s)
{
  $keys = array_keys($s->getParameters());
  $width = null;
  foreach ($keys as $v) {
    if (strpos($v, 'px') !== false || strpos($v, '%') !== false || is_numeric($v) || strpos($v, 'auto') !== false) {
      $width = is_numeric($v) ? $v . 'px' : $v;
      break;
    }
  }

  return sprintf('<table width="%s">%s</table>', $width, $s->getContent());
}

function row(ShortcodeInterface $s)
{
  return sprintf('<tr>%s</tr>', $s->getContent());
}

function col(ShortcodeInterface $s)
{
  $keys = array_keys($s->getParameters());
  $align = in_array('tengah', $keys) ? 'center' : (in_array('kanan', $keys) ? 'right' : 'left');
  $valign = in_array('atas', $keys) ? 'top' : (in_array('bawah', $keys) ? 'bottom' : 'middle');

  $width = null;
  $height = null;
  foreach ($keys as $v) {
    if (strpos($v, 'px') !== false || strpos($v, '%') !== false || is_numeric($v) || strpos($v, 'auto') !== false) {
      if (is_null($width)) {
        $width = is_numeric($v) ? $v . 'px' : $v;
      } else {
        $height = is_numeric($v) ? $v . 'px' : $v;
      }
    }
  }

  return sprintf(in_array('header', $keys) ? '<th style="width: %s;height: %s;padding: 7px 9px;border: solid 1px #9ca3af" align="%s" valign="%s">%s</th>' : '<td style="width:%s;height:%s;padding: 7px 9px;border: solid 1px #9ca3af" align="%s" valign="%s">%s</td>', $width, $height, $align, $valign, $s->getContent());
}

function soal(ShortcodeInterface $s)
{
  $keys = $s->getParameters();
  $num = $s->getParameter('no');
  $type = $s->getParameter('jenis');
  $score = $s->getParameter('skor');
  $shuffle = in_array('acak', $keys);
  $json = getJson($s->getContent());
  $content = null;
  $options = [];
  $corrects = [];
  $answer = null;
  $relations = [];
  $labels = [];

  if (count($json)) {
    foreach ($json as $j) {
      $opt = json_decode($j);
      if (isValidJSON($j)) {
        if (isset($opt->code)) {
          $options[$opt->code] = $opt->content;
          $corrects[$opt->code] = $opt->correct == 'benar' ? true : false;
          $relations[$opt->code] = $opt->relation ?? null;
          if (isset($opt->label)) {
            array_push($labels, $opt->label);
          }
        } elseif (isset($opt->answer)) {
          $answer = $opt->answer;
        } elseif (isset($opt->text)) {
          $content = $opt->text;
        }
      }
    }
  }

  $data = [
    'num' => intval($num),
    'type' => $type,
    'score' => doubleval($score),
    'text' => $content,
    'options' => $options,
    'shuffle' => $shuffle,
    'corrects' => $corrects,
    'answer' => $answer,
    'relations' => $relations,
    'labels' => $labels,
  ];
  return json_encode($data);
}

function text(ShortcodeInterface $s)
{
  return json_encode([
    'text' => trim($s->getContent())
  ]);
}

function opsi(ShortcodeInterface $s)
{
  $keys = array_keys($s->getParameters());
  return json_encode([
    'label' => $s->getParameter('label') ?? null,
    'code' => @$keys[0] ?? null,
    'correct' => in_array('benar', $keys) ? true : false,
    'relation' => $s->getParameter('relasi') ? [explode(",", $s->getParameter('relasi'))[0]] : null,
    'content' => trim($s->getContent())
  ]);
}
function jawaban(ShortcodeInterface $s)
{
  return json_encode([
    'answer' => trim($s->getContent())
  ]);
}

function getJson($string)
{
  $pattern = '
    /
    \{              # { character
        (?:         # non-capturing group
            [^{}]   # anything that is not a { or }
            |       # OR
            (?R)    # recurses the entire pattern
        )*          # previous group zero or more times
    \}              # } character
    /x
    ';

  preg_match_all($pattern, $string, $matches);
  return $matches[0];
}

function isValidJSON($string)
{
  json_decode($string);
  return (json_last_error() == JSON_ERROR_NONE);
}

function cleanCodeTags($content)
{
  return preg_replace('/^(<br\s*\/?>)*|(<br\s*\/?>)*$/i', '', $content);
}
