import 'ekko-lightbox/dist/ekko-lightbox';
import $ from 'jquery';
window.jQuery = $;
window.$ = $;
$('body').on('click', '[data-toggle="lightbox"]', function (event) {
    event.preventDefault();
    $(this).ekkoLightbox();
});
