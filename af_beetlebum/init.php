<?php
class Af_Beetlebum extends Plugin {

	private $host;

	function about() {
		return array(1.2,
			"Display Beetlebum comic directly in feed.",
			"Joschasa");
	}

	function api_version() {
		return 2;
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "blog.beetlebum.de") !== FALSE) {
			if (strpos($article["plugin_data"], "beetlebum,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($article["link"]));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					$entries = $xpath->query('(//img[@src])');

					$matches = array();

					foreach ($entries as $entry) {

						if (preg_match("/(http:\/\/.*\/wp-content\/uploads\/.*)/i", $entry->getAttribute("src"), $matches)) {
							$basenode = $entry;
							break;
						}
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "beetlebum,$owner_uid:" . $article["plugin_data"];
					}
				}
			} else if (isset($article["stored"]["content"])) {
				$article["content"] = $article["stored"]["content"];
			}
		}

		return $article;
	}
}
?>
