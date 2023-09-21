import $ from 'jquery';
window.jQuery = $;
window.$ = $;
import 'select2/dist/js/select2.min';

$(document).ready(function () {
    $('#property_features').select2();
});
