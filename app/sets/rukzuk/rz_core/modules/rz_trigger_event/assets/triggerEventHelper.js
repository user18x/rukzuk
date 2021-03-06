define(['jquery'], function ($) {

    var getSelector = function(eventData) {
        var selector;
        if (eventData.eventLimit == 'all') {
            selector = $('.listen_' + eventData.stateName);
        }
        if (eventData.eventLimit == 'below') {
            selector = $('#' + eventData.parentUnitId).find('.listen_' + eventData.stateName);
        }
        if (eventData.eventLimit == 'above') {
            selector = $('#' + eventData.parentUnitId).parents('.listen_' + eventData.stateName);
        }
        if (eventData.eventLimit == 'sibling') {
            selector = $('#' + eventData.parentUnitId).parents().first().find('.listen_' + eventData.stateName);
        }
        return selector;
    };

    var trigger_event_click_mouseover = function (event) {
        event.stopPropagation();
        var eventData = event.data;
        var selector = getSelector(eventData);

        if (eventData.eventMode == 'toggle') {
            selector.toggleClass(eventData.stateName);
            $('#' + eventData.parentUnitId).toggleClass(eventData.selector);
        } else if (eventData.eventMode == 'set') {
            selector.addClass(eventData.stateName);
            $('#' + eventData.parentUnitId).addClass(eventData.selector);
        } else {
            selector.removeClass(eventData.stateName);
            $('#' + eventData.parentUnitId).removeClass(eventData.selector);
        }
    };


    var trigger_event_scroll = function(event) {
        var eventData = event.data;
        var selector = getSelector(eventData);
        var element = $('#' + eventData.parentUnitId);
        var fracs = element.fracs();
        var visiblePartOfElement = Math.round(fracs.visible * 100);


        if (eventData.scrollConfig == 'center') {
            if (fracs.rects) {
                var elementTop = fracs.rects.viewport.top;
                var elementHeight = fracs.rects.element.height;
                var viewPortHeight = $(window).height();

                if ((elementTop + elementHeight / 2) < viewPortHeight / 2) {
                    element.addClass('top50Screen');
                }
                if ((elementTop + elementHeight / 2) > viewPortHeight / 2) {
                    element.addClass('bottom50Screen');
                }
                if (element.hasClass('top50Screen') && element.hasClass('bottom50Screen')) {
                    selector.addClass(eventData.stateName);
                    element.addClass(eventData.selector);
                }
                if ((visiblePartOfElement < 1) && (eventData.eventMode == 'toggle')) {
                    selector.removeClass(eventData.stateName);
                    element.removeClass(eventData.selector + ' top50Screen bottom50Screen');
                }
            }
        } else if (eventData.scrollConfig.match(/^in/)) {
            if ((visiblePartOfElement < 1) && (eventData.eventMode == 'toggle')) {
                selector.removeClass(eventData.stateName);
                element.removeClass(eventData.selector + ' top50Screen bottom50Screen ');
            }
            if (visiblePartOfElement >= eventData.scrollConfig.substr(2)) {
                if (eventData.eventMode == 'reset') {
                    selector.removeClass(eventData.stateName);
                    element.removeClass(eventData.selector);
                } else {

                    selector.addClass(eventData.stateName);
                    element.addClass(eventData.selector);
                }
            }
        } else {
            if ((visiblePartOfElement > 1) && (eventData.eventMode == 'toggle')) {
                selector.removeClass(eventData.stateName);
                element.removeClass(eventData.selector + ' top50Screen bottom50Screen ');
            }
            if (visiblePartOfElement <= eventData.scrollConfig.substr(3)) {
                if (eventData.eventMode == 'reset') {
                    selector.removeClass(eventData.stateName);
                    element.removeClass(eventData.selector);
                } else {
                    selector.addClass(eventData.stateName);
                    element.addClass(eventData.selector);
                }
            }
        }

    };

    var initAllEvents = function () {
        for (var unit in window.rz_trigger_event) {
            var eventData = window.rz_trigger_event[unit];
            var parentUnit = $('#' + eventData.parentUnitId);

            if (eventData.eventType == 'click') {
                parentUnit.on('click', eventData,trigger_event_click_mouseover);
            }
            if (eventData.eventType == 'mouseover') {
                parentUnit.on('mouseover', eventData, trigger_event_click_mouseover);
                parentUnit.on('mouseout', eventData, trigger_event_click_mouseover);
            }
            if (eventData.eventType == 'scroll') {
                $(window).on('scroll', eventData, trigger_event_scroll);
                $(window).trigger('scroll');
            }
        }
    };
    return {
        initAllEvents: initAllEvents
    };
});