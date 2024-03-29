<?php declare(strict_types = 1);

namespace MailPoet\EmailEditor\Engine;

if (!defined('ABSPATH')) exit;


class SettingsController {

  const ALLOWED_BLOCK_TYPES = [
    'core/button',
    'core/buttons',
    'core/paragraph',
    'core/heading',
    'core/column',
    'core/columns',
    'core/image',
    'core/list',
    'core/list-item',
  ];

  const DEFAULT_SETTINGS = [
    'enableCustomUnits' => ['px', '%'],
    '__experimentalFeatures' => [
      'color' => [
        'custom' => true,
        'text' => true,
        'background' => true,
        'customGradient' => false,
        'defaultPalette' => true,
        'palette' => [
          'default' => [],
        ],
      ],
    ],
  ];

  /**
   * Width of the email in pixels.
   * @var string
   */
  const EMAIL_WIDTH = '660px';

  /**
   * Color of email layout background.
   * @var string
   */
  const EMAIL_LAYOUT_BACKGROUND = '#cccccc';

  /**
   * Padding of the email in pixels.
   * @var string
   */
  const EMAIL_PADDING = '10px';

  /**
   * Gap between blocks in flex layouts
   * @var string
   */
  const FLEX_GAP = '16px';

  /**
   * Default styles applied to the email. These are going to be replaced by style settings.
   * This is currently more af a proof of concept that we can apply styles to the email.
   * We will gradually replace these hardcoded values with styles saved as global styles or styles saved with the email.
   * @var array
   */
  const DEFAULT_EMAIL_CONTENT_STYLES = [
    'typography' => [
      'fontFamily' => "Arial, 'Helvetica Neue', Helvetica, sans-serif",
      'fontSize' => '16px',
    ],
    'h1' => [
      'typography' => [
        'fontSize' => '32px',
      ],
    ],
    'h2' => [
      'typography' => [
        'fontSize' => '24px',
      ],
    ],
    'h3' => [
      'typography' => [
        'fontSize' => '18px',
      ],
    ],
    'h4' => [
      'typography' => [
        'fontSize' => '16px',
      ],
    ],
    'h5' => [
      'typography' => [
        'fontSize' => '14px',
      ],
    ],
    'h6' => [
      'typography' => [
        'fontSize' => '12px',
      ],
    ],
  ];

  private $availableStylesheets = '';

  public function getSettings(): array {
    $coreDefaultSettings = get_default_block_editor_settings();
    $coreThemeData = \WP_Theme_JSON_Resolver::get_core_data();
    $coreSettings = $coreThemeData->get_settings();

    // Enable custom spacing
    $coreSettings['spacing']['units'] = ['px'];
    $coreSettings['spacing']['padding'] = true;
    // Typography
    $coreSettings['typography']['dropCap'] = false; // Showing large initial letter cannot be implemented in emails
    $coreSettings['typography']['fontWeight'] = false; // Font weight will be handled by the font family later

    $theme = $this->getTheme();

    // body selector is later transformed to .editor-styles-wrapper
    // setting padding for bottom and top is needed because \WP_Theme_JSON::get_stylesheet() set them only for .wp-site-blocks selector
    $contentVariables = 'body {';
    $contentVariables .= 'padding-bottom: var(--wp--style--root--padding-bottom);';
    $contentVariables .= 'padding-top: var(--wp--style--root--padding-top);';
    $contentVariables .= '--wp--style--block-gap:' . self::FLEX_GAP . ';';
    $contentVariables .= '}';

    $settings = array_merge($coreDefaultSettings, self::DEFAULT_SETTINGS);
    $settings['allowedBlockTypes'] = self::ALLOWED_BLOCK_TYPES;
    $flexEmailLayoutStyles = file_get_contents(__DIR__ . '/flex-email-layout.css');

    $settings['styles'] = [
      $coreDefaultSettings['defaultEditorStyles'][0],
      ['css' => wp_get_global_stylesheet(['base-layout-styles'])],
      ['css' => $theme->get_stylesheet()],
      ['css' => $contentVariables],
      ['css' => $flexEmailLayoutStyles],
    ];

    $settings['__experimentalFeatures'] = $coreSettings;
    // Enable border radius, color, style and width where possible
    $settings['__experimentalFeatures']['border'] = [
      "radius" => true,
      "color" => true,
      "style" => true,
      "width" => true,
    ];

    // Enabling alignWide allows full width for specific blocks such as columns, heading, image, etc.
    $settings['alignWide'] = true;

    return $settings;
  }

  /**
   * @return array{contentSize: string, layout: string}
   */
  public function getLayout(): array {
    return [
      'contentSize' => self::EMAIL_WIDTH,
      'layout' => 'constrained',
    ];
  }

  public function getEmailContentStyles(): array {
    return self::DEFAULT_EMAIL_CONTENT_STYLES;
  }

  public function getAvailableStylesheets(): string {
    if ($this->availableStylesheets) return $this->availableStylesheets;
    $coreThemeData = \WP_Theme_JSON_Resolver::get_core_data();
    $this->availableStylesheets = $coreThemeData->get_stylesheet();
    return $this->availableStylesheets;
  }

  /**
   * @return array{width: string, background: string, padding: array{bottom: string, left: string, right: string, top: string}}
   */
  public function getEmailLayoutStyles(): array {
    return [
      'width' => self::EMAIL_WIDTH,
      'background' => self::EMAIL_LAYOUT_BACKGROUND,
      'padding' => [
        'bottom' => self::EMAIL_PADDING,
        'left' => self::EMAIL_PADDING,
        'right' => self::EMAIL_PADDING,
        'top' => self::EMAIL_PADDING,
      ],
    ];
  }

  public function getLayoutWidthWithoutPadding(): string {
    $layoutStyles = $this->getEmailLayoutStyles();
    $width = $this->parseNumberFromStringWithPixels($layoutStyles['width']);
    $width -= $this->parseNumberFromStringWithPixels($layoutStyles['padding']['left']);
    $width -= $this->parseNumberFromStringWithPixels($layoutStyles['padding']['right']);
    return "{$width}px";
  }

  /**
   * This functions converts an array of styles to a string that can be used in HTML.
   */
  public function convertStylesToString(array $styles): string {
    $cssString = '';
    foreach ($styles as $property => $value) {
      $cssString .= $property . ':' . $value . ';';
    }
    return trim($cssString); // Remove trailing space and return the formatted string
  }

  public function parseStylesToArray(string $styles): array {
    $styles = explode(';', $styles);
    $parsedStyles = [];
    foreach ($styles as $style) {
      $style = explode(':', $style);
      if (count($style) === 2) {
        $parsedStyles[trim($style[0])] = trim($style[1]);
      }
    }
    return $parsedStyles;
  }

  public function parseNumberFromStringWithPixels(string $string): float {
    return (float)str_replace('px', '', $string);
  }

  public function getTheme(): \WP_Theme_JSON {
    $themeJson = (string)file_get_contents(dirname(__FILE__) . '/theme.json');
    $themeJson = json_decode($themeJson, true);
    /** @var array $themeJson */
    return new \WP_Theme_JSON($themeJson);
  }
}
