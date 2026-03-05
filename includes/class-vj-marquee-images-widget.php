<?php

if (!class_exists('Elementor\\Widget_Base')) {
    return;
}

if (class_exists('VJ_Marquee_Images_Widget')) {
    return;
}

class VJ_Marquee_Images_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'vj_marquee_images';
    }

    public function get_title() {
        return 'VJ Marquee Images';
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return array('general');
    }

    public function get_style_depends() {
        return array('vj-marquee-banner');
    }

    public function get_script_depends() {
        return array('vj-marquee-banner');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => 'Content',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'images',
            array(
                'label' => 'Images',
                'type' => \Elementor\Controls_Manager::GALLERY,
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'animation_section',
            array(
                'label' => 'Animation',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'speed',
            array(
                'label' => 'Scroll Speed (seconds)',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 40,
                'min' => 6,
                'max' => 300,
                'step' => 1,
                'selectors' => array(
                    '{{WRAPPER}} .vj-marquee-banner' => '--vj-marquee-duration: {{VALUE}}s;',
                ),
            )
        );

        $this->add_control(
            'gap',
            array(
                'label' => 'Item Gap (px)',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 10,
                        'max' => 160,
                    ),
                ),
                'default' => array(
                    'size' => 48,
                    'unit' => 'px',
                ),
                'selectors' => array(
                    '{{WRAPPER}} .vj-marquee-banner' => '--vj-marquee-gap: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            array(
                'label' => 'Style',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'bg_color',
            array(
                'label' => 'Background Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vj-marquee-banner' => 'background: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'image_height',
            array(
                'label' => 'Image Height',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 16,
                        'max' => 120,
                    ),
                ),
                'default' => array(
                    'size' => 36,
                    'unit' => 'px',
                ),
                'selectors' => array(
                    '{{WRAPPER}} .vj-marquee-banner' => '--vj-marquee-image-height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $images = isset($settings['images']) && is_array($settings['images']) ? $settings['images'] : array();
        if (empty($images)) {
            return;
        }

        $classes = 'vj-marquee-banner vj-marquee-banner--widget vj-marquee-banner--images';

        $items = $this->render_items($images);
        if ($items === '') {
            return;
        }

        echo '<div class="' . esc_attr($classes) . '">';
        echo '<div class="vj-marquee-banner__inner">';
        echo '<div class="vj-marquee-banner__marquee vj-marquee-marquee" aria-hidden="true">';
        echo '<div class="vj-marquee-banner__track">';
        echo '<div class="vj-marquee-banner__group">';
        echo $items;
        echo '</div>';
        echo '<div class="vj-marquee-banner__group" aria-hidden="true">';
        echo $items;
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    private function render_items($images) {
        $output = '';
        foreach ($images as $image) {
            $output .= '<span class="vj-marquee-banner__item">';
            if (!empty($image['id'])) {
                $output .= wp_get_attachment_image($image['id'], 'full', false, array('class' => 'vj-marquee-banner__image'));
            } elseif (!empty($image['url'])) {
                $output .= '<img class="vj-marquee-banner__image" src="' . esc_url($image['url']) . '" alt="" />';
            }
            $output .= '</span>';
        }

        return $output;
    }
}
