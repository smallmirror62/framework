/**
 * Leaps GridView widget.
 *
 * This is the JavaScript widget used by the Leaps\Grid\GridView widget.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
(function ($) {
    $.fn.leapsGridView = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.leapsGridView');
            return false;
        }
    };

    var defaults = {
        filterUrl: undefined,
        filterSelector: undefined
    };

    var gridData = {};

    var gridEvents = {
        /**
         * beforeFilter event is triggered before filtering the grid.
         * The signature of the event handler should be:
         *     function (event)
         * where
         *  - event: an Event object.
         *
         * If the handler returns a boolean false, it will stop filter form submission after this event. As
         * a result, afterFilter event will not be triggered.
         */
        beforeFilter: 'beforeFilter',
        /**
         * afterFilter event is triggered after filtering the grid and filtered results are fetched.
         * The signature of the event handler should be:
         *     function (event)
         * where
         *  - event: an Event object.
         */
        afterFilter: 'afterFilter'
    };
    
    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                var settings = $.extend({}, defaults, options || {});
                gridData[$e.attr('id')] = {settings: settings};

                var enterPressed = false;
                $(document).off('change.leapsGridView keydown.leapsGridView', settings.filterSelector)
                    .on('change.leapsGridView keydown.leapsGridView', settings.filterSelector, function (event) {
                        if (event.type === 'keydown') {
                            if (event.keyCode !== 13) {
                                return; // only react to enter key
                            } else {
                                enterPressed = true;
                            }
                        } else {
                            // prevent processing for both keydown and change events
                            if (enterPressed) {
                                enterPressed = false;
                                return;
                            }
                        }

                        methods.applyFilter.apply($e);

                        return false;
                    });
            });
        },

        applyFilter: function () {
            var $grid = $(this), event;
            var settings = gridData[$grid.attr('id')].settings;
            var data = {};
            $.each($(settings.filterSelector).serializeArray(), function () {
                data[this.name] = this.value;
            });

            $.each(leaps.getQueryParams(settings.filterUrl), function (name, value) {
                if (data[name] === undefined) {
                    data[name] = value;
                }
            });

            var pos = settings.filterUrl.indexOf('?');
            var url = pos < 0 ? settings.filterUrl : settings.filterUrl.substring(0, pos);

            $grid.find('form.gridview-filter-form').remove();
            var $form = $('<form action="' + url + '" method="get" class="gridview-filter-form" style="display:none" data-pjax></form>').appendTo($grid);
            $.each(data, function (name, value) {
                $form.append($('<input type="hidden" name="t" value="" />').attr('name', name).val(value));
            });
            
            event = $.Event(gridEvents.beforeFilter);
            $grid.trigger(event);
            if (event.result === false) {
                return;
            }

            $form.submit();
            
            $grid.trigger(gridEvents.afterFilter);
        },

        setSelectionColumn: function (options) {
            var $grid = $(this);
            var id = $(this).attr('id');
            gridData[id].selectionColumn = options.name;
            if (!options.multiple) {
                return;
            }
            var checkAll = "#" + id + " input[name='" + options.checkAll + "']";
            var inputs = "#" + id + " input[name='" + options.name + "']";
            $(document).off('click.leapsGridView', checkAll).on('click.leapsGridView', checkAll, function () {
                $grid.find("input[name='" + options.name + "']:enabled").prop('checked', this.checked);
            });
            $(document).off('click.leapsGridView', inputs + ":enabled").on('click.leapsGridView', inputs + ":enabled", function () {
                var all = $grid.find("input[name='" + options.name + "']").length == $grid.find("input[name='" + options.name + "']:checked").length;
                $grid.find("input[name='" + options.checkAll + "']").prop('checked', all);
            });
        },

        getSelectedRows: function () {
            var $grid = $(this);
            var data = gridData[$grid.attr('id')];
            var keys = [];
            if (data.selectionColumn) {
                $grid.find("input[name='" + data.selectionColumn + "']:checked").each(function () {
                    keys.push($(this).parent().closest('tr').data('key'));
                });
            }
            return keys;
        },

        destroy: function () {
            return this.each(function () {
                $(window).unbind('.leapsGridView');
                $(this).removeData('leapsGridView');
            });
        },

        data: function () {
            var id = $(this).attr('id');
            return gridData[id];
        }
    };
})(window.jQuery);
