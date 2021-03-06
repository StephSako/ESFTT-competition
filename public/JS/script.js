$(document).ready(function() {
    $('.modal').modal();

    $("#dropdowner").dropdown();

    $('select').formSelect();

    $('.sidenav').sidenav({
        closeOnClick: true,
        draggable: true,
        preventScrolling: true
    });

    $('.tabs').tabs();

    $('.tooltipped').tooltip();

    $('li[id^="select-options"]').on('touchend', function (e) {
        e.stopPropagation();
    });

    $('.collapsible').collapsible({
        accordion : false
    });
});