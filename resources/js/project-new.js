$(function () {
    'use strict';

    Autocomplete.init();

    Selection.init({
        className: 'default-assignee',
        items: Autocomplete.users,
        placeHolderSelector: '#default_assignee-id'
    });
});
