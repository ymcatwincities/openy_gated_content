<?php

namespace Drupal\openy_gated_content\Plugin\video_embed_field\Provider;

use Drupal\Component\Serialization\Json;
use Drupal\video_embed_field\ProviderPluginBase;

/**
 * A Vimeo Event provider plugin.
 *
 * In Open Y used video_embed_field, maybe in future it will be replaced by
 * core oEmbed.
 *
 * @see https://www.drupal.org/project/video_embed_field/issues/2997799
 * @see https://developer.vimeo.com/api/oembed/rles#embedding-a-recurring-live-event
 *
 * @VideoEmbedProvider(
 *   id = "vimeo_event",
 *   title = @Translation("Vimeo Event")
 * )
 */
class VimeoEvent extends ProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function renderEmbedCode($width, $height, $autoplay) {
    $iframe = [
      '#type' => 'video_embed_iframe',
      '#provider' => 'vimeo_event',
      '#url' => sprintf('https://vimeo.com/event/%s/embed', $this->getVideoId()),
      '#query' => [
        'autoplay' => $autoplay,
      ],
      '#attributes' => [
        'width' => $width,
        'height' => $height,
        'frameborder' => '0',
        'allowfullscreen' => 'allowfullscreen',
      ],
    ];
    if ($time_index = $this->getTimeIndex()) {
      $iframe['#fragment'] = sprintf('t=%s', $time_index);
    }
    return $iframe;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteThumbnailUrl() {
    $data = $this->oEmbedData();
    // @todo maybe better to use custom image instead vimeo default.
    return isset($data['thumbnail_url']) ? $data['thumbnail_url'] : 'https://i.vimeocdn.com/video/default_852x480.jpeg';
  }

  /**
   * Get the vimeo event oEmbed data.
   *
   * @return array
   *   An array of data from the oEmbed endpoint.
   */
  protected function oEmbedData() {
    $url = 'https://vimeo.com/api/oembed.json?url=' . $this->getInput();
    $response = \Drupal::service('http_client')->get($url, [
      'headers' => [
        // For private events we need to add HTTP_REFERER header.
        'Referer' => $_SERVER['HTTP_REFERER'],
      ],
    ]);
    $content = (string) $response->getBody();
    return Json::decode($content);
  }

  /**
   * {@inheritdoc}
   */
  public static function getIdFromInput($input) {
    preg_match('/^https?:\/\/vimeo\.com\/event\/(?<id>[0-9]*)?(\/[a-zA-Z0-9]+)?$/', $input, $matches);
    return isset($matches['id']) ? $matches['id'] : FALSE;
  }

  /**
   * Get the time index from the URL.
   *
   * @return string|false
   *   A time index parameter to pass to the frame or FALSE if none is found.
   */
  protected function getTimeIndex() {
    preg_match('/\#t=(?<time_index>(\d+)s)$/', $this->input, $matches);
    return isset($matches['time_index']) ? $matches['time_index'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    $data = $this->oEmbedData();
    return $data['title'];
  }

}
