<?php
// Tagcloud extension, https://github.com/adegans/yellow-tagcloud

class YellowTagCloud {
	const VERSION = "1.0.1";
	public $yellow; // access to API

	// Handle initialisation
	public function onLoad($yellow) {
		$this->yellow = $yellow;
	}

	// Handle page content element
	public function onParseContentElement($page, $name, $text, $attributes, $type) {
		$output = "";

		if($name == "tagcloud" && ($type=="block" || $type=="inline")) {
			if($this->yellow->extension->isExisting("blog")) {
				$blog_home = $this->yellow->system->get("blogStartLocation");

				if ($blog_home == "auto") {
					$pages = $page->getChildren();
				} else {
					$pages = $this->yellow->content->index();
				}

				list($maximum_tags, $minimum_font, $maximum_font) = $this->yellow->toolbox->getTextArguments($text);
				if (is_string_empty($maximum_tags)) $maximum_tags = 40;
				if (is_string_empty($minimum_font)) $minimum_font = 12;
				if (is_string_empty($maximum_font)) $maximum_font = 34;

				$tags = array()	;
				foreach ($pages as $key => $collection) {
					$page_tags = $collection->get("tag");
					if(!empty($page_tags)) {
						$page_tags = explode(",", $page_tags);

						foreach($page_tags as $tag) {
							$tag = trim($tag);
							if(!array_key_exists($tag, $tags)) {
								$tags[$tag] = 1;
							} else {
								$tags[$tag] = $tags[$tag]+1;
							}
						}
					}
				}

				$max_qty = max(array_values($tags));
				$min_qty = min(array_values($tags));
				$spread = $max_qty - $min_qty;
				if($spread == 0) $spread = 1;
				$step = ($maximum_font - $minimum_font)/($spread);

				// Sort, strip and sort the array
				arsort($tags, SORT_NUMERIC);
				$tags = array_slice($tags, 0, $maximum_tags, true);
				ksort($tags, SORT_STRING);

				$output = "<div class=\"tag-cloud ".htmlspecialchars($name)."\">\n";
				foreach($tags as $tag => $count) {
					$size = $minimum_font + (($count - $min_qty) * $step);
					$size = round($size, 2);
					$output .= " <a class=\"tag-cloud-link\" href=\"".$page->getLocation(true).$this->yellow->lookup->normaliseArguments("tag:$tag")."\" style=\"font-size:". $size ."px;\">".$tag."</a> ";
				}
				$output .= "</div>\n";
			} else {
				$output = "<p style=\"color:#F00;\">Error: Blog extension is not installed!</p>";
			}
		}
		return $output;
	}
}
?>
