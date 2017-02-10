<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Plugin names should start with a three letter prefix which is
// unique and reserved for each plugin author ("abc" is just an example).
// Uncomment and edit this line to override:
$plugin['name'] = 'yab_image';

// Allow raw HTML help, as opposed to Textile.
// 0 = Plugin help is in Textile format, no raw HTML allowed (default).
// 1 = Plugin help is in raw HTML.  Not recommended.
# $plugin['allow_html_help'] = 1;

$plugin['version'] = '0.5';
$plugin['author'] = 'Tommy Schmucker';
$plugin['author_uri'] = 'http://www.yablo.de/';
$plugin['description'] = 'Tiny txp:image replacement that allows you to display an image with an assigned caption';

// Plugin load order:
// The default value of 5 would fit most plugins, while for instance comment
// spam evaluators or URL redirectors would probably want to run earlier
// (1...4) to prepare the environment for everything else that follows.
// Values 6...9 should be considered for plugins which would work late.
// This order is user-overrideable.
$plugin['order'] = '5';

// Plugin 'type' defines where the plugin is loaded
// 0 = public       : only on the public side of the website (default)
// 1 = public+admin : on both the public and admin side
// 2 = library      : only when include_plugin() or require_plugin() is called
// 3 = admin        : only on the admin side
$plugin['type'] = '0';

// Plugin "flags" signal the presence of optional capabilities to the core plugin loader.
// Use an appropriately OR-ed combination of these flags.
// The four high-order bits 0xf000 are available for this plugin's private use
if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); // This plugin wants to receive "plugin_prefs.{$plugin['name']}" events
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); // This plugin wants to receive "plugin_lifecycle.{$plugin['name']}" events

$plugin['flags'] = '';

if (!defined('txpinterface'))
        @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---
/**
 *
 * This plugin is released under the GNU General Public License Version 2 and above
 * Version 2: http://www.gnu.org/licenses/gpl-2.0.html
 * Version 3: http://www.gnu.org/licenses/gpl-3.0.html
 */

if (class_exists('\Textpattern\Tag\Registry'))
{
	Txp::get('\Textpattern\Tag\Registry')
		->register('yab_image');
}

function yab_image($atts)
{
	global $img_dir;

	static $cache = array();

	extract(lAtts(array(
		'align'        => 'center',
		'class'        => '',
		'escape'       => '',
		'html_id'      => '',
		'id'           => '',
		'name'         => '',
		'style'        => '',
		'wraptag'      => '',
		'alt_caption'  => '',
		'alt_alt'      => '',
		'alt_title'    => '',
		'alt_as_title' => 0 // 0 or 1
	), $atts));

	if ($name)
	{
		if (isset($cache['n'][$name]))
		{
			$rs = $cache['n'][$name];
		}
		else
		{
			$name = doSlash($name);
			$rs = safe_row('*', 'txp_image', "name = '$name' limit 1");
			$cache['n'][$name] = $rs;
		}
	}
	elseif ($id)
	{
		if (isset($cache['i'][$id]))
		{
			$rs = $cache['i'][$id];
		}
		else
		{
			$id = (int) $id;
			$rs = safe_row('*', 'txp_image', "id = $id limit 1");
			$cache['i'][$id] = $rs;
		}
	}
	else
	{
		trigger_error(gTxt('unknown_image'));
		return;
	}

	if ($rs)
	{
		extract($rs);

		if ($escape == 'html')
		{
			$alt = htmlspecialchars($alt);
			$alt_alt = htmlspecialchars($alt_alt);
			$caption = htmlspecialchars($caption);
			$alt_caption = htmlspecialchars($alt_caption);
			$alt_title = htmlspecialchars($alt_title);
		}

		$img_title = '';
		if ($alt_title)
		{
			$img_title .= ' title = "'.$alt_title.'"';
		}
		else
		{
			if ($alt_as_title)
			{
				if ($alt_alt)
				{
					$img_alt .= ' title="'.$alt_alt.'"';
				}
				else
				{
					if ($alt)
					{
						$img_alt .= ' title="'.$alt.'"';
					}
				}
			}
			else
			{
				if ($alt_caption)
				{
					$img_title .= ' title = "'.$alt_caption.'"';
				}
				else
				{
					if ($caption)
					{
						$img_title .= ' title = "'.$caption.'"';
					}
				}
			}
		}

	$img_alt = '';
	if ($alt_alt)
	{
		$img_alt .= ' alt="'.$alt_alt.'"';
	}
	else
	{
		if ($alt)
		{
			$img_alt .= ' alt="'.$alt.'"';
		}
	}

		$out =
			'<img src="'.hu.$img_dir.'/'.$id.$ext.'" width="'.$w.'" height="'.$h.'"'.
				$img_alt.
				$img_title.
				(($html_id and !$wraptag) ? ' id="'.$html_id.'"' : '').
				(($class and !$wraptag) ? ' class="'.$class.'"' : '').
				($style ? ' style="'.$style.'"' : '').
		' />';

		if ($caption or $alt_caption)
		{
			if ($alt_caption)
			{
				$out .= doTag(
					$alt_caption,
					'small',
					'caption',
					' style="display:block;width:'.$w.'px;"',
					''
				);
			}
			else
			{
				$out .= doTag(
					$caption,
					'small',
					'caption',
					' style="display:block;width:'.$w.'px;"',
					''
				);
			}

			if ($align == 'left' or $align == 'right')
			{
				$out = doTag(
					$out,
					'span',
					'img-caption-'.$align,
					' style="float:'.$align.';"',
					''
				);
			}
			if ($align == 'center')
			{
				$out = doTag(
					$out,
					'span',
					'img-caption-'.$align,
					' style="display:block;text-align:'.$align.';"',
				''
				);
			}
		}

		return ($wraptag) ? doTag($out, $wraptag, $class, '', $html_id) : $out;
	}

	trigger_error(gTxt('unknown_image'));
}
# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN CSS ---
<style type="text/css">
	h1, h2, h3
	h1 code, h2 code, h3 code {
		margin-bottom: 0.6em;
		font-weight: bold
	}
	h1 {
		font-size: 1.4em
	}
	h2 {
		font-size: 1.25em
	}
	h3 {
		margin-bottom: 0;
		font-size: 1.1em
	}
	table {
		margin-bottom: 1em
	}
</style>
# --- END PLUGIN CSS ---
-->
<!--
# --- BEGIN PLUGIN HELP ---
h1. yab_image

This plugin allows you to display an image with an assigned caption as caption :). The width of the caption will always fits the width of the displayed image.

h2. Usage

You can use this plugin almost the same as @<txp:image />@. The behavior is a little bit different: If a caption for an image is available and if you want to float the image and the caption, use the @align@ attribute. This will float the image *and* the caption.

If no image caption (neither as tag attribute nor in image tab) is given @<txp:yab_image />@ works similar as @<txp:image />@·

F.i.: @<txp:yab_image id="5" align="right" />@

h2. Attributes

The following attributes works as expected (as in @<txp:image />@·) when no caption is given:

*id*
Specifies the id assigned at upload of the image to display. Can be found on the images tab. If both name and id are specified, name is used while id is ignored.
(Integer)
*name*
Specifies which image to display by its image name as shown on the images tab.
(String)
*class*
CSS class attribute applied to the wraptag, if set, otherwise to the img tag.
(Default: unset)
*escape*
Escape HTML entities such as <, > and & for the image's alt and title attributes.
(Default: unset)
*html_id*
The HTML id attribute applied to the wraptag, if set, otherwise to the img tag.
(Default: unset)
*style*
Inline CSS style rule.
(Default: unset)
*wraptag*
HTML tag to be used to wrap the img tag, specified without brackets.
(Default: unset)

The following attributes are new oder modified:


*align*
Alignment of the image and the caption.
left, right, center (default: center)
*alt_caption*
Alternate caption, overwrites caption given in image tab.
(Default: unset)
*alt_alt*
Alternate img alt, overwrites alternate text given in image tab.
(Default: unset)
*alt_title*
Alternate title, overwrites caption text given in image tab, which is regulary used for img title attribute.
(Default: unset)
*alt_as_title*
If set, the alt_alt tag attribute or the alternate text is used for the img title attribute.
(Default: 0)

h2. Some weird logic and priorities:

Tag attributes will always overwrites given text in the image tab!

Intentionally usage of alt_as_title tag attribute:
Mainly it could be used for displaying hyperlinks in the caption (the thingy, which is shown under the image) without breaking the HTML validity.
If you want to display hyperlinks in the caption so you can either write these hyperlinks as *raw HTML in the caption field in your image tab* or as *textile markup in the tag attribute*:

F.i.: @<txp:yab_image id="5" align="right" alt_caption='This is a "Link (Link)":http://textpattern.com/ in a caption' />@

*Important:*
* Use single quotes for tags when insert double quotes!
* Don't start with an double quote (@alt_caption='"Link (Link)":http://textpattern.com/ in a caption'@ will not work).
Use a beginning whitespace or text before the double quote (@alt_caption=' "Link (Link)":http://textpattern.com/ in a caption'@).
* Be sure the line will be textiled.

Problem: The generated img title attribute is broken, because the hyperlink with the double quotes breaks it.
Solution: Now you can additionally use the *alt_title* tag attribute to set a specific img title or the *alt_as_title* so that the img title will generated from alternate text in the image tab.

F.i.: @<txp:yab_image id="5" align="right" alt_caption='This is a "Link (Link)":http://textpattern.com/ in a caption' alt_as_title="1" />@

or

F.i.: @<txp:yab_image id="5" align="right" alt_caption='This is a "Link (Link)":http://textpattern.com/ in a caption' alt_title="Nice picture which shows something" />@

h2. Styling

The plugin will create new HTML elements (other than @<txp:image />@):

If an image caption is given:
@<small class="caption">given caption here</small>@: This elements will wrap the caption.

And if additionally the attribute @align@ is used:
@<span class="img-caption-{align}">image+caption</span>@, where @{align}@ will be your given align: This span is wrapped around the image and the caption. So you have the ability to set different paddings for different floats.

As of version 0.3 @<txp:yab_image />@ will produce (X)HTML-strict output. The align attribute in the image element will be completly removed. So the HTML markup will be different from @<txp:image />@.

You can always use the @wraptag@ attribute to wrap all with another html element.
Depending on your usage and used wraptag you have to clear the floats.
# --- END PLUGIN HELP ---
-->
<?php
}
?>
