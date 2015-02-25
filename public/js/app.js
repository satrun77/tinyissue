$(function () {
    'use strict';

    if ($('.global-notice').html().length > 0) {
        $('.global-notice').slideDown();

        setTimeout(function () {
            $('.global-notice').slideUp();
        }, 15000);

        $('.global-notice').live('click', function () {
            $('.global-notice').slideUp();
        });
    }

    // Confirm links
    $('.close-issue, .delete-project, #users-list .delete').on('click', function (e) {
        return ConfirmDialog.show($(this), function () {
            return true;
        });
    });
});

var GlobalSaving = {
    status: false,
    _saving: null,
    messageHolder: '',
    saving: function () {
        if (this._saving === null) {
            this._saving = $('.global-saving');
        }
        return this._saving;
    },
    toggle: function () {
        if (this.status) {
            this.saving().hide();
            this.status = false;
        } else {
            this.saving().show();
            this.status = true;
        }
    },
    show: function (message) {
        this.messageHolder = this.saving().find('span').html();
        this.saving().find('span').html(message);
        this.toggle();
    },
    hide: function () {
        this.toggle();
        this.saving().find('span').html(this.messageHolder);
    }
};

var ConfirmDialog = {
    show: function (el, callback) {
        if (confirm(el.data('message'))) {
            return callback(el);
        }
        return false;
    }
};

var Ajax = {
    post: function (url, data, callback) {
        $.ajax({
            url: url,
            type: "POST",
            headers: {'X-XSRF-TOKEN': $.cookie('XSRF-TOKEN')},
            data: data,
            dataType: "json"
        }).done(callback);
    },
    relPost: function (url, data, callback) {
        this.post(TINY.baseUrl + url, data, callback);
    },
    get: function (url, callback) {
        $.getJSON(url, callback);
    },
    relGet: function (url, callback) {
        this.get(TINY.baseUrl + url, callback);
    }
};

var Autocomplete = {
    suggestions: {},
    users: null,
    input: null,
    instance: null,
    selected: [],
    options: {
        url: '/project/inactive_users',
        usersSelector: '.datalist_user',
        inputSelector: '#add-user-project',
        template: function (ui) {
            return '<li class="project-user' + ui.item.id + '">' +
                '<a href="" data-user-id="' + ui.item.id + '" class="delete">Remove</a>' +
                '' + ui.item.label + '' +
                '<input type="hidden" name="user[' + ui.item.id + ']" value="' + ui.item.id + '" />' +
                '</li>';
        },
        onSelect: function (el, item) {
            return true;
        },
        onRemove: function (el, item) {
            return true;
        }
    },
    init: function (options) {
        if (this.input === null) {
            this.options = $.extend(this.options, options);
            this.users = $(this.options.usersSelector);
            this.input = $(this.options.inputSelector);
            Ajax.relGet(this.options.url, $.proxy(this.load, this));
        }
        return this;
    },
    load: function (data) {
        this.suggestions = $.map(data, function (value, key) {
            return {
                id: key,
                label: value
            };
        });

        this.instance = $(this.input);
        this.instance.autocomplete({
            source: this.suggestions,
            select: $.proxy(function (event, ui) {
                var append = $($.proxy(this.options.template, this, ui)());
                append.find('.delete').on('click', $.proxy(function (e) {
                    e.preventDefault();
                    if ($.proxy(this.options.onRemove, this, append, ui.item.id)()) {
                        this.remove(append, ui.item.id);
                    }
                }, this));
                if ($.proxy(this.options.onSelect, this, append, ui.item)()) {
                    this.select(append, ui.item);
                }
            }, this),
            close: $.proxy(this.close, this),
            open: $.proxy(function () {
                this.instance.autocomplete("widget").width(this.input.outerWidth());
            }, this)
        });
    },
    remove: function (append, id) {
        append.remove();
        this.selected = $.grep(this.selected, function (item) {
            return id !== item;
        });
        this.filterSuggestions();
    },
    select: function (append, item) {
        append.appendTo(this.users);
        this.selected.push(item.id);
        this.filterSuggestions();
    },
    filterSuggestions: function () {
        var source = $.grep(this.suggestions, $.proxy(function (element) {
            return $.inArray(element.id, this.selected) === -1;
        }, this));
        this.instance.autocomplete('option', 'source', source);
    },
    close: function () {
        this.input.val('');
    }
};

var Selection = {
    selected: null,
    options: {
        className: 'default-assignee',
        items: null,
        itemSelector: 'li',
        placeHolderSelector: ''
    },
    init: function(options) {
        var me = this;
        this.options = $.extend(this.options, options);
        this.options.items.on({
            mouseenter: function() {
                return me.showHighlight($(this).css('cursor', 'pointer'));
            },
            mouseleave: function() {
                return me.removeHighligt($(this));
            },
            click: function() {
                return me.select($(this));
            }
        }, this.options.itemSelector);
    },
    showHighlight: function(el) {
        el.addClass(this.options.className);
    },
    removeHighligt: function(el) {
        if (!this.isEqual(el)) {
            el.removeClass(this.options.className);
        }
    },
    select: function(el) {
        if (this.selected) {
            this.selected.removeClass(this.options.className);
            if (this.isEqual(el)) {
                $(this.options.placeHolderSelector).val('');
                this.selected = null;
                return false;
            }
        }
        this.selected = el;
        this.showHighlight(this.selected);
        $(this.options.placeHolderSelector).val(this.selected.find('input').val());
        return true;
    },
    isEqual:  function(el2) {
        return (this.selected && this.selected.find('input').val() === el2.find('input').val());
    }
};

function Discussion() {
    var instance = null;
    var options = {
        name: 'comment',
        selector: '.issue-discussion'
    };

    function getId(el) {
        return el.data(options.name + '-id');
    }

    function getEdit(id) {
        return $('#' + options.name + id + ' .' + options.name + '-edit');
    }

    function getContent(id) {
        return $('#' + options.name + id + ' .content');
    }

    return {
        init: function (args) {
            options = $.extend(options, args);
            instance = $(options.selector);
            if (instance.length == 0) {
                return this;
            }
            instance.find('li .edit').on('click', $.proxy(function (e) {
                e.preventDefault();
                return this.edit($(e.currentTarget));
            }, this));
            instance.find('li .delete').on('click', $.proxy(function (e) {
                e.preventDefault();
                return this.remove($(e.currentTarget));
            }, this));
            instance.find('li .save').on('click', $.proxy(function (e) {
                e.preventDefault();
                return this.save($(e.currentTarget));
            }, this));
            instance.find('li .cancel').on('click', $.proxy(function (e) {
                e.preventDefault();
                return this.cancel($(e.currentTarget));
            }, this));
            return this;
        },
        edit: function (el) {
            var id = getId(el);
            getContent(id).hide();
            getEdit(id).show();
        },
        save: function (el) {
            var id = getId(el);
            var url = $('#' + options.name + id + ' .edit').attr('href');
            var textarea = $('#' + options.name + id + ' textarea');

            textarea.attr('disabled', 'disabled');
            GlobalSaving.toggle();

            Ajax.post(url, {body: textarea.val()}, function (data) {
                textarea.removeAttr('disabled');
                getEdit(id).hide();
                getContent(id).html(data.text).show();
                GlobalSaving.toggle();
            });
        },
        cancel: function (el) {
            var id = getId(el);
            getEdit(id).hide();
            getContent(id).show();
        },
        remove: function (el) {
            ConfirmDialog.show(el, function (el) {
                GlobalSaving.show('Deleting');
                var id = getId(el);
                Ajax.get(el.attr('href'), function () {
                    $('#' + options.name + id).fadeOut();
                    GlobalSaving.hide();
                });
            });
        }
    }
}
