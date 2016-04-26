$(function () {
    'use strict';

    Autocomplete.init();

    // Radio button selection
    CheckableButtons().init();

    Selection.init({
        className: 'default-assignee',
        items: Autocomplete.users,
        placeHolderSelector: '#default_assignee-id'
    });

    //Autocomplete.init({
    //    url: '/administration/tags/suggestions',
    //    usersSelector: '.datalist_tag',
    //    inputSelector: '#add-tag-project',
    //    template: function (ui) {
    //        return '<li class="project-user' + ui.item.id + '">' +
    //            '<a href="" data-user-id="' + ui.item.id + '" class="delete">Remove</a>' +
    //            '' + ui.item.label + '' +
    //            '<input type="hidden" name="user[' + ui.item.id + ']" value="' + ui.item.id + '" />' +
    //            '</li>';
    //    },
    //    onSelect: function (el, item) {
    //        return true;
    //    },
    //    onRemove: function (el, item) {
    //        return true;
    //    }
    //});
});
