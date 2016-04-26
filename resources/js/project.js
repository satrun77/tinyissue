$(function () {
    'use strict';

    // Radio button selection
    CheckableButtons().init();

    // Manage issue comments & activity tabs
    $('.activities .nav-tabs a').addClass('has-event').on('click', function(e) {
        e.preventDefault();
        var link = $(this).parent(), siblings = link.siblings();
        Ajax.get($(this).attr('href'), function (data) {
            GlobalSaving.toggle();
            $('.activities .discussion.comments').html(data.activity);
            siblings.removeClass('active');
            link.addClass('active');
        });
    }).first().click();

    Discussion().init({
        name: 'comment',
        selector: '.discussion.comments'
    });

    Discussion().init({
        name: 'note',
        selector: '.discussion.notes'
    });

    // Left column assign users
    $('.delete-from-project').addClass('has-event').on('click', function (e) {
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
    $('.assign-user').addClass('has-event').on('click', function (e) {
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
    $('.change-project').addClass('has-event').on('click', function (e) {
        e.preventDefault();
        var issue = $(this).data('issue-id');
        var project = $(this).data('project-id');
        GlobalSaving.toggle();
        Ajax.relPost('/project/issue/' + issue + '/change_project', {project_id: project}, function (data) {
            GlobalSaving.toggle();
            window.location = data.url;
        });
    });

    // File Uploader
    Uploader().init();
});

function CheckableButtons() {

    function _highlightActive(element, input, isToggle) {
        var color = input.data('color');
        if (input.is(':checked')) {
            element.addClass('active').css({
                'color': 'white',
                'border-color': color,
                'background': color
            });
        } else if (isToggle) {
            _resetInput(element);
        }
    }

    function _resetInput(input) {
        var color = input.find('input').data('color');
        input.removeClass('active').css({
            'color': color,
            'border-color': color,
            'background': 'white'
        });
    }

    function _resetSiblings(element) {
        element.siblings().each(function() {
            _resetInput($(this));
        });
    }

    function _initRadioButtons(element, input) {
        _resetSiblings(element);
        _highlightActive(element, input, false);
    }

    function _initCheckboxButtons(element, input) {
        _highlightActive(element, input, true);
    }

    return {
        init: function () {

            $('.radio-btn .btn, .checkbox-btn .btn').addClass('has-event').on('click', function () {
                var element = $(this);
                if (element.parent().hasClass('radio-btn')) {
                     _initRadioButtons(element, element.find('input'));
                } else {
                    _initCheckboxButtons(element, element.find('input'));
                }
            });

            return this;
        }
    }
}
