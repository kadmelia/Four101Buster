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
     * Already visited links.
     *
     * @var array
     */
    private $externalLinks = array();
	
	/**
	* Constructor
	* 
	*/
	public function __construct($site) {

        $this->site = $site;

        $this->parsePage($this->site);

        print_r($this->links);
        print_r($this->externalLinks);

	}
	
	private function parsePage($page) {

        // Get all link on a page
        try {

        } catch (Exception $e) {

        }

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

//        print_r($addresses);

	}

    private function buildFullUrl($url) {

        // Check if URL is not already "full"
        if (preg_match("#^http#", $url))
            return $url;

        // Check if starting "/" is present
        if (!preg_match("#^/#", $url))
            $url = "/" . $url;

        return $this->site . $url;

    }
  
}

$buster = new Four101Buster("http://www.perdu.com");