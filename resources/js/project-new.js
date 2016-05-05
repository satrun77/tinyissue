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
});
