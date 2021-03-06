<?php
class Af_DAUJones extends Plugin {

	private $host;

	function about() {
		return array(1.2,
			"Load complete DAUJones story into feed.",
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

		if (strpos($article["link"], "daujones.com") !== FALSE) {
			if (strpos($article["plugin_data"], "daujones,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					// first remove advertisement stuff
					$stuff = $xpath->query('(//form)|(//span)|(//div[@class="rightnav"])|(//div[@class="kommentare"])|(//a)|(//h2[@style])|(//center)');

					foreach ($stuff as $removethis) {
						$removethis->parentNode->removeChild($removethis);
					}

					$entries = $xpath->query('(//div[@class="maincontent"])');

					foreach ($entries as $entry) {

						$basenode = $entry;
						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "daujones,$owner_uid:" . $article["plugin_data"];
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
