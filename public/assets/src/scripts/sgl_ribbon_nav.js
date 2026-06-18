(function (window, document) {
    'use strict';

    function syncHeaderOffset() {
        var header = document.querySelector('.header');
        var ribbon = document.querySelector('[data-sgl-ribbon]');
        if (!header && !ribbon) {
            return;
        }

        var offset = 0;

        if (header) {
            offset = Math.max(offset, header.getBoundingClientRect().bottom);
        }

        if (ribbon) {
            offset = Math.max(offset, ribbon.getBoundingClientRect().bottom);
        }

        document.documentElement.style.setProperty('--sgl-header-offset', Math.ceil(offset) + 'px');
    }

    function activateRibbonTab(root, targetTab, shouldFocus) {
        if (!root || !targetTab) {
            return;
        }

        var sectionKey = targetTab.getAttribute('data-section');
        var tabs = root.querySelectorAll('.sgl-ribbon-tab');
        var panels = root.querySelectorAll('.sgl-ribbon-panel');

        tabs.forEach(function (tab) {
            var isActive = tab === targetTab;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            tab.setAttribute('tabindex', isActive ? '0' : '-1');
        });

        panels.forEach(function (panel) {
            var isActive = panel.getAttribute('data-section-panel') === sectionKey;
            panel.classList.toggle('is-active', isActive);
            if (isActive) {
                panel.removeAttribute('hidden');
            } else {
                panel.setAttribute('hidden', 'hidden');
            }
        });

        if (shouldFocus) {
            targetTab.focus();
        }

        window.requestAnimationFrame(syncHeaderOffset);
    }

    function bindRibbon(root) {
        if (!root || root.dataset.ribbonReady === '1') {
            return;
        }

        root.dataset.ribbonReady = '1';

        if (document.body) {
            document.body.classList.add('sgl-ribbon-layout');
        }

        var tabs = Array.prototype.slice.call(root.querySelectorAll('.sgl-ribbon-tab'));
        if (!tabs.length) {
            return;
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                activateRibbonTab(root, tab, false);
            });

            tab.addEventListener('keydown', function (event) {
                var targetIndex = index;

                if (event.key === 'ArrowRight') {
                    targetIndex = (index + 1) % tabs.length;
                } else if (event.key === 'ArrowLeft') {
                    targetIndex = (index - 1 + tabs.length) % tabs.length;
                } else if (event.key === 'Home') {
                    targetIndex = 0;
                } else if (event.key === 'End') {
                    targetIndex = tabs.length - 1;
                } else {
                    return;
                }

                event.preventDefault();
                activateRibbonTab(root, tabs[targetIndex], true);
            });
        });

        var activeTab = root.querySelector('.sgl-ribbon-tab.is-active') || tabs[0];
        activateRibbonTab(root, activeTab, false);

        if ('ResizeObserver' in window) {
            var header = document.querySelector('.header');
            var ribbon = document.querySelector('[data-sgl-ribbon]');
            var resizeObserver = new ResizeObserver(function () {
                syncHeaderOffset();
            });

            if (header) {
                resizeObserver.observe(header);
            }

            if (ribbon) {
                resizeObserver.observe(ribbon);
            }
        }

        window.addEventListener('resize', syncHeaderOffset, { passive: true });
        window.requestAnimationFrame(syncHeaderOffset);
    }

    function bootRibbon() {
        document.querySelectorAll('[data-sgl-ribbon]').forEach(function (root) {
            bindRibbon(root);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootRibbon);
    } else {
        bootRibbon();
    }
})(window, document);