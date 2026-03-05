document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('vj-marquee-content-type');
    var fontSource = document.getElementById('vj-marquee-font-source');
    var fontSelect = document.getElementById('vj-marquee-font-family-select');
    var fontCustom = document.getElementById('vj-marquee-font-family-custom');
    var imageButton = document.getElementById('vj-marquee-image-select');
    var imageClear = document.getElementById('vj-marquee-image-clear');
    var imageInput = document.getElementById('vj-marquee-image-ids');
    var imagePreview = document.getElementById('vj-marquee-image-preview');
    var navItems = document.querySelectorAll('.vj-marquee-nav-item');
    var cards = document.querySelectorAll('.vj-marquee-card[data-tab]');
    var activeTab = 'general';

    if (!select) {
        return;
    }

    var setActiveTab = function (tab) {
        activeTab = tab;
        navItems.forEach(function (btn) {
            btn.classList.toggle('is-active', btn.dataset.tab === tab);
        });
        render();
    };

    var render = function () {
        var mode = select.value;
        var isElementor = mode === 'elementor';
        var isText = mode === 'text';
        var isImages = mode === 'images';
        var showAnim = !isElementor;
        var showAppearance = !isElementor;

        if (!showAnim && activeTab === 'animation') {
            activeTab = 'content';
        }
        if (!showAppearance && activeTab === 'appearance') {
            activeTab = 'content';
        }

        navItems.forEach(function (btn) {
            var tab = btn.dataset.tab;
            var allowed = true;
            if (tab === 'animation' && !showAnim) {
                allowed = false;
            }
            if (tab === 'appearance' && !showAppearance) {
                allowed = false;
            }
            btn.classList.toggle('is-hidden', !allowed);
            if (!allowed && btn.classList.contains('is-active')) {
                btn.classList.remove('is-active');
            }
            if (allowed && tab === activeTab) {
                btn.classList.add('is-active');
            }
        });

        cards.forEach(function (card) {
            var show = card.dataset.tab === activeTab;
            if (show) {
                if (card.classList.contains('vj-marquee-section--text')) {
                    show = isText;
                }
                if (card.classList.contains('vj-marquee-section--images')) {
                    show = isImages;
                }
                if (card.classList.contains('vj-marquee-section--elementor')) {
                    show = isElementor;
                }
                if (card.classList.contains('vj-marquee-section--media') && isElementor) {
                    show = false;
                }
            }
            card.style.display = show ? 'block' : 'none';
        });

        document.querySelectorAll('.vj-marquee-field--elementor').forEach(function (row) {
            row.style.display = isElementor ? 'table-row' : 'none';
        });
        document.querySelectorAll('.vj-marquee-field--text-only').forEach(function (row) {
            row.style.display = isText ? 'table-row' : 'none';
        });
        document.querySelectorAll('.vj-marquee-field--images').forEach(function (row) {
            row.style.display = isImages ? 'table-row' : 'none';
        });
        document.querySelectorAll('.vj-marquee-field--media').forEach(function (row) {
            row.style.display = isElementor ? 'none' : 'table-row';
        });

        if (fontSource) {
            var isGoogle = fontSource.value === 'google';
            document.querySelectorAll('.vj-marquee-field--font-weights').forEach(function (row) {
                row.style.display = isGoogle && isText ? 'table-row' : 'none';
            });
        }

        if (fontSelect && fontCustom) {
            if (fontSelect.value === '__custom__') {
                fontCustom.style.display = 'block';
            } else {
                fontCustom.style.display = 'none';
                if (fontSelect.value !== '') {
                    fontCustom.value = fontSelect.value;
                } else {
                    fontCustom.value = '';
                }
            }
        }
    };

    navItems.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setActiveTab(btn.dataset.tab);
        });
    });

    select.addEventListener('change', render);
    if (fontSource) {
        fontSource.addEventListener('change', render);
    }
    if (fontSelect) {
        fontSelect.addEventListener('change', render);
    }

    setActiveTab('general');
    render();

    if (imageButton && imageInput && imagePreview) {
        var frame;
        var refresh = function (ids) {
            imagePreview.innerHTML = '';
            ids.forEach(function (id) {
                var img = document.createElement('img');
                img.src = wp.media.attachment(id).get('url');
                img.className = 'vj-marquee-thumb';
                var wrap = document.createElement('span');
                wrap.className = 'vj-marquee-thumb-wrap';
                wrap.dataset.id = id;
                wrap.appendChild(img);
                imagePreview.appendChild(wrap);
            });
        };

        imageButton.addEventListener('click', function (event) {
            event.preventDefault();
            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({ title: 'Select Images', button: { text: 'Use images' }, multiple: true });
            frame.on('select', function () {
                var ids = frame.state().get('selection').map(function (att) {
                    return att.id;
                });
                imageInput.value = ids.join(',');
                refresh(ids);
            });
            frame.open();
        });

        if (imageClear) {
            imageClear.addEventListener('click', function (event) {
                event.preventDefault();
                imageInput.value = '';
                imagePreview.innerHTML = '';
            });
        }

        if (imageInput.value) {
            refresh(
                imageInput.value
                    .split(',')
                    .map(function (value) {
                        return parseInt(value, 10);
                    })
                    .filter(Boolean)
            );
        }
    }
});
