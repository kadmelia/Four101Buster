<?php
require "vendor/autoload.php";

class Four101Buster {
	
	private $site;

    /**
     * Already visited links.
     *
     * @var array
     */
    private $links = array();

    /**
     * Found external links.
     *
     * @var array
     */
    private $externalLinks = array();

    /**
     * Broken links found.
     *
     * @var array
     */
    private $broken = array();
	
	/**
	* Constructor
	* 
	*/
	public function __construct($site) {

        $this->site = $site;

        $this->parsePage($this->site);

        $this->bust404s();

        print_r($this->broken);

	}

    /**
     * Parse a site a grab all link on it.
     *
     * Start with given URL and call itself on all links found.
     *
     * *RECURSIVE*
     *
     * @param $page URL to parse
     */
    private function parsePage($page) {

        // Get all link on a page
        $qp = QueryPath::withHTML($page, 'a');

        $addresses = array();

        echo "Analyse $page\n";

		foreach($qp as $link) {

            // Get href link
            $address = $link->attr("href");

            // Try removing domain
            $address = str_replace($this->site, "", $address);

            // if link is still "external" link, store it but don't parse it
            if (preg_match("#^http#", $address)) {
                $this->externalLinks[] = $address;
                continue;
            }

            // If we already visited this link, pass it
            if (in_array($address, $this->links))
                continue;

            $addresses[] = $address;

        }

        $addresses = array_unique($addresses);

        foreach ($addresses as $address)
            $this->links[] = $address;

        foreach ($addresses as $address) {
            $this->parsePage($this->buildFullUrl($address));

        }

	}

    /**
     * Build full URL with URL without domain.
     *
     * @param $url
     * @return string
     */
    private function buildFullUrl($url) {

        // Check if URL is not already "full"
        if (preg_match("#^http#", $url))
            return $url;

        // Check if starting "/" is present
        if (!preg_match("#^/#", $url))
            $url = "/" . $url;

        return $this->site . $url;

    }

    /**
     * Bust 404 from parsed links.
     *
     * @return void
     */
    private function bust404s() {

        foreach ($this->links as $link) {

            if ($this->is404($link))
                $this->broken[] = $link;

        }

    }

    /**
     * Check if given URL is a 404.
     *
     * @use cURL
     * @param $url
     * @return bool
     */
    private function is404($url) {

        echo "Is 404 : $url\n";

        $handle = curl_init($url);
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        // Get the HTML or whatever is linked in $url.
        $response = curl_exec($handle);

        // Get return code
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        // Check for 404
        return $httpCode == 404;

    }
}

if (isset($argv[1]))
    $buster = new Four101Buster($argv[1]);