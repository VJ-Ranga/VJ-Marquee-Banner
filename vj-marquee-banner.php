<?php
/**
 * Plugin Name: VJ Marquee Banner
 * Description: Adds a scrolling announcement banner above the header.
 * Version: 1.2.1
 * Author: VJ Ranga
 * Author URI: https://vjranga.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vj-marquee-banner
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('VJ_MARQUEE_BANNER_VERSION')) {
    define('VJ_MARQUEE_BANNER_VERSION', '1.2.1');
}

final class VJ_Marquee_Banner {
    const OPTION_KEY = 'vj_marquee_banner_options';
    const LEGACY_OPTION_KEY = 'elessi_topbar_banner_options';

    private $rendered = false;

    public function __construct() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('nasa_before_header_structure', array($this, 'render'), 5);
        add_action('wp_body_open', array($this, 'render_fallback'), 5);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_shortcode('vj_marquee_banner', array($this, 'shortcode'));
    }

    private function defaults() {
        return array(
            'enabled' => 1,
            'content_type' => 'text',
            'text' => 'Free Shipping for orders above Rs 5000',
            'url' => '',
            'elementor_template' => '',
            'speed' => '40',
            'bg_color' => '#111111',
            'text_color' => '#ffffff',
            'height' => '36',
            'font_size' => '14',
            'gap' => '48',
            'font_weight' => '600',
            'letter_spacing' => '0.3',
            'text_transform' => 'none',
            'font_family' => '',
            'font_source' => 'inherit',
            'font_weights' => '400;600;700',
            'image_ids' => '',
            'image_height' => '36',
            'padding' => '0',
            'margin' => '0',
            'padding_top' => '0',
            'padding_right' => '0',
            'padding_bottom' => '0',
            'padding_left' => '0',
            'margin_top' => '0',
            'margin_right' => '0',
            'margin_bottom' => '0',
            'margin_left' => '0',
        );
    }

    private function get_options() {
        $options = get_option(self::OPTION_KEY, array());
        if (empty($options)) {
            $legacy = get_option(self::LEGACY_OPTION_KEY, array());
            if (!empty($legacy)) {
                $options = $legacy;
            }
        }
        return wp_parse_args($options, $this->defaults());
    }

    private function register_assets() {
        $handle = 'vj-marquee-banner';

        wp_register_style(
            $handle,
            plugins_url('assets/css/vj-marquee-banner.css', __FILE__),
            array(),
            VJ_MARQUEE_BANNER_VERSION
        );

        wp_register_script(
            $handle,
            plugins_url('assets/js/vj-marquee-banner.js', __FILE__),
            array(),
            VJ_MARQUEE_BANNER_VERSION,
            true
        );
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'vj-marquee-banner',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    private function build_inline_css($options, $scope) {
        if (!in_array($options['content_type'], array('text', 'images'), true)) {
            return '';
        }

        $bg_color = $this->sanitize_css_value($options['bg_color']);
        $text_color = $this->sanitize_css_value($options['text_color']);
        $speed = floatval($options['speed']);
        if ($speed <= 0) {
            $speed = 40;
        }
        if ($speed > 300) {
            $speed = 300;
        }

        $gap = floatval($options['gap']);
        if ($gap < 10) {
            $gap = 10;
        }
        if ($gap > 160) {
            $gap = 160;
        }

        $css = '';
        if ($bg_color) {
            $css .= $scope . '{background:' . $bg_color . ';}';
        }

        $css .= $scope . '{--vj-marquee-duration:' . $speed . 's;';
        $css .= '--vj-marquee-gap:' . $gap . 'px;';

        $padding_top = $this->sanitize_css_value(isset($options['padding_top']) ? $options['padding_top'] : '');
        $padding_right = $this->sanitize_css_value(isset($options['padding_right']) ? $options['padding_right'] : '');
        $padding_bottom = $this->sanitize_css_value(isset($options['padding_bottom']) ? $options['padding_bottom'] : '');
        $padding_left = $this->sanitize_css_value(isset($options['padding_left']) ? $options['padding_left'] : '');
        if ($padding_top !== '' || $padding_right !== '' || $padding_bottom !== '' || $padding_left !== '') {
            $css .= 'padding-top:' . ($padding_top !== '' ? $padding_top : '0') . ';';
            $css .= 'padding-right:' . ($padding_right !== '' ? $padding_right : '0') . ';';
            $css .= 'padding-bottom:' . ($padding_bottom !== '' ? $padding_bottom : '0') . ';';
            $css .= 'padding-left:' . ($padding_left !== '' ? $padding_left : '0') . ';';
        } else {
            $padding_legacy = $this->sanitize_css_value(isset($options['padding']) ? $options['padding'] : '');
            if ($padding_legacy !== '') {
                $css .= 'padding:' . $padding_legacy . ';';
            }
        }

        $margin_top = $this->sanitize_css_value(isset($options['margin_top']) ? $options['margin_top'] : '');
        $margin_right = $this->sanitize_css_value(isset($options['margin_right']) ? $options['margin_right'] : '');
        $margin_bottom = $this->sanitize_css_value(isset($options['margin_bottom']) ? $options['margin_bottom'] : '');
        $margin_left = $this->sanitize_css_value(isset($options['margin_left']) ? $options['margin_left'] : '');
        if ($margin_top !== '' || $margin_right !== '' || $margin_bottom !== '' || $margin_left !== '') {
            $css .= 'margin-top:' . ($margin_top !== '' ? $margin_top : '0') . ';';
            $css .= 'margin-right:' . ($margin_right !== '' ? $margin_right : '0') . ';';
            $css .= 'margin-bottom:' . ($margin_bottom !== '' ? $margin_bottom : '0') . ';';
            $css .= 'margin-left:' . ($margin_left !== '' ? $margin_left : '0') . ';';
        } else {
            $margin_legacy = $this->sanitize_css_value(isset($options['margin']) ? $options['margin'] : '');
            if ($margin_legacy !== '') {
                $css .= 'margin:' . $margin_legacy . ';';
            }
        }

        if ($options['content_type'] === 'text') {
            $height = floatval($options['height']);
            if ($height < 24) {
                $height = 24;
            }
            if ($height > 120) {
                $height = 120;
            }

            $font_size = floatval($options['font_size']);
            if ($font_size < 10) {
                $font_size = 10;
            }
            if ($font_size > 40) {
                $font_size = 40;
            }

            $font_weight = intval($options['font_weight']);
            if ($font_weight < 300) {
                $font_weight = 300;
            }
            if ($font_weight > 800) {
                $font_weight = 800;
            }

            $letter_spacing = floatval($options['letter_spacing']);
            if ($letter_spacing < -2) {
                $letter_spacing = -2;
            }
            if ($letter_spacing > 8) {
                $letter_spacing = 8;
            }

            $text_transform = in_array($options['text_transform'], array('none', 'uppercase', 'lowercase', 'capitalize'), true)
                ? $options['text_transform']
                : 'none';

            $font_family = sanitize_text_field($options['font_family']);
            $font_family = str_replace(array(';', '{', '}', '<', '>'), '', $font_family);

            if ($text_color) {
                $css .= '--vj-marquee-text-color:' . $text_color . ';';
            }

            $css .= '--vj-marquee-height:' . $height . 'px;';
            $css .= '--vj-marquee-font-size:' . $font_size . 'px;';
            $css .= '--vj-marquee-font-weight:' . $font_weight . ';';
            $css .= '--vj-marquee-letter-spacing:' . $letter_spacing . 'px;';
            $css .= '--vj-marquee-text-transform:' . $text_transform . ';';
            if ($font_family !== '') {
                $css .= '--vj-marquee-font-family:' . $font_family . ';';
            }
        }

        if ($options['content_type'] === 'images') {
            $image_height = floatval($options['image_height']);
            if ($image_height < 16) {
                $image_height = 16;
            }
            if ($image_height > 120) {
                $image_height = 120;
            }
            $css .= '--vj-marquee-image-height:' . $image_height . 'px;';
        }

        $css .= '}';

        if ($text_color && $options['content_type'] === 'text') {
            $css .= $scope . ',' . $scope . ' a{color:' . $text_color . ';}';
        }

        return $css;
    }

    public function admin_assets($hook) {
        if ($hook !== 'settings_page_vj-marquee-banner') {
            return;
        }

        wp_enqueue_media();
        $this->register_assets();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style(
            'vj-marquee-banner-admin',
            plugins_url('assets/css/vj-marquee-admin.css', __FILE__),
            array(),
            VJ_MARQUEE_BANNER_VERSION
        );
        wp_enqueue_script(
            'vj-marquee-banner-admin',
            plugins_url('assets/js/vj-marquee-admin.js', __FILE__),
            array('jquery', 'wp-color-picker'),
            VJ_MARQUEE_BANNER_VERSION,
            true
        );
        wp_enqueue_style('vj-marquee-banner');
        wp_enqueue_script('vj-marquee-banner');

        $options = $this->get_options();
        if (in_array($options['content_type'], array('text', 'images'), true)) {
            $this->maybe_enqueue_google_font($options);
            $custom_css = $this->build_inline_css($options, '.vj-marquee-preview-frame .vj-marquee-banner');
            if ($custom_css !== '') {
                wp_add_inline_style('vj-marquee-banner', $custom_css);
            }
        }
    }

    private function maybe_enqueue_google_font($options) {
        if (empty($options['font_family']) || $options['font_source'] !== 'google') {
            return;
        }

        $family = sanitize_text_field($options['font_family']);
        $family = trim($family);
        if ($family === '') {
            return;
        }

        $weights = isset($options['font_weights']) ? sanitize_text_field($options['font_weights']) : '';
        $weights = preg_replace('/[^0-9;]+/', ';', $weights);
        $weights = trim($weights, ';');
        if ($weights === '') {
            $weights = '400';
        }

        $family_param = str_replace(' ', '+', $family);
        $url = 'https://fonts.googleapis.com/css2?family=' . $family_param . ':wght@' . $weights . '&display=swap';
        wp_enqueue_style('vj-marquee-banner-font', $url, array(), null);
    }

    private function get_detected_fonts() {
        $fonts = array();
        $paths = array();

        $stylesheet = get_stylesheet_directory();
        $template = get_template_directory();

        $paths[] = $stylesheet . '/style.css';
        if ($template !== $stylesheet) {
            $paths[] = $template . '/style.css';
        }

        $css_dirs = array(
            $stylesheet . '/css',
            $stylesheet . '/assets/css',
            $template . '/css',
            $template . '/assets/css',
        );

        foreach ($css_dirs as $dir) {
            if (is_dir($dir)) {
                foreach (glob($dir . '/*.css') as $file) {
                    $paths[] = $file;
                }
            }
        }

        $fonts = $this->extract_fonts_from_paths($paths);

        $fonts = apply_filters('vj_marquee_banner_fonts', $fonts);
        return apply_filters('elessi_topbar_banner_fonts', $fonts);
    }

    private function extract_fonts_from_paths($paths) {
        $fonts = array();
        $seen = array();

        foreach ($paths as $path) {
            if (!is_file($path)) {
                continue;
            }

            $css = file_get_contents($path);
            if ($css === false) {
                continue;
            }

            $fonts = array_merge($fonts, $this->extract_fonts_from_css($css));
        }

        $unique = array();
        foreach ($fonts as $font) {
            $key = strtolower($font);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $font;
        }

        sort($unique);
        return $unique;
    }

    private function extract_fonts_from_css($css) {
        $fonts = array();

        if (preg_match_all('/@font-face[^}]*font-family\s*:\s*(["\"]?)([^;"\']+)\1/i', $css, $matches)) {
            foreach ($matches[2] as $font) {
                $name = $this->sanitize_font_name($font);
                if ($name !== '') {
                    $fonts[] = $name;
                }
            }
        }

        if (preg_match_all('/font-family\s*:\s*([^;}{]+);/i', $css, $matches)) {
            foreach ($matches[1] as $list) {
                foreach (explode(',', $list) as $font) {
                    $name = $this->sanitize_font_name($font);
                    if ($name !== '') {
                        $fonts[] = $name;
                    }
                }
            }
        }

        return $fonts;
    }

    private function sanitize_css_value($value) {
        $value = is_string($value) ? $value : '';
        $value = wp_strip_all_tags($value);
        $value = str_replace(array(';', '{', '}', '<', '>'), '', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        return trim($value);
    }

    private function sanitize_font_name($font) {
        $font = trim($font);
        $font = preg_replace('/!important/i', '', $font);
        $font = trim($font, " \t\n\r\0\x0B\"'");

        if ($font === '') {
            return '';
        }

        $generic = array(
            'inherit',
            'initial',
            'unset',
            'serif',
            'sans-serif',
            'monospace',
            'cursive',
            'fantasy',
            'system-ui',
        );

        if (in_array(strtolower($font), $generic, true)) {
            return '';
        }

        if (strpos($font, 'var(') === 0) {
            return '';
        }

        return $font;
    }

    public function enqueue_assets() {
        $this->register_assets();
        if (is_admin()) {
            return;
        }

        $handle = 'vj-marquee-banner';

        $options = $this->get_options();
        $has_banner = !empty($options['enabled']);

        if (!$has_banner) {
            return;
        }

        wp_enqueue_style($handle);

        if ($has_banner && in_array($options['content_type'], array('text', 'images'), true)) {
            wp_enqueue_script($handle);
            $this->maybe_enqueue_google_font($options);
            $custom_css = $this->build_inline_css($options, '.vj-marquee-banner');
            if ($custom_css !== '') {
                wp_add_inline_style($handle, $custom_css);
            }
        }
    }

    public function render() {
        if (is_admin() || $this->rendered) {
            return;
        }

        $html = $this->get_banner_html();
        if ($html === '') {
            return;
        }

        echo $html;
        $this->rendered = true;
    }

    public function render_fallback() {
        if ($this->is_legacy_theme()) {
            return;
        }

        $this->render();
    }

    public function shortcode() {
        return $this->get_banner_html();
    }

    private function get_banner_html() {
        $options = $this->get_options();
        if (empty($options['enabled'])) {
            return '';
        }

        $content_type = in_array($options['content_type'], array('text', 'images', 'elementor'), true)
            ? $options['content_type']
            : 'text';

        if ($content_type === 'elementor') {
            $template_id = absint($options['elementor_template']);
            $content = $this->render_elementor_template($template_id);
            if (!$content) {
                return '';
            }

            return '<div class="vj-marquee-banner vj-marquee-banner--elementor" role="region" aria-label="' . esc_attr__('Site announcement', 'vj-marquee-banner') . '">'
                . '<div class="vj-marquee-banner__inner">'
                . '<div class="vj-marquee-banner__elementor">' . $content . '</div>'
                . '</div>'
                . '</div>';
        }

        if ($content_type === 'images') {
            $ids = $this->get_image_ids($options['image_ids']);
            if (empty($ids)) {
                return '';
            }

            $items = $this->render_image_items($ids);
            if ($items === '') {
                return '';
            }

            return '<div class="vj-marquee-banner vj-marquee-banner--images" role="region" aria-label="' . esc_attr__('Site announcement', 'vj-marquee-banner') . '">'
                . '<div class="vj-marquee-banner__inner">'
                . '<div class="vj-marquee-banner__marquee vj-marquee-marquee" aria-hidden="true">'
                . '<div class="vj-marquee-banner__track">'
                . '<div class="vj-marquee-banner__group">' . $items . '</div>'
                . '<div class="vj-marquee-banner__group" aria-hidden="true">' . $items . '</div>'
                . '</div>'
                . '</div>'
                . '</div>'
                . '</div>';
        }

        $text = wp_kses_post($options['text']);
        $texts = $this->get_text_items($text);
        if (empty($texts)) {
            return '';
        }
        $url = esc_url($options['url']);

        return '<div class="vj-marquee-banner vj-marquee-banner--text" role="region" aria-label="' . esc_attr__('Site announcement', 'vj-marquee-banner') . '">'
            . '<div class="vj-marquee-banner__inner">'
            . '<div class="vj-marquee-banner__marquee vj-marquee-marquee" aria-hidden="true">'
            . '<div class="vj-marquee-banner__track">'
            . '<div class="vj-marquee-banner__group">' . $this->render_repeated_items($texts, $url) . '</div>'
            . '<div class="vj-marquee-banner__group" aria-hidden="true">' . $this->render_repeated_items($texts, $url) . '</div>'
            . '</div>'
            . '</div>'
            . '<div class="vj-marquee-banner__sr">' . $this->render_text_only($texts, $url) . '</div>'
            . '</div>'
            . '</div>';
    }

    private function get_text_items($text) {
        if ($text === '') {
            return array();
        }

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $items = array();
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $items[] = $line;
            }
        }

        return $items;
    }

    private function get_repeat_count($count) {
        if ($count <= 1) {
            return 6;
        }
        if ($count <= 2) {
            return 4;
        }
        if ($count <= 3) {
            return 3;
        }
        if ($count <= 6) {
            return 2;
        }

        return 1;
    }

    private function get_image_ids($ids) {
        if ($ids === '') {
            return array();
        }

        $raw = array_map('trim', explode(',', $ids));
        $clean = array();
        foreach ($raw as $id) {
            $id = absint($id);
            if ($id > 0) {
                $clean[] = $id;
            }
        }

        return $clean;
    }

    private function render_repeated_items($texts, $url) {
        $repeat = $this->get_repeat_count(count($texts));
        $items = '';
        for ($i = 0; $i < $repeat; $i++) {
            foreach ($texts as $text) {
                $items .= '<span class="vj-marquee-banner__item">';
                if (!empty($url)) {
                    $items .= '<a href="' . $url . '">' . $text . '</a>';
                } else {
                    $items .= $text;
                }
                $items .= '</span>';
            }
        }

        return $items;
    }

    private function render_image_items($ids) {
        $repeat = $this->get_repeat_count(count($ids));
        $items = '';
        for ($i = 0; $i < $repeat; $i++) {
            foreach ($ids as $id) {
                $image = wp_get_attachment_image($id, 'full', false, array('class' => 'vj-marquee-banner__image'));
                if (!$image) {
                    continue;
                }
                $items .= '<span class="vj-marquee-banner__item">' . $image . '</span>';
            }
        }

        return $items;
    }

    private function render_text_only($texts, $url) {
        $plain = array();
        foreach ($texts as $text) {
            $plain[] = wp_strip_all_tags($text);
        }
        $label = implode(' | ', $plain);

        if (!empty($url)) {
            return '<a href="' . $url . '">' . esc_html($label) . '</a>';
        }

        return esc_html($label);
    }

    public function register_menu() {
        add_options_page(
            'VJ Marquee Banner',
            'VJ Marquee Banner',
            'manage_options',
            'vj-marquee-banner',
            array($this, 'settings_page')
        );
    }

    public function register_settings() {
        register_setting(
            'vj_marquee_banner_group',
            self::OPTION_KEY,
            array($this, 'sanitize_options')
        );

        add_settings_section(
            'vj_marquee_banner_general',
            'Core Configuration',
            '__return_false',
            'vj-marquee-banner',
            array(
                'before_section' => '<div class="vj-marquee-card %s" data-tab="general">',
                'after_section' => '</div>',
                'section_class' => 'vj-marquee-section vj-marquee-section--general',
            )
        );

        add_settings_section(
            'vj_marquee_banner_text',
            'Text Content',
            '__return_false',
            'vj-marquee-banner',
            array(
                'before_section' => '<div class="vj-marquee-card %s" data-tab="content">',
                'after_section' => '</div>',
                'section_class' => 'vj-marquee-section vj-marquee-section--text',
            )
        );

        add_settings_section(
            'vj_marquee_banner_images',
            'Image Content',
            '__return_false',
            'vj-marquee-banner',
            array(
                'before_section' => '<div class="vj-marquee-card %s" data-tab="content">',
                'after_section' => '</div>',
                'section_class' => 'vj-marquee-section vj-marquee-section--images',
            )
        );

        add_settings_section(
            'vj_marquee_banner_animation',
            'Animation',
            '__return_false',
            'vj-marquee-banner',
            array(
                'before_section' => '<div class="vj-marquee-card %s" data-tab="animation">',
                'after_section' => '</div>',
                'section_class' => 'vj-marquee-section vj-marquee-section--media',
            )
        );

        add_settings_section(
            'vj_marquee_banner_style',
            'Appearance',
            '__return_false',
            'vj-marquee-banner',
            array(
                'before_section' => '<div class="vj-marquee-card %s" data-tab="appearance">',
                'after_section' => '</div>',
                'section_class' => 'vj-marquee-section vj-marquee-section--media',
            )
        );

        add_settings_section(
            'vj_marquee_banner_elementor',
            'Elementor Block',
            '__return_false',
            'vj-marquee-banner',
            array(
                'before_section' => '<div class="vj-marquee-card %s" data-tab="content">',
                'after_section' => '</div>',
                'section_class' => 'vj-marquee-section vj-marquee-section--elementor',
            )
        );

        add_settings_field(
            'enabled',
            'Enable Banner',
            array($this, 'field_enabled'),
            'vj-marquee-banner',
            'vj_marquee_banner_general',
            array('class' => 'vj-marquee-field vj-marquee-field--shared')
        );

        add_settings_field(
            'content_type',
            'Content Source',
            array($this, 'field_content_type'),
            'vj-marquee-banner',
            'vj_marquee_banner_general',
            array('class' => 'vj-marquee-field vj-marquee-field--shared')
        );

        add_settings_field(
            'text',
            'Messages (one per line)',
            array($this, 'field_text'),
            'vj-marquee-banner',
            'vj_marquee_banner_text',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only')
        );

        add_settings_field(
            'url',
            'Target Link (optional)',
            array($this, 'field_url'),
            'vj-marquee-banner',
            'vj_marquee_banner_text',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only')
        );

        add_settings_field(
            'image_ids',
            'Images',
            array($this, 'field_image_gallery'),
            'vj-marquee-banner',
            'vj_marquee_banner_images',
            array('class' => 'vj-marquee-field vj-marquee-field--images')
        );

        add_settings_field(
            'elementor_template',
            'Elementor Template',
            array($this, 'field_elementor_template'),
            'vj-marquee-banner',
            'vj_marquee_banner_elementor',
            array('class' => 'vj-marquee-field vj-marquee-field--elementor')
        );

        add_settings_field(
            'speed',
            'Scroll Speed (seconds)',
            array($this, 'field_speed'),
            'vj-marquee-banner',
            'vj_marquee_banner_animation',
            array('class' => 'vj-marquee-field vj-marquee-field--media')
        );

        add_settings_field(
            'bg_color',
            'Background Color',
            array($this, 'field_bg_color'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--media')
        );

        add_settings_field(
            'text_color',
            'Text Color',
            array($this, 'field_text_color'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only')
        );

        add_settings_field(
            'height',
            'Banner Height (px)',
            array($this, 'field_height'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only')
        );

        add_settings_field(
            'font_size',
            'Font Size (px)',
            array($this, 'field_font_size'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only')
        );

        add_settings_field(
            'gap',
            'Item Gap (px)',
            array($this, 'field_gap'),
            'vj-marquee-banner',
            'vj_marquee_banner_animation',
            array('class' => 'vj-marquee-field vj-marquee-field--media')
        );

        add_settings_field(
            'font_weight',
            'Font Weight',
            array($this, 'field_font_weight'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only')
        );

        add_settings_field(
            'letter_spacing',
            'Letter Spacing (px)',
            array($this, 'field_letter_spacing'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only')
        );

        add_settings_field(
            'text_transform',
            'Text Transform',
            array($this, 'field_text_transform'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only')
        );

        add_settings_field(
            'font_family',
            'Font Family',
            array($this, 'field_font_family'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only')
        );

        add_settings_field(
            'font_source',
            'Font Source',
            array($this, 'field_font_source'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only')
        );

        add_settings_field(
            'font_weights',
            'Font Weights',
            array($this, 'field_font_weights'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--text-only vj-marquee-field--font-weights')
        );

        add_settings_field(
            'image_height',
            'Image Height (px)',
            array($this, 'field_image_height'),
            'vj-marquee-banner',
            'vj_marquee_banner_style',
            array('class' => 'vj-marquee-field vj-marquee-field--images')
        );

        add_settings_field('padding_box', 'Padding (TRBL)', array($this, 'field_padding_box'), 'vj-marquee-banner', 'vj_marquee_banner_style', array('class' => 'vj-marquee-field vj-marquee-field--media'));
        add_settings_field('margin_box', 'Margin (TRBL)', array($this, 'field_margin_box'), 'vj-marquee-banner', 'vj_marquee_banner_style', array('class' => 'vj-marquee-field vj-marquee-field--media'));
    }

    public function sanitize_options($input) {
        $defaults = $this->defaults();
        $output = array();

        $output['enabled'] = isset($input['enabled']) ? 1 : 0;
        $content_type = isset($input['content_type']) ? sanitize_text_field($input['content_type']) : $defaults['content_type'];
        $output['content_type'] = in_array($content_type, array('text', 'images', 'elementor'), true) ? $content_type : $defaults['content_type'];
        $output['text'] = isset($input['text']) && $input['text'] !== ''
            ? wp_kses_post($input['text'])
            : $defaults['text'];
        $output['url'] = isset($input['url']) ? esc_url_raw($input['url']) : '';

        $template_id = isset($input['elementor_template']) ? absint($input['elementor_template']) : 0;
        $allowed_template_types = array('elementor-hf', 'elementor_library');
        if ($template_id > 0 && !in_array(get_post_type($template_id), $allowed_template_types, true)) {
            $template_id = 0;
        }
        if ($template_id > 0 && $this->is_elementor_kit($template_id)) {
            $template_id = 0;
        }
        $output['elementor_template'] = $template_id ? (string) $template_id : '';

        $speed = isset($input['speed']) ? floatval($input['speed']) : floatval($defaults['speed']);
        if ($speed < 6) {
            $speed = 6;
        }
        if ($speed > 300) {
            $speed = 300;
        }
        $output['speed'] = (string) $speed;

        $output['bg_color'] = isset($input['bg_color']) ? $this->sanitize_css_value($input['bg_color']) : $defaults['bg_color'];
        $output['text_color'] = isset($input['text_color']) ? $this->sanitize_css_value($input['text_color']) : $defaults['text_color'];

        if (!$output['bg_color']) {
            $output['bg_color'] = $defaults['bg_color'];
        }
        if (!$output['text_color']) {
            $output['text_color'] = $defaults['text_color'];
        }

        $height = isset($input['height']) ? floatval($input['height']) : floatval($defaults['height']);
        if ($height < 24) {
            $height = 24;
        }
        if ($height > 120) {
            $height = 120;
        }
        $output['height'] = (string) $height;

        $font_size = isset($input['font_size']) ? floatval($input['font_size']) : floatval($defaults['font_size']);
        if ($font_size < 10) {
            $font_size = 10;
        }
        if ($font_size > 40) {
            $font_size = 40;
        }
        $output['font_size'] = (string) $font_size;

        $gap = isset($input['gap']) ? floatval($input['gap']) : floatval($defaults['gap']);
        if ($gap < 10) {
            $gap = 10;
        }
        if ($gap > 160) {
            $gap = 160;
        }
        $output['gap'] = (string) $gap;

        $font_weight = isset($input['font_weight']) ? intval($input['font_weight']) : intval($defaults['font_weight']);
        if ($font_weight < 300) {
            $font_weight = 300;
        }
        if ($font_weight > 800) {
            $font_weight = 800;
        }
        $output['font_weight'] = (string) $font_weight;

        $letter_spacing = isset($input['letter_spacing']) ? floatval($input['letter_spacing']) : floatval($defaults['letter_spacing']);
        if ($letter_spacing < -2) {
            $letter_spacing = -2;
        }
        if ($letter_spacing > 8) {
            $letter_spacing = 8;
        }
        $output['letter_spacing'] = (string) $letter_spacing;

        $text_transform = isset($input['text_transform']) ? sanitize_text_field($input['text_transform']) : $defaults['text_transform'];
        $output['text_transform'] = in_array($text_transform, array('none', 'uppercase', 'lowercase', 'capitalize'), true)
            ? $text_transform
            : $defaults['text_transform'];

        $font_family = isset($input['font_family']) ? sanitize_text_field($input['font_family']) : '';
        $font_family = str_replace(array(';', '{', '}', '<', '>'), '', $font_family);
        $output['font_family'] = $font_family;

        $font_source = isset($input['font_source']) ? sanitize_text_field($input['font_source']) : $defaults['font_source'];
        $output['font_source'] = in_array($font_source, array('inherit', 'google'), true) ? $font_source : $defaults['font_source'];

        $font_weights = isset($input['font_weights']) ? sanitize_text_field($input['font_weights']) : $defaults['font_weights'];
        $font_weights = preg_replace('/[^0-9; ]+/', '', $font_weights);
        $font_weights = preg_replace('/\s+/', '', $font_weights);
        $font_weights = trim($font_weights, ';');
        if ($font_weights === '') {
            $font_weights = $defaults['font_weights'];
        }
        $output['font_weights'] = $font_weights;

        $image_ids = isset($input['image_ids']) ? sanitize_text_field($input['image_ids']) : '';
        $image_ids = preg_replace('/[^0-9,]+/', '', $image_ids);
        $image_ids = trim($image_ids, ',');
        $output['image_ids'] = $image_ids;

        $image_height = isset($input['image_height']) ? floatval($input['image_height']) : floatval($defaults['image_height']);
        if ($image_height < 16) {
            $image_height = 16;
        }
        if ($image_height > 120) {
            $image_height = 120;
        }
        $output['image_height'] = (string) $image_height;

        $output['padding_top'] = isset($input['padding_top']) ? $this->sanitize_css_value($input['padding_top']) : $defaults['padding_top'];
        $output['padding_right'] = isset($input['padding_right']) ? $this->sanitize_css_value($input['padding_right']) : $defaults['padding_right'];
        $output['padding_bottom'] = isset($input['padding_bottom']) ? $this->sanitize_css_value($input['padding_bottom']) : $defaults['padding_bottom'];
        $output['padding_left'] = isset($input['padding_left']) ? $this->sanitize_css_value($input['padding_left']) : $defaults['padding_left'];

        $output['margin_top'] = isset($input['margin_top']) ? $this->sanitize_css_value($input['margin_top']) : $defaults['margin_top'];
        $output['margin_right'] = isset($input['margin_right']) ? $this->sanitize_css_value($input['margin_right']) : $defaults['margin_right'];
        $output['margin_bottom'] = isset($input['margin_bottom']) ? $this->sanitize_css_value($input['margin_bottom']) : $defaults['margin_bottom'];
        $output['margin_left'] = isset($input['margin_left']) ? $this->sanitize_css_value($input['margin_left']) : $defaults['margin_left'];

        $padding = isset($input['padding']) ? $this->sanitize_css_value($input['padding']) : '';
        $output['padding'] = $padding !== '' ? $padding : (isset($defaults['padding']) ? $defaults['padding'] : '0');
        $margin = isset($input['margin']) ? $this->sanitize_css_value($input['margin']) : '';
        $output['margin'] = $margin !== '' ? $margin : (isset($defaults['margin']) ? $defaults['margin'] : '0');

        return $output;
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="wrap vj-marquee-settings">';
        echo '<form method="post" action="options.php">';
        settings_fields('vj_marquee_banner_group');

        echo '<div class="vj-marquee-header">';
        echo '<div class="vj-marquee-header-main">';
        echo '<div class="vj-marquee-title">VJ Marquee Banner</div>';
        echo '<div class="vj-marquee-subtitle">Configure your site announcements and promotional tickers.</div>';
        echo '</div>';
        echo '<button type="submit" class="button button-primary vj-marquee-save">Save Changes</button>';
        echo '</div>';

        echo '<div class="vj-marquee-preview">';
        echo '<div class="vj-marquee-preview-head">';
        echo '<span class="vj-marquee-preview-label">Live Preview</span>';
        echo '<span class="vj-marquee-preview-dot"></span>';
        echo '<span class="vj-marquee-preview-meta">1920 x 50 (Desktop)</span>';
        echo '</div>';
        $preview = $this->get_banner_html();
        if ($preview) {
            echo '<div class="vj-marquee-preview-frame">' . $preview . '</div>';
            echo '<p class="description">Save to refresh the preview.</p>';
        } else {
            echo '<div class="vj-marquee-preview-frame vj-marquee-preview-empty">Enable the banner and save to see a preview.</div>';
        }
        echo '</div>';

        echo '<div class="vj-marquee-layout">';
        echo '<aside class="vj-marquee-sidebar">';
        echo '<div class="vj-marquee-nav">';
        echo '<button type="button" class="vj-marquee-nav-item is-active" data-tab="general">General</button>';
        echo '<button type="button" class="vj-marquee-nav-item" data-tab="content">Content</button>';
        echo '<button type="button" class="vj-marquee-nav-item" data-tab="animation">Animation</button>';
        echo '<button type="button" class="vj-marquee-nav-item" data-tab="appearance">Appearance</button>';
        echo '</div>';
        echo '<div class="vj-marquee-card vj-marquee-shortcode">';
        echo '<h3>Shortcode</h3>';
        echo '<code>[vj_marquee_banner]</code>';
        echo '<p class="description">Use this shortcode in any content area or builder.</p>';
        echo '</div>';
        echo '</aside>';

        echo '<main class="vj-marquee-main">';
        do_settings_sections('vj-marquee-banner');
        echo '<div class="vj-marquee-footer">';
        echo '<button type="submit" class="button button-primary">Save Changes</button>';
        echo '<div class="vj-marquee-credits">Designed by <a href="https://vjranga.com" target="_blank" rel="noopener">vjranga.com</a></div>';
        echo '</div>';
        echo '</main>';
        echo '</div>';

        echo '</form>';
        echo '</div>';
    }

    public function field_enabled() {
        $options = $this->get_options();
        $checked = !empty($options['enabled']) ? 'checked' : '';
        echo '<label><input type="checkbox" name="' . self::OPTION_KEY . '[enabled]" value="1" ' . $checked . '> Show marquee banner</label>';
        echo '<p class="description">Enable or disable the marquee globally.</p>';
    }

    public function field_content_type() {
        $options = $this->get_options();
        $value = $options['content_type'];
        echo '<select id="vj-marquee-content-type" name="' . self::OPTION_KEY . '[content_type]">';
        echo '<option value="text" ' . selected($value, 'text', false) . '>Dynamic Text Marquee</option>';
        echo '<option value="images" ' . selected($value, 'images', false) . '>Image Marquee</option>';
        echo '<option value="elementor" ' . selected($value, 'elementor', false) . '>Elementor Template</option>';
        echo '</select>';
        echo '<p class="description">Choose a content source for the banner.</p>';
    }

    public function field_shortcode() {
        echo '<code>[vj_marquee_banner]</code>';
        echo '<p class="description">Use this shortcode in any content area or builder.</p>';
    }

    public function field_text() {
        $options = $this->get_options();
        $value = esc_textarea($options['text']);
        echo '<textarea name="' . self::OPTION_KEY . '[text]" rows="3" class="large-text">' . $value . '</textarea>';
        echo '<p class="description">Add one message per line.</p>';
    }

    public function field_image_gallery() {
        $options = $this->get_options();
        $ids = $this->get_image_ids($options['image_ids']);
        $value = esc_attr(implode(',', $ids));

        echo '<input type="hidden" id="vj-marquee-image-ids" name="' . self::OPTION_KEY . '[image_ids]" value="' . $value . '">';
        echo '<div class="vj-marquee-image-controls">';
        echo '<button type="button" class="button" id="vj-marquee-image-select">Select Images</button> ';
        echo '<button type="button" class="button" id="vj-marquee-image-clear">Clear</button>';
        echo '</div>';
        echo '<div id="vj-marquee-image-preview" class="vj-marquee-image-preview">';
        foreach ($ids as $id) {
            $thumb = wp_get_attachment_image($id, array(80, 80), false, array('class' => 'vj-marquee-thumb'));
            if ($thumb) {
                echo '<span class="vj-marquee-thumb-wrap" data-id="' . esc_attr($id) . '">' . $thumb . '</span>';
            }
        }
        echo '</div>';
        echo '<p class="description">Choose one or more images for the marquee.</p>';
    }

    public function field_url() {
        $options = $this->get_options();
        $value = esc_attr($options['url']);
        echo '<input type="url" name="' . self::OPTION_KEY . '[url]" value="' . $value . '" class="regular-text" placeholder="https://">';
    }

    public function field_elementor_template() {
        if (!$this->get_elementor_template_post_types()) {
            echo '<p class="description">Elementor templates are not available.</p>';
            return;
        }

        $options = $this->get_options();
        $value = absint($options['elementor_template']);
        $templates = $this->get_elementor_templates();

        echo '<select name="' . self::OPTION_KEY . '[elementor_template]">';
        echo '<option value="0">Select a template</option>';
        foreach ($templates as $template) {
            $label = $template['title'];
            if ($template['type']) {
                $label .= ' (' . $template['type'] . ')';
            }
            echo '<option value="' . esc_attr($template['id']) . '" ' . selected($value, $template['id'], false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">Uses Elementor templates (library or header/footer).</p>';
    }

    public function field_speed() {
        $options = $this->get_options();
        $value = esc_attr($options['speed']);
        echo '<input type="number" name="' . self::OPTION_KEY . '[speed]" value="' . $value . '" class="small-text" min="6" max="300" step="1">';
        echo '<p class="description">Lower is faster. Default is 40 seconds.</p>';
    }

    public function field_bg_color() {
        $options = $this->get_options();
        $this->render_color_field('bg_color', $options['bg_color'], '#111111');
    }

    public function field_text_color() {
        $options = $this->get_options();
        $this->render_color_field('text_color', $options['text_color'], '#ffffff');
    }

    private function render_color_field($key, $value, $placeholder) {
        $id = 'vj-marquee-' . $key;
        echo '<div class="vj-marquee-color-field">';
        echo '<input type="text" id="' . esc_attr($id) . '" name="' . self::OPTION_KEY . '[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" class="regular-text vj-marquee-color-text" placeholder="' . esc_attr($placeholder) . '">';
        echo '<input type="text" class="vj-marquee-color-picker" data-target="#' . esc_attr($id) . '" value="' . esc_attr($value) . '">';
        echo '</div>';
        echo '<p class="description">Pick from palette or type hex, rgb/rgba, hsl, or CSS variable.</p>';
    }

    public function field_height() {
        $options = $this->get_options();
        $value = esc_attr($options['height']);
        echo '<input type="number" name="' . self::OPTION_KEY . '[height]" value="' . $value . '" class="small-text" min="24" max="120" step="1">';
    }

    public function field_font_size() {
        $options = $this->get_options();
        $value = esc_attr($options['font_size']);
        echo '<input type="number" name="' . self::OPTION_KEY . '[font_size]" value="' . $value . '" class="small-text" min="10" max="40" step="1">';
    }

    public function field_gap() {
        $options = $this->get_options();
        $value = esc_attr($options['gap']);
        echo '<input type="number" name="' . self::OPTION_KEY . '[gap]" value="' . $value . '" class="small-text" min="10" max="160" step="1">';
        echo '<p class="description">Space between repeated items.</p>';
    }

    public function field_font_weight() {
        $options = $this->get_options();
        $value = esc_attr($options['font_weight']);
        echo '<select name="' . self::OPTION_KEY . '[font_weight]">';
        foreach (array('300', '400', '500', '600', '700', '800') as $weight) {
            echo '<option value="' . $weight . '" ' . selected($value, $weight, false) . '>' . $weight . '</option>';
        }
        echo '</select>';
    }

    public function field_letter_spacing() {
        $options = $this->get_options();
        $value = esc_attr($options['letter_spacing']);
        echo '<input type="number" name="' . self::OPTION_KEY . '[letter_spacing]" value="' . $value . '" class="small-text" min="-2" max="8" step="0.1">';
    }

    public function field_text_transform() {
        $options = $this->get_options();
        $value = $options['text_transform'];
        $choices = array(
            'none' => 'None',
            'uppercase' => 'Uppercase',
            'lowercase' => 'Lowercase',
            'capitalize' => 'Capitalize',
        );
        echo '<select name="' . self::OPTION_KEY . '[text_transform]">';
        foreach ($choices as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function field_image_height() {
        $options = $this->get_options();
        $value = esc_attr($options['image_height']);
        echo '<input type="number" name="' . self::OPTION_KEY . '[image_height]" value="' . $value . '" class="small-text" min="16" max="120" step="1">';
    }

    public function field_padding_box() {
        $options = $this->get_options();
        echo '<div class="vj-marquee-boxmodel">';
        echo '<div class="vj-marquee-boxmodel-grid">';
        $this->render_box_input('Top', 'padding_top', $options['padding_top'], '0 or 8px');
        $this->render_box_input('Right', 'padding_right', $options['padding_right'], '0 or 16px');
        $this->render_box_input('Bottom', 'padding_bottom', $options['padding_bottom'], '0 or 8px');
        $this->render_box_input('Left', 'padding_left', $options['padding_left'], '0 or 16px');
        echo '</div>';
        echo '<p class="description">Use any valid CSS unit (px, rem, em, %, vh, vw).</p>';
        echo '</div>';
    }

    public function field_margin_box() {
        $options = $this->get_options();
        echo '<div class="vj-marquee-boxmodel">';
        echo '<div class="vj-marquee-boxmodel-grid">';
        $this->render_box_input('Top', 'margin_top', $options['margin_top'], '0 or 12px');
        $this->render_box_input('Right', 'margin_right', $options['margin_right'], '0 or auto');
        $this->render_box_input('Bottom', 'margin_bottom', $options['margin_bottom'], '0 or 12px');
        $this->render_box_input('Left', 'margin_left', $options['margin_left'], '0 or auto');
        echo '</div>';
        echo '<p class="description">Applies outside the banner (top, right, bottom, left).</p>';
        echo '</div>';
    }

    private function render_box_input($label, $key, $value, $placeholder) {
        echo '<label class="vj-marquee-boxmodel-item">';
        echo '<span>' . esc_html($label) . '</span>';
        echo '<input type="text" name="' . self::OPTION_KEY . '[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" class="regular-text" placeholder="' . esc_attr($placeholder) . '">';
        echo '</label>';
    }

    public function field_font_family() {
        $options = $this->get_options();
        $current = trim($options['font_family']);
        $fonts = $this->get_detected_fonts();
        $is_custom = $current !== '' && !in_array($current, $fonts, true);

        echo '<select id="vj-marquee-font-family-select" class="regular-text">';
        echo '<option value="">Theme Default</option>';
        foreach ($fonts as $font) {
            echo '<option value="' . esc_attr($font) . '" ' . selected($current, $font, false) . '>' . esc_html($font) . '</option>';
        }
        echo '<option value="__custom__" ' . selected($is_custom, true, false) . '>Custom…</option>';
        echo '</select>';

        $style = $is_custom ? '' : ' style="display:none;"';
        $value = esc_attr($current);
        echo '<input type="text" id="vj-marquee-font-family-custom" name="' . self::OPTION_KEY . '[font_family]" value="' . $value . '" class="regular-text" placeholder="Type a custom font"' . $style . '>';
        echo '<p class="description">Select a detected font or choose Custom to type your own.</p>';
    }

    public function field_font_source() {
        $options = $this->get_options();
        $value = $options['font_source'];
        echo '<select id="vj-marquee-font-source" name="' . self::OPTION_KEY . '[font_source]">';
        echo '<option value="inherit" ' . selected($value, 'inherit', false) . '>Inherit from theme</option>';
        echo '<option value="google" ' . selected($value, 'google', false) . '>Google Font (load)</option>';
        echo '</select>';
        echo '<p class="description">Use Google Font only if you need a custom typeface.</p>';
    }

    public function field_font_weights() {
        $options = $this->get_options();
        $value = esc_attr($options['font_weights']);
        echo '<input type="text" name="' . self::OPTION_KEY . '[font_weights]" value="' . $value . '" class="regular-text" placeholder="400;600;700">';
        echo '<p class="description">Only used for Google Fonts.</p>';
    }

    private function is_legacy_theme() {
        $template = get_template();
        $stylesheet = get_stylesheet();

        if (in_array($template, array('elessi-theme', 'elessi-theme-child'), true)) {
            return true;
        }

        if (in_array($stylesheet, array('elessi-theme', 'elessi-theme-child'), true)) {
            return true;
        }

        return (strpos($template, 'elessi') !== false) || (strpos($stylesheet, 'elessi') !== false);
    }

    private function get_elementor_templates() {
        $post_types = $this->get_elementor_template_post_types();
        if (empty($post_types)) {
            return array();
        }

        $posts = get_posts(array(
            'post_type' => $post_types,
            'post_status' => array('publish', 'draft', 'private'),
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $templates = array();
        foreach ($posts as $post) {
            $type = get_post_meta($post->ID, 'ehf_template_type', true);
            if (!$type) {
                $type = get_post_meta($post->ID, '_elementor_template_type', true);
            }
            if ($this->is_elementor_kit($post->ID, $type)) {
                continue;
            }
            $templates[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $this->format_template_type($type),
            );
        }

        return $templates;
    }

    private function format_template_type($type) {
        if (!$type) {
            return '';
        }

        $map = array(
            'header' => 'Header',
            'footer' => 'Footer',
            'type_header' => 'Header',
            'type_footer' => 'Footer',
            'type_before_footer' => 'Before Footer',
            'custom' => 'Custom Block',
        );

        return isset($map[$type]) ? $map[$type] : ucfirst(str_replace('type_', '', $type));
    }

    private function is_elementor_kit($template_id, $type = '') {
        $normalized = strtolower(trim((string) $type));
        if ($normalized === 'kit') {
            return true;
        }

        $meta_type = get_post_meta($template_id, '_elementor_template_type', true);
        if (strtolower((string) $meta_type) === 'kit') {
            return true;
        }

        $title = get_the_title($template_id);
        return strtolower(trim((string) $title)) === 'default kit';
    }

    private function get_elementor_template_post_types() {
        $post_types = array();
        if (post_type_exists('elementor_library')) {
            $post_types[] = 'elementor_library';
        }
        if (post_type_exists('elementor-hf')) {
            $post_types[] = 'elementor-hf';
        }

        return $post_types;
    }

    private function render_elementor_template($template_id) {
        if (!$template_id || !class_exists('Elementor\\Plugin')) {
            return '';
        }

        $content = Elementor\Plugin::instance()->frontend->get_builder_content_for_display($template_id);
        if (!$content) {
            return '';
        }

        return $content;
    }
}

new VJ_Marquee_Banner();
