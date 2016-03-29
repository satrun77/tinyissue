$(function () {
    'use strict';

    var globalNotice = $('.global-notice');
    if (globalNotice.html().length > 0) {
        globalNotice.slideDown();

        setTimeout(function () {
            globalNotice.slideUp();
        }, 15000);

        globalNotice.on('click', function () {
            globalNotice.slideUp();
        });
    }

    // Confirm links
    $('.close-issue, .delete-project, #users-list .delete').on('click', function () {
        return ConfirmDialog.show($(this), function () {
            return true;
        });
    });

    // Load project progress
    var projects = $('.project.load-progress');
    if (projects.length > 0) {
        var projectIds = [];
        projects.each(function () {
            projectIds.push(parseInt($(this).data('project-id'), 10))
        });
        Ajax.relPost('projects/progress', {ids: projectIds}, function (data) {
            projects.each(function () {
                var project = $(this), id = parseInt(project.data('project-id'), 10);
                if (id > 0) {
                    project.append(data.progress[id]['html']);
                    project.find('.progress-bar').width(data.progress[id]['value'] + '%')
                }
            });
        });
    }

    var tags = $('.tagit');
    if (tags.length > 0) {
        tags.on('tokenfield:initialize', function (e) {
            var input = $(this).data('bs.tokenfield').$input;
            input.autocomplete({
                open: function () {
                    input.autocomplete('widget').outerWidth(input.outerWidth());
                }
            });
        });
        tags.on('tokenfield:createdtoken', function (e) {
            $(e.relatedTarget).css('background-color', e.attrs.bgcolor);
        });
        tags.on('tokenfield:createtoken', function (e) {
            var existingTokens = $(this).tokenfield('getTokens');
            $.each(existingTokens, function (index, token) {
                if (token.value === e.attrs.value) {
                    e.preventDefault();
                }
            });
        });
        tags.tokenfield({
            autocomplete: {
                source: TINY.baseUrl + "administration/tags/suggestions",
                delay: 100
            },
            allowEditing: false
        });
    }

    var exportIssues = $('#export-project-issues');
    if (exportIssues.length > 0) {
        exportIssues.on('click', 'input.btn', function (e) {
            e.preventDefault();
            GlobalSaving.show('Exporting...');
            Ajax.post(exportIssues.attr('action'), exportIssues.serialize(), function (data) {
                exportIssues.find('.form-actions .btn-link').remove();
                $(data.link).prependTo(exportIssues.find('.form-actions div')).effect("highlight");
                GlobalSaving.hide();
            });
        });
    }

    // Clickable elements
    $('.vlink').on('click', function (e) {
        e.preventDefault();
        return window.location = $(this).data('url');
    });

    // Mobile/Tablet screen
    SidebarEvents().init();

    // Kanban board
    Kanban().init();
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
    init: function (options) {
        var me = this;
        this.options = $.extend(this.options, options);
        this.options.items.on({
            mouseenter: function () {
                return me.showHighlight($(this).css('cursor', 'pointer'));
            },
            mouseleave: function () {
                return me.removeHighligt($(this));
            },
            click: function () {
                return me.select($(this));
            }
        }, this.options.itemSelector);
    },
    showHighlight: function (el) {
        el.addClass(this.options.className);
    },
    removeHighligt: function (el) {
        if (!this.isEqual(el)) {
            el.removeClass(this.options.className);
        }
    },
    select: function (el) {
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
    isEqual: function (el2) {
        return (this.selected && this.selected.find('input').val() === el2.find('input').val());
    }
};

function Discussion() {
    var instance = null;
    var options = {
        name: 'comment',
        selector: '.discussion'
    };

    function getId(el) {
        return el.data(options.name + '-id');
    }

    function getEdit(id) {
        return $('#' + options.name + id + ' .form');
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
            var url = $('#' + options.name + id + ' .edit a').attr('href');
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

function Uploader() {
    var instance;
    var errorClass = 'text-danger';
    var successClass = 'text-success';
    var options = {};

    function setMessage(data, removeClass, addClass, message) {
        $(data.context).find('.status').removeClass(removeClass).addClass(addClass).text(message);
    }

    function setError(data, message) {
        setMessage(data, successClass, errorClass, message);
    }

    function onDone(e, data) {
        setMessage(data, errorClass, successClass, options['messageSuccess']);
        if (data.result.error) {
            setError(data, data.result.error);
        }
    }

    function onFail(e, data) {
        setError(data, options['messageFailed']);
    }

    function onAdd(e, data) {
        var template = $($('#upload-template').html());
        data.context = template.appendTo('#upload-queue');
        $.each(data.files, function (index, file) {
            template.find('.name span:first').text(file.name);
            template.find('.close').data(data);
        });
    }

    function onProgress(e, data) {
        if (e.isDefaultPrevented()) {
            return false;
        }
        var progress = Math.floor(data.loaded / data.total * 100);
        if (data.context) {
            data.context.each(function () {
                $(this).find('.progress')
                    .attr('aria-valuenow', progress)
                    .children().first().css(
                    'width',
                    progress + '%'
                );
            });
        }
    }

    return {
        init: function () {
            instance = $('#upload');
            if (instance.length == 0) {
                return this;
            }
            options = instance.data();
            instance.fileupload({
                dataType: 'json',
                autoUpload: true,
                limitMultiFileUploads: 1,
                url: TINY.basePath + 'project/' + TINY.projectId + '/issue/upload_attachment'
            });
            instance.on('fileuploaddone', onDone);
            instance.on('fileuploadfail', onFail);
            instance.on('fileuploadadd', onAdd);
            instance.on('fileuploadprogress', onProgress);
            instance.prop('disabled', !$.support.fileInput);
            instance.parent().addClass($.support.fileInput ? undefined : 'disabled');

            $(document).on('click', '.queue-item .close', function (e) {
                e.preventDefault();
                var button = $(this),
                    data = button.data(),
                    file = data.files[0];

                GlobalSaving.show(button.data('message'));
                Ajax.relPost('project/' + TINY.projectId + '/issue/remove_attachment', {
                    _token: TINY.token,
                    upload_token: $('input[name=upload_token]').val(),
                    filename: file.name
                }, function () {
                    GlobalSaving.hide();
                    data.abort();
                    $(data.context).remove();
                });
            });

            return this;
        }
    }
}

function SidebarEvents() {
    var bodyWidth = $(document.body).outerWidth(true);

    // Only enabled if body width is small
    function isEnabled() {
        return bodyWidth < 768;
    }

    return {
        init: function () {
            if (!isEnabled()) {
                return;
            }

            var slideout = new Slideout({
                'panel': document.getElementById('content'),
                'menu': document.getElementById('sidebar'),
                'padding': 260,
                'tolerance': 70
            });

            return this;
        }
    }
}

function Kanban() {
    var kanban, container, task;

    return {
        init: function () {
            kanban = $('.kanban');
            if (!kanban.length > 0) {
                return;
            }

            var win = $(window);
            container = kanban.find(".column .content");
            task = kanban.find('.issue');
            setKanbantWidth = function () {
                var bodyWidth = win.width();
                if (bodyWidth < 768) {
                    kanban.parent().width(bodyWidth - 20);
                }
            };
            setKanbantWidth();
            win.resize(setKanbantWidth);

            container.sortable({
                connectWith: ".kanban .column .content",
                placeholder: "ui-state-highlight",
                receive: function (event, ui) {
                    var url = ui.item.data('url');
                    var data = {
                        newtag: ui.item.parents('.content').data('column'),
                        oldtag: ui.item.data('column'),
                        _token: TINY.token
                    };
                    ui.item.data('column', data.newtag);

                    Ajax.relPost(url, data, function (data) {
                        GlobalSaving.hide();
                    });
                }
            }).disableSelection();

            return this;
        }
    }
}

