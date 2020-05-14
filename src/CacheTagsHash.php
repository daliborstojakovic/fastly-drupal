<?php

namespace Drupal\fastly;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class CacheTagsHash.
 *
 * @package Drupal\fastly
 */
class CacheTagsHash implements CacheTagsHashInterface {

  /**
   * Fastly settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * CacheTagsHash constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('fastly.settings');
  }

  /**
   * Maps cache tags to hashes.
   *
   * Used when the Surrogate-Key/X-Drupal-Cache-Tags header size otherwise
   * exceeds 16 KB.
   *
   * @param string[] $cache_tags
   *   The cache tags in the header.
   *
   * @return string[]
   *   The hashes to use instead in the header.
   */
  public function cacheTagsToHashes(array $cache_tags) {
    $siteCode = getenv('FASTLY_CACHE_TAG_PREFIX');
    $cache_tags_length = getenv('FASTLY_CACHE_TAG_HASH_LENGTH');
    if (!$siteCode) {
      $siteCode = $this->config->get('site_id');
    }
    if (!$cache_tags_length) {
      $cache_tags_length = $this->config->get('cache_tag_hash_length');
    }
    $cache_tags_length = !$cache_tags_length ? self::CACHE_TAG_HASH_LENGTH : $cache_tags_length;
    $hashes = [];
    foreach ($cache_tags as $cache_tag) {
      $cache_tag = $siteCode ? $siteCode . ':' . $cache_tag : $cache_tag;
      $hashes[] = substr(md5($cache_tag), 0, $cache_tags_length);
    }
    return $hashes;
  }

}
