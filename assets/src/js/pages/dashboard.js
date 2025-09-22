(function () {
    'use strict';

    admin.pages.dashboard = function () {

        $(document).ready(function(){
            initCollapsibleWidgets();
            initDraggableWidgets();
        });

        function initCollapsibleWidgets() {
            // Convert h4 headers to proper widget headers with controls
            $('.widget h4').each(function() {
                const $h4 = $(this);
                const $widget = $h4.closest('.widget');
                const $container = $widget.closest('.widget-container');
                const widgetId = $widget.attr('id');

                if (!widgetId) return; // Skip widgets without IDs

                const headerTitle = $h4.text();

                // Create new header structure
                const headerHtml = `
                    <div class="widget-header">
                        <div class="widget-title">
                            <span class="widget-title-text">${headerTitle}</span>
                        </div>
                        <div class="widget-controls">
                            <button class="widget-control-btn widget-narrower-btn" title="Make Narrower" data-action="narrower">
                                <i class="fa fa-minus"></i>
                            </button>
                            <button class="widget-control-btn widget-wider-btn" title="Make Wider" data-action="wider">
                                <i class="fa fa-plus"></i>
                            </button>
                            <button class="widget-control-btn widget-collapse-btn" title="Collapse/Expand" data-action="collapse">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>
                    </div>
                `;

                // Replace h4 with new header
                $h4.replaceWith(headerHtml);

                // Check saved state from cookie
                const isCollapsed = Cookies.get('widget_' + widgetId + '_collapsed') === 'true';
                if (isCollapsed) {
                    collapseWidget($widget, false); // false = no animation on init
                }

                // Check saved width from cookie
                const savedWidth = Cookies.get('widget_' + widgetId + '_width');
                if (savedWidth) {
                    setWidgetWidth($container, parseInt(savedWidth));
                }
            });

            // Handle control button clicks
            $(document).on('click', '.widget-control-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const action = $(this).data('action');
                const $widget = $(this).closest('.widget');
                const $container = $widget.closest('.widget-container');
                const widgetId = $widget.attr('id');

                if (action === 'collapse') {
                    const $content = $widget.find('.widget_int');

                    if ($content.is(':visible')) {
                        collapseWidget($widget, true);
                        Cookies.set('widget_' + widgetId + '_collapsed', 'true', { expires: 365 });
                    } else {
                        expandWidget($widget, true);
                        Cookies.set('widget_' + widgetId + '_collapsed', 'false', { expires: 365 });
                    }
                } else if (action === 'wider') {
                    makeWidgetWider($container, widgetId);
                } else if (action === 'narrower') {
                    makeWidgetNarrower($container, widgetId);
                }
            });

            // Handle title area clicks for collapse (not on control buttons)
            $(document).on('click', '.widget-title', function(e) {
                e.preventDefault();
                const $widget = $(this).closest('.widget');
                const $content = $widget.find('.widget_int');
                const widgetId = $widget.attr('id');

                if ($content.is(':visible')) {
                    collapseWidget($widget, true);
                    Cookies.set('widget_' + widgetId + '_collapsed', 'true', { expires: 365 });
                } else {
                    expandWidget($widget, true);
                    Cookies.set('widget_' + widgetId + '_collapsed', 'false', { expires: 365 });
                }
            });
        }

        function collapseWidget($widget, animate = true) {
            const $content = $widget.find('.widget_int');
            const $collapseBtn = $widget.find('.widget-collapse-btn i');

            if (animate) {
                $content.slideUp(200);
            } else {
                $content.hide();
            }

            $collapseBtn.removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $widget.addClass('widget-collapsed');
        }

        function expandWidget($widget, animate = true) {
            const $content = $widget.find('.widget_int');
            const $collapseBtn = $widget.find('.widget-collapse-btn i');

            if (animate) {
                $content.slideDown(200);
            } else {
                $content.show();
            }

            $collapseBtn.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $widget.removeClass('widget-collapsed');
        }

        function makeWidgetWider($container, widgetId) {
            const currentWidth = getWidgetWidth($container);
            const maxColumns = getMaxColumns();

            if (currentWidth < maxColumns) {
                const newWidth = currentWidth + 1;
                setWidgetWidth($container, newWidth);
                Cookies.set('widget_' + widgetId + '_width', newWidth, { expires: 365 });
            }
        }

        function makeWidgetNarrower($container, widgetId) {
            const currentWidth = getWidgetWidth($container);

            if (currentWidth > 1) {
                const newWidth = currentWidth - 1;
                setWidgetWidth($container, newWidth);
                Cookies.set('widget_' + widgetId + '_width', newWidth, { expires: 365 });
            }
        }

        function getWidgetWidth($container) {
            // Check CSS grid-column-end style or data attribute
            for (let i = 1; i <= 4; i++) {
                if ($container.hasClass(`widget-width-${i}`)) {
                    return i;
                }
            }
            return 1; // default width
        }

        function setWidgetWidth($container, width) {
            // Remove existing width classes
            $container.removeClass('widget-width-1 widget-width-2 widget-width-3 widget-width-4');
            // Add new width class
            $container.addClass(`widget-width-${width}`);
        }

        function getMaxColumns() {
            // Determine max columns based on screen size
            const screenWidth = $(window).width();
            if (screenWidth < 768) return 1;  // Mobile
            if (screenWidth < 1200) return 2; // Tablet
            if (screenWidth < 1600) return 3; // Desktop
            return 4; // Large desktop
        }

        function initDraggableWidgets() {
            const container = document.getElementById('dashboard-widgets');
            if (!container) return;

            // Load saved widget order from cookies
            loadWidgetOrder();

            // Make widgets draggable
            $('.widget-container').each(function() {
                const widget = this;
                widget.draggable = true;

                // Drag start
                widget.addEventListener('dragstart', function(e) {
                    $(this).addClass('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', this.outerHTML);
                    e.dataTransfer.setData('text/plain', $(this).data('widget'));
                });

                // Drag end
                widget.addEventListener('dragend', function(e) {
                    $(this).removeClass('dragging');
                    $('.widget-container').removeClass('drag-over');
                });

                // Drag over
                widget.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';

                    if (!$(this).hasClass('dragging')) {
                        $(this).addClass('drag-over');
                    }
                });

                // Drag leave
                widget.addEventListener('dragleave', function(e) {
                    $(this).removeClass('drag-over');
                });

                // Drop
                widget.addEventListener('drop', function(e) {
                    e.preventDefault();
                    $(this).removeClass('drag-over');

                    const draggedWidget = $('.widget-container.dragging')[0];
                    if (draggedWidget && draggedWidget !== this) {
                        // Insert the dragged widget before or after this widget
                        const rect = this.getBoundingClientRect();
                        const midY = rect.top + rect.height / 2;

                        if (e.clientY < midY) {
                            this.parentNode.insertBefore(draggedWidget, this);
                        } else {
                            this.parentNode.insertBefore(draggedWidget, this.nextSibling);
                        }

                        // Save new order
                        saveWidgetOrder();
                    }
                });
            });

            // Container drop handling
            container.addEventListener('dragover', function(e) {
                e.preventDefault();
            });

            container.addEventListener('drop', function(e) {
                e.preventDefault();
                // If dropped on empty space, append to end
                const draggedWidget = $('.widget-container.dragging')[0];
                if (draggedWidget) {
                    this.appendChild(draggedWidget);
                    saveWidgetOrder();
                }
            });
        }

        function saveWidgetOrder() {
            const widgetOrder = [];
            $('.widget-container').each(function() {
                const widgetId = $(this).data('widget');
                if (widgetId) {
                    widgetOrder.push(widgetId);
                }
            });

            // Save to cookie for 1 year
            Cookies.set('dashboard_widget_order', JSON.stringify(widgetOrder), { expires: 365 });
        }

        function loadWidgetOrder() {
            const savedOrder = Cookies.get('dashboard_widget_order');
            if (!savedOrder) return;

            try {
                const widgetOrder = JSON.parse(savedOrder);
                const container = $('#dashboard-widgets');

                // Reorder widgets based on saved order
                widgetOrder.forEach(function(widgetId) {
                    const widget = container.find(`[data-widget="${widgetId}"]`);
                    if (widget.length) {
                        container.append(widget);
                    }
                });
            } catch (e) {
                console.warn('Could not load widget order from cookies:', e);
            }
        }
    };
})();