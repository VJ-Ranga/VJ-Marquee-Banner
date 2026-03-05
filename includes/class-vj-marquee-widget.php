<?php

if (!class_exists('Elementor\\Widget_Base')) {
    return;
}

if (class_exists('VJ_Marquee_Widget')) {
    return;
}

class VJ_Marquee_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'vj_marquee_banner';
    }

    public function get_title() {
        return 'VJ Marquee Banner';
    }

    public function get_icon() {
        return 'eicon-bullhorn';
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

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'text',
            array(
                'label' => 'Text',
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'Free Shipping for orders above Rs 5000',
                'rows' => 2,
            )
        );

        $repeater->add_control(
            'link',
            array(
                'label' => 'Link',
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => 'https://',
                'show_external' => true,
            )
        );

        $this->add_control(
            'items',
            array(
                'label' => 'Items',
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => array(
                    array(
                        'text' => 'Free Shipping for orders above Rs 5000',
                    ),
                ),
                'title_field' => '{{{ text }}}',
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

        $this->start_controls_tabs('text_color_tabs');

        $this->start_controls_tab(
            'text_color_normal',
            array(
                'label' => 'Normal',
            )
        );

        $this->add_control(
            'text_color',
            array(
                'label' => 'Text Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vj-marquee-banner__item, {{WRAPPER}} .vj-marquee-banner__item a' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'text_color_hover',
            array(
                'label' => 'Hover',
            )
        );

        $this->add_control(
            'text_color_hover',
            array(
                'label' => 'Text Color',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vj-marquee-banner__item a:hover' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'text_typography',
                'selector' => '{{WRAPPER}} .vj-marquee-banner__item, {{WRAPPER}} .vj-marquee-banner__item a',
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            array(
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .vj-marquee-banner__item, {{WRAPPER}} .vj-marquee-banner__item a',
            )
        );

        $this->add_control(
            'height',
            array(
                'label' => 'Height',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 24,
                        'max' => 120,
                    ),
                ),
                'default' => array(
                    'size' => 36,
                    'unit' => 'px',
                ),
                'selectors' => array(
                    '{{WRAPPER}} .vj-marquee-banner' => '--vj-marquee-height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $classes = 'vj-marquee-banner vj-marquee-banner--widget vj-marquee-banner--text';

        $items = $this->render_items($settings);
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

        $sr_text = $this->render_sr_text($settings);
        if ($sr_text !== '') {
            echo '<div class="vj-marquee-banner__sr">' . $sr_text . '</div>';
        }

        echo '</div>';
        echo '</div>';
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

    private function render_items($settings) {
        $items = isset($settings['items']) && is_array($settings['items']) ? $settings['items'] : array();
        if (empty($items)) {
            return '';
        }

        $repeat = $this->get_repeat_count(count($items));
        $output = '';
        $index = 0;
        for ($i = 0; $i < $repeat; $i++) {
            foreach ($items as $item) {
                $text = isset($item['text']) ? wp_kses_post($item['text']) : '';
                if ($text === '') {
                    continue;
                }

                $output .= '<span class="vj-marquee-banner__item">' . $this->render_item($item, $index) . '</span>';
                $index++;
            }
        }

        return $output;
    }

    private function render_item($item, $index) {
        $text = isset($item['text']) ? wp_kses_post($item['text']) : '';
        if ($text === '') {
            return '';
        }

        $url = isset($item['link']['url']) ? $item['link']['url'] : '';
        if ($url) {
            $this->add_link_attributes('link_' . $index, $item['link']);
            return '<a ' . $this->get_render_attribute_string('link_' . $index) . '>' . $text . '</a>';
        }

        return $text;
    }

    private function render_sr_text($settings) {
        $items = isset($settings['items']) && is_array($settings['items']) ? $settings['items'] : array();
        if (empty($items)) {
            return '';
        }

        $plain = array();
        foreach ($items as $item) {
            if (!empty($item['text'])) {
                $plain[] = wp_strip_all_tags($item['text']);
            }
        }

        if (empty($plain)) {
            return '';
        }

        return esc_html(implode(' | ', $plain));
    }
}
