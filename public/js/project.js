$(function () {
    'use strict';

    // Comment Actions
    var discussion = $('.issue-discussion');

    discussion.find('li .edit').on('click', function (e) {
        e.preventDefault();
        var id = $(this).data('comment-id');
        $('#comment' + id + ' .issue').hide();
        $('#comment' + id + ' .comment-edit').show();
    });

    discussion.find('li .delete').on('click', function (e) {
        e.preventDefault();
        ConfirmDialog.show($(this), function (el) {
            GlobalSaving.show('Deleting');
            var id = el.data('comment-id');
            Ajax.get(el.attr('href'), function () {
                $('#comment' + id).fadeOut();
                GlobalSaving.hide();
            });
        });
    });

    discussion.find('li .save').on('click', function (e) {
        e.preventDefault();
        var el = $(this);
        var id = el.data('comment-id');
        var url = $('#comment' + id + ' .edit').attr('href');
        var textarea = $('#comment' + id + ' textarea');

        textarea.attr('disabled', 'disabled');
        GlobalSaving.toggle();

        Ajax.post(url, {body: textarea.val()}, function (data) {
            textarea.removeAttr('disabled');
            $('#comment' + id + ' .comment-edit').hide();
            $('#comment' + id + ' .issue').html(data.comment).show();
            GlobalSaving.toggle();
        });
    });

    discussion.find('li .cancel').on('click', function (e) {
        e.preventDefault();
        var id = $(this).data('comment-id');
        $('#comment' + id + ' .comment-edit').hide();
        $('#comment' + id + ' .issue').show();
    });

    // Left column assign users
    $('.delete-from-project').on('click', function (e) {
        e.preventDefault();
        var user_id = $(this).data('user-id');
        ConfirmDialog.show($(this), function (el) {
            GlobalSaving.toggle();
            Ajax.post(el.attr('href'), {user_id: user_id}, function (data) {
                $('#project-user' + user_id).remove();
                GlobalSaving.toggle();
            });
        });
    });

    $('#sidebar #add-user-project').on('mouseover', function (e) {
        e.preventDefault();
        var project = $(this).data('project-id');
        Autocomplete.init({
            url: '/project/inactive_users/' + project,
            usersSelector: '.sidebar-users',
            template: function (ui) {
                return '<li id="project-user' + ui.item.id + '">' +
                    '<a href="" data-message="Are you sure you want to remove this user from the project?" data-user-id="' + ui.item.id + '" data-project-id="' + project + '" class="delete">Remove</a>' +
                    '' + ui.item.label + '' +
                    '</li>';
            },
            onSelect: function (el, item) {
                GlobalSaving.toggle();
                Ajax.relPost('/project/' + project + '/assign_user', {user_id: item.id}, $.proxy(function () {
                    GlobalSaving.toggle();
                    this.select(el, item);
                }, this));
                return false;
            },
            onRemove: function (el, id) {
                var ui = this;
                ConfirmDialog.show(el.find('a'), function () {
                    GlobalSaving.toggle();
                    Ajax.relPost('/project/' + project + '/unassign_user', {user_id: id}, function (data) {
                        ui.remove(el, id);
                        GlobalSaving.toggle();
                    });
                });
                return false;
            }
        });
    });

    // Issue assign user
    $('.assign-user').on('click', function (e) {
        e.preventDefault();
        var issue = $(this).data('issue-id');
        var user = $(this).data('assign-id');
        GlobalSaving.toggle();
        Ajax.relPost('/project/issue/' + issue + '/assign', {user_id: user}, function () {
            var assigned_to = $('.assigned-to');
            var assign_to = assigned_to.find('.user' + user);

            assigned_to.find('.assigned').removeClass('assigned');
            assign_to.addClass('assigned');
            assigned_to.find('.currently_assigned').html(assign_to.html());

            GlobalSaving.toggle();
        });
    });

    // Change issue project
    $('.change-project').on('click', function (e) {
        e.preventDefault();
        var issue = $(this).data('issue-id');
        var project = $(this).data('project-id');
        GlobalSaving.toggle();
        Ajax.relPost('/project/issue/' + issue + '/change_project', {project_id: project}, function (data) {
            GlobalSaving.toggle();
            window.location = data.url;
        });
    });

    // Clickable elements
    $('.vlink').on('click', function (e) {
        e.preventDefault();
        return window.location = $(this).data('url');
    });

    // Uploadify
    var upload = $('#upload');
    if (upload.length > 0) {
        upload.uploadify({
            buttonImage: '',
            swf: TINY.basePath + 'js/uploadify/uploadify.swf',
            uploader: TINY.basePath + 'project/' + TINY.projectId + '/issue/upload_attachment',
            formData: {
                session: $('input[name=session]').val(),
                _token: TINY.token,
                upload_token: $('input[name=upload_token]').val()
            },
            auto: true,
            multi: true,
            queueSizeLimit: 10,
            removeCompleted: false,
            itemTemplate: '<div id="${fileID}" class="queue-item">\
                        <a class="delete" data-file-name="${fileName}" data-file-id="${fileID}">X</a>\
                            <span class="fileName">${fileName} (${fileSize})</span><span class="data"></span>\
                        </div>',
            onUploadStart: function (file) {
                $('#' + file.id + ' a').attr('data-file-name', file.name);
            }
        });
        $(document).on('click', '.queue-item .delete', function (e) {
            e.preventDefault();
            GlobalSaving.show('Deleting...');
            var fileName = $(this).data('file-name');
            var fileId = $(this).data('file-id');
            Ajax.relPost('project/' + TINY.projectId + '/issue/remove_attachment', {
                session: $('input[name=session]').val(),
                _token: TINY.token,
                upload_token: $('input[name=upload_token]').val(),
                filename: fileName
            }, function () {
                GlobalSaving.hide();
                upload.uploadify('cancel', fileId);
            });
        });
    }
});