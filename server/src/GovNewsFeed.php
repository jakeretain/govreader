<?php
namespace GovReader;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Response;
use MarkWilson\XmlToJson\XmlToJsonConverter;
use Nette\Caching\Cache;

/**
 * Loads news feed data from gov.uk for news and communications
 */
final class GovNewsFeed {

    /**
     * Uses the ISO-8601 standard for specifying time intervals. PT1H - 1 hour; PT30M - 30 minutes
     */
    private const CACHE_LIFETIME = 'PT1H';

    /**
     * Department short codes against atom feed slug
     */
    private const DEPARTMENT_DETAILS = [
        'AGO'   => [
            'slug'  => 'attorney-generals-office',
            'name'  => 'Attorney General\'s Office',
            'hex'   => '#9f1888'
            ],
        'CO'    => [
            'slug'  => 'cabinet-office',
            'name'  => 'Cabinet Office',
            'hex'   => '#005abb'
            ],
        'DBEIS' => [
            'slug'  => 'department-for-business-energy-and-industrial-strategy',
            'name'  => 'Department for Business, Energy & Industrial Strategy',
            'hex'   => '#003479'
            ],
        'DDCMS' => [
            'slug'  => 'department-for-digital-culture-media-sport',
            'name'  => 'Department for Digital, Culture, Media & Sport',
            'hex'   => '#d40072'
            ],
        'DE'    => [
            'slug'  => 'department-for-education',
            'name'  => 'Department for Education',
            'hex'   => '#003a69'
            ],
        'DEFRA' => [
            'slug'  => 'department-for-environment-food-rural-affairs',
            'name'  => 'Department for Environment, Food & Rural Affairs',
            'hex'   => '#00a33b'
            ],
        'DID'   => [
            'slug'  => 'department-for-international-development',
            'name'  => 'Department for International Development',
            'hex'   => '#002878'
            ],
        'DIT'   => [
            'slug'  => 'department-for-international-trade',
            'name'  => 'Department for International Trade',
            'hex'   => '#cf102d'
            ],
        'DT'    => [
            'slug'  => 'department-for-transport',
            'name'  => 'Department for Transport',
            'hex'   => '#006c56'
            ],
        'DWP'   => [
            'slug'  => 'department-for-work-pensions',
            'name'  => 'Department for Work & Pensions',
            'hex'   => '#00beb7'
            ],
        'DHSC'  => [
            'slug'  => 'department-of-health-and-social-care',
            'name'  => 'Department for Health & Social Care',
            'hex'   => '#00ad93'
            ],
        'FCO'   => [
            'slug'  => 'foreign-commonwealth-office',
            'name'  => 'Foreign & Commonwealth Office',
            'hex'   => '#003e74'
            ],
        'HMT'   => [
            'slug'  => 'hm-treasury',
            'name'  => 'HM Treasury',
            'hex'   => '#af292e'
            ],
        'HO'    => [
            'slug'  => 'home-office',
            'name'  => 'Home Office',
            'hex'   => '#9325b2'
            ],
        'MO'    => [
            'slug'  => 'ministry-of-defence',
            'name'  => 'Ministry of Defence',
            'hex'   => '#4d2942'
            ],
        'MHCLG' => [
            'slug'  => 'ministry-of-housing-communities-and-local-government',
            'name'  => 'Ministry of Housing, Communities & Local Government',
            'hex'   => '#099'
            ],
        'MOJ'   => [
            'slug'  => 'ministry-of-justice',
            'name'  => 'Ministry of Justice',
            'hex'   => '#0b0c0c'
            ],
        'NIO'   => [
            'slug'  => 'northern-ireland-office',
            'name'  => 'Northern Ireland Office',
            'hex'   => '#002663'
            ],
        'OAGS'  => [
            'slug'  => 'office-of-the-advocate-general-for-scotland',
            'name'  => 'Office of the Advocate General of Scotland',
            'hex'   => '#002663'
            ],
        'OLHC'  => [
            'slug'  => 'the-office-of-the-leader-of-the-house-of-commons',
            'name'  => 'Office of the Leader of the House of Commons',
            'hex'   => '#317023'
            ],
        'OLHL'  => [
            'slug'  => 'office-of-the-leader-of-the-house-of-lords',
            'name'  => 'Office of the Leader of the House of Lords',
            'hex'   => '#9c132e'
            ],
        'OSSS'  => [
            'slug'  => 'office-of-the-secretary-of-state-for-scotland',
            'name'  => 'Office of the Secretary of State for Scotland',
            'hex'   => '#002663'
            ],
        'OSSW'  => [
            'slug'  => 'office-of-the-secretary-of-state-for-wales',
            'name'  => 'Office of the Secretary of State for Wales',
            'hex'   => '#a33038'
            ],
        'UKEF'  => [
            'slug'  => 'uk-export-finance',
            'name'  => 'UK Export Finance',
            'hex'   => '#cf102d'
            ]
    ];

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var XmlToJsonConverter
     */
    private $xmlToJsonConverter;

    /**
     * @param HttpClient $httpClient
     * @param XmlToJsonConverter $converter
     * @param Cache $cache
     */
    public function __construct(HttpClient $httpClient, XmlToJsonConverter $converter, Cache $cache)
    {
        $this->httpClient = $httpClient;
        $this->xmlToJsonConverter = $converter;
        $this->cache = $cache;
    }

    /**
     * Load the feed data array from cache or fetch new one
     *
     * @param string|null $departmentId
     * @return mixed[][]|mixed[][][]
     */
    public function get(?string $departmentId): array
    {
        if ($departmentId !== null && !in_array($departmentId, array_keys(self::DEPARTMENT_DETAILS))) {
            throw new \InvalidArgumentException('Unrecognised department id');
        }

        $feeds = $this->loadData();

        if ($departmentId !== null) {
            return $this->buildResponseItem($departmentId, $feeds[$departmentId]);
        }

        $formattedFeeds = [];

        foreach ($feeds as $departmentId => $feed) {
            $formattedFeeds[] = $this->buildResponseItem($departmentId, $feed);
        }

        return $formattedFeeds;
    }

    /**
     * Build the individual department item, including the feed and department details
     *
     * @param string $departmentId
     * @param mixed $feed
     * @return mixed
     */
    private function buildResponseItem(string $departmentId, array $feed): array {
        return [
            'id'   => $departmentId,
            'name' => self::DEPARTMENT_DETAILS[$departmentId]['name'],
            'hex'  => self::DEPARTMENT_DETAILS[$departmentId]['hex'],
            'feed' => $feed
        ];
    }

    /**
     * Get department feeds from cache or request them
     *
     * @return mixed[][]
     */
    private function loadData(): array {
        $cacheId = 'department-feeds';
        $feeds = $this->cache->load($cacheId);

        if ($feeds !== null) {
            return $feeds;
        }

        $now = new \DateTime();
        $expiryTime = $now->add(new \DateInterval(self::CACHE_LIFETIME));
        $this->cache->save($cacheId, $this->requestData(), [Cache::EXPIRATION => $expiryTime->getTimestamp()]);
        return $this->cache->load($cacheId);
    }

    /**
     * Request atom feed data for every department, in parallel.
     *
     * @return mixed[][][]
     */
    private function requestData(): array {
        $promises = [];

        foreach (self::DEPARTMENT_DETAILS as $departmentId => $department) {
            $promises[$departmentId] = $this->httpClient->getAsync(
                'https://www.gov.uk/government/organisations/' . $department['slug'] . '.atom'
            );
        }

        /**
         * @var Response[] $responses
         */
        $responses = Promise\unwrap($promises);

        $results = [];
        foreach ($responses as $departmentId => $response) {
            $results[$departmentId] = $this->xmlToJsonConverter->asArray(
                new \SimpleXMLElement($response->getBody())
            )['feed'];
        }

        return $results;
    }
}
